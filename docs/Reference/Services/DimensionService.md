# DimensionService

Utility class for working with physical dimension codes.

**Namespace:** `Galaxon\Quantities\Services`

---

## Overview

The `DimensionService` provides tools for parsing, composing, validating, and transforming dimension codes. Dimension codes represent fundamental physical dimensions using single-letter codes with optional exponents, based on the ISQ (International System of Quantities). For example, `'MLT-2'` represents mass x length x time^-2 (force).

All methods are static. The class has no constructor or instance state.

---

## Constants

### `DIMENSION_CODES`

Maps single-letter dimension codes to their quantity type name and base unit symbols (SI, English, or common). See [Dimensions and Base Units](../../Concepts/DimensionsAndBaseUnits.md).

---

## Validation methods

### isValid()

```php
public static function isValid(string $dimension): bool
```

Check if a dimension code string is valid. An empty string is valid and represents a dimensionless quantity.

```php
DimensionService::isValid('L');       // true
DimensionService::isValid('MLT-2');   // true
DimensionService::isValid('');        // true (dimensionless)
DimensionService::isValid('X');       // false (invalid letter)
DimensionService::isValid('L2M-1');   // true
```

---

## Composition methods

### decompose()

```php
public static function decompose(string $dimension): array
```

Decompose a dimension code string into an associative array mapping dimension letters to their exponents.

**Returns:** `array<string, int>`

**Throws:** [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) if the dimension code is invalid.

```php
$terms = DimensionService::decompose('MLT-2');
// ['M' => 1, 'L' => 1, 'T' => -2]

$terms = DimensionService::decompose('L2');
// ['L' => 2]

$terms = DimensionService::decompose('');
// [] (empty for dimensionless)
```

### compose()

```php
public static function compose(array $dimTerms): string
```

Compose an associative array of dimension terms into a normalized dimension code string. Terms are automatically sorted into canonical order.

**Parameters:** `array<string, int> $dimTerms`

```php
$dim = DimensionService::compose(['M' => 1, 'L' => 1, 'T' => -2]);
// 'MLT-2'

$dim = DimensionService::compose(['T' => -2, 'L' => 1, 'M' => 1]);
// 'MLT-2' (sorted to canonical order)

$dim = DimensionService::compose([]);
// '' (dimensionless)
```

---

## Comparison methods

### lessThanOrEqual()

```php
public static function lessThanOrEqual(string $dimension1, string $dimension2): bool
```

Check if `$dimension1` is a subset of `$dimension2`. Returns true if every dimension term in `$dimension1` exists in `$dimension2` with the same sign and an equal or smaller absolute exponent. Used by `toDerived()` to determine whether a unit's dimension fits inside a quantity's dimension.

**Throws:** [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) if either dimension code is invalid.

```php
DimensionService::lessThanOrEqual('MLT-2', 'MLT-2');  // true (equal)
DimensionService::lessThanOrEqual('M', 'MLT-2');       // true (subset)
DimensionService::lessThanOrEqual('L', 'L2');           // true (exponent 1 <= 2)
DimensionService::lessThanOrEqual('L2', 'L');           // false (exponent 2 > 1)
DimensionService::lessThanOrEqual('T', 'T-2');          // false (different signs)
DimensionService::lessThanOrEqual('', 'MLT-2');         // true (empty is subset of anything)
```

---

## Binary arithmetic methods

### sub()

```php
public static function sub(string $dimension1, string $dimension2): string
```

Subtract `$dimension2` from `$dimension1`. Subtracts each exponent in `$dimension2` from the corresponding exponent in `$dimension1`. Terms that cancel to zero are removed. Terms in `$dimension2` that are not in `$dimension1` are ignored.

**Throws:** [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) if either dimension code is invalid.

```php
DimensionService::sub('ML2T-2', 'MLT-2');  // 'L' (energy - force = length)
DimensionService::sub('MLT-2', 'MLT-2');   // '' (identical = dimensionless)
DimensionService::sub('M', 'L');            // 'M' (no overlap, unchanged)
DimensionService::sub('L', 'L2');           // 'L-1' (can produce negative exponents)
```

---

## Power methods

### pow()

```php
public static function pow(string $dimension, int $exponent): string
```

Apply an exponent to every term in a dimension code. Each term's existing exponent is multiplied by the given exponent.

**Throws:** [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) if the dimension code is invalid.

```php
$dim = DimensionService::pow('L', 3);
// 'L3'

$dim = DimensionService::pow('T-1', 2);
// 'T-2'

$dim = DimensionService::pow('MLT-2', 2);
// 'M2L2T-4'
```

---

## Transformation methods

### normalize()

```php
public static function normalize(string $dimension): string
```

Normalize a dimension code to canonical form by decomposing and recomposing it. This sorts terms into canonical order and removes explicit exponents of 1.

**Throws:** [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) if the dimension code is invalid.

```php
$norm = DimensionService::normalize('TLM');
// 'MLT' (sorted to canonical order)

$norm = DimensionService::normalize('L1');
// 'L' (removes exponent of 1)
```

---

## Utility methods

### letterToInt()

```php
public static function letterToInt(string $letter): int
```

Convert a dimension code letter to its position index (0-based) in the canonical ordering.

**Throws:** [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) if the letter is not a valid dimension code.

```php
$idx = DimensionService::letterToInt('M');  // 0
$idx = DimensionService::letterToInt('L');  // 1
$idx = DimensionService::letterToInt('T');  // 5
DimensionService::letterToInt('X');         // throws FormatException
```

### countUnits()

```php
public static function countUnits(string $dimension): int
```

Count the total number of base unit slots in a dimension code. Each dimension term contributes the absolute value of its exponent.

**Throws:** [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) if the dimension code is invalid.

```php
DimensionService::countUnits('L');      // 1
DimensionService::countUnits('L2');     // 2
DimensionService::countUnits('MLT-2'); // 4 (M:1 + L:1 + T:2)
DimensionService::countUnits('');       // 0 (dimensionless)
```

---

## Base unit methods

### getBaseUnitSymbol()

```php
public static function getBaseUnitSymbol(string $dimensionLetterCode, bool $si): string
```

Get the base unit symbol for a dimension letter code. When `$si` is true, returns the SI base unit symbol. When false, returns the English base unit symbol if one exists, otherwise falls back to the SI or common base unit.

**Throws:** [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) if the dimension code letter is invalid.

```php
DimensionService::getBaseUnitSymbol('M', true);   // 'kg'
DimensionService::getBaseUnitSymbol('M', false);  // 'lb'
DimensionService::getBaseUnitSymbol('L', true);   // 'm'
DimensionService::getBaseUnitSymbol('L', false);  // 'ft'
DimensionService::getBaseUnitSymbol('T', true);   // 's'
DimensionService::getBaseUnitSymbol('T', false);  // 's' (no English unit, falls back to SI)
DimensionService::getBaseUnitSymbol('D', true);   // 'B' (common base unit)
```

### getBaseUnitTerm()

```php
public static function getBaseUnitTerm(string $dimensionLetterCode, bool $si): UnitTerm
```

Get the base unit as a `UnitTerm` object for a dimension letter code. Delegates to `getBaseUnitSymbol()` and parses the result.

**Throws:** [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) if the dimension code letter is invalid.

```php
$term = DimensionService::getBaseUnitTerm('M', true);
// UnitTerm representing 'kg'

$term = DimensionService::getBaseUnitTerm('L', false);
// UnitTerm representing 'ft'
```

### getBaseCompoundUnit()

```php
public static function getBaseCompoundUnit(string $dimension, bool $si): CompoundUnit
```

Convert a dimension code to a `CompoundUnit` composed of SI or English base units. Each dimension term is converted to a `UnitTerm` with the appropriate exponent.

**Throws:** [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) if the dimension code is invalid.

```php
$du = DimensionService::getBaseCompoundUnit('MLT-2', true);
// CompoundUnit representing kg*m/s2

$du = DimensionService::getBaseCompoundUnit('MLT-2', false);
// CompoundUnit representing lb*ft/s2
```

---

## Usage examples

```php
use Galaxon\Quantities\Services\DimensionService;

// Validate user input.
if (DimensionService::isValid($userDimension)) {
    $normalized = DimensionService::normalize($userDimension);
}

// Calculate result dimension for multiplication.
$dim1 = 'MLT-2';  // Force
$dim2 = 'L';      // Length
$terms1 = DimensionService::decompose($dim1);
$terms2 = DimensionService::decompose($dim2);

// Add exponents for multiplication.
foreach ($terms2 as $code => $exp) {
    $terms1[$code] = ($terms1[$code] ?? 0) + $exp;
}
$result = DimensionService::compose($terms1);
// 'ML2T-2' (Energy)

// Build the SI base compound unit for a dimension.
$du = DimensionService::getBaseCompoundUnit('ML2T-2', true);
// CompoundUnit representing kg*m2/s2
```

---

## See also

- **[QuantityTypeService](QuantityTypeService.md)** - Registry using dimension codes
- **[CompoundUnit](../Internal/CompoundUnit.md)** - Compound unit representation
- **[UnitTerm](../Internal/UnitTerm.md)** - Individual unit terms
- **[Quantity](../Quantity.md)** - Base quantity class
