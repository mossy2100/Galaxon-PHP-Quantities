# FloatWithError

Represents a floating-point value with tracked numerical error.

## Overview

The `FloatWithError` class wraps a float value together with an estimate of its accumulated numerical error. This allows tracking precision loss through chains of arithmetic operations, which is particularly important in unit conversion where multiple multiplications and divisions can compound rounding errors.

The error tracking uses a relative error model. Each operation (multiplication, division, inversion) accumulates error based on the precision limits of IEEE 754 double-precision floating-point arithmetic. The `relativeError` property provides a normalised measure of precision loss.

### Key Features

- Immutable value objects (all operations return new instances)
- Tracks relative error through arithmetic operations
- Supports multiplication, division, and inversion
- Allows comparison of conversion paths by precision
- Integer values start with zero error

## Properties

### value

```php
public readonly float $value
```

The floating-point numeric value.

### error

```php
public readonly float $error
```

The absolute error estimate. This represents the maximum expected deviation from the true value due to floating-point precision limits.

### relativeError

```php
public float $relativeError { get; }
```

The relative error as a proportion of the absolute value. Calculated as `error / |value|`. Returns 0.0 when the value is zero (to avoid division by zero).

This property is useful for comparing the precision of different conversion paths.

## Constructor

### __construct()

```php
public function __construct(float|int $value, float $error = 0.0)
```

Create a new FloatWithError instance.

**Parameters:**
- `$value` (float|int) - The numeric value
- `$error` (float) - The absolute error estimate (default: 0.0)

**Behavior:**
- If `$value` is an integer and `$error` is 0.0, the error remains 0.0 (integers are exact)
- If `$value` is a float and `$error` is 0.0, error is estimated from the float's precision
- If `$error` is provided, it is used directly

**Examples:**
```php
// Integer with no error
$exact = new FloatWithError(1000);
echo $exact->error; // 0.0

// Float with estimated error
$approx = new FloatWithError(3.14159);
echo $approx->error; // Small value based on float precision

// Explicit error
$measured = new FloatWithError(100.0, 0.5);
echo $measured->error; // 0.5
```

## Inspection Methods

### isInteger()

```php
public function isInteger(): bool
```

Check if the value represents a mathematical integer (no fractional part).

**Returns:**
- `bool` - True if the value has no fractional component

**Examples:**
```php
$int = new FloatWithError(42.0);
$int->isInteger(); // true

$float = new FloatWithError(42.5);
$float->isInteger(); // false
```

## Arithmetic Methods

### mul()

```php
public function mul(self|float|int $other): self
```

Multiply this value by another, accumulating errors.

**Parameters:**
- `$other` (self|float|int) - The value to multiply by

**Returns:**
- `self` - A new instance with the product and combined error

**Behavior:**
- Errors are combined using the formula for multiplication error propagation
- Multiplying by an exact integer preserves precision
- Multiplying by a FloatWithError combines both error estimates

**Examples:**
```php
$a = new FloatWithError(100.0);
$b = new FloatWithError(0.3048);  // feet to metres

$result = $a->mul($b);
echo $result->value; // 30.48
echo $result->relativeError; // Combined relative error
```

### div()

```php
public function div(self|float|int $other): self
```

Divide this value by another, accumulating errors.

**Parameters:**
- `$other` (self|float|int) - The value to divide by

**Returns:**
- `self` - A new instance with the quotient and combined error

**Throws:**
- `DivisionByZeroError` - If dividing by zero

**Examples:**
```php
$distance = new FloatWithError(1000.0);
$time = new FloatWithError(60.0);

$speed = $distance->div($time);
echo $speed->value; // 16.666...
```

### inv()

```php
public function inv(): self
```

Return the multiplicative inverse (1/value).

**Returns:**
- `self` - A new instance with the inverted value and propagated error

**Throws:**
- `DivisionByZeroError` - If the value is zero

**Examples:**
```php
$factor = new FloatWithError(2.54);  // inches to cm
$inverse = $factor->inv();           // cm to inches
echo $inverse->value; // 0.3937...
```

### pow()

```php
public function pow(int $exponent): self
```

Raise this value to an integer power.

**Parameters:**
- `$exponent` (int) - The exponent to raise to

**Returns:**
- `self` - A new instance with the result and propagated error

**Behavior:**
- Negative exponents are supported (equivalent to 1/value^|exponent|)
- Error is propagated according to power rules

**Examples:**
```php
$factor = new FloatWithError(1000.0);  // km to m
$squared = $factor->pow(2);             // km2 to m2
echo $squared->value; // 1000000.0
```

## Usage Examples

### Tracking Conversion Precision

```php
use Galaxon\Quantities\FloatWithError;

// Direct conversion factor (high precision)
$direct = new FloatWithError(0.3048);  // feet to metres

// Indirect via inches (lower precision)
$ftToIn = new FloatWithError(12);
$inToCm = new FloatWithError(2.54);
$cmToM = new FloatWithError(0.01);

$indirect = $ftToIn->mul($inToCm)->mul($cmToM);

// Compare precision
if ($direct->relativeError < $indirect->relativeError) {
    echo "Direct conversion is more precise";
}
```

### Building Conversion Factors

```php
use Galaxon\Quantities\FloatWithError;

// Conversion from yards to metres
$ydToFt = new FloatWithError(3);      // 3 feet per yard (exact)
$ftToM = new FloatWithError(0.3048);  // feet to metres

$ydToM = $ydToFt->mul($ftToM);
echo $ydToM->value; // 0.9144
echo $ydToM->relativeError; // Error from ftToM only (ydToFt was exact)
```

## See Also

- **[Conversion](Conversion.md)** - Uses FloatWithError for conversion factors
- **[Converter](Converter.md)** - Selects conversion paths based on error
