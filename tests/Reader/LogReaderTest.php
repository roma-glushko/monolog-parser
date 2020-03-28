<?php

declare(strict_types=1);

namespace MonologReader\Test\Reader;

use DateTime;
use MonologParser\Reader\LogReader;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Class LogReaderTest
 */
class LogReaderTest extends TestCase
{
    /**
     *
     */
    public function testReader()
    {
        $filePath = __DIR__ . '/../files/sample.log';
        $reader = new LogReader($filePath);

        $log = $reader[0];

        $this->assertInstanceOf(DateTime::class, $log['date']);
        $this->assertEquals('test', $log['logger']);
        $this->assertEquals('INFO', $log['level']);
        $this->assertEquals('foobar', $log['message']);
        $this->assertArrayHasKey('foo', $log['context']);

        $log = $reader[1];

        $this->assertInstanceOf(DateTime::class, $log['date']);
        $this->assertEquals('system', $log['logger']);
        $this->assertEquals('DEBUG', $log['level']);
        $this->assertEquals('foobar', $log['message']);
        $this->assertArrayNotHasKey('foo', $log['context']);
    }

    public function testIterator()
    {
        // the test.log file contains 2 log lines
        $filePath = __DIR__ . '/../files/sample.log';
        $reader = new LogReader($filePath);
        $lines = array();
        $keys = array();

        $this->assertTrue($reader->offsetExists(0));
        $this->assertTrue($reader->offsetExists(1));
        $this->assertTrue($reader->offsetExists(2));

        $this->assertFalse($reader->offsetExists(99));

        $this->assertEquals(3, count($reader));

        foreach ($reader as $i => $log) {
            $test = $reader[0];
            $lines[] = $log;
            $keys[] = $i;
        }

        $this->assertEquals([0, 1], $keys);
        $this->assertEquals('test', $lines[0]['logger']);
        $this->assertEquals('system', $lines[1]['logger']);

    }

    /**
     * @expectedException RuntimeException
     */
    public function testException()
    {
        $filePath = __DIR__ . '/../files/sample.log';

        $reader = new LogReader($filePath);

        $reader[5] = 'foo';
    }
}