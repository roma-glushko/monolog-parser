<?php

declare(strict_types=1);

namespace MonologParser\Parser;

use DateTime;

/**
 * Class LogParser
 */
class LogParser implements LogParserInterface
{
    protected $pattern = '/^\[(?P<date>.*)\] (?P<logger>\w+).(?P<level>\w+): (?P<message>.*) (?P<context>[^ ]+) (?P<extra>[^ ]+)$/s';

    /**
     * @param string $pattern
     */
    public function __construct(string $pattern = null)
    {
        $this->pattern = ($pattern) ?: $this->pattern;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $log): array
    {
        if( !is_string($log) || $log === '') {
            return [];
        }

        preg_match($this->pattern, $log, $data);

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
}