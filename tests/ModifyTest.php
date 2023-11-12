<?php

use PHPUnit\Framework\TestCase;
use React\EventLoop\Factory;
use React\Promise\Deferred;
use React\Promise;

use DateTime as PhpDateTime;
use Choval\DateTime;

class ModifyTest extends TestCase
{


    /**
     * Generates a random date
     */
    public function randomDate() : string {
        $year = mt_rand(2000, date('Y'));
        $month = mt_rand(1,12);
        $day = mt_rand(1,31);
        $hour = mt_rand(0,23);
        $min = mt_rand(0,59);
        $sec = mt_rand(0,59);
        $time = gmmktime( $hour, $min, $sec, $month, $day, $year);
        return date('c', $time);
    }



    /**
     * Random dates data provider
     */
    public function randomDates() : array {
        $out = [];
        for($i=0;$i<300;$i++) {
            $out[] = [ $this->randomDate() ];
        }
        return $out;
    }



    /**
     * Next month test
     */
    public function testNextMonth()
    {
        $date = '2019-10-31';
        $d = new DateTime($date);
        $this->assertEquals($date, $d->format('Y-m-d'));
        $d->nextMonth(31);
        $this->assertEquals('2019-11-30', $d->format('Y-m-d'));
        $d->nextMonth(31);
        $this->assertEquals('2019-12-31', $d->format('Y-m-d'));
    }


    /**
     * Prev month test
     */
    public function testPrevMonth()
    {
        $date = '2019-03-29';
        $d = new DateTime($date);
        $this->assertEquals($date, $d->format('Y-m-d'));
        $d->prevMonth(29);
        $this->assertEquals('2019-02-28', $d->format('Y-m-d'));
        $d->prevMonth(29);
        $this->assertEquals('2019-01-29', $d->format('Y-m-d'));
    }
}
