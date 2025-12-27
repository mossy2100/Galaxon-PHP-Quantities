# Conversion

Represents an affine transformation for unit conversion with error tracking. This class implements the mathematical relationship between two measurement units and can be combined with other conversions to create efficient conversion paths.

## Overview

The `Conversion` class implements the affine transformation formula:

**y = m·x + k**

Where:
- **m** is the multiplier (scale factor)
- **k** is the offset (additive constant)
- **x** is the input value in the source unit
- **y** is the output value in the destination unit

**Key features:**
- Error tracking through `FloatWithError` for multiplier and offset
- Error score calculation for finding optimal conversion paths
- Reversible conversions via `invert()`
- Composable conversions with four combination patterns
- Support for both linear (k=0) and affine (k≠0) transformations

## Constructor

```php
public function __construct(
    string $srcUnit,
    string $destUnit,
    float|FloatWithError $multiplier,
    float|FloatWithError $offset = 0
)
```

Creates a new Conversion instance.

**Parameters:**
- `$srcUnit` (string) - The source unit symbol
- `$destUnit` (string) - The destination unit symbol
- `$multiplier` (float|FloatWithError) - The scale factor (cannot be zero)
- `$offset` (float|FloatWithError) - The additive offset (default 0)

**Throws:** `ValueError` if the multiplier is zero

**Examples:**

```php
// Simple linear conversion: meters to centimeters
$mToCm = new Conversion('m', 'cm', 100);
// y = 100x + 0

// Affine conversion: Celsius to Fahrenheit
$cToF = new Conversion('C', 'F', 1.8, 32.0);
// y = 1.8x + 32

// Conversion with explicit error tracking
$conv = new Conversion(
    'kg',
    'lb',
    new FloatWithError(2.20462, 0.00001),
    new FloatWithError(0, 0)
);

// Using integers for exact conversions
$kmToM = new Conversion('km', 'm', 1000);
// No rounding error for exact integer multipliers
```

## Properties

### srcUnit

```php
public readonly string $srcUnit
```

The source unit symbol.

### destUnit

```php
public readonly string $destUnit
```

The destination unit symbol.

### multiplier

```php
public readonly FloatWithError $multiplier
```

The scale factor as a FloatWithError instance. Cannot be zero.

### offset

```php
public readonly FloatWithError $offset
```

The additive offset as a FloatWithError instance. Typically zero except for affine conversions like temperature scales.

### totalAbsoluteError

```php
public float $totalAbsoluteError { get }
```

A heuristic metric for comparing conversion quality and finding optimal paths.

**Formula:** `totalAbsoluteError = multiplier->absoluteError + offset->absoluteError`

**Purpose:** Lower values indicate more accurate conversions. Used by the graph-based pathfinding algorithm to minimize error accumulation.

**Note:** This is a simplified heuristic assuming a representative input value of 1. The actual error for a specific conversion depends on the input value.

**Example:**

```php
$conv1 = new Conversion('m', 'ft', 3.28084, 0);
$conv2 = new Conversion('m', 'ft', new FloatWithError(3.28084, 0.001), 0);

echo $conv1->totalAbsoluteError;  // ~1.8e-15 (ULP error only)
echo $conv2->totalAbsoluteError;  // ~0.001 (explicit error)

// conv1 is preferred for optimal conversion paths
```

## Methods

### apply()

```php
public function apply(float|FloatWithError $value): FloatWithError
```

Apply the conversion transformation to an input value.

**Parameters:**
- `$value` (float|FloatWithError) - The input value in the source unit

**Returns:** FloatWithError - The converted value in the destination unit with tracked error

**Formula:** `result = multiplier × value + offset`

**Examples:**

```php
// Simple linear conversion
$mToCm = new Conversion('m', 'cm', 100);
$result = $mToCm->apply(5.0);
echo $result->value;  // 500.0

// Affine conversion
$cToF = new Conversion('C', 'F', 1.8, 32.0);
$freezing = $cToF->apply(0.0);
echo $freezing->value;  // 32.0 (0°C = 32°F)

$boiling = $cToF->apply(100.0);
echo $boiling->value;  // 212.0 (100°C = 212°F)

// With error tracking
$conv = new Conversion('m', 'ft', new FloatWithError(3.28084, 0.00001));
$measurement = new FloatWithError(10.0, 0.01);  // 10m ± 1cm
$result = $conv->apply($measurement);
// Result includes propagated errors from both the conversion and measurement

// Using plain numbers (automatically wrapped)
$result2 = $mToCm->apply(7);  // int
$result3 = $mToCm->apply(3.5);  // float
```

### invert()

```php
public function invert(): self
```

Create the inverse conversion that goes from destination unit back to source unit.

**Returns:** Conversion - A new conversion from destUnit to srcUnit

**Mathematical derivation:**
```
Given:   b = a × m₁ + k₁
Solve:   a = (b - k₁) / m₁
         a = b × (1/m₁) + (-k₁/m₁)
Result:  m = 1/m₁, k = -k₁/m₁
```

**Examples:**

```php
// Invert linear conversion
$mToCm = new Conversion('m', 'cm', 100);
$cmToM = $mToCm->invert();
// cm = m × 100 + 0  →  m = cm × 0.01 + 0

echo $cmToM->srcUnit;  // 'cm'
echo $cmToM->destUnit;    // 'm'
echo $cmToM->multiplier->value;  // 0.01

// Invert affine conversion
$cToF = new Conversion('C', 'F', 1.8, 32.0);
$fToC = $cToF->invert();
// F = C × 1.8 + 32  →  C = F × 0.5556 + (-17.778)

echo $fToC->multiplier->value;  // ~0.5556
echo $fToC->offset->value;      // ~-17.778

// Round-trip verification
$celsius = 100.0;
$fahrenheit = $cToF->apply($celsius);
$backToCelsius = $fToC->apply($fahrenheit);
echo $backToCelsius->value;  // 100.0 (with small rounding error)

// Double inversion returns to original
$original = new Conversion('m', 'km', 0.001);
$doubleInverted = $original->invert()->invert();
// Same parameters as original (with rounding error)
```

## Combination Methods

These methods combine two conversions to create a direct conversion path. Used by the graph-based pathfinding algorithm to build efficient multi-step conversions.

### combineSequential()

```php
public function combineSequential(self $other): self
```

Combine two conversions that flow in sequence: **source → intermediate → destination**

**Pattern:** A → B (this), B → C (other) = A → C (result)

**Mathematical derivation:**
```
Given:   b = a × m₁ + k₁  (this conversion)
         c = b × m₂ + k₂  (other conversion)
Combine: c = (a × m₁ + k₁) × m₂ + k₂
         c = a × (m₁ × m₂) + (k₁ × m₂ + k₂)
Result:  m = m₁ × m₂, k = k₁ × m₂ + k₂
```

**Examples:**

```php
// meters → kilometers → miles
$mToKm = new Conversion('m', 'km', 0.001);
$kmToMi = new Conversion('km', 'mi', 0.621371);

$mToMi = $mToKm->combineSequential($kmToMi);
// Result: m → mi with multiplier ≈ 0.000621371

$result = $mToMi->apply(1000);  // 1000m
echo $result->value;  // ~0.621371 miles

// Temperature conversion chain
$cToK = new Conversion('C', 'K', 1, 273.15);
$kToF = new Conversion('K', 'F', 1.8, -459.67);

$cToF = $cToK->combineSequential($kToF);
// Equivalent to direct C → F conversion
```

### combineConvergent()

```php
public function combineConvergent(self $other): self
```

Combine two conversions that flow toward a intermediate unit: **source → intermediate ← destination**

**Pattern:** A → C (this), B → C (other) = A → B (result)

**Mathematical derivation:**
```
Given:   c = a × m₁ + k₁  (this: source → intermediate)
         c = b × m₂ + k₂  (other: destination → intermediate)
Equate:  a × m₁ + k₁ = b × m₂ + k₂
Solve:   b = (a × m₁ + k₁ - k₂) / m₂
         b = a × (m₁/m₂) + ((k₁ - k₂)/m₂)
Result:  m = m₁/m₂, k = (k₁ - k₂)/m₂
```

**Examples:**

```php
// Convert between Fahrenheit and Celsius via Kelvin
$cToK = new Conversion('C', 'K', 1, 273.15);
$fToK = new Conversion('F', 'K', 0.5556, 255.372);

$cToF = $cToK->combineConvergent($fToK);
// Both convert to K, so we can derive C → F

// Verify known conversion
$freezing = $cToF->apply(0.0);
echo $freezing->value;  // ~32.0°F

// Converting between feet and meters via inches
$ftToIn = new Conversion('ft', 'in', 12);
$mToIn = new Conversion('m', 'in', 39.3701);

$ftToM = $ftToIn->combineConvergent($mToIn);
```

### combineDivergent()

```php
public function combineDivergent(self $other): self
```

Combine two conversions that flow away from an intermediate unit: **source ← intermediate → destination**

**Pattern:** C → A (this), C → B (other) = A → B (result)

**Mathematical derivation:**
```
Given:   a = c × m₁ + k₁  (this: intermediate → source)
         b = c × m₂ + k₂  (other: intermediate → destination)
Solve:   c = (a - k₁) / m₁
         b = ((a - k₁) / m₁) × m₂ + k₂
         b = a × (m₂/m₁) + (k₂ - k₁ × m₂/m₁)
Result:  m = m₂/m₁, k = k₂ - k₁ × (m₂/m₁)
```

**Examples:**

```php
// Convert between centimeters and inches via meters
$mToCm = new Conversion('m', 'cm', 100);
$mToIn = new Conversion('m', 'in', 39.3701);

$cmToIn = $mToCm->combineDivergent($mToIn);
// Result: cm → in

$result = $cmToIn->apply(10.0);  // 10cm
echo $result->value;  // ~3.93701 inches

// Temperature scales from Kelvin
$kToC = new Conversion('K', 'C', 1, -273.15);
$kToF = new Conversion('K', 'F', 1.8, -459.67);

$cToF = $kToC->combineDivergent($kToF);
```

### combineOpposite()

```php
public function combineOpposite(self $other): self
```

Combine two conversions that flow in opposite directions: **source ← intermediate ← destination**

**Pattern:** C → A (this), B → C (other) = A → B (result)

**Mathematical derivation:**
```
Given:   a = c × m₁ + k₁  (this: intermediate → source)
         c = b × m₂ + k₂  (other: destination → intermediate)
Subst:   a = (b × m₂ + k₂) × m₁ + k₁
Invert:  b = ((a - k₁) / m₁ - k₂) / m₂
         b = a × (1/(m₁ × m₂)) + ((-k₂ - k₁/m₁) / m₂)
Result:  m = 1/(m₁ × m₂), k = (-k₂ - k₁/m₁) / m₂
```

**Examples:**

```php
// Convert meters to feet going through both directions
$kmToM = new Conversion('km', 'm', 1000);    // km → m
$ftToKm = new Conversion('ft', 'km', 0.0003048);  // ft → km

$mToFt = $kmToM->combineOpposite($ftToKm);
// Going: ft → km → m, but we want m → ft

// Temperature conversion with opposite flows
$kToC = new Conversion('K', 'C', 1, -273.15);  // K → C
$fToK = new Conversion('F', 'K', 0.5556, 255.372);  // F → K

$cToF = $kToC->combineOpposite($fToK);
// Going: F → K → C, derive C → F
```

## String Representation

### __toString()

```php
public function __toString(): string
```

Convert the conversion to a human-readable string.

**Format:** `"destUnit = srcUnit * multiplier + offset (error score: X)"`

Simplifications:
- Omits multiplier if it equals 1
- Omits offset if it equals 0
- Shows ± sign for offset

**Examples:**

```php
$mToCm = new Conversion('m', 'cm', 100);
echo $mToCm;
// "cm = m * 100 (error score: 0)"

$cToF = new Conversion('C', 'F', 1.8, 32.0);
echo $cToF;
// "F = C * 1.8 + 32 (error score: 3.552713678800501e-15)"

$kToC = new Conversion('K', 'C', 1, -273.15);
echo $kToC;
// "C = K - 273.15 (error score: 5.684341886080802e-14)"

$identity = new Conversion('m', 'm', 1, 0);
echo $identity;
// "m = m (error score: 0)"
```

## Usage Examples

### Basic Unit Conversion

```php
// Length conversion
$mToFt = new Conversion('m', 'ft', 3.28084);
$height = $mToFt->apply(1.8);  // 1.8 meters
echo $height->value;  // ~5.906 feet

// Temperature conversion
$cToF = new Conversion('C', 'F', 1.8, 32.0);
$bodyTemp = $cToF->apply(37.0);
echo $bodyTemp->value;  // 98.6°F
```

### Conversion with Measurement Uncertainty

```php
// Scientific measurement with error bars
$measurement = new FloatWithError(10.5, 0.05);  // 10.5m ± 5cm

$mToFt = new Conversion(
    'm',
    'ft',
    new FloatWithError(3.28084, 0.00001)
);

$result = $mToFt->apply($measurement);
echo $result->value;  // ~34.45 feet
echo $result->absoluteError;  // Propagated error
echo $result->relativeError;  // Relative error as a fraction
```

### Building Conversion Chains

```php
// Multi-step conversion: yards → feet → inches
$ydToFt = new Conversion('yd', 'ft', 3);
$ftToIn = new Conversion('ft', 'in', 12);

$ydToIn = $ydToFt->combineSequential($ftToIn);
// Result: yd → in with multiplier 36

$result = $ydToIn->apply(2.5);  // 2.5 yards
echo $result->value;  // 90 inches
```

### Round-Trip Conversions

```php
// Verify conversion reversibility
$cToF = new Conversion('C', 'F', 1.8, 32.0);
$fToC = $cToF->invert();

$celsius = 25.0;
$fahrenheit = $cToF->apply($celsius);
$backToCelsius = $fToC->apply($fahrenheit);

echo $backToCelsius->value;  // 25.0 (with minimal rounding error)
```

### Error Comparison

```php
// Compare conversion accuracy
$exact = new Conversion('m', 'cm', 100);
$approx = new Conversion('m', 'cm', new FloatWithError(100, 0.1));

echo $exact->totalAbsoluteError;    // ~0 (exact conversion)
echo $approx->totalAbsoluteError;   // 0.1 (approximate conversion)

// Use in pathfinding: prefer conversions with lower total absolute error
```

## Best Practices

1. **Use exact integers when possible**: Integer multipliers avoid floating-point rounding errors.

2. **Provide explicit error estimates**: When conversion factors are measured or approximate, wrap them in `FloatWithError` with known uncertainty.

3. **Minimize conversion chains**: Longer chains accumulate more error. Direct conversions are preferred when available.

4. **Check error levels**: Use `totalAbsoluteError` to compare alternative conversion paths and choose the most accurate.

5. **Validate round-trips**: For critical conversions, verify that `invert()` correctly reverses the transformation.

6. **Document offset units**: For affine conversions, clearly document what the offset represents (e.g., "32°F is the offset from 0°C").

## Mathematical Background

### Affine Transformations

An affine transformation is a linear transformation plus a translation:

**y = m·x + k**

Where:
- **m** is the linear scaling (stretch/shrink)
- **k** is the translation (shift)

**Properties:**
- Preserves parallel lines
- Preserves ratios of distances along parallel lines
- Composable: multiple affine transformations can be combined into a single affine transformation

### Error Propagation

Errors propagate through the affine transformation according to standard rules:

**For y = m·x + k:**
- Absolute error: δy = |m|·δx + δk
- Relative error: δy/|y| ≈ δm/|m| + δx/|x| (when k=0)

This is automatically handled by `FloatWithError` arithmetic.

### Composition Algebra

The four combination patterns cover all possible topologies when combining two conversions through an intermediate unit:

1. **Sequential (→→)**: Both conversions flow in the same direction
2. **Convergent (→←)**: Conversions flow toward the intermediate unit
3. **Divergent (←→)**: Conversions flow away from the intermediate unit
4. **Opposite (←←)**: Both conversions flow in the opposite direction

These patterns enable building any conversion path through a graph of unit relationships.

## See Also

- `FloatWithError` - Underlying error tracking mechanism
- `UnitConverter` - Uses Conversion in graph-based pathfinding
- Affine transformation theory in linear algebra
