<?php

use PHPUnit\Framework\TestCase;
use React\EventLoop\Factory;
use React\Promise\Deferred;
use React\Promise;

use DateTime as PhpDateTime;
use Choval\DateTime;

class ParsersTest extends TestCase {

  

  /**
   * Parses timezone parsing
   */
  public function testTimezoneParse() {
    $rows = [
      'America/Argentina/Buenos_Aires',
      '-0300',
      '-03:00',
      -3,
      '-3',
    ];
    $offset = -10800;
    $date = new PhpDateTime;
    foreach($rows as $row ) {
      $tz = DateTime::timezoneParse( $row );
      $this->assertInstanceOf( DateTimeZone::class, $tz );
      $this->assertEquals( $offset, $tz->getOffset( $date ) );
    }
  }



  /**
   * Performs datetime parsing
   */
  public function testDatetimeParse() {
    $timestamp = 946684800;
    $rows = [
      $timestamp,
      '2000-01-01T00:00:00+00:00',
      '2000-01-01 00:00:00',
      '01/01/2000',
    ];

    $php_obj = new PhpDateTime;
    $php_obj->setTimestamp( $timestamp );
    $formatted = $php_obj->format('c');

    foreach($rows as $row) {
      $obj = DateTime::datetimeParse( $row );
      $this->assertInstanceOf( PhpDateTime::class, $obj );
      $this->assertEquals( $formatted, $obj->format('c') );
    }
  }



  /**
   * Performs interval parsing
   */
  public function testIntervalParse() {
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



}

