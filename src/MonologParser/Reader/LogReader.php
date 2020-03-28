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
     * @var integer
     */
    protected $lineCount;

    /**
     * @var LogParserInterface
     */
    protected $parser;

    /**
     * @param string $filePath
     * @param LogParserInterface|null $parser
     */
    public function __construct(string $filePath, LogParserInterface $parser = null)
    {
        $this->parser = $parser ?? new LogParser();
        $this->file = new SplFileObject($filePath, 'r');
        $i = 0;

        while (!$this->file->eof()) {
            $this->file->current();
            $this->file->next();
            $i++;
        }

        $this->lineCount = $i;
    }

    /**
     * @param LogParserInterface $parser
     * @return void
     */
    public function setParser(LogParserInterface $parser): void
    {
        $this->parser = $parser;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $offset < $this->lineCount;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        $key = $this->file->key();
        $this->file->seek($offset);
        $log = $this->current();
        $this->file->seek($key);
        $this->file->current();

        return $log;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        throw new RuntimeException("LogReader is read-only.");
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        throw new RuntimeException("LogReader is read-only.");
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->file->rewind();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->file->next();
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->parser->parse($this->file->current());
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->file->key();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->file->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->lineCount;
    }
}