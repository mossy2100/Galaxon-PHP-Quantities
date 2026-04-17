# Part Decomposition

---

## Overview

Some quantities are naturally expressed as a combination of units — angles in *degrees*, *arcminutes*, and *arcseconds*; time in *hours*, *minutes*, and *seconds*; lengths in *feet* and *inches*. The part decomposition methods let you convert between a single-unit value and its multi-unit parts.

---

## Converting to parts with `toParts()`

The `toParts()` method breaks a quantity into integer components for each part unit, with only the smallest unit having a fractional value. By default it uses the [built-in part units](#built-in-part-units) for the quantity type.

```php
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Time;

// Angle uses ['deg', 'arcmin', 'arcsec'] by default.
$angle = new Angle(45.504200, 'deg');
$parts = $angle->toParts(precision: 2);
// ['sign' => 1, 'deg' => 45, 'arcmin' => 30, 'arcsec' => 15.12]

// Time uses ['y', 'mo', 'w', 'd', 'h', 'min', 's'] by default.
$time = new Time(3661, 's');
$parts = $time->toParts();
// ['sign' => 1, 'y' => 0, ..., 'h' => 1, 'min' => 1, 's' => 1.0]
```

The result always includes a `sign` key (`1` for positive/zero, `-1` for negative) and an entry for every part unit used (zero-valued entries are kept). The actual part values are always non-negative.

Use the `precision` parameter to round the smallest unit:

```php
$time = new Time(3661.789, 's');

$parts = $time->toParts(precision: 1);
// [..., 'h' => 1, 'min' => 1, 's' => 1.8]

$parts = $time->toParts(precision: 0);
// [..., 'h' => 1, 'min' => 1, 's' => 2.0]
```

If rounding the smallest unit causes it to overflow (e.g. 59.9 seconds rounds to 60), the larger parts are adjusted automatically.

Pass `$partUnitSymbols` to use a different set of part units for this call. Prefixed and compound symbols are accepted:

```php
// Custom subset of the default time units.
$time = new Time(3661, 's');
$parts = $time->toParts(partUnitSymbols: ['h', 'min', 's']);
// ['sign' => 1, 'h' => 1, 'min' => 1, 's' => 1.0]

// Prefixed units for length.
$length = new Length(5300, 'm');
$parts = $length->toParts(partUnitSymbols: ['km', 'm']);
// ['sign' => 1, 'km' => 5, 'm' => 300.0]
```

---

## Creating from parts with `fromParts()`

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

By default, the result is expressed in the base English unit for the quantity type's dimension (e.g. `'s'` for Time, `'°'` for Angle, `'ft'` for Length). Pass `$si = true` to use the SI base unit instead:

```php
// Result in SI base unit (meters) instead of English (feet).
$length = Length::fromParts(['ft' => 5, 'in' => 6], si: true);
echo $length;  // 1.6764 m

// Of course, you can always convert afterwards.
$length = Length::fromParts(['ft' => 5, 'in' => 6]);
echo $length->to('m');  // 1.6764 m
```

Prefixed and compound unit symbols are also accepted:

```php
$length = Length::fromParts(['km' => 5, 'm' => 300], true);
echo $length;  // 5300 m

$energy = Energy::fromParts(['kW*h' => 3.6]);
echo $energy->to('MJ');  // 12.96 MJ
```

---

## Parsing parts with `parseParts()`

The `parseParts()` method parses a multi-unit string into a quantity. Parts are separated by whitespace; there must be no space between a value and its unit symbol, and unit symbols containing spaces (e.g. `US gal`) are not supported. Only the first part may be negative.

```php
// Time parsed from a multi-unit string.
$time = Time::parseParts('1h 30min 45s');
echo $time;  // 5445 s

// Angle parsed from a DMS-style string.
$angle = Angle::parseParts("45° 30'");
echo $angle;  // 45.5°

// Negative quantities — only the first part may carry the sign.
$time = Time::parseParts('-2h 15min');
echo $time;  // -8100 s
```

As with `fromParts()`, the result is expressed in the base English unit by default. Pass `$si = true` to use the SI base unit:

```php
$length = Length::parseParts('5ft 6in', si: true);
echo $length;  // 1.6764 m
```

---

## Formatting parts with `formatParts()`

The `formatParts()` method produces a human-readable string, using the built-in part units for the quantity type:

```php
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Time;

// Angle (default parts: deg, arcmin, arcsec).
$angle = new Angle(45.5083333333, 'deg');
echo $angle->formatParts();                // 45° 30′ 30″
echo $angle->formatParts(precision: 1);    // 45° 30′ 30.0″

// Time (default parts: y, mo, w, d, h, min, s).
$time = new Time(90061, 's');
echo $time->formatParts();                 // 1d 1h 1min 1s

// Zero components are omitted by default.
$time = new Time(3600, 's');
echo $time->formatParts();                         // 1h
echo $time->formatParts(showZeros: true);          // 1h 0min 0s

// Negative values.
$time = new Time(-3661, 's');
echo $time->formatParts();                 // -1h 1min 1s
```

Pass `$partUnitSymbols` to use a different set of part units for this call:

```php
$height = new Length(68, 'in');
echo $height->formatParts(partUnitSymbols: ['ft', 'in']);  // 5ft 8in
```

---

## Built-in part units

The parts methods use these baked-in defaults when called without an explicit `$partUnitSymbols` array:

| Quantity Type | Part Units                           |
| ------------- | ------------------------------------ |
| Angle         | `deg`, `arcmin`, `arcsec`            |
| Time          | `y`, `mo`, `w`, `d`, `h`, `min`, `s` |
| Length        | `mi`, `yd`, `ft`, `in`               |

The result unit for `fromParts()` and `parseParts()` is determined by the `$si` parameter: the English base unit by default (e.g. `ft`, `s`, `°`, `lb`) or the SI base unit if `$si = true` (e.g. `m`, `s`, `rad`, `kg`).

Other quantity types have no built-in part units; calling a parts method on them without an explicit list throws `LogicException`.

To use a different set of units, just pass them inline. There is no global state to configure or reset — every call is independent:

```php
// Time as h/min/s instead of the full y/mo/w/d/h/min/s.
$time = new Time(5400, 's');
echo $time->formatParts(partUnitSymbols: ['h', 'min', 's']);  // 1h 30min

// Length as ft/in instead of mi/yd/ft/in.
$height = new Length(68, 'in');
echo $height->formatParts(partUnitSymbols: ['ft', 'in']);     // 5ft 8in
```

### Mass: imperial and US customary part lists

Mass has no built-in part units (the choice between imperial stones, US customary tons, and so on is application-specific), but the `Mass` class exposes both common lists as constants:

```php
use Galaxon\Quantities\QuantityType\Mass;

Mass::IMP_PART_UNITS;  // ['LT', 'st', 'lb', 'oz']
Mass::US_PART_UNITS;   // ['tn', 'lb', 'oz', 'gr']

$weight = new Mass(157, 'lb');
echo $weight->formatParts(partUnitSymbols: Mass::IMP_PART_UNITS);  // 11st 3lb

$produce = new Mass(52, 'oz');
echo $produce->formatParts(partUnitSymbols: Mass::US_PART_UNITS);  // 3lb 4oz
```

---

## See also

- **[Quantity](../Reference/Quantity.md#parts-methods)** — Full reference for `fromParts()`, `toParts()`, `parseParts()`, and `formatParts()`.
- **[String Functions](StringFunctions.md)** — Parsing and formatting quantities as strings.
- **[Angle](../Reference/QuantityType/Angle.md)** — DMS (degrees, arcminutes, arcseconds) parts support.
- **[Time](../Reference/QuantityType/Time.md)** — Time decomposition and `DateInterval` interoperability.
