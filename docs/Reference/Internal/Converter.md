# Converter

Manages unit conversions for a measurement dimension.

---

## Overview

The `Converter` class is responsible for finding and computing conversions between units of the same physical dimension. It uses the multiton pattern to maintain one instance per dimension (e.g., one for length, one for mass, etc.).

The conversion system works by:
1. Storing direct conversions provided by quantity type definitions
2. Automatically discovering indirect conversion paths via graph traversal
3. Applying prefix adjustments when converting between prefixed units
4. Tracking numerical precision to prefer shorter, more accurate paths

All conversions use the linear transformation formula: `destValue = srcValue * factor`

### Path Discovery Strategies

When `findConversion()` is called, the converter tries these strategies in order (cheapest first), stopping as soon as an exact conversion is found:

1. **Inverse** — if dest->src exists, invert it
2. **Prefix** — if the units share the same base, compute the prefix ratio
3. **Exponentiation** — if both units are single-term with the same exponent > 1 (e.g., m2 -> ft2), find the base conversion and raise it to the power
4. **Unit term pairs** — for compound units with matching term counts, convert each pair individually
5. **Combination** — compose two known conversions via an intermediate unit (sequential, convergent, divergent, or opposite)

If no exact conversion is found, the converter generates batches of new conversions between all known unit pairs and repeats until the target conversion is discovered or no further progress can be made.

---

## Properties

### dimension

```php
private(set) string $dimension
```

The dimension code for this converter (e.g., `'L'` for length, `'MLT-2'` for force).

### units

```php
private(set) array $units
```

Units registered with this converter, keyed by ASCII symbol.

Type: `array<string, DerivedUnit>`

### conversionMatrix

```php
private(set) array $conversionMatrix
```

Conversion matrix storing known conversions between units.

Type: `array<string, array<string, Conversion>>`

Structure: `$conversionMatrix[$srcSymbol][$destSymbol] = Conversion`

### quantityType

```php
public ?QuantityType $quantityType { get; }
```

The quantity type this converter is for (e.g., the `QuantityType` for length), or `null` if the dimension has no registered quantity type. Resolved via `QuantityTypeService::getByDimension()`.

---

## Instance Management

The `Converter` uses the multiton pattern. The constructor is private; use `getInstance()` to obtain instances.

### getInstance()

```php
public static function getInstance(string $dimension): self
```

Get the Converter instance for a given dimension. Creates a new instance on first access, loading conversion definitions from the registered quantity type.

**Parameters:**
- `$dimension` (string) - Dimension code (e.g., `'L'`, `'M'`, `'MLT-2'`).

**Returns:** `Converter`

**Throws:**
- [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) - If the dimension code is invalid.

### getInstances()

```php
public static function getInstances(): array
```

Get all Converter instances created so far.

**Returns:** `array<string, Converter>` - Keyed by dimension code.

### removeInstance()

```php
public static function removeInstance(string $dimension): void
```

Remove the cached Converter instance for a given dimension.

### removeAllInstances()

```php
public static function removeAllInstances(): void
```

Clear all cached Converter instances. Forces new instances to be created on next access. Primarily intended for test isolation.

---

## Unit Methods

### hasUnit()

```php
public function hasUnit(DerivedUnit $unit): bool
```

Check if a unit is registered with this converter.

### addUnit()

```php
public function addUnit(DerivedUnit $unit): void
```

Add a unit to the converter's unit list. Does nothing if the unit is already present.

### removeUnit()

```php
public function removeUnit(DerivedUnit $derivedUnit): void
```

Remove a unit from the unit list.

### removeAllUnits()

```php
public function removeAllUnits(): void
```

Remove all units from the unit list.

---

## Conversion Lookup Methods

### findConversion()

```php
public function findConversion(
    string|UnitInterface $srcUnit,
    string|UnitInterface $destUnit
): ?Conversion
```

Find the conversion between two units, discovering new paths if necessary.

Returns a cached conversion if available, otherwise discovers a path using the strategies described in the overview. Discovered conversions are cached for future use.

For dimensions containing `'C'` (currency), automatically calls `CurrencyService::refresh()` to ensure fresh exchange rate data.

**Parameters:**
- `$srcUnit` (string|UnitInterface) - The source unit.
- `$destUnit` (string|UnitInterface) - The destination unit.

**Returns:** `?Conversion` - The conversion, or `null` if no path exists.

**Throws:**
- [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) - If a unit string cannot be parsed.
- [`DimensionMismatchException`](../Exceptions/DimensionMismatchException.md) - If either unit has the wrong dimension for this converter.

### findConversionFactor()

```php
public function findConversionFactor(
    string|UnitInterface $srcUnit,
    string|UnitInterface $destUnit
): ?float
```

Get just the conversion factor between two units. Convenience wrapper around `findConversion()`.

**Returns:** `?float` - The conversion factor, or `null` if no path exists.

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
- `$value` (float) - The value to convert.
- `$srcUnit` (string|UnitInterface) - The source unit.
- `$destUnit` (string|UnitInterface) - The destination unit.

**Returns:** `float` - The converted value.

**Throws:**
- [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) - If a unit string cannot be parsed.
- [`DimensionMismatchException`](../Exceptions/DimensionMismatchException.md) - If either unit has the wrong dimension.
- `LogicException` - If no conversion path exists between the units.

### getConversion()

```php
public function getConversion(
    string|DerivedUnit $srcUnit,
    string|DerivedUnit $destUnit
): ?Conversion
```

Look up a conversion directly from the matrix. Does **not** discover new paths — returns `null` if the conversion is not already cached. Use `findConversion()` for path discovery.

### hasConversion()

```php
public function hasConversion(
    string|DerivedUnit $srcUnit,
    string|DerivedUnit $destUnit
): bool
```

Check whether a conversion between two units is already cached in the matrix.

---

## Conversion Management Methods

### loadConversions()

```php
public function loadConversions(bool $replaceExisting = false): void
```

Load conversion definitions for this converter's dimension from the registered quantity type class.

**Parameters:**
- `$replaceExisting` (bool) - If `true`, replace existing conversions between the same units. Default: `false`.

**Throws:**
- [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) - If a unit symbol cannot be parsed.
- `DomainException` - If the factor is invalid.
- [`DimensionMismatchException`](../Exceptions/DimensionMismatchException.md) - If a conversion's dimension doesn't match this converter.

### addConversion()

```php
public function addConversion(
    Conversion $conversion,
    bool $replaceExisting = false
): bool
```

Add a conversion to the matrix. Also registers both units.

**Parameters:**
- `$conversion` (Conversion) - The conversion to add.
- `$replaceExisting` (bool) - If `true`, replace any existing conversion between the same units. Default: `false`.

**Returns:** `bool` - `true` if the conversion was added, `false` if it already existed and was not replaced.

**Throws:**
- [`DimensionMismatchException`](../Exceptions/DimensionMismatchException.md) - If the conversion's dimension doesn't match this converter.

### removeConversion()

```php
public function removeConversion(Conversion $conversion): void
```

Remove a specific conversion from the matrix.

### removeAllConversions()

```php
public function removeAllConversions(): void
```

Remove all conversions from the matrix.

### removeConversionsByUnit()

```php
public function removeConversionsByUnit(Unit $unit): void
```

Remove all conversions involving a given unit. Used when unloading a unit from the registry.

---

## Usage Examples

### Basic Conversion

```php
use Galaxon\Quantities\Internal\Converter;

// Convert length
$length = Converter::getInstance('L');
$meters = $length->convert(5280, 'ft', 'm');
echo "$meters m"; // 1609.344 m

// Convert with prefixes
$km = $length->convert(1000, 'm', 'km');
echo "$km km"; // 1 km
```

### Working with Compound Units

```php
use Galaxon\Quantities\Internal\Converter;

// Force conversion
$force = Converter::getInstance('MLT-2');
$newtons = $force->convert(1, 'lbf', 'N');
```

### Cross-System Conversions

```php
use Galaxon\Quantities\Internal\Converter;

$volume = Converter::getInstance('L3');
$liters = $volume->convert(1, 'imp gal', 'L');
echo "$liters L"; // ~4.546 L (Imperial gallon)
```

---

## See Also

- **[Conversion](Conversion.md)** - Represents a single unit conversion.
- **[FloatWithError](FloatWithError.md)** - Tracks precision through operations.
- **[ConversionService](../Services/ConversionService.md)** - Stores registered conversions.
- **[DerivedUnit](DerivedUnit.md)** - Compound unit representation.
- **[Quantity](../Quantity.md)** - Uses Converter for unit conversion.
