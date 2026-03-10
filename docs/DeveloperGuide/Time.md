# Time

The `Time` class extends `Quantity` with interoperability with PHP's `DateInterval` class.

---

## DateInterval Interoperability

### Creating from DateInterval

Use `fromDateInterval()` to create a `Time` quantity from a PHP `DateInterval`:

```php
use Galaxon\Quantities\QuantityType\Time;
use DateInterval;

$interval = new DateInterval('P1Y2M3DT4H5M6S');
$time = Time::fromDateInterval($interval);
echo $time->to('s');   // result in seconds
echo $time->to('d');   // result in days
```

Inverted (negative) intervals are handled automatically:

```php
$interval = new DateInterval('PT1H30M');
$interval->invert = 1;
$time = Time::fromDateInterval($interval);
echo $time->to('min');  // -90 min
```

### Converting to DateInterval

Use `toDateInterval()` to convert a `Time` quantity to a PHP `DateInterval`:

```php
$time = new Time(90061, 's');
$interval = $time->toDateInterval();
// $interval->d == 1, $interval->h == 1, $interval->i == 1, $interval->s == 1
```

Negative time values set the `invert` flag on the resulting `DateInterval`.

### DateInterval Specifier String

Use `toDateIntervalSpecifier()` to get the ISO 8601 duration string:

```php
$time = new Time(90061, 's');
echo $time->toDateIntervalSpecifier();  // P1DT1H1M1S

$time = new Time(3600, 's');
echo $time->toDateIntervalSpecifier();  // PT1H

$time = new Time(0, 's');
echo $time->toDateIntervalSpecifier();  // P0D
```

The specifier rounds to the nearest second (precision 0), and zero-value components are omitted.

---

## Time Parts

Time quantities support part decomposition by default using the full set of time units:

```php
$time = new Time(90061, 's');

$parts = $time->toParts();
// ['sign' => 1, 'y' => 0, 'mo' => 0, 'w' => 0, 'd' => 1, 'h' => 1, 'min' => 1, 's' => 1.0]

echo $time->formatParts();
// 1d 1h 1min 1s

// Use a subset of units
echo $time->formatParts(['h', 'min', 's']);
// 25h 1min 1s
```

---

## Conversion Notes

Time conversions use these standard relationships:

- 1 minute = 60 seconds
- 1 hour = 60 minutes
- 1 day = 24 hours
- 1 week = 7 days
- 1 year = 12 months = 365.2425 days (Gregorian average)
- 1 century = 100 years

The year-to-day conversion uses the Gregorian calendar average (365.2425), which accounts for leap years. This means month and year conversions are approximate.

---

## See Also

- [Time reference](../Reference/QuantityType/Time.md)
- [Part Decomposition](PartDecomposition.md) — General parts documentation.
