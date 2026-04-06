# Part Decomposition

---

## Overview

Some quantities are naturally expressed as a combination of units — angles in degrees, arcminutes, and arcseconds; time in hours, minutes, and seconds; lengths in feet and inches. The part decomposition methods let you convert between a single-unit value and its multi-unit parts.

---

## Converting to Parts with `toParts()`

The `toParts()` method breaks a quantity into integer components for each unit configured for its quantity type, with only the smallest unit having a fractional value. The set of parts used is read from `QuantityPartsService` — see [Default Part Units](#default-part-units) below for how to configure them.

```php
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\QuantityType\Time;

// Angle uses ['deg', 'arcmin', 'arcsec'] by default.
$angle = new Angle(45.504200, 'deg');
$parts = $angle->toParts(precision: 2);
// ['sign' => 1, 'deg' => 45, 'arcmin' => 30, 'arcsec' => 15.12]

// Time uses ['y', 'mo', 'w', 'd', 'h', 'min', 's'] by default.
$time = new Time(3661, 's');
$parts = $time->toParts();
// ['sign' => 1, 'y' => 0, 'mo' => 0, 'w' => 0, 'd' => 0, 'h' => 1, 'min' => 1, 's' => 1.0]
```

The result always includes a `sign` key (`1` for positive/zero, `-1` for negative) and an entry for every configured part unit (zero-valued entries are kept). The actual part values are always non-negative.

Use the `precision` parameter to round the smallest unit:

```php
$time = new Time(3661.789, 's');

$parts = $time->toParts(precision: 1);
// [..., 'h' => 1, 'min' => 1, 's' => 1.8]

$parts = $time->toParts(precision: 0);
// [..., 'h' => 1, 'min' => 1, 's' => 2.0]
```

If rounding the smallest unit causes it to overflow (e.g. 59.9 seconds rounds to 60), the larger parts are adjusted automatically.

---

## Creating from Parts with `fromParts()`

The `fromParts()` method creates a quantity from an array of unit-value pairs:

```php
// Angle from DMS.
$angle = Angle::fromParts(['deg' => 45, 'arcmin' => 30, 'arcsec' => 15.12]);
echo $angle;  // 45.5042°

// Time from components.
$time = Time::fromParts(['h' => 2, 'min' => 30, 's' => 45]);
echo $time;  // 9045 s

// Negative value using sign.
$time = Time::fromParts(['h' => 1, 'min' => 30, 'sign' => -1]);
echo $time;  // -5400 s
```

The result unit is determined by the configured result unit symbol for the quantity type (e.g. `'s'` for Time, `'deg'` for Angle). To produce a quantity in a different unit, change the configured result unit via `QuantityPartsService::setResultUnitSymbol()` (see [Default Part Units](#default-part-units) below).

---

## Formatting Parts with `formatParts()`

The `formatParts()` method produces a human-readable string, using the configured part units for the quantity type:

```php
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\Services\QuantityPartsService;

// Angle (default parts: deg, arcmin, arcsec).
$angle = new Angle(45.5083333333, 'deg');
echo $angle->formatParts();                // 45° 30′ 30″
echo $angle->formatParts(precision: 1);    // 45° 30′ 30.0″

// Time (default parts: y, mo, w, d, h, min, s).
$time = new Time(90061, 's');
echo $time->formatParts();                 // 1d 1h 1min 1s

// Reconfigure Time to use only h, min, s.
QuantityPartsService::setPartUnitSymbols(Time::getQuantityType(), ['h', 'min', 's']);
echo $time->formatParts();                 // 25h 1min 1s

// Configure Length for feet and inches.
QuantityPartsService::setPartUnitSymbols(Length::getQuantityType(), ['ft', 'in']);
$height = new Length(68, 'in');
echo $height->formatParts();               // 5ft 8in
echo $height->formatParts(precision: 0);   // 5ft 8in

// Zero components are omitted by default.
$time = new Time(3600, 's');
echo $time->formatParts();                         // 1h
echo $time->formatParts(showZeros: true);          // 1h 0min 0s

// Negative values.
$time = new Time(-3661, 's');
echo $time->formatParts();                 // -1h 1min 1s
```

---

## Default Part Units

Some quantity types have default part unit symbols and result unit symbols. You can read and customise these at runtime via the `QuantityPartsService`:

```php
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\Services\QuantityPartsService;

// Check the defaults
QuantityPartsService::getPartUnitSymbols(Time::getQuantityType());
// ['y', 'mo', 'w', 'd', 'h', 'min', 's']
QuantityPartsService::getPartUnitSymbols(Angle::getQuantityType());
// ['deg', 'arcmin', 'arcsec']

// Customise for your application
QuantityPartsService::setPartUnitSymbols(Time::getQuantityType(), ['h', 'min', 's']);
QuantityPartsService::setResultUnitSymbol(Time::getQuantityType(), 'min');
```

The Mass class provides convenience methods for setting up imperial or US customary parts:

```php
use Galaxon\Quantities\QuantityType\Mass;

Mass::setImperialParts();
// Sets: ['LT', 'st', 'lb', 'oz'] with result unit 'lb'

$weight = new Mass(157, 'lb');
echo $weight->formatParts();  // 11st 3lb
```

---

## See Also

- **[Quantity](../Reference/Quantity.md#parts-methods)** — Full reference for `fromParts()`, `toParts()`, `parseParts()`, and `formatParts()`.
- **[String Functions](StringFunctions.md)** — Parsing and formatting quantities as strings.
- **[Angle](../Reference/QuantityType/Angle.md)** — DMS (degrees, arcminutes, arcseconds) parts support.
- **[Time](../Reference/QuantityType/Time.md)** — Time decomposition and `DateInterval` interoperability.
