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
    public function testReader(): void
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

        $log = $reader[2];

        $this->assertInstanceOf(DateTime::class, $log['date']);
        $this->assertEquals('report', $log['logger']);
        $this->assertEquals('ERROR', $log['level']);
        // multiline message
        $this->assertStringContainsString('unable to connect to tcp://127.0.0.1:5672', $log['message']);
        $this->assertStringContainsString('Amqp\Config->createConnection()', $log['message']);
        $this->assertStringContainsString('Symfony\Component\Console\Application->run()', $log['message']);
        $this->assertStringContainsString('#22 {main}', $log['message']);
    }

    /**
     *
     */
    public function testIterator(): void
    {
        // the test.log file contains 2 log lines
        $filePath = __DIR__ . '/../files/sample.log';
        $reader = new LogReader($filePath);

        $lines = [];
        $keys = [];

        $this->assertTrue($reader->offsetExists(0));
        $this->assertTrue($reader->offsetExists(1));
        $this->assertTrue($reader->offsetExists(2));

        $this->assertFalse($reader->offsetExists(100));

        $this->assertEquals(3, count($reader));

        foreach ($reader as $i => $log) {
            $lines[] = $log;
            $keys[] = $i;
        }

        $this->assertEquals([0, 1, 2], $keys);
        $this->assertEquals('test', $lines[0]['logger']);
        $this->assertEquals('system', $lines[1]['logger']);
        $this->assertEquals('report', $lines[2]['logger']);

    }

    /**
     */
    public function testReadonlyMode(): void
    {
        $filePath = __DIR__ . '/../files/sample.log';

        $this->expectException(RuntimeException::class);

        $reader = new LogReader($filePath);
        $reader[5] = 'foo';

        $this->expectException(RuntimeException::class);
        unset($reader[2]);
    }

    /**
     */
    public function testBrokenLog(): void
    {
        $filePath = __DIR__ . '/../files/broken.log';

        $reader = new LogReader($filePath);

        $this->assertEquals(0, count($reader));
    }
}