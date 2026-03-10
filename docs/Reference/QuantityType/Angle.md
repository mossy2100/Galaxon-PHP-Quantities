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

For the complete list of angular units, see [Supported Units: Angle](../../DeveloperGuide/2.6_SupportedUnits.md#angle).

---

## Constants

| Constant       | Type  | Value     | Description                                                 |
| -------------- | ----- | --------- | ----------------------------------------------------------- |
| `RAD_EPSILON`  | float | `1e-9`    | Default absolute tolerance for angle comparisons in radians |
| `TRIG_EPSILON` | float | `1e-15`   | Tolerance for detecting zero in trigonometric calculations  |

---

## Methods

### Extraction Methods

#### `toRadians(): float`

Get the angle value in radians.

```php
$angle = new Angle(180, 'deg');
$radians = $angle->toRadians(); // 3.14159...
```

### Transformation Methods

#### `isRadians(): bool`

Check if the angle's current unit is radians.

```php
$angle = new Angle(M_PI, 'rad');
$angle->isRadians(); // true

$angle2 = new Angle(180, 'deg');
$angle2->isRadians(); // false
```

#### `wrap(bool $signed = true): Angle`

Normalize an angle to a standard range.

- **Signed (default):** Range is `(-half turn, half turn]`, e.g., `(-180°, 180°]` or `(-π, π]`
- **Unsigned:** Range is `[0, full turn)`, e.g., `[0°, 360°)` or `[0, 2π)`

```php
$angle = new Angle(270, 'deg');
$wrapped = $angle->wrap();        // -90°
$unsigned = $angle->wrap(false);  // 270°

$angle2 = new Angle(450, 'deg');
$wrapped2 = $angle2->wrap();      // 90°
```

### Trigonometric Methods

These methods are convenient if you prefer to work with angles in degrees (or any other unit) rather than radians, which PHP's built-in functions require. They also improve on PHP's handling of singularities: PHP's tan(M_PI / 2) returns a large finite number (~1.6e16) rather than INF,  because the floating-point representation of π/2 is not exact. The `Angle` trig methods detect when the denominator is within floating-point precision of zero and return ±INF with the correct sign, giving mathematically consistent results for tan(), sec(), csc(), and cot() at their singularities.

All trigonometric methods return float values.

#### `sin(): float`
#### `cos(): float`
#### `tan(): float`
#### `sec(): float`
#### `csc(): float`
#### `cot(): float`

```php
$angle = new Angle(45, 'deg');
$angle->sin();  // 0.7071...
$angle->cos();  // 0.7071...
$angle->tan();  // 1.0

$angle90 = new Angle(90, 'deg');
$angle90->tan();  // INF (handled gracefully)
$angle90->sec();  // INF
```

### String Parsing

#### `static parse(string $value): Angle`

Parse an angle from a string. Supports:

- Standard quantity format: `"45 deg"`, `"3.14159 rad"`, `"100 grad"`
- Degree-minute-second notation: `"45° 30' 15\""`, `"45°30′15″"`
- Signed values: `"-45° 30' 15\""`

The Unicode prime `′` and double-prime `″` are accepted for arcminutes and arcseconds respectively, as well as single-quote `'` and double-quote `"`.

```php
$a1 = Angle::parse('45 deg');
$a2 = Angle::parse('1.5708 rad');
$a3 = Angle::parse("45° 30' 15\"");  // 45.504166...°
$a4 = Angle::parse('-45°30′15″');    // Negative angle
```

### Comparison Methods

#### `approxEqual(mixed $other, float $relTol = 0, float $absTol = Angle::RAD_EPSILON): bool`

Compare angles with tolerances appropriate for angular measurements. Comparison is performed in radians.

```php
$a1 = new Angle(180, 'deg');
$a2 = new Angle(M_PI, 'rad');
$a1->approxEqual($a2);  // true
```

---

## Parts Methods

The `Angle` class supports decomposition into degrees, arcminutes, and arcseconds:

```php
$angle = new Angle(45.5042, 'deg');
$parts = $angle->toParts();
// ['deg' => 45, 'arcmin' => 30, 'arcsec' => 15.12, 'sign' => 1]

// Create from parts
$angle = Angle::fromParts([
    'deg' => 45,
    'arcmin' => 30,
    'arcsec' => 15,
    'sign' => 1
]);
```

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\Angle;

// Create angles in different units
$radians = new Angle(M_PI / 4, 'rad');
$degrees = new Angle(45, 'deg');
$gradians = new Angle(50, 'grad');

// Convert between units
$inDegrees = $radians->to('deg');  // 45°

// Trigonometry
$sin45 = $degrees->sin();  // 0.7071...

// Normalize angles
$large = new Angle(720, 'deg');
$normalized = $large->wrap();  // 0°

// Parse DMS notation
$dms = Angle::parse("23° 26' 21\"");  // Earth's axial tilt
```

---

## See Also

- **[Supported Units: Angle](../../DeveloperGuide/2.6_SupportedUnits.md#angle)** - Complete list of angular units
- **[Quantity](../Quantity.md)** - Base class documentation
- **[SolidAngle](SolidAngle.md)** - Related quantity for solid angles
