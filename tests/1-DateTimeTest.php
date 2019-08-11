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
    return date('Y-m-d', $time);
  }



  /**
   * Random dates data provider
   */
  public function randomDates() : array {
    $out = [];
    for($i=0;$i<3;$i++) {
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


}


