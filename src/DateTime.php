<?php
namespace Choval;

use \DateTimeInterface;
use \DateTime as PhpDateTime;

class DateTime {



  const ATOM = "Y-m-d\TH:i:sP";
  const COOKIE = "l, d-M-Y H:i:s T";
  const ISO8601 = "Y-m-d\TH:i:sO";
  const RFC822 = "D, d M y H:i:s O";
  const RFC850 = "l, d-M-y H:i:s T";
  const RFC1036 = "D, d M y H:i:s O";
  const RFC1123 = "D, d M Y H:i:s O";
  const RFC2822 = "D, d M Y H:i:s O";
  const RFC3339 = "Y-m-d\TH:i:sP";
  const RFC3339_EXTENDED = "Y-m-d\TH:i:s.vP";
  const RSS = "D, d M Y H:i:s O";
  const W3C = "Y-m-d\TH:i:sP";



  protected $time;
  protected $offset;
  protected $format;
  protected $obj;
  protected $tzobj;



  /**
   * Constructor
   */
  public function __construct(string $time='now', $timezone=NULL, string $format=NULL) {
    if(is_null($timezone)) {
      $tz = date_default_timezone_get();
      $this->tzobj = timezone_open($tz);
    } else {
      $this->tzobj = static::timezoneParse($timezone);
    }
    $this->offset = $this->tzobj->getOffset( new PhpDateTime() );
    if(is_null($format)) {
      if(is_numeric($time)) {
        $this->format = 'U';
      }
      $this->obj = static::datetimeParse($time, $this->tzobj);
    } else {
      $this->format = $format;
      $this->obj = date_create_from_format( $format, $time, $this->tzobj );
    }
    $this->time = $this->obj->getTimestamp();
  }



  /**
   * Creats from a format
   */
  static function createFromFormat(string $format, string $time='now', $timezone=NULL) : self {
    return new static( $time, $timezone, $format);
  }



  /**
   * Returns a \DateInterval
   */
  public static function intervalParse($interval) : \DateInterval {
    if(is_string($interval)) {
      if(preg_match('/^P([0-9]+[YMDW])*(T([0-9]+[HMS])+)?$/', $interval)) {
        $interval = new \DateInterval($interval);
      } else {
        $interval = \DateInterval::createFromDateString($interval);
      }
    }
    if(is_object($interval) && is_a($interval, \DateInterval::class)) {
      return $interval;
    }
    return new \DateInterval('P0D');
  }



  /**
   * Returns a \DateTimeZone
   */
  public static function timezoneParse($timezone) : \DateTimeZone {
    if(is_string($timezone)) {
      if(preg_match('/^(?P<symbol>[\+\-])?(?P<hour>[01]?[0-9])?[:]?(?P<min>[0-9]0)?$/',$timezone, $match)) {
        $symbol = $match['symbol'] ?? '+';
        $hour = (int)$match['hour'];
        $min = (int) ($match['min'] ?? 0);
        $formatted = sprintf('%s%02d%02d', $symbol, $hour, $min);
        $timezone = timezone_open($formatted);
      } else {
        $timezone = timezone_open($timezone);
      }
    }
    if(is_object($timezone) && is_a($timezone, \DateTimeZone::class)) {
      return $timezone;
    }
    return new \DateTimeZone('+0000');
  }



  /**
   * Returns a \DateTime
   */
  public static function datetimeParse($time='now', $timezone=null) : PhpDateTime {
    if($timezone) {
      $timezone = static::timezoneParse($timezone);
    }
    if(is_numeric($time)) {
      $obj = new PhpDateTime();
      $obj->setTimestamp($time);
      if($timezone) {
        $obj->setTimezone($timezone);
      }
    } else {
      if($timezone) {
        $obj = date_create($time, $timezone);
      } else {
        $obj = date_create( $time );
      }
    }
    return $obj;
  }



  /**
   * Adds some time to the datetime, accepts:
   * - string
   * - DateInterval
   * - interval_spec for DateInterval
   */
  public function add($interval) : self {
    $interval = static::intervalParse($interval);
    $this->obj->add($interval);
    return $this;
  }



  /**
   * Substracts some time to the datetime, accepts:
   * - string
   * - DateInterval
   * - interval_spec for DateInterval
   */
  public function sub($interval) : self {
    $interval = static::intervalParse($interval);
    $this->obj->sub($interval);
    return $this;
  }



  /**
   * Outputs the date in a specific format
   */
  public function format(string $format='c') : string {
    return $this->obj->format($format);
  }



  /**
   * toString magic method
   */
  public function __toString() {
    return $this->format('c');
  }



  /**
   * Sleep
   */
  public function __sleep() {
    $out = [
      'time' => $this->time,
      'offset' => $this->offset,
      'format' => $this->format,
    ];
    return $out;
  }



  /**
   * Wakeup
   */
  public function __wakeup() {
    $this->tzobj = static::timezoneParse( $this->offset );
    $this->obj = static::datetimeParse( $this->time, $this->tzobj );
  }



  /**
   * Returns a \DateTimeZone
   */
  public function getTimezone() : \DateTimeZone {
    return $this->tzobj;
  }



  /**
   * Gets the timezone offset
   */
  public function getOffset() : int {
    return $this->offset;
  }



  /**
   * Gets the timestamp
   */
  public function getTimestamp() : int {
    return $this->time;
  }



  /**
   * Sets the date
   */
  public function setDate( int $year, int $month, int $day) : self {
    $this->obj->setDate($year, $month, $day);
    $this->time = (int)$this->obj->format('U');
    return $this;
  }



  /**
   * Sets the ISO date
   */
  public function setISODate( int $year, int $week, int $second=0) : self {
    $this->obj->setISODate($year, $week, $second);
    $this->time = (int)$this->obj->format('U');
    return $this;
  }



  /**
   * Sets the time
   */
  public function setTime( int $hour, int $minute, int $second=0) : self {
    $this->obj->setTime($hour, $minute, $second);
    $this->time = (int)$this->obj->format('U');
    return $this;
  }



  /**
   * Sets the timestamp
   */
  public function setTimestamp( int $time ) : self {
    $this->time = $time;
    $this->obj = new PhpDateTime();
    $this->obj->setTimestamp($time);
    $this->obj->setTimezone($this->tzobj);
    return $this;
  }



  /**
   * Sets the timezone
   */
  public function setTimezone( $timezone ) : self {
    $timezone = static::timezoneParse( $timezone );
    if($timezone) {
      $this->tzobj = $timezone;
      $this->obj->setTimezone($timezone);
    }
    return $this;
  }



  /**
   * Modify
   */
  public function modify(string $modify) : self {
    $this->obj->modify($modify);
    $this->time = (int)$this->obj->format('U');
    return $this;
  }



  /**
   * set state
   */
  public function __set_state(array $array) : self {
    $obj = new self;
    $obj->time = $array['time'];
    $obj->offset = $array['offset'];
    $obj->format = $array['format'];
    $obj->obj = $array['obj'];
    $obj->tzobj = $array['tzobj'];
    return $obj;
  }



  /**
   * Diff
   */
  public function diff( DateTimeInterface $datetime2, bool $absolute = FALSE ) : \DateInterval {
    return $this->obj->diff( $datetime2, $absolute );
  }



  /**
   * create from immutable
   */
  public static function createFromImmutable( \DateTimeImmutable $datetime ) : self {
    $dtobj = PhpDateTime::createFromImmutable( $datetime );
    $obj = new self($dtobj);
    return $obj;
  }



  /**
   * last errors
   */
  public static function getLastErrors() : array {
    return PhpDateTime::getLastErrors();
  }



  /**
   * Atom
   */
  public function atom() {
    return $this->format(static::ATOM);
  }



  /**
   * Cookie
   */
  public function cookie() {
    return $this->format(static::COOKIE);
  }



  /**
   * ISO8601
   */
  public function iso8601() {
    return $this->format(static::ISO8601);
  }
  public function iso() {
    return $this->iso8601();
  }



  /**
   * RFCs
   */
  public function rfc822() {
    return $this->format(static::RFC822);
  }
  public function rfc850() {
    return $this->format(static::RFC850);
  }
  public function rfc1036() {
    return $this->format(static::RFC1036);
  }
  public function rfc1123() {
    return $this->format(static::RFC1123);
  }
  public function rfc2822() {
    return $this->format(static::RFC2822);
  }
  public function rfc3339() {
    return $this->format(static::RFC3339);
  }
  public function rfc3339Extended() {
    return $this->format(static::RFC3339_EXTENDED);
  }



  /**
   * RSS
   */
  public function rss() {
    return $this->format(static::RSS);
  }



  /**
   * W3C
   */
  public function w3c() {
    return $this->format(static::W3C);
  }



}

