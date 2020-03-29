<?php

declare(strict_types=1);

namespace MonologParser\Parser;

use DateTime;

/**
 * Class LogParser
 */
class LogParser implements LogParserInterface
{
    protected $metaPattern = '/^\[(?P<date>.*)\] (?P<logger>\w+).(?P<level>\w+): /';
    protected $recordPattern = '/^\[(?P<date>.*)\] (?P<logger>\w+).(?P<level>\w+): (?P<message>.*) (?P<context>[\[\{].*[\}\]]) (?P<extra>[\[\{].*[\]\{])$/s';

    /**
     * @param string|null $recordPattern
     * @param string|null $metaPattern
     */
    public function __construct(?string $recordPattern = null, ?string $metaPattern = null)
    {
        $this->recordPattern = ($recordPattern) ?: $this->recordPattern;
        $this->metaPattern = ($metaPattern) ?: $this->metaPattern;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $log): array
    {
        if( !is_string($log) || $log === '') {
            return [];
        }

        preg_match($this->recordPattern, $log, $data);

        if (!isset($data['date'])) {
            return [];
        }

        return [
            'date' => DateTime::createFromFormat('Y-m-d H:i:s', $data['date']),
            'logger' => $data['logger'],
            'level' => $data['level'],
            'message' => $data['message'],
            'context' => json_decode($data['context'], true),
            'extra' => json_decode($data['extra'], true)
        ];
    }

    /**
     * @inheritDoc
     */
    function parseMeta(string $log): array
    {
        if (!is_string($log) || $log === '') {
            return [];
        }

        preg_match($this->metaPattern, $log, $data);

        if (!isset($data['date'])) {
            return [];
        }

        return [
            'date' => DateTime::createFromFormat('Y-m-d H:i:s', $data['date']),
            'level' => $data['level'],
        ];
    }
}