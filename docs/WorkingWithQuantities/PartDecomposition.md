# Part Decomposition

---

## Overview

Some quantities are naturally expressed as a combination of units — angles in *degrees*, *arcminutes*, and *arcseconds*; time in *hours*, *minutes*, and *seconds*; lengths in *feet* and *inches*. The part decomposition methods let you convert between a single-unit value and its multi-unit parts.

---

## Converting to Parts with `toParts()`

The `toParts()` method breaks a quantity into integer components for each part unit, with only the smallest unit having a fractional value. By default it uses the [built-in part units](#built-in-part-units) for the quantity type.

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

Pass `$partUnitSymbols` to use a different set of part units for this call:

```php
$time = new Time(3661, 's');
$parts = $time->toParts(partUnitSymbols: ['h', 'min', 's']);
// ['sign' => 1, 'h' => 1, 'min' => 1, 's' => 1.0]
```

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

By default, the result is expressed in the [built-in result unit](#built-in-part-units) for the quantity type (e.g. `'s'` for Time, `'deg'` for Angle). Pass `$resultUnitSymbol` to choose a different unit:

```php
// Express the assembled result in minutes instead of the default 's'.
$time = Time::fromParts(['h' => 1, 'min' => 30], resultUnitSymbol: 'min');
echo $time;  // 90 min
```

---

## Parsing parts with `parseParts()`

The `parseParts()` method parses a multi-unit string into a quantity. Parts are separated by whitespace; there must be no space between a value and its unit symbol. Only the first part may be negative.

```php
// Time parsed from a multi-unit string.
$time = Time::parseParts('1h 30min 45s');
echo $time;  // 5445 s

// Angle parsed from a DMS-style string.
$angle = Angle::parseParts('45deg 30arcmin');
echo $angle;  // 45.5°

// Negative quantities — only the first part may carry the sign.
$time = Time::parseParts('-2h 15min');
echo $time;  // -8100 s
```

As with `fromParts()`, the result is expressed in the built-in result unit by default. Pass `$resultUnitSymbol` to choose a different unit:

```php
$time = Time::parseParts('1h 30min', resultUnitSymbol: 'min');
echo $time;  // 90 min
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

## Built-in Part Units

The four parts methods use these baked-in defaults when called without explicit `$partUnitSymbols` or `$resultUnitSymbol`:

| Quantity Type | Part Units                           | Result Unit |
|---------------|--------------------------------------|-------------|
| Length        | `mi`, `yd`, `ft`, `in`               | `ft`        |
| Time          | `y`, `mo`, `w`, `d`, `h`, `min`, `s` | `s`         |
| Angle         | `deg`, `arcmin`, `arcsec`            | `deg`       |
| Mass          | *(none — must pass explicitly)*      | `lb`        |

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

### Mass: Imperial and US Customary part lists

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
