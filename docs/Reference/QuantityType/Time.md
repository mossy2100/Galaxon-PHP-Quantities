# Time

Represents time quantities with integration to PHP's DateInterval.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Time` class handles time durations and provides conversion to/from PHP's native `DateInterval` class.

For the complete list of time units, see [Units: Time](../../Concepts/Units.md#time).

---

## DateInterval interoperability methods

### fromDateInterval()

```php
public static function fromDateInterval(DateInterval $interval): self
```

Create a Time from a PHP DateInterval object.

Uses average values for variable-length periods:
- 1 year = 365.2425 days (Gregorian average)
- 1 month = 30.436875 days (365.2425 / 12)

```php
$interval = new DateInterval('P1Y2M3DT4H5M6S');
$time = Time::fromDateInterval($interval);
echo $time->to('s')->value;  // Total seconds

// Negative intervals are supported
$interval->invert = 1;
$negativeTime = Time::fromDateInterval($interval);
```

### toDateIntervalSpecifier()

```php
public function toDateIntervalSpecifier(): string
```

Convert to a DateInterval specification string (ISO 8601 duration format). The specifier rounds to the nearest second, and zero-value components are omitted.

```php
$time = new Time(90061, 's');
echo $time->toDateIntervalSpecifier();  // P1DT1H1M1S

$time = new Time(3600, 's');
echo $time->toDateIntervalSpecifier();  // PT1H

$time = new Time(0, 's');
echo $time->toDateIntervalSpecifier();  // P0D
```

### toDateInterval()

```php
public function toDateInterval(): DateInterval
```

Convert to a PHP DateInterval object. More accurate than `toDateIntervalSpecifier()` because fractional seconds are preserved via the DateInterval's `$f` (microseconds) property, rather than rounding to the nearest second.

```php
$time = new Time(3661, 's');  // 1 hour, 1 minute, 1 second
$interval = $time->toDateInterval();

echo $interval->h;  // 1
echo $interval->i;  // 1
echo $interval->s;  // 1

// Negative times set the invert flag
$negativeTime = new Time(-3600, 's');
$interval = $negativeTime->toDateInterval();
echo $interval->invert;  // 1
```

---

## Parts

Time quantities support part decomposition by default using the full set of time units (years, months, weeks, days, hours, minutes, seconds):

```php
$time = new Time(90061, 's');

$parts = $time->toParts();
// ['sign' => 1, 'y' => 0, 'mo' => 0, 'w' => 0, 'd' => 1, 'h' => 1, 'min' => 1, 's' => 1.0]

echo $time->formatParts();
// 1d 1h 1min 1s
```

---

## Time Conversions

The following conversions are defined:

| From      | To     | Factor   |
|-----------|--------|----------|
| minute    | second | 60       |
| hour      | minute | 60       |
| day       | hour   | 24       |
| week      | day    | 7        |
| year      | month  | 12       |
| year      | day    | 365.2425 |
| century   | year   | 100      |

**Note:** Month and year conversions use average values. For calendar-accurate calculations, use PHP's DateTime/DateInterval classes directly.

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\Time;

// Create time durations
$seconds = new Time(3600, 's');
$minutes = new Time(90, 'min');
$hours = new Time(2.5, 'h');

// Convert between units
$inMinutes = $seconds->to('min');  // 60 min
$inHours = $minutes->to('h');      // 1.5 h

// Milliseconds and microseconds
$ms = new Time(1500, 'ms');
$us = new Time(1000000, 'us');

// Work with DateInterval
$interval = new DateInterval('PT2H30M');
$time = Time::fromDateInterval($interval);
$totalMinutes = $time->to('min')->value;  // 150

// Convert back to DateInterval
$time2 = new Time(5400, 's');
$interval2 = $time2->toDateInterval();
echo $interval2->format('%H:%I:%S');  // "01:30:00"

// Arithmetic
$total = $hours->add($minutes);  // 4 hours total
```

---

## See Also

- **[Units: Time](../../Concepts/Units.md#time)** — Complete list of time units.
- **[Quantity](../Quantity.md)** — Base class documentation.
- **[QuantityPartsService](../Services/QuantityPartsService.md)** — General parts formatting and parsing.
- **[PHP DateInterval](https://www.php.net/manual/en/class.dateinterval.php)** — Native PHP class.
