<?php
namespace Choval;

use \DateTimeInterface;
use \Serializable;
use \DateTime as PhpDateTime;

class DateTime implements Serializable
{



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
    protected $holidays = [];



    /**
     * Constructor
     */
    public function __construct(string $time='now', $timezone=NULL, string $format=NULL)
    {
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
        $this->time = 0;
        if ($this->obj) {
            $this->time = $this->obj->getTimestamp();
        } else {
            $this->obj = PhpDateTime::createFromFormat('U', '0');;
        }
    }



    /**
     * Creats from a format
     */
    static function createFromFormat(string $format, string $time='now', $timezone=NULL) : self
    {
        return new static( $time, $timezone, $format);
    }



    /**
     * Returns a \DateInterval
     */
    public static function intervalParse($interval) : \DateInterval
    {
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
    public static function timezoneParse($timezone) : \DateTimeZone
    {
        if(is_int($timezone)) {
            if($timezone >= -12 && $timezone <= 14) {
                $timezone = (string)$timezone;
            } else {
                $symbol = ($timezone < 0) ? '-' : '+';
                $hours = abs( floor($timezone/3600) );
                $minutes = floor( $timezone/60 );
                $timezone = sprintf( '%s%02d%02d', $symbol, $hours, $minutes );
            }
        }
        if(is_string($timezone)) {
            if(preg_match('/^(?P<symbol>[\+\-])?(?P<hour>[01]?[0-9])?[:]?(?P<min>[0-9][05])?$/',$timezone, $match)) {
                $symbol = empty($match['symbol']) ? '+': $match['symbol'];
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
    public static function datetimeParse($time='now', $timezone=null) : PhpDateTime
    {
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
        if ($obj === false) {
            throw new \RuntimeException('Not a valid date');
        }
        return $obj;
    }



    /**
     * Adds some time to the datetime, accepts:
     * - string
     * - DateInterval
     * - interval_spec for DateInterval
     */
    public function add($interval) : self
    {
        $interval = static::intervalParse($interval);
        $this->obj->add($interval);
        $this->time = (int)$this->obj->format('U');
        return $this;
    }



    /**
     * Substracts some time to the datetime, accepts:
     * - string
     * - DateInterval
     * - interval_spec for DateInterval
     */
    public function sub($interval) : self
    {
        $interval = static::intervalParse($interval);
        $this->obj->sub($interval);
        $this->time = (int)$this->obj->format('U');
        return $this;
    }



    /**
     * Outputs the date in a specific format
     */
    public function format(string $format='c', $timezone=NULL) : string
    {
        $obj = clone $this->obj;
        if(!is_null($timezone)) {
            $timezone = static::timezoneParse( $timezone );
            if($timezone) {
                $obj->setTimezone($timezone);
            }
        }
        return $obj->format($format);
    }



    /**
     * toString magic method
     */
    public function __toString()
    {
        return $this->iso();
    }



    /**
     * Clone method
     */
    public function __clone() {
        $this->obj = clone $this->obj;
        $this->tzobj = clone $this->tzobj;
    }



    /**
     * Serialize
     */
    public function serialize() {
        $out = [
            'time' => $this->time,
            'offset' => $this->offset,
            'format' => $this->format,
            'holidays' => $this->holidays,
        ];
        return serialize($out);
    }



    /**
     * Unserialize
     */
    public function unserialize($data) {
        $in = unserialize($data);
        $this->time = $in['time'];
        $this->offset = $in['offset'];
        $this->format = $in['format'];
        $this->holidays = $in['holidays'];
        $this->tzobj = static::timezoneParse( $this->offset );
        $this->obj = static::datetimeParse( $this->time, $this->tzobj );
    }



    /**
     * Returns a \DateTimeZone
     */
    public function getTimezone() : \DateTimeZone
    {
        return $this->tzobj;
    }



    /**
     * Gets the timezone offset
     */
    public function getOffset() : int
    {
        return $this->offset;
    }



    /**
     * Gets the timestamp
     */
    public function getTimestamp() : int
    {
        return $this->time;
    }



    /**
     * Sets the date
     */
    public function setDate( int $year, int $month, int $day) : self
    {
        $this->obj->setDate($year, $month, $day);
        $this->time = (int)$this->obj->format('U');
        return $this;
    }



    /**
     * Sets the ISO date
     */
    public function setISODate( int $year, int $week, int $second=0) : self
    {
        $this->obj->setISODate($year, $week, $second);
        $this->time = (int)$this->obj->format('U');
        return $this;
    }



    /**
     * Sets the time
     */
    public function setTime( int $hour, int $minute, int $second=0) : self
    {
        $this->obj->setTime($hour, $minute, $second);
        $this->time = (int)$this->obj->format('U');
        return $this;
    }



    /**
     * Sets the timestamp
     */
    public function setTimestamp( int $time ) : self
    {
        $this->time = $time;
        $this->obj = new PhpDateTime();
        $this->obj->setTimestamp($time);
        $this->obj->setTimezone($this->tzobj);
        return $this;
    }



    /**
     * Sets the timezone
     */
    public function setTimezone( $timezone ) : self
    {
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
    public function modify(string $modify) : self
    {
        $this->obj->modify($modify);
        $this->time = (int)$this->obj->format('U');
        return $this;
    }



    /**
     * set state
     */
    public function __set_state(array $array) : self
    {
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
    public function diff( DateTimeInterface $datetime2, bool $absolute = FALSE ) : \DateInterval
    {
        return $this->obj->diff( $datetime2, $absolute );
    }



    /**
     * create from immutable
     */
    public static function createFromImmutable( \DateTimeImmutable $datetime ) : self
    {
        $dtobj = PhpDateTime::createFromImmutable( $datetime );
        $obj = new self($dtobj);
        return $obj;
    }



    /**
     * last errors
     */
    public static function getLastErrors() : array
    {
        return PhpDateTime::getLastErrors();
    }



    /**
     * Atom
     */
    public function atom()
    {
        return $this->format(static::ATOM);
    }



    /**
     * Cookie
     */
    public function cookie()
    {
        return $this->format(static::COOKIE);
    }



    /**
     * ISO8601
     */
    public function iso8601()
    {
        return $this->format(static::ISO8601);
    }



    /**
     * ISO format shorthand, but timezone with colon
     */
    public function iso()
    {
        return $this->format('Y-m-d\TH:i:sP');
    }



    /**
     * Format for MySQL datetime 
     */
    public function mysql($timezone=null) {
        return $this->format('Y-m-d H:i:s', $timezone);
    }



    /**
     * RFCs
     */
    public function rfc822()
    {
        return $this->format(static::RFC822);
    }
    public function rfc850()
    {
        return $this->format(static::RFC850);
    }
    public function rfc1036()
    {
        return $this->format(static::RFC1036);
    }
    public function rfc1123()
    {
        return $this->format(static::RFC1123);
    }
    public function rfc2822()
    {
        return $this->format(static::RFC2822);
    }
    public function rfc3339()
    {
        return $this->format(static::RFC3339);
    }
    public function rfc3339Extended()
    {
        return $this->format(static::RFC3339_EXTENDED);
    }



    /**
     * RSS
     */
    public function rss()
    {
        return $this->format(static::RSS);
    }



    /**
     * W3C
     */
    public function w3c()
    {
        return $this->format(static::W3C);
    }



    /**
     * Start of day
     */
    public function startOfDay() : self
    {
        return $this->setTime(0,0,0);
    }



    /**
     * End of day
     */
    public function endOfDay() : self
    {
        return $this->setTime(23,59,59);
    }



    /**
     * Midday
     */
    public function midDay() : self
    {
        return $this->setTime(12,0,0);
    }



    /**
     * First day of year
     */
    public function firstDayOfYear(int $year=NULL) : self
    {
        if(is_null($year)) {
            $year = (int)$this->format('Y');
        }
        return $this->setDate( $year, 1, 1);
    }



    /**
     * Last day of year
     */
    public function lastDayOfYear(int $year=NULL) : self
    {
        if(is_null($year)) {
            $year = (int)$this->format('Y');
        }
        return $this->setDate( $year, 12, 31);
    }



    /**
     * First day of month
     */
    public function firstDayOfMonth(int $month=NULL) : self
    {
        if(is_null($month)) {
            $month = (int)$this->format('n');
        }
        $year = (int)$this->format('Y');
        return $this->setDate( $year, $month, 1);
    }



    /**
     * Last day of month
     */
    public function lastDayOfMonth(int $month=NULL) : self
    {
        if(is_null($month)) {
            $month = (int)$this->format('n');
        }
        $year = (int)$this->format('Y');
        $this->setDate( $year, $month, 1)
            ->add('1 month')
            ->sub('1 day');
        return $this;
    }



    /**
     * First sunday of month
     */
    public function firstSundayOfMonth(int $month=NULL) : self
    {
        $this->firstDayOfMonth($month);
        $diff = 7 - (int)$this->format('N');
        if($diff) {
            $this->add($diff.' days');
        }
        return $this;
    }



    /**
     * Last sunday of month
     */
    public function lastSundayOfMonth(int $month=NULL) : self
    {
        $this->lastDayOfMonth($month);
        $diff = $this->format('N');
        if($diff != '7') {
            $this->sub($diff.' days');
        }
        return $this;
    }



    /**
     * First monday of month
     */
    public function firstMondayOfMonth(int $month=NULL) : self
    {
        $this->firstDayOfMonth($month);
        while( $this->format('N') != '1') {
            $this->add('1 day');
        }
        return $this;
    }



    /**
     * Last monday of month
     */
    public function lastMondayOfMonth(int $month=NULL) : self
    {
        $this->lastDayOfMonth($month);
        while( $this->format('N') != '1' ) {
            $this->sub('1 day');
        }
        return $this;
    }



    /**
     * First friday of month
     */
    public function firstFridayOfMonth(int $month=NULL) : self
    {
        $this->firstDayOfMonth($month);
        while( $this->format('N') != '5' ) {
            $this->add('1 day');
        }
        return $this;
    }



    /**
     * Last friday of month
     */
    public function lastFridayOfMonth(int $month=NULL) : self
    {
        $this->lastDayOfMonth($month);
        while( $this->format('N') != '5' ) {
            $this->sub('1 day');
        }
        return $this;
    }



    /**
     * Sets holidays
     */
    public function setHolidays(array $holidays) : self
    {
        $this->holidays = [];
        foreach($holidays as $holiday) {
            $this->addHoliday( $holiday );
        }
        return $this;
    }



    /**
     * Get holidays
     */
    public function getHolidays() : array
    {
        return $this->holidays;
    }



    /**
     * Adds holiday
     */
    public function addHoliday(string $date) : self
    {
        if(
            preg_match('/^[01][0-9]-[0-3][0-9]$/', $date)
            ||
            preg_match('/^[0-9]{4}-[01][0-9]-[0-3][0-9]$/', $date)
        ) {
            if(!in_array($date, $this->holidays)) {
                $this->holidays[] = $date;
            }
        } else {
            throw new \Exception('Non valid holiday date. Please use YYYY-MM-DD or MM-DD');
        }
        return $this;
    }



    /**
     * Is holiday
     */
    public function isHoliday() : bool
    {
        $formatted_full = $this->format('Y-m-d');
        $formatted_part = $this->format('m-d');
        return (in_array($formatted_full, $this->holidays) || in_array($formatted_part, $this->holidays) );
    }



    /**
     * Is work day
     */
    public function isWorkDay() : bool
    {
        $dow = $this->format('N');
        if($dow > 5) {
            return false;
        }
        return !$this->isHoliday();
    }



    /**
     * First workday of month
     */
    public function firstWorkDayOfMonth(int $month=NULL) : self
    {
        $this->firstDayOfMonth($month);
        while( !$this->isWorkDay() ) {
            $this->add('1 day');
        }
        return $this;
    }



    /**
     * Last workday of month
     */
    public function lastWorkDayOfMonth(int $month=NULL) : self
    {
        $this->lastDayOfMonth($month);
        while( !$this->isWorkDay() ) {
            $this->sub('1 day');
        }
        return $this;
    }



    /**
     * First workday of year
     */
    public function firstWorkDayOfYear(int $year=NULL) : self
    {
        $this->firstDayOfYear($year);
        while( !$this->isWorkDay() ) {
            $this->add('1 day');
        }
        return $this;
    }



    /**
     * Last workday of year
     */
    public function lastWorkDayOfYear(int $year=NULL) : self
    {
        $this->lastDayOfYear($year);
        while( !$this->isWorkDay() ) {
            $this->sub('1 day');
        }
        return $this;
    }


    /**
     * Next month
     */
    public function nextMonth(int $day=NULL) : self
    {
        $month = $this->format('n');
        $next = $month+1;
        if ($next > 12) {
            $next = 1;
        }
        $day = $day ?? $this->format('j');
        $this->add('1 month');
        while ($this->format('j') < $day) {
            $this->add('1 day');
        }
        while ($this->format('n') > $next) {
            $this->sub('1 day');
        }
        return $this;
    }


    /**
     * Prev month
     */
    public function prevMonth(int $day=NULL) : self
    {
        $month = $this->format('n');
        $prev = $month-1;
        if ($prev < 1) {
            $prev = 12;
        }
        $day = $day ?? $this->format('j');
        $this->sub('1 month');
        while ($this->format('j') > $day) {
            $this->sub('1 day');
        }
        while ($this->format('j') < $day) {
            $this->add('1 day');
        }
        while ($this->format('n') > $prev) {
            $this->sub('1 day');
        }
        return $this;
    }
}

