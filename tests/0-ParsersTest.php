<?php

use Choval\DateTime;
use DateTime as PhpDateTime;
use PHPUnit\Framework\TestCase;
use React\EventLoop\Factory;
use React\Promise\Deferred;
use React\Promise;

class ParsersTest extends TestCase
{
    /**
     * Parses timezone parsing
     */
    public function testTimezoneParse()
    {
        $rows = [
            'America/Argentina/Buenos_Aires',
            '-0300',
            '-03:00',
            -3,
            '-3',
        ];
        $offset = -10800;
        $date = new PhpDateTime;
        date_default_timezone_set($rows[0]);
        foreach($rows as $row ) {
            $tz = DateTime::timezoneParse( $row );
            $this->assertInstanceOf( DateTimeZone::class, $tz );
            $this->assertEquals( $offset, $tz->getOffset( $date ) );
        }
    }



    /**
     * Performs datetime parsing
     */
    public function testDatetimeParse() 
    {
        $timestamp = 946684800;
        $rows = [
            $timestamp,
            '2000-01-01T00:00:00+00:00',
            '2000-01-01 00:00:00 UTC',
            '01/01/2000 UTC',
        ];

        $php_obj = new PhpDateTime;
        $php_obj->setTimestamp( $timestamp );
        $formatted = $php_obj->format('c');

        $tz = $php_obj->getTimezone();
        foreach($rows as $row) {
            $obj = DateTime::datetimeParse( $row , $tz);
            $obj->setTimezone($tz);
            $this->assertInstanceOf( PhpDateTime::class, $obj );
            $this->assertEquals( $formatted, $obj->format('c') );
        }
    }



    /**
     * Performs interval parsing
     */
    public function testIntervalParse()
    {
        $rows = [
            'P10DT1H',
            '+10 days, 1 hour',
        ];

        foreach($rows as $row) {
            $obj = DateTime::intervalParse( $row );
            $this->assertInstanceOf( DateInterval::class, $obj );
            $this->assertEquals( '00-00-10 01:00:00', $obj->format('%Y-%M-%D %H:%I:%S') );
        }
    }


    /**
     * Test failed parse
     */
    public function testFailedParse()
    {
        $this->expectException( \RuntimeException::class );
        $obj = new DateTime('non valid');
        $out = $obj->format('Y-m-d');
    }
}
