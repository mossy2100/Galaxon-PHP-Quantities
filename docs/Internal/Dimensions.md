# Dimensions

Utility class for working with physical dimension codes.

**Namespace:** `Galaxon\Quantities`

---

## Overview

Dimension codes represent the fundamental physical dimensions of a quantity using single-letter codes with optional exponents. For example:

- `L` - length
- `M` - mass
- `T-2LM` - force (mass × length × time⁻²)
- `1` - dimensionless

This class provides methods for validating, decomposing, composing, and transforming dimension codes.

---

## Dimension Codes

Based on the International System of Quantities (ISQ) with some additions:

| Code | Name                | SI Base Unit  |
|------|---------------------|---------------|
| M    | mass                | kg            |
| L    | length              | m             |
| A    | angle               | rad           |
| D    | data                | B             |
| C    | currency            | XAU           |
| T    | time                | s             |
| I    | electric current    | A             |
| N    | amount of substance | mol           |
| H    | temperature         | K             |
| J    | luminous intensity  | cd            |

**Notes:**
- `A` (angle) is not in ISQ but needed for this system
- `H` replaces ISQ's `Θ` (theta) for easier typing
- `C` is reserved for future currency support
- `1` represents dimensionless quantities

---

## Methods

### Validation

#### `static isValid(string $dimension): bool`

Check if a dimension code string is valid.

```php
Dimensions::isValid('L');       // true
Dimensions::isValid('MLT-2');   // true
Dimensions::isValid('1');       // true (dimensionless)
Dimensions::isValid('X');       // false (invalid letter)
Dimensions::isValid('L2M-1');   // true
```

### Decomposition and Composition

#### `static decompose(string $dimension): array`

Decompose a dimension code into an array of codes and exponents.

```php
$terms = Dimensions::decompose('MLT-2');
// ['M' => 1, 'L' => 1, 'T' => -2]

$terms = Dimensions::decompose('L2');
// ['L' => 2]

$terms = Dimensions::decompose('1');
// [] (empty for dimensionless)
```

#### `static compose(array $dimTerms): string`

Compose an array of dimension terms into a normalized dimension code.

```php
$dim = Dimensions::compose(['M' => 1, 'L' => 1, 'T' => -2]);
// 'MLT-2'

$dim = Dimensions::compose(['L' => 2]);
// 'L2'

$dim = Dimensions::compose([]);
// '1' (dimensionless)
```

### Transformation

#### `static normalize(string $dimension): string`

Normalize a dimension code to canonical form.

```php
$norm = Dimensions::normalize('TLM');
// 'MLT' (sorted to canonical order)

$norm = Dimensions::normalize('L1');
// 'L' (removes exponent of 1)
```

#### `static applyExponent(string $dimension, int $exponent): string`

Apply an exponent to a dimension code.

```php
$dim = Dimensions::applyExponent('L', 3);
// 'L3'

$dim = Dimensions::applyExponent('T-1', 2);
// 'T-2'

$dim = Dimensions::applyExponent('MLT-2', 2);
// 'M2L2T-4'
```

### Utility Methods

#### `static letterToInt(string $letter): ?int`

Convert a dimension letter to its position index.

```php
$idx = Dimensions::letterToInt('M');  // 0
$idx = Dimensions::letterToInt('L');  // 1
$idx = Dimensions::letterToInt('X');  // null (invalid)
```

#### `static getSiUnitTermSymbol(string $code): ?string`

Get the SI unit symbol for a dimension code letter.

```php
$symbol = Dimensions::getSiUnitTermSymbol('M');  // 'kg'
$symbol = Dimensions::getSiUnitTermSymbol('L');  // 'm'
$symbol = Dimensions::getSiUnitTermSymbol('T');  // 's'
```

#### `static getSiUnitTerm(string $code): UnitTerm`

Get the SI UnitTerm for a dimension code letter.

```php
$term = Dimensions::getSiUnitTerm('M');
// UnitTerm representing 'kg'
```

---

## Canonical Ordering

Dimension codes are sorted in a specific order for consistency:

```
M, L, A, D, C, T, I, N, H, J
```

This affects how compound dimensions are formatted:

```php
// Input in any order
$dim = Dimensions::normalize('TLM');
// Output in canonical order: 'MLT'
```

---

## Usage Examples

```php
use Galaxon\Quantities\Internal\Dimensions;

// Validate user input
if (Dimensions::isValid($userDimension)) {
    $normalized = Dimensions::normalize($userDimension);
}

// Calculate result dimension for multiplication
$dim1 = 'MLT-2';  // Force
$dim2 = 'L';      // Length
$terms1 = Dimensions::decompose($dim1);
$terms2 = Dimensions::decompose($dim2);

// Add exponents for multiplication
foreach ($terms2 as $code => $exp) {
    $terms1[$code] = ($terms1[$code] ?? 0) + $exp;
}
$result = Dimensions::compose($terms1);
// 'ML2T-2' (Energy)
```

---

## See Also

- **[QuantityTypeRegistry](../Registry/QuantityTypeRegistry.md)** - Registry using dimension codes
- **[DerivedUnit](DerivedUnit.md)** - Uses dimension codes
- **[UnitTerm](UnitTerm.md)** - Individual unit terms
