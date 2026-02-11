# Converter

Manages unit conversions for a measurement dimension.

## Overview

The `Converter` class is responsible for finding and computing conversions between units of the same physical dimension. It uses the multiton pattern to maintain one instance per dimension (e.g., one for length, one for mass, etc.).

The conversion system works by:
1. Storing direct conversions from the ConversionRegistry
2. Automatically discovering indirect conversion paths via graph traversal
3. Applying prefix adjustments when converting between prefixed units
4. Tracking numerical precision to prefer shorter, more accurate paths

All conversions use the linear transformation formula: `destValue = srcValue * factor`

### Key Features

- Automatic path discovery through conversion graph
- Precision-aware path selection via error tracking
- Prefix algebra for prefixed unit conversions
- Unit expansion (e.g., N → kg\*m\*s⁻²)
- Unit merging (e.g., m\*ft → m²)

## Properties

### dimension

```php
private(set) string $dimension
```

The dimension code for this converter (e.g., 'L' for length, 'MLT-2' for force).

### units

```php
private(set) array $units
```

Array of unprefixed units registered with this converter, keyed by ASCII symbol.

## Factory Methods

### getByDimension()

```php
public static function getByDimension(string $dimension): self
```

Get the Converter instance for a given dimension.

**Parameters:**
- `$dimension` (string) - Dimension code (e.g., 'L', 'M', 'MLT-2')

**Returns:**
- `self` - The Converter for this dimension

**Throws:**
- `FormatException` - If the dimension code is invalid

**Examples:**
```php
$lengthConverter = Converter::getByDimension('L');
$forceConverter = Converter::getByDimension('MLT-2');
```

### clear()

```php
public static function clear(): void
```

Clear all Converter instances. Resets the multiton cache, forcing new instances to be created on next access. Primarily intended for test isolation.

## Conversion Methods

### getConversion()

```php
public function getConversion(
    string|UnitInterface $srcUnit,
    string|UnitInterface $destUnit
): ?Conversion
```

Find the conversion between two units.

**Parameters:**
- `$srcUnit` (string|UnitInterface) - The source unit
- `$destUnit` (string|UnitInterface) - The destination unit

**Returns:**
- `?Conversion` - The conversion, or null if no path exists

**Throws:**
- `DomainException` - If either unit is invalid for this dimension

**Behavior:**
- Returns cached conversion if available
- Discovers new paths via graph traversal
- Handles prefix adjustments automatically

**Examples:**
```php
$converter = Converter::getByDimension('L');
$conversion = $converter->getConversion('m', 'ft');
echo $conversion->factor->value; // 3.28084...
```

### getConversionFactor()

```php
public function getConversionFactor(
    string|UnitInterface $srcUnit,
    string|UnitInterface $destUnit
): ?float
```

Get just the conversion factor between two units.

**Parameters:**
- `$srcUnit` (string|UnitInterface) - The source unit
- `$destUnit` (string|UnitInterface) - The destination unit

**Returns:**
- `?float` - The conversion factor, or null if no path exists

**Examples:**
```php
$converter = Converter::getByDimension('L');
$factor = $converter->getConversionFactor('km', 'm'); // 1000.0
```

### convert()

```php
public function convert(
    float $value,
    string|UnitInterface $srcUnit,
    string|UnitInterface $destUnit
): float
```

Convert a numeric value from one unit to another.

**Parameters:**
- `$value` (float) - The value to convert
- `$srcUnit` (string|UnitInterface) - The source unit
- `$destUnit` (string|UnitInterface) - The destination unit

**Returns:**
- `float` - The converted value

**Throws:**
- `DomainException` - If either unit is invalid
- `LogicException` - If no conversion path exists

**Examples:**
```php
$converter = Converter::getByDimension('L');
$metres = $converter->convert(100, 'ft', 'm'); // 30.48
```

## Static Conversion Methods

### expand()

```php
public static function expand(float $value, DerivedUnit $unit): array
```

Expand named units to their base unit components.

**Parameters:**
- `$value` (float) - The value
- `$unit` (DerivedUnit) - The unit to expand

**Returns:**
- `array{float, DerivedUnit}` - The adjusted value and expanded unit

**Behavior:**
- Converts units like N → kg\*m\*s⁻²
- Adjusts the value by the expansion factor
- Recursively expands nested units
- Merges compatible units after expansion

**Examples:**
```php
$unit = DerivedUnit::parse('N');
[$value, $expanded] = Converter::expand(1.0, $unit);
echo $expanded->asciiSymbol; // 'kg*m/s2'
```

### merge()

```php
public static function merge(float $value, DerivedUnit $unit): array
```

Merge unit terms that share the same dimension.

**Parameters:**
- `$value` (float) - The value
- `$unit` (DerivedUnit) - The unit to merge

**Returns:**
- `array{float, DerivedUnit}` - The adjusted value and merged unit

**Behavior:**
- Converts mixed units like m\*ft → m²
- Adjusts the value by the conversion factor
- First unit of each dimension is kept

**Examples:**
```php
$unit = DerivedUnit::parse('m*ft');
[$value, $merged] = Converter::merge(1.0, $unit);
echo $merged->asciiSymbol; // 'm2'
echo $value; // 0.3048
```

## Modification Methods

### addUnit()

```php
public function addUnit(DerivedUnit $derivedUnit): void
```

Add a unit to this converter. Strips prefixes, and also adds merged and expanded variants if applicable.

**Parameters:**
- `$derivedUnit` (DerivedUnit) - The unit to add

## Validation Methods

### validateUnit()

```php
public function validateUnit(string|UnitInterface $value): DerivedUnit
```

Validate that a unit is valid for this converter's dimension.

**Parameters:**
- `$value` (string|UnitInterface) - The unit to validate

**Returns:**
- `DerivedUnit` - The validated unit as a DerivedUnit

**Throws:**
- `DomainException` - If the unit has the wrong dimension

## Usage Examples

### Basic Conversion

```php
use Galaxon\Quantities\Converter;

// Convert length
$length = Converter::getByDimension('L');
$metres = $length->convert(5280, 'ft', 'm');
echo "$metres m"; // 1609.344 m

// Convert with prefixes
$km = $length->convert(1000, 'm', 'km');
echo "$km km"; // 1 km
```

### Working with Compound Units

```php
use Galaxon\Quantities\Converter;
use Galaxon\Quantities\DerivedUnit;

// Force conversion
$force = Converter::getByDimension('MLT-2');
$newtons = $force->convert(1, 'lbf', 'N');

// Expand to base units
$unit = DerivedUnit::parse('N');
[$value, $expanded] = Converter::expand(1.0, $unit);
// $expanded is 'kg*m/s2'
```

### Cross-System Conversions

```php
use Galaxon\Quantities\Converter;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;

// Load Imperial units first
UnitRegistry::loadSystem(System::Imperial);

// Now convert
$volume = Converter::getByDimension('L3');
$litres = $volume->convert(1, 'imp gal', 'L');
echo "$litres L"; // ~4.546 L (Imperial gallon)
```

## See Also

- **[Conversion](Conversion.md)** - Represents a single unit conversion
- **[ConversionRegistry](Registry/ConversionRegistry.md)** - Stores registered conversions
- **[DerivedUnit](DerivedUnit.md)** - Compound unit representation
- **[Quantity](Quantity.md)** - Uses Converter for unit conversion
