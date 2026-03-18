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

For the complete list of angular units, see [Units: Angle](../../Concepts/Units.md#angle).

---

## Constants

| Constant       | Type  | Value     | Description                                                 |
| -------------- | ----- | --------- | ----------------------------------------------------------- |
| `RAD_EPSILON`  | float | `1e-9`    | Default absolute tolerance for angle comparisons in radians |
| `TRIG_EPSILON` | float | `1e-15`   | Tolerance for detecting zero in trigonometric calculations  |

---

## Transformation methods

### toRadians()

```php
public function toRadians(): float
```

Get the angle value in radians.

```php
$angle = new Angle(180, 'deg');
$radians = $angle->toRadians(); // 3.14159...
```

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

These methods also improve on PHP's handling of singularities: PHP's tan(M_PI / 2) returns a large finite number (~1.6e16) rather than INF, because the floating-point representation of π/2 is not exact. The `Angle` trig methods detect when the denominator is within floating-point precision of zero and return ±INF with the correct sign, giving mathematically consistent results for tan(), sec(), csc(), and cot() at their singularities.

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

## Comparison methods

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

## DMS Parts

Angles support part decomposition into degrees, arcminutes, and arcseconds by default:

```php
$angle = new Angle(45.504200, 'deg');

$parts = $angle->toParts();
// ['sign' => 1, 'deg' => 45, 'arcmin' => 30, 'arcsec' => 15.12]

echo $angle->formatParts();
// 45° 30′ 15.12″

$restored = Angle::fromParts(['deg' => 45, 'arcmin' => 30, 'arcsec' => 15]);
echo $restored;  // 45.504167 deg
```

---

## Usage Examples

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

## See Also

- **[Units: Angle](../../Concepts/Units.md#angle)** — Complete list of angular units.
- **[Quantity](../Quantity.md)** — Base class documentation.
- **[Part Decomposition](PartDecomposition.md)** — General parts formatting and parsing.
- **[SolidAngle](SolidAngle.md)** — Related quantity for solid angles.
