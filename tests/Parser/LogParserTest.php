<?php

declare(strict_types=1);

namespace MonologReader\Test\Parser;

use DateTime;
use Exception;
use MonologParser\Parser\LogParser;
use PHPUnit\Framework\TestCase;

/**
 * Class LogParserTest
 */
class LogParserTest extends TestCase
{
    /**
     * @dataProvider validRecordProvider
     *
     * @param string $record
     * @param string $logger
     * @param string $level
     * @param string $message
     * @param array $context
     * @param array $extra
     *
     * @return void
     *
     * @throws Exception
     */
    public function testLogParser(
        string $record,
        string $logger,
        string $level,
        string $message,
        array $context,
        array $extra
    ): void {
        $now = new DateTime();
        $record = sprintf($record, $now->format('Y-m-d H:i:s'));

        $parser = new LogParser();
        $result = $parser->parse($record);

        $this->assertEquals($logger, $result['logger']);
        $this->assertEquals($level, $result['level']);
        $this->assertEquals($message, $result['message']);
        $this->assertEquals($context, $result['context']);
        $this->assertEquals($extra, $result['extra']);
    }

    /**
     * @dataProvider emptyParserProvider
     *
     * @param string $record
     * @return void
     */
    public function testLogParserEmpty(string $record): void
    {
        $parser = new LogParser();

        $this->assertEquals([], $parser->parse($record));
        $this->assertEquals([], $parser->parseMeta($record));
    }

    /**
     * @dataProvider nodateParserProvider
     *
     * @param string $record
     * @return void
     */
    public function testLogParserNoDate(string $record): void
    {
        $parser = new LogParser();

        $this->assertFalse($parser->parse($record)['date']);
        $this->assertFalse($parser->parseMeta($record)['date']);
    }

    /**
     * @return array
     */
    public function nodateParserProvider(): array
    {
        return [
            ['[testdate] report.ERROR: test [] []'],
            ['[2020-02-80 14:19] test.DEBUG: hello [] []'],
        ];
    }

    /**
     * @return array
     */
    public function emptyParserProvider(): array
    {
        return [
            [''],
            ['not empty but completely wrong format [] []'],
        ];
    }

    /**
     * @return array
     */
    public function validRecordProvider(): array
    {
        return [
            [
                '[%s] report.WARNING: success [] []',
                'report',
                'WARNING',
                'success',
                [],
                [],
            ],
            [
                '[%s] test.INFO: hello {"config":"1"} []',
                'test',
                'INFO',
                'hello',
                ['config' => '1'],
                [],
            ],
            [
                '[%s] report.INFO: Desktop [{"config":"1"},{"path":"~/home/user/desktop"}] []',
                'report',
                'INFO',
                'Desktop',
                [['config' => '1'], ['path' => '~/home/user/desktop']],
                [],
            ],
            [
                '[%s] system.INFO: SpaceX is going to blow [{"spaceX":"100"},[]] []',
                'system',
                'INFO',
                'SpaceX is going to blow',
                [['spaceX' => '100'], []],
                [],
            ],
            [
                '[%s] station.INFO: SpaceY is out of Earth [{"spaceX":"12","SpaceY":"12 200"},{"pilot":"Eliot"}] []',
                'station',
                'INFO',
                'SpaceY is out of Earth',
                [['spaceX' => '12', 'SpaceY' => '12 200'], ['pilot' => 'Eliot']],
                [],
            ],
            [
                '[%s] loggy.INFO: Ellon is coming home [{"target":"earth","from":"mars and moon"},{"nextStop":"USA"}] []',
                'loggy',
                'INFO',
                'Ellon is coming home',
                [['target' => 'earth', 'from' => 'mars and moon'], ['nextStop' => 'USA']],
                [],
            ],
            [
                '[%s] station.INFO: Detected messages from aliens [{"x":"R121z","12zqd":"22,.2 ssdxZs4#$"},{"$$1ss~~sO.o":"---SWSzaaw1123e< >..>>"}] [{"hello":"humans"},{"nice to meet":"you"}]',
                'station',
                'INFO',
                'Detected messages from aliens',
                [['x' => 'R121z', '12zqd' => '22,.2 ssdxZs4#$'], ['$$1ss~~sO.o' => '---SWSzaaw1123e< >..>>']],
                [['hello' => 'humans'], ['nice to meet' => 'you']],
            ],
        ];
    }
}