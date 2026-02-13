# Conversion

Represents a unit conversion between two units of the same dimension.

## Overview

The `Conversion` class encapsulates a conversion relationship between a source unit and a destination unit. It stores the conversion factor as a `FloatWithError` to track numerical precision through chains of conversions.

Conversions are linear transformations of the form: `destValue = srcValue * factor`

The class provides methods for:
- Combining conversions to create new conversion paths
- Inverting conversions
- Raising conversions to a power (for area, volume, etc.)

All operations return new instances, maintaining immutability.

### Key Features

- Immutable conversion objects
- Precision tracking via FloatWithError
- Four combination methods for building conversion graphs
- Support for exponentiation (area, volume conversions)

## Properties

### srcUnit

```php
public readonly DerivedUnit $srcUnit
```

The source unit for the conversion.

### destUnit

```php
public readonly DerivedUnit $destUnit
```

The destination unit for the conversion.

### factor

```php
public readonly FloatWithError $factor
```

The conversion factor with error tracking. To convert from source to destination, multiply by this factor.

## Constructor

### __construct()

```php
public function __construct(
    string|UnitInterface $srcUnit,
    string|UnitInterface $destUnit,
    float|FloatWithError $factor
)
```

Create a new Conversion instance.

**Parameters:**
- `$srcUnit` (string|UnitInterface) - The source unit (symbol or object)
- `$destUnit` (string|UnitInterface) - The destination unit (symbol or object)
- `$factor` (float|FloatWithError) - The conversion factor

**Throws:**
- `DomainException` - If the source and destination units have different dimensions

**Examples:**
```php
// Create a conversion from feet to metres
$ftToM = new Conversion('ft', 'm', 0.3048);

// Create a conversion with explicit error tracking
$factor = new FloatWithError(0.3048, 1e-10);
$ftToM = new Conversion('ft', 'm', $factor);
```

## Transformation Methods

### inv()

```php
public function inv(): self
```

Return the inverse conversion (destination to source).

**Returns:**
- `self` - A new Conversion with source and destination swapped

**Examples:**
```php
$ftToM = new Conversion('ft', 'm', 0.3048);
$mToFt = $ftToM->inv();
echo $mToFt->factor->value; // 3.28084...
```

### pow()

```php
public function pow(int $exponent): self
```

Raise the conversion to a power for area, volume, etc.

**Parameters:**
- `$exponent` (int) - The power to raise to

**Returns:**
- `self` - A new Conversion with exponentiated units and factor

**Examples:**
```php
$ftToM = new Conversion('ft', 'm', 0.3048);
$ft2ToM2 = $ftToM->pow(2);  // Square feet to square metres
echo $ft2ToM2->factor->value; // 0.09290304
```

## Combination Methods

These methods combine two conversions to create a new conversion path. The four methods handle different topologies in the conversion graph.

### combineSequential()

```php
public function combineSequential(self $other): self
```

Combine two conversions in sequence: A→B and B→C to get A→C.

**Parameters:**
- `$other` (self) - The second conversion (B→C)

**Returns:**
- `self` - A new Conversion from this source to other's destination

**Precondition:**
- `$this->destUnit` must equal `$other->srcUnit`

**Examples:**
```php
$ftToIn = new Conversion('ft', 'in', 12);
$inToCm = new Conversion('in', 'cm', 2.54);
$ftToCm = $ftToIn->combineSequential($inToCm);
echo $ftToCm->factor->value; // 30.48
```

### combineConvergent()

```php
public function combineConvergent(self $other): self
```

Combine two conversions that share a destination: A→C and B→C to get A→B.

**Parameters:**
- `$other` (self) - The second conversion (B→C)

**Returns:**
- `self` - A new Conversion from this source to other's source

**Precondition:**
- `$this->destUnit` must equal `$other->destUnit`

**Examples:**
```php
$ftToM = new Conversion('ft', 'm', 0.3048);
$ydToM = new Conversion('yd', 'm', 0.9144);
$ftToYd = $ftToM->combineConvergent($ydToM);
echo $ftToYd->factor->value; // 0.333...
```

### combineDivergent()

```php
public function combineDivergent(self $other): self
```

Combine two conversions that share a source: C→A and C→B to get A→B.

**Parameters:**
- `$other` (self) - The second conversion (C→B)

**Returns:**
- `self` - A new Conversion from this destination to other's destination

**Precondition:**
- `$this->srcUnit` must equal `$other->srcUnit`

**Examples:**
```php
$mToFt = new Conversion('m', 'ft', 3.28084);
$mToYd = new Conversion('m', 'yd', 1.09361);
$ftToYd = $mToFt->combineDivergent($mToYd);
echo $ftToYd->factor->value; // 0.333...
```

### combineOpposite()

```php
public function combineOpposite(self $other): self
```

Combine two conversions in opposite directions: C→A and B→C to get A→B.

**Parameters:**
- `$other` (self) - The second conversion (B→C)

**Returns:**
- `self` - A new Conversion from this destination to other's source

**Precondition:**
- `$this->srcUnit` must equal `$other->destUnit`

**Examples:**
```php
$mToFt = new Conversion('m', 'ft', 3.28084);
$ydToM = new Conversion('yd', 'm', 0.9144);
$ftToYd = $mToFt->combineOpposite($ydToM);
echo $ftToYd->factor->value; // 0.333...
```

## Usage Examples

### Building a Conversion Chain

```php
use Galaxon\Quantities\Internal\Conversion;

// Miles to kilometres via multiple steps
$miToFt = new Conversion('mi', 'ft', 5280);
$ftToIn = new Conversion('ft', 'in', 12);
$inToCm = new Conversion('in', 'cm', 2.54);
$cmToM = new Conversion('cm', 'm', 0.01);
$mToKm = new Conversion('m', 'km', 0.001);

$miToKm = $miToFt
    ->combineSequential($ftToIn)
    ->combineSequential($inToCm)
    ->combineSequential($cmToM)
    ->combineSequential($mToKm);

echo $miToKm->factor->value; // 1.609344
```

### Area Conversion

```php
use Galaxon\Quantities\Internal\Conversion;

// Convert acres to square metres
$acreToFt2 = new Conversion('acre', 'ft2', 43560);
$ftToM = new Conversion('ft', 'm', 0.3048);
$ft2ToM2 = $ftToM->pow(2);

$acreToM2 = $acreToFt2->combineSequential($ft2ToM2);
echo $acreToM2->factor->value; // 4046.86...
```

## See Also

- **[Converter](Converter.md)** - Manages conversion paths between units
- **[FloatWithError](FloatWithError.md)** - Tracks precision through operations
- **[ConversionRegistry](../Registry/ConversionRegistry.md)** - Stores registered conversions
