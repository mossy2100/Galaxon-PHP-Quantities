# FloatWithError

Represents a floating-point value with tracked numerical error.

## Overview

The `FloatWithError` class wraps a float value together with an estimate of its accumulated absolute error. This allows tracking precision loss through chains of arithmetic operations, which is particularly important in unit conversion where multiple multiplications and divisions can compound rounding errors.

Each operation (addition, subtraction, multiplication, division, inversion, exponentiation) accumulates error based on the precision limits of IEEE 754 double-precision floating-point arithmetic. The `relativeError` property provides a normalised measure of precision loss.

Implements `Stringable`, formatting as `"value ± absoluteError"`.

### Key Features

- Immutable value objects (all operations return new instances)
- Tracks absolute error through arithmetic operations
- Supports addition, subtraction, negation, multiplication, division, inversion, and exponentiation
- Allows comparison of conversion paths by precision
- Integer values start with zero error

## Properties

### value

```php
private(set) float $value
```

The floating-point numeric value.

### absoluteError

```php
private(set) float $absoluteError
```

The absolute error estimate. This represents the maximum expected deviation from the true value due to floating-point precision limits.

### relativeError

```php
public float $relativeError { get; }
```

The relative error as a proportion of the absolute value. Calculated as `absoluteError / |value|`. Returns `INF` when the value is zero but error is non-zero. Returns 0.0 when both value and error are zero.

This property is useful for comparing the precision of different conversion paths.

## Constructor

### __construct()

```php
public function __construct(float $value, ?float $error = null)
```

Create a new FloatWithError instance.

**Parameters:**
- `$value` (float) - The numeric value
- `$error` (?float) - The absolute error estimate (default: null)

**Behavior:**
- If `$error` is null and `$value` is an exact integer, the error is 0.0 (integers are exact)
- If `$error` is null and `$value` is a non-integer float, error is estimated as half the ULP of the value
- If `$error` is provided, it is used directly

**Examples:**
```php
// Integer with no error
$exact = new FloatWithError(1000);
echo $exact->absoluteError; // 0.0

// Float with estimated error
$approx = new FloatWithError(3.14159);
echo $approx->absoluteError; // Small value based on float precision

// Explicit error
$measured = new FloatWithError(100.0, 0.5);
echo $measured->absoluteError; // 0.5
```

## Inspection Methods

### isExactInt()

```php
public function isExactInt(): bool
```

Check if the value represents an exact mathematical integer (no fractional part and zero error).

**Returns:**
- `bool` - True if the value has no fractional component and zero absolute error

**Examples:**
```php
$int = new FloatWithError(42.0);
$int->isExactInt(); // true

$float = new FloatWithError(42.5);
$float->isExactInt(); // false
```

## Arithmetic Methods

### add()

```php
public function add(float|self $other): self
```

Add another value to this one, accumulating errors.

**Parameters:**
- `$other` (float|self) - The value to add

**Returns:**
- `self` - A new instance with the sum and combined error

**Behavior:**
- Absolute errors add directly
- Adding a FloatWithError combines both error estimates

### sub()

```php
public function sub(float|self $other): self
```

Subtract another value from this one, accumulating errors.

**Parameters:**
- `$other` (float|self) - The value to subtract

**Returns:**
- `self` - A new instance with the difference and combined error

**Behavior:**
- Absolute errors add directly (subtraction has the same error propagation as addition)

### neg()

```php
public function neg(): self
```

Negate this value. Error magnitude is unchanged.

**Returns:**
- `self` - A new instance with the negated value and the same error

### mul()

```php
public function mul(float|self $other): self
```

Multiply this value by another, accumulating errors.

**Parameters:**
- `$other` (float|self) - The value to multiply by

**Returns:**
- `self` - A new instance with the product and combined error

**Behavior:**
- Relative errors add in multiplication
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
public function div(float|self $other): self
```

Divide this value by another, accumulating errors.

**Parameters:**
- `$other` (float|self) - The value to divide by

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

**Throws:**
- `DivisionByZeroError` - If the base is zero and the exponent is negative

**Behavior:**
- Relative error multiplies by |exponent|
- Negative exponents are supported (equivalent to 1/value^|exponent|)
- Exponent of 0 returns 1.0 with propagated error
- Exponent of 1 returns same value with propagated error

**Examples:**
```php
$factor = new FloatWithError(1000.0);  // km to m
$squared = $factor->pow(2);             // km2 to m2
echo $squared->value; // 1000000.0
```

## String Methods

### __toString()

```php
public function __toString(): string
```

Convert to a string representation showing value and absolute error.

**Returns:**
- `string` - Formatted as `"value ± absoluteError"` (e.g. `"3.14159 ± 2.22e-16"`)

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
