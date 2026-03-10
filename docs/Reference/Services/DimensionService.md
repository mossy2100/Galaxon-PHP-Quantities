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

Maps single-letter dimension codes to their quantity type name and base unit symbols (SI, English, or common).

| Code | Quantity Type | SI Base Unit | English Base Unit | Common Base Unit |
|------|---------------|:------------:|:-----------------:|:----------------:|
| `M` | mass | `kg` | `lb` | |
| `L` | length | `m` | `ft` | |
| `A` | angle | `rad` | `deg` | |
| `D` | data | | | `B` |
| `C` | money | | | `XAU` |
| `T` | time | `s` | | |
| `I` | electric current | `A` | | |
| `N` | amount of substance | `mol` | | |
| `H` | temperature | `K` | `degR` | |
| `J` | luminous intensity | `cd` | | |

The order of codes determines the canonical ordering of terms within a dimension code (used for comparison and formatting).

**Design notes:**

- `A` (angle) is not part of the ISQ but is needed for this system.
- `C` is reserved for a future currency extension, using `XAU` (gold troy ounces) as the base unit.
- `H` is used for temperature instead of the ISQ's Greek capital theta, because ASCII is easier to type.

---

## Validation

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

## Decomposition and Composition

### decompose()

```php
public static function decompose(string $dimension): array
```

Decompose a dimension code string into an associative array mapping dimension letters to their exponents.

**Returns:** `array<string, int>`

**Throws:** `FormatException` if the dimension code is invalid.

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

## Transformation

### normalize()

```php
public static function normalize(string $dimension): string
```

Normalize a dimension code to canonical form by decomposing and recomposing it. This sorts terms into canonical order and removes explicit exponents of 1.

**Throws:** `FormatException` if the dimension code is invalid.

```php
$norm = DimensionService::normalize('TLM');
// 'MLT' (sorted to canonical order)

$norm = DimensionService::normalize('L1');
// 'L' (removes exponent of 1)
```

### applyExponent()

```php
public static function applyExponent(string $dimension, int $exponent): string
```

Apply an exponent to every term in a dimension code. Each term's existing exponent is multiplied by the given exponent.

**Throws:** `DomainException` if the dimension code is invalid.

```php
$dim = DimensionService::applyExponent('L', 3);
// 'L3'

$dim = DimensionService::applyExponent('T-1', 2);
// 'T-2'

$dim = DimensionService::applyExponent('MLT-2', 2);
// 'M2L2T-4'
```

---

## Utility Methods

### letterToInt()

```php
public static function letterToInt(string $letter): int
```

Convert a dimension code letter to its position index (0-based) in the canonical ordering.

**Throws:** `DomainException` if the letter is not a valid dimension code.

```php
$idx = DimensionService::letterToInt('M');  // 0
$idx = DimensionService::letterToInt('L');  // 1
$idx = DimensionService::letterToInt('T');  // 5
DimensionService::letterToInt('X');         // throws DomainException
```

### getBaseUnitSymbol()

```php
public static function getBaseUnitSymbol(string $dimensionLetterCode, bool $si): string
```

Get the base unit symbol for a dimension letter code. When `$si` is true, returns the SI base unit symbol. When false, returns the English base unit symbol if one exists, otherwise falls back to the SI or common base unit.

**Throws:** `DomainException` if the dimension code letter is invalid.

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

**Throws:** `DomainException` if the dimension code letter is invalid.

```php
$term = DimensionService::getBaseUnitTerm('M', true);
// UnitTerm representing 'kg'

$term = DimensionService::getBaseUnitTerm('L', false);
// UnitTerm representing 'ft'
```

### getBaseDerivedUnit()

```php
public static function getBaseDerivedUnit(string $dimension, bool $si): DerivedUnit
```

Convert a dimension code to a `DerivedUnit` composed of SI or English base units. Each dimension term is converted to a `UnitTerm` with the appropriate exponent.

**Throws:** `DomainException` if the dimension code is invalid.

```php
$du = DimensionService::getBaseDerivedUnit('MLT-2', true);
// DerivedUnit representing kg*m/s2

$du = DimensionService::getBaseDerivedUnit('MLT-2', false);
// DerivedUnit representing lb*ft/s2
```

---

## Usage Examples

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

// Build the SI base derived unit for a dimension.
$du = DimensionService::getBaseDerivedUnit('ML2T-2', true);
// DerivedUnit representing kg*m2/s2
```

---

## See Also

- **[QuantityTypeService](QuantityTypeService.md)** - Registry using dimension codes
- **[DerivedUnit](../Internal/DerivedUnit.md)** - Compound unit representation
- **[UnitTerm](../Internal/UnitTerm.md)** - Individual unit terms
- **[Quantity](../Quantity.md)** - Base quantity class
