# Choval\DateTime

This is a drop-in replacement for [PHP's DateTime](https://www.php.net/manual/en/class.datetime.php).  
Some parsers have been added to ease the use, see Usage and Differences.

## Installation

```sh
composer require choval/datetime
```

## Simple drop-in

Before

```php
$a = new DateTime;
```

With it replaced

```php
use Choval\DateTime;
$a = new DateTime;
```

The following examples will use the full class name to avoid confussion.

## Usage

```php
// Working with timestamps
$t = 946684800;     // 2000-01-01 00:00:00+00:00

// An int is interpretated as a timestamp
$a = new Choval\DateTime( $t );
echo $a->format('c');       // 2000-01-01T00:00:00+00:00
echo $a->getTimestamp();    // 946684800

// Using PHP's DateTime for the same task
$b = \DateTime::createFromFormat( 'U', $t );
echo $b->format('c');       // 2000-01-01T00:00:00+00:00
echo $b->getTimestamp();    // 946684800

// Still a drop-in ;-)
$c = Choval\DateTime::createFromFormat( 'U', $t );
echo $c->format('c');       // 2000-01-01T00:00:00+00:00
echo $c->getTimestamp();    // 946684800
```

### Differences

If replacing PHP's DateTime, this class behaves almost exactly alike. With the exception of the constructor handling `int` as a timestamp even for low numbers.

Other than 

```php
// Flexible timezone parameter
// -3, '-03' , '-0300', '-03:00', 'America/Argentina/Buenos_Aires'
// or (new DateTimeZone('America/Argentina/Buenos_Aires'))
$d = new Choval\DateTime( '2000-01-01', '-0300');
echo $d->format('c');       // 2000-01-01T00:00:00-03:00
echo $d->getTimestamp();    // 946674000

// The constructor accepts a format as the third parameter
$e = new Choval\DateTime( '31/01/2000', '-3', 'd/m/Y' );
echo $e->format('c');       // 2000-01-31T00:00:00-03:00

// Similarly in PHP's DateTime
$f = \DateTime::createFromFormat( 'd/m/Y', '31/01/2000', (new DateTimeZone('America/Argentina/Buenos_Aires')) );

// Yet again, still a drop-in ;-)
$g = Choval\DateTime::createFromFormat( 'd/m/Y', '31/01/2000', (new DateTimeZone('America/Argentina/Buenos_Aires')) );

// Or ease it
$h = Choval\DateTime::createFromFormat( 'd/m/Y', '31/01/2000', '-03:00' );

// `add` and `sub` accept DateTime modifier formats, DateInterval objects or interval_spec (ie: P1Y for 1 year)
$e->add('+3 hours');
$e->add('PT3H');
$e->add(new DateInterval('PT3H'));
$e->modify('+3 hours');
echo $e->format('c');       // 2000-01-31T12:00:00-03:00
```

#### Constructor

The `__constructor` method allows a third parameter for passing the format of the first.

```php
__construct(
	string $time='now',
	$timezone=NULL,
	string $format=NULL
	)
```

This allows passing custom format date time strings.

```php
$a = new Choval\DateTime( '31/12/2019', '-3', 'd/m/Y' );
echo $a->format('c');	// 2019-12-31T00:00:00-03:00
```

#### Format

The `format` method allows a second paratemer for passing the timezone of the output, otherwise the timezone of the object is used.

The passed timezone will not change the one of the object itself. See the following example:

```php
$a = new Choval\DateTime('2019-01-01 00:00:00', '-03:00');

echo $a->format('c');
// 2019-01-01T00:00:00-03:00

echo $a->format('c', '+08:00');
// 2019-01-01T11:00:00+08:00

echo $a->format('c', 'UTC');
// 2019-01-01T03:00:00+00:00

echo $a->format('c');
// 2019-01-01T00:00:00-03:00
// Notice how the original timezone is kept
```


### Extras

The following methods were added to move around dates and printing/returning them as strings.

#### Time modifiers

* startOfDay(void) : self
* midDay(void) : self
* endOfDay(void) : self

```php
$a = new Choval\DateTime('2000-01-01 12:34:56', 'UTC');
echo $a->startOfDay()->format('H:i:s');	// 00:00:00
echo $a->midDay()->format('H:i:s');		// 12:00:00
echo $a->endOfDay()->format('H:i:s');	// 23:59:59

echo $a->format('c');			// 2000-01-01T23:59:59+00:00

$a->setTimezone('+01:00');
echo $a->format('H:i:s');					// 00:59:59
echo $a->format('c');			// 2000-01-02T00:59:59+01:00
echo $a->format('c', 'UTC');	// 2000-01-01T23:59:59+00:00

echo $a->endOfDay()->format('H:i:s');	// 23:59:59
echo $a->format('c');			// 2000-01-02T23:59:59+01:00
```

#### Date modifiers for the year

* firstDayOfYear([int $year]) : self
* lastDayOfYear([int $year]) : self

```php
$year = new Choval\DateTime;
$since = $year->firstDayOfYear()->startOfDay();
$till = $year->lastDayOfYear()->endOfDay();

// Useful for SQL
$sql = "SELECT * FROM users WHERE created >= ? AND created <= ?";
$q = $db->query($sql, [ $since,$till ]);
$rows = yield $q->fetchAll();
```

#### Date modifiers for the month

* firstDayOfMonth([int $month]) : self
* lastDayOfMonth([int $month]) : self
* firstSundayOfMonth([int $month]) : self
* lastSundayOfMonth([int $month]) : self
* firstMondayOfMonth([int $month]) : self
* lastMondayOfMonth([int $month]) : self
* firstFridayOfMonth([int $month]) : self
* lastFridayOfMonth([int $month]) : self

#### Workday

A non-weekend day and non-holiday (see Holidays).

* isWorkDay(void) : bool
* firstWorkDayOfMonth([int $month]) : self
* lastWorkDayOfMonth([int $month]) : self
* firstWorkDayOfYear([int $year]) : self
* lastWorkDayOfYear([int $year]) : self

#### Holidays

* setHolidays(array $holidays) : DateTime
* getHolidays(void) : array
* addHoliday(string $date)
* isHoliday(void) : bool

```php
$a = new Choval\DateTime('2019-06-30');
// Holidays need to be in YYYY-MM-DD or MM-DD format
// Set Holidays, overwrites current list.
$a->setHolidays([
	'01-01',	// New year
	'12-25',	// Christmas
]);

// A holiday that is in one specific year
$a->addHoliday('2019-01-02');

// Gets the holidays
$holidays = $a->getHolidays();

// First workday of the year
// Notice how its not new year,
// not 2019-01-02 and not on a weekend.
$b = clone $a;
$b->firstWorkDayOfYear();
echo $b->format('Y-m-d');		// Returns 2019-01-03 
// since the 2nd of january was added as aholiday for 2019.

$b->sub('1 year')->firstWorkDayOfYear();
echo $b->format('Y-m-d');		// Returns 2018-01-02
// 2019-01-02 was added as a holiday with a year,
// meaning that only on that year its a holiday.
// New year was added without a year, meaning every year.
```


#### Formats

* atom(void) : string
* iso(void) : string
* cookie(void) : string
* iso8601(void) : string
* rfc822(void) : string
* rfc850(void) : string
* rfc1036(void) : string
* rfc1123(void) : string
* rfc2822(void) : string
* rfc3339(void) : string
* rfc3339Extended(void) : string
* rss(void) : string
* w3c(void) : string

Note that the ISO8601 constant returns the timezone without colon format. If needed, use the `atom` format or the `iso` method, both return `Y-m-d\TH:i:sP`.

