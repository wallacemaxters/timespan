<?php

use WallaceMaxters\Timespan\Timespan;
use PHPUnit\Framework\TestCase;
use WallaceMaxters\Timespan\Exceptions\InvalidFormatException;
use WallaceMaxters\Timespan\Parser;

class TimespanTest extends TestCase
{
    public function testGetSeconds()
    {
        $this->assertEquals(90, (new Timespan(0, 1, 30))->seconds);
        $this->assertEquals(90, (new Timespan(0, 0, 90))->seconds);
        $this->assertEquals(3600, (new Timespan(1, 0, 0))->seconds);

        $this->assertEquals(PHP_INT_MAX, (new Timespan(0, 0, PHP_INT_MAX))->seconds);
    }

    public function testSetMinutes()
    {
        foreach ([
            new Timespan(0, 1.5, 0),
            (new Timespan)->setMinutes(1.5)
        ] as $time) {

    
            $this->assertEquals('00:01:30', $time->format());
    
            $this->assertEqualsWithDelta(60 + 30, $time->seconds, 0.1);
        }
    }

    public function testSetHours()
    {
        
        foreach ([
            new Timespan(1.5, 0, 0),
            (new Timespan)->setHours(1.5)
        ] as $time) {
            $this->assertEquals('01:30:00', $time->format());
        }
    }

    public function testAsMinutes()
    {
        $timespan = new Timespan(0, 0, 30);

        $this->assertEquals(
            0.5,
            $timespan->asMinutes()
        );
    }


    public function testAsHours()
    {
        $timespan = new Timespan(0, 30, 0);

        $this->assertEquals(
            0.5,
            $timespan->asHours()
        );
    }

    public function testFormat()
    {
        $times = [
            ['00:01:10', [0, 1, 10]],
            ['00:01:11', [0, 0, 71]],
            ['01:00:00', [0, 0, 3600]],
            ['02:00:00', [0, 60, 3600]],
            ['01:05:00', [1, 5, 0]],
        ];

        foreach ($times as [$expected, $args]) {
            $timespan = new Timespan(...$args);
            $this->assertEquals($expected, $timespan->format());
        }

        $times_with_custom_formats = [
            ['01 minutes and 10 seconds', [0, 1, 10], '%i minutes and %s seconds'],
            ['+00:01:11', [0, 0, 71], '%R%h:%i:%s'],
            ['-01:00:00', [0, 0, -3600], Timespan::DEFAULT_FORMAT],
        ];

        foreach ($times_with_custom_formats as [$expected, $args, $format]) {
            $timespan = new Timespan(...$args);
            $this->assertEquals($expected, $timespan->format($format));
        }
    }

    public function testAdd()
    {
        $timespan = new Timespan();

        $this->assertEquals(0, $timespan->seconds);

        $this->assertEquals(59, $timespan->add(0, 0, 59)->seconds);

        $this->assertEquals(60 + 59, $timespan->add(0, 1)->seconds);

        $this->assertEquals(3600 + 60 + 59, $timespan->add(1)->seconds);

        $timespan = new Timespan();

        $this->assertEquals(-3600, $timespan->add(-1)->seconds);
    }

    public function testSetTime()
    {
        $timespan = new Timespan();

        $timespan->setTime(1, 1, 1);

        $this->assertEquals(3600 + 60 + 1, $timespan->seconds);
    }

    public function testIsEmpty()
    {
        foreach ([-1, 0, 1] as $minute) {
            $timespan = new Timespan(0, $minute, 0);

            $this->assertEquals($minute === 0, $timespan->isEmpty());
        }

        $this->assertTrue((new Timespan())->isEmpty());
    }

    public function testDiff()
    {
        $t1 = new Timespan(0, 2, 10);
        $t2 = new Timespan(0, 3, 30);

        $diff = $t1->diff($t2);

        $this->assertInstanceOf(Timespan::class, $diff);
        $this->assertEquals('00:01:20', $diff->format());
        $this->assertEquals(60 + 20, $diff->seconds);


        $diff = $t2->diff($t1, false);

        $this->assertTrue($diff->isNegative());
        $this->assertEquals('-00:01:20', $diff->format());
        $this->assertEquals(-60 -20, $diff->seconds);
    }


    public function testAddFromString()
    {
        $timespan = new TimeSpan(0, 1, 0);

        $this->assertEquals(60, $timespan->seconds);
        $this->assertEquals(60 + 30, $timespan->addFromString('+30 seconds')->seconds);
        $this->assertEquals(60 + 30 + 60, $timespan->addFromString('+1 minutes')->seconds);
        $this->assertEquals(60 + 30 + 60 + 3600, $timespan->addFromString('+1 hours')->seconds);
        $this->assertEquals(60 + 30 + 60 + 3600 - 1800, $timespan->addFromString('-30 minutes')->seconds);
    }

    public function testCreateFromString()
    {
        foreach ([
            '+2 days' => '48:00:00',
            '-3 days' => '-72:00:00',
            '+1 day +1 minutes +30 seconds' => '24:01:30',
        ] as $string => $expected) {
            $this->assertEquals($expected, Timespan::createFromString($string)->format());
        }
    }

    public function testCreateFromFormat()
    {
        foreach ([
            120  => [Timespan::DEFAULT_FORMAT, '00:02:00'],
            90   => [Timespan::DEFAULT_FORMAT, '00:01:30'],
            15   => ['%s seconds', '15 seconds'],
            15   => ['%R%s seconds', '+15 seconds'],
            -15  => ['%R%s seconds', '-15 seconds'],
        ] as $expected => $args) {

            $this->assertEquals(
                $expected,
                Timespan::createFromFormat(...$args)->seconds
            );
        }


        try {
            Timespan::createFromFormat('invalid', '00:00:04');
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            $this->assertInstanceOf(InvalidFormatException::class, $th);
        }
    }

    public function testAddMinutes()
    {
        $timespan = new Timespan(0, 1, 30);
        $this->assertEquals(90, $timespan->seconds);

        $timespan->addMinutes(2);
        $this->assertEquals(90 + 120, $timespan->seconds);
    }

    public function testAddHours()
    {
        $timespan = new Timespan(0, 0, 30);
        $this->assertEquals(30, $timespan->seconds);

        $timespan->addHours(2);
        $this->assertEquals(30 + 7200, $timespan->seconds);
    }

    public function testGetUnits()
    {
        $timespan = new Timespan(1, 2, 30);

        $units = $timespan->getUnits();

        foreach (['hours', 'minutes', 'seconds', 'total_minutes'] as $key) {
            $this->assertArrayHasKey($key, $units);
        }
    }

    public function testisValidFormat()
    {
        $this->assertFalse(Parser::isValidFormat('invalid', '00:00:00'));

        $this->assertTrue(Parser::isValidFormat('%h:%i:%s', '00:00:00'));
    }

    public function testCreateFromDateDiff()
    {
        $timespan = Timespan::createFromDateDiff(
            new DateTime('2015-01-01 23:00:00'),
            new DateTime('2015-01-03 02:00:00')
        );

        $this->assertEquals('27:00:00', $timespan->format());

        $timespan = Timespan::createFromDateDiff(
            new DateTime('2021-01-03 02:00:00'),
            new DateTime('2021-01-01 23:00:00')
        );

        $this->assertEquals('-27:00:00', $timespan->format());

        $timespan = Timespan::createFromDateDiff(
            new DateTime('2021-01-01 12:00:00'),
            new DateTime('2022-01-01 13:00:02')
        );

        $this->assertEquals(31539602, $timespan->seconds);
        $this->assertEquals('8761:00:02', $timespan->format('%h:%i:%s'));
    }

    public function testJsonSerialize()
    {
        $time = new Timespan(0, 1, 2);

        $this->assertEquals('"00:01:02"', json_encode($time));
    }


    public function testToString()
    {
        $time = new Timespan(0, 1, 2);

        $this->assertEquals('00:01:02', (string) $time);
    }

    public function testSum()
    {
        $arr = [
            new Timespan(0, 0, 1),
            new Timespan(0, 0, 2),
            new Timespan(0, 0, 3)
        ];

        $timespan = (new Timespan(0, 0, 4))->sum(...$arr);

        $this->assertEqualsWithDelta(
            10,
            $timespan->seconds, 
            0.1
        );
    }
}
