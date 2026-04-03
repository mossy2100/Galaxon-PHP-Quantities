# ConversionService

Static service for managing unit conversions across all dimensions.

**Namespace:** `Galaxon\Quantities\Services`

---

## Overview

The `ConversionService` provides a static interface for adding, removing, finding, and performing conversions between units. It delegates to the appropriate `Converter` instance based on the units' dimension.

Key responsibilities:
- Loading conversion definitions from all registered quantity types
- Adding and removing conversions
- Finding conversions between units (with or without path discovery)
- Converting values between units
- Removing conversions by unit or unit system

All methods are static. The dimension is inferred from the units provided — there is no explicit dimension parameter.

---

## Registry Methods

### add()

```php
public static function add(Conversion $conversion, bool $replaceExisting = false): void
```

Add a `Conversion` object to the appropriate `Converter`.

**Parameters:**
- `$conversion` (Conversion) - The conversion to add.
- `$replaceExisting` (bool) - If `true`, replace any existing conversion between the same units. Default: `false`.

### remove()

```php
public static function remove(Conversion $conversion): void
```

Remove a specific conversion from the appropriate `Converter`.

### removeByUnit()

```php
public static function removeByUnit(Unit $unit): void
```

Remove all conversions involving a given unit from all `Converter` instances.

### removeBySystem()

```php
public static function removeBySystem(UnitSystem $system): void
```

Remove all conversions involving units from a specific measurement system.

**Parameters:**
- `$system` (UnitSystem) - The unit system whose conversions should be removed.

### get()

```php
public static function get(
    string|UnitInterface $srcUnit,
    string|UnitInterface $destUnit
): ?Conversion
```

Get a known conversion from the matrix without attempting to discover new paths.

**Parameters:**
- `$srcUnit` (string|UnitInterface) - The source unit.
- `$destUnit` (string|UnitInterface) - The destination unit.

**Returns:** `?Conversion` - The conversion, or `null` if not in the matrix.

**Throws:**
- [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) - If a unit string cannot be parsed.
- [`UnknownUnitException`](../Exceptions/UnknownUnitException.md) - If a unit string contains unknown units.
- [`DimensionMismatchException`](../Exceptions/DimensionMismatchException.md) - If the dimensions don't match.

### has()

```php
public static function has(
    string|UnitInterface $srcUnit,
    string|UnitInterface $destUnit
): bool
```

Check whether a conversion exists in the matrix.

**Throws:**
- [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) - If a unit string cannot be parsed.
- [`UnknownUnitException`](../Exceptions/UnknownUnitException.md) - If a unit string contains unknown units.
- [`DimensionMismatchException`](../Exceptions/DimensionMismatchException.md) - If the dimensions don't match.

---

## Computation Methods

### convert()

```php
public static function convert(
    float $value,
    string|UnitInterface $srcUnit,
    string|UnitInterface $destUnit
): float
```

Convert a value from one unit to another. Discovers conversion paths if necessary.

**Parameters:**
- `$value` (float) - The numeric value to convert.
- `$srcUnit` (string|UnitInterface) - The source unit.
- `$destUnit` (string|UnitInterface) - The destination unit.

**Returns:** `float` - The converted value.

**Throws:**
- [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) - If a unit string cannot be parsed.
- [`UnknownUnitException`](../Exceptions/UnknownUnitException.md) - If a unit string contains unknown units.
- [`DimensionMismatchException`](../Exceptions/DimensionMismatchException.md) - If the dimensions don't match.
- `LogicException` - If no conversion path exists between the units.

### find()

```php
public static function find(
    string|UnitInterface $srcUnit,
    string|UnitInterface $destUnit
): ?Conversion
```

Find a conversion between two units, discovering new paths if necessary. Unlike `get()`, this method will attempt path-finding if no direct conversion exists.

**Returns:** `?Conversion` - The conversion, or `null` if no path exists.

**Throws:**
- [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) - If a unit string cannot be parsed.
- [`UnknownUnitException`](../Exceptions/UnknownUnitException.md) - If a unit string contains unknown units.
- [`DimensionMismatchException`](../Exceptions/DimensionMismatchException.md) - If the dimensions don't match.

---

## Usage Examples

```php
use Galaxon\Quantities\Internal\Conversion;
use Galaxon\Quantities\Services\ConversionService;

// Check for a known conversion
if (ConversionService::has('m', 'ft')) {
    $conv = ConversionService::get('m', 'ft');
    echo "1 m = {$conv->factor->value} ft";
}

// Find a conversion (with path discovery)
$conv = ConversionService::find('mi', 'km');
echo "1 mi = {$conv->factor->value} km";

// Convert a value directly
$feet = ConversionService::convert(100, 'm', 'ft');

// Get just the factor
$factor = ConversionService::find('kg', 'lb')?->factor->value;

// Add a custom conversion
ConversionService::add(new Conversion('m', 'ft', 3.28084));

// Remove all conversions for a unit system
ConversionService::removeBySystem(UnitSystem::Financial);
```

---

## See Also

- **[Conversion](../Internal/Conversion.md)** - Conversion class documentation.
- **[Converter](../Internal/Converter.md)** - Manages conversion paths for a single dimension.
- **[UnitService](UnitService.md)** - Unit registry.
- **[UnitSystem](../UnitSystem.md)** - Measurement system enum.
