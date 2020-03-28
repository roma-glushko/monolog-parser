<?php

declare(strict_types=1);

namespace MonologParser\Parser;

/**
 * Interface LogParserInterface
 */
interface LogParserInterface
{
    /**
     * @param string $log
     *
     * @return array
     */
    function parse(string $log): array;
}