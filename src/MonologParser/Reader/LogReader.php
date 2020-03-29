<?php

declare(strict_types=1);

namespace MonologParser\Reader;

use ArrayAccess;
use Countable;
use Iterator;
use MonologParser\Parser\LogParser;
use MonologParser\Parser\LogParserInterface;
use RuntimeException;
use SplFileObject;

/**
 * Class LogReader
 */
class LogReader implements Iterator, ArrayAccess, Countable
{
    /**
     * @var SplFileObject
     */
    protected $file;

    /**
     * @var int
     */
    private $currentOffset;

    /**
     * @var int
     */
    protected $recordCount;

    /**
     * @var LogParserInterface
     */
    protected $parser;

    /**
     * @var []
     */
    private $recordMap;

    /**
     * @param string $filePath
     * @param LogParserInterface|null $parser
     */
    public function __construct(string $filePath, LogParserInterface $parser = null)
    {
        $this->parser = $parser ?? new LogParser();
        $this->file = new SplFileObject($filePath, 'r');

        $this->recordMap = $this->getRecordMap();
        $this->recordCount = count($this->recordMap);
        $this->currentOffset = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $offset < $this->recordCount;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        $previousOffset = $this->currentOffset;
        $key = $this->file->key();

        $this->currentOffset = $offset;
        $log = $this->current();

        $this->currentOffset = $previousOffset;
        $this->file->seek($key);
        $this->file->current();

        return $log;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        throw new RuntimeException('LogReader is read-only.');
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        throw new RuntimeException('LogReader is read-only.');
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->currentOffset = 0;
        $this->file->rewind();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->currentOffset++;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $currentRecord = $this->getRecord($this->currentOffset);

        $recordStart = $currentRecord['start'];
        $recordEnd = $currentRecord['end'];
        $this->file->seek($recordStart);

        $buffer = '';

        for ($i = $recordStart; $i <= $recordEnd; $i++) {
            $buffer .= $this->file->current();
            $this->file->next();
        }

        return $this->parser->parse($buffer);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->currentOffset;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->currentOffset < $this->recordCount;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->recordCount;
    }

    /**
     * @param int $offset
     *
     * @return array
     */
    private function getRecord(int $offset): array
    {
        return $this->recordMap[$offset];
    }

    /**
     * Get positions of all needed log records in the file including multiline records
     *
     * @return array
     */
    private function getRecordMap(): array
    {
        $recordMap = [];
        $fileLinePointer = 0;
        $currentRecord = [];

        while (!$this->file->eof()) {
            $fileLine = $this->file->current();
            $logMeta = $this->parser->parseMeta($fileLine);

            // the line is part of log record
            if ([] === $logMeta) {
                $this->file->next();
                $fileLinePointer++;
                continue;
            }

            // it's time to flush information about previous record
            if ([] !== $currentRecord) {
                $currentRecord['end'] = $fileLinePointer - 1;
                $recordMap[] = $currentRecord;

                $currentRecord = [];
            }

            // the line is the beginning of the new log record
            if ([] === $currentRecord) {
                $currentRecord = [
                    'start' => $fileLinePointer,
                    'date' => $logMeta['date'],
                    'level' => $logMeta['level'],
                ];
            }

            $this->file->next();
            $fileLinePointer++;
        }

        // flush information about the last record
        if ([] !== $currentRecord) {
            $currentRecord['end'] = $fileLinePointer;
            $recordMap[] = $currentRecord;
        }

        return $recordMap;
    }
}