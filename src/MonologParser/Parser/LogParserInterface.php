<?php

declare(strict_types=1);

namespace MonologParser\Parser;

/**
 * Interface LogParserInterface
 */
interface LogParserInterface
{
    /**
     * Parse full record data
     *
     * @param string $log
     *
     * @return array
     */
    function parse(string $log): array;

    /**
     * Parse log record meta data
     *
     * @param string $log
     *
     * @return array
     */
    function parseMeta(string $log): array;
}