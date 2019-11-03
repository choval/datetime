<?php

use PHPUnit\Framework\TestCase;
use React\EventLoop\Factory;
use React\Promise\Deferred;
use React\Promise;

use DateTime as PhpDateTime;
use Choval\DateTime;

class DateTimeTest extends TestCase {


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
     * @dataProvider randomDates
     */
    public function testDropInReplacement($date) {
        $a = new PhpDateTime($date);
        $b = new DateTime($date);
        $this->assertEquals($a->format('U'), $b->format('U') );

        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $a = new PhpDateTime($date);
        $b = new DateTime($date);
        $this->assertEquals($a->format('U'), $b->format('U') );

        $tz = new DateTimeZone('Asia/Tokyo');
        $a = new PhpDateTime($date, $tz);
        $b = new DateTime($date, $tz);
        $this->assertEquals($a->format('U'), $b->format('U') );
    }



    /**
     * @dataProvider randomDates
     */
    public function testConstructorFormat($date) {
        $format = 'd/m/Y H:i:s P';
        $a = new PhpDateTime($date);
        $formatted = $a->format($format);

        $b = new DateTime($formatted, 0, $format);
        $this->assertEquals($a->format('U'), $b->format('U') );
    }



    /**
     * @dataProvider randomDates
     */
    public function testFormatWithTimezone($date) {
        $a = new PhpDateTime($date);
        $a->setTimezone( new DateTimeZone('Asia/Tokyo') );
        $b = new DateTime($a->format('U'));
        $this->assertNotEquals( $a->getTimezone(), $b->getTimezone() );
        $this->assertEquals($a->format('c'), $b->format('c', 'Asia/Tokyo'));
    }



    /**
     * @dataProvider randomDates
     */
    public function testTimeModifiers($date) {
        $a = new DateTime($date);
        $date = $a->format('Y-m-d');
        $this->assertEquals( $date.' 00:00:00', $a->startOfDay()->format('Y-m-d H:i:s') );
        $this->assertEquals( $date.' 12:00:00', $a->midDay()->format('Y-m-d H:i:s') );
        $this->assertEquals( $date.' 23:59:59', $a->EndOfDay()->format('Y-m-d H:i:s') );
    }



    /**
     * @dataProvider randomDates
     */
    public function testDateModifiers($date) {
        $a = new DateTime($date);
        $year = $a->format('Y');
        $this->assertEquals( $year.'-01-01', $a->firstDayOfYear()->format('Y-m-d') );
        $this->assertEquals( $year.'-12-31', $a->lastDayOfYear()->format('Y-m-d') );

        $a = new DateTime($date);
        $ym = $a->format('Y-m');
        $this->assertEquals( $ym.'-01', $a->firstDayOfMonth()->format('Y-m-d') );
        $this->assertEquals( $ym, $a->firstSundayOfMonth()->format('Y-m') );
        $this->assertEquals( 7, $a->format('N') );
        $this->assertEquals( $ym, $a->lastSundayOfMonth()->format('Y-m') );
        $this->assertEquals( 7, $a->format('N') );
        $this->assertEquals( $ym, $a->firstMondayOfMonth()->format('Y-m') );
        $this->assertEquals( 1, $a->format('N') );
        $this->assertEquals( $ym, $a->lastMondayOfMonth()->format('Y-m') );
        $this->assertEquals( 1, $a->format('N') );
        $this->assertEquals( $ym, $a->firstFridayOfMonth()->format('Y-m') );
        $this->assertEquals( 5, $a->format('N') );
        $this->assertEquals( $ym, $a->lastFridayOfMonth()->format('Y-m') );
        $this->assertEquals( 5, $a->format('N') );

        $this->assertTrue( $a->isWorkDay() );

        $b = clone $a;
        $a->firstMondayOfMonth();
        $b->firstWorkDayOfMonth();
        $this->assertEquals( $a->format('Y-m-d'), $b->format('Y-m-d') );

        $a->lastFridayOfMonth();
        $b->lastWorkDayOfMonth();
        $this->assertEquals( $a->format('Y-m-d'), $b->format('Y-m-d') );

        $a->setHolidays(['01-01']);
        $a->addHoliday('12-31');

        $this->assertGreaterThanOrEqual( 1228, (int)$a->lastWorkDayOfYear()->format('md') );
        $this->assertLessThanOrEqual( 104, (int)$a->firstWorkDayOfYear()->format('md') );

        $a = new DateTime($date);
        $this->assertEquals('01', $a->lastDayOfMonth()->add('1 day')->format('d') );
    }



    public function testNextPrevMonth()
    {
        $date = '2019-10-31';
        $d = new DateTime($date);
        $this->assertEquals($date, $d->format('Y-m-d'));
        $d->nextMonth();
        $this->assertEquals('2019-11-30', $d->format('Y-m-d'));
        $d->nextMonth(31);
        $this->assertEquals('2019-12-31', $d->format('Y-m-d'));
    }
}
