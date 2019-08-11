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

### Extras

The following methods were added:

* atom
* cookie
* iso8601
* iso (alias of iso8601)
* rfc822
* rfc850
* rfc1036
* rfc1123
* rfc2822
* rfc3339
* rfc3339Extended
* rss
* w3c


