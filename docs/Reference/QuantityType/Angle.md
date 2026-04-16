# Angle

Represents angle quantities with support for various angular units, trigonometric methods, and special parsing.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Angle` class provides specialized handling for angular measurements, including:

- Trigonometric functions (sin, cos, tan, sec, csc, cot)
- Angle normalization/wrapping
- Parsing of degree-minute-second notation (e.g., `45° 30' 15"`)
- Specialized approximate equality comparison

---

## Unit definitions

| Name      | ASCII symbol | Unicode symbol | Alternate symbol | Prefixes     | Systems     |
| --------- | ------------ | -------------- | ---------------- | ------------ | ----------- |
| radian    | `rad`        |                |                  | all metric   | SI          |
| degree    | `deg`        | `°`            |                  |              | SI Accepted |
| arcminute | `arcmin`     | `′`            | `'`              |              | SI Accepted |
| arcsecond | `arcsec`     | `″`            | `"`              | small metric | SI Accepted |
| gradian   | `grad`       |                |                  |              | Common      |
| turn      | `turn`       |                |                  |              | Common      |

---

## Conversion definitions

| From     | To       | Factor |
| -------- | -------- | ------ |
| `turn`   | `rad`    | τ (2π) |
| `turn`   | `deg`    | 360    |
| `deg`    | `arcmin` | 60     |
| `arcmin` | `arcsec` | 60     |
| `turn`   | `grad`   | 400    |

---

## Constants

| Constant       | Type  | Value   | Description                                                 |
| -------------- | ----- | ------- | ----------------------------------------------------------- |
| `RAD_EPSILON`  | float | `1e-9`  | Default absolute tolerance for angle comparisons in radians |
| `TRIG_EPSILON` | float | `1e-15` | Tolerance for detecting zero in trigonometric calculations  |

---

## SI unit and dimension

In the SI system, the *radian* is considered dimensionless because it can be defined as a ratio of arc length to radius, producing an SI unit of *m*/*m*, or nothing. The dimension code for *radian* is therefore normally `1`. For the purpose of this package, however, it was preferable to have a dedicated `Angle` class, which necessitated a dedicated dimension code, which was allocated the letter `A`.

Similarly, the *radian* is referred to as a *derived* unit in the SI system; in this package it is used as a *base* unit.

See [Dimensions and Base Units](../../Concepts/DimensionsAndBaseUnits.md) for more information.

---

## Square degrees, arcminutes, and arcseconds

If you wish to work with square degrees, arcminutes, or arcseconds, use `deg2`, `arcmin2`, or `arcsec2`, respectively, as the unit symbols. The package doesn't currently permit exponents with unit symbols containing non-letter characters.

---

## Overridden methods

### approxEqual()

```php
public function approxEqual(mixed $other, float $relTol = 0, float $absTol = self::RAD_EPSILON): bool
```

Compare angles with tolerances appropriate for angular measurements. Comparison is performed in radians.

```php
$a1 = new Angle(180, 'deg');
$a2 = new Angle(M_PI, 'rad');
$a1->approxEqual($a2);  // true
```

---

## Trigonometric methods

These methods are convenient if you prefer to work with angles in degrees (or any other unit) rather than radians, which PHP's built-in functions require. All return float values.

### sin()

```php
public function sin(): float
```

### cos()

```php
public function cos(): float
```

### tan()

```php
public function tan(): float
```

### sec()

```php
public function sec(): float
```

### csc()

```php
public function csc(): float
```

### cot()

```php
public function cot(): float
```

### Examples

```php
$angle = new Angle(45, 'deg');
$angle->sin();   // 0.7071067811865...
$angle->cos();   // 0.7071067811865...
$angle->tan();   // 1.0
$angle->sec();   // 1.4142135623731...
$angle->csc();   // 1.4142135623731...
$angle->cot();   // 1.0
```

These methods also improve on PHP's handling of singularities: PHP's `tan(M_PI/2)` returns a large finite number (~1.6e16) rather than INF, because the floating-point representation of π/2 is not exact. The `Angle` trig methods detect when the denominator is within floating-point precision of zero and return ±INF with the correct sign, giving mathematically consistent results for tan(), sec(), csc(), and cot() at their singularities.

```php
// Singularities return ±INF with the correct sign.
$right = new Angle(90, 'deg');
$right->tan();   // INF
$right->sec();   // INF

$zero = new Angle(0, 'deg');
$zero->csc();    // INF
$zero->cot();    // INF
```

---

## Transformation methods

### wrap()

```php
public function wrap(bool $signed = true): Quantity
```

Normalize an angle to a standard range.

- **Signed (default):** The result will be > -180° and <= 180° (or > -π and <= π for radians).
- **Unsigned:** The result will be >= 0° and < 360° (or >= 0 and < 2π for radians).

```php
$angle = new Angle(270, 'deg');
echo $angle->wrap();          // -90 deg
echo $angle->wrap(false);     // 270 deg

$angle2 = new Angle(720, 'deg');
echo $angle2->wrap();         // 0 deg
echo $angle2->wrap(false);    // 0 deg
```

---

## Conversion methods

### toRadians()

```php
public function toRadians(): float
```

Get the angle value in radians.

```php
$angle = new Angle(180, 'deg');
$radians = $angle->toRadians(); // 3.14159...
```

---

## Parts

Angles support part decomposition into *degrees*, *arcminutes*, and *arcseconds* by default. To use a different set of part units (e.g. just degrees and arcminutes), pass `partUnitSymbols` to the parts method.

```php
$angle = new Angle(45.504200, 'deg');

$parts = $angle->toParts(precision: 2);
// ['sign' => 1, 'deg' => 45, 'arcmin' => 30, 'arcsec' => 15.12]

echo $angle->formatParts(precision: 2);
// 45° 30′ 15.12″

$restored = Angle::fromParts(['deg' => 45, 'arcmin' => 30, 'arcsec' => 15]);
echo $restored;  // 45.50417°

// Decompose into degrees and arcminutes only.
echo $angle->formatParts(precision: 2, partUnitSymbols: ['deg', 'arcmin']);
// 45° 30.25′
```

---

## Usage examples

```php
use Galaxon\Quantities\QuantityType\Angle;

// Create angles in different units.
$radians = new Angle(M_PI / 4, 'rad');
$degrees = new Angle(45, 'deg');
$gradians = new Angle(50, 'grad');

// Convert between units.
$inDegrees = $radians->to('deg');  // 45°

// Parse DMS notation.
$dms = Angle::parse("23° 26' 21\"");  // Earth's axial tilt
```

---

## See also

- **[Quantity](../Quantity.md)** — Base class documentation.
- **[Part Decomposition](../../WorkingWithQuantities/PartDecomposition.md)** — General parts formatting and parsing.
- **[SolidAngle](SolidAngle.md)** — Related quantity for solid angles.
