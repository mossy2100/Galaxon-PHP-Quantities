# Dimensions

Utility class for working with physical dimension codes.

**Namespace:** `Galaxon\Quantities`

---

## 

---

## Methods

### Validation

#### `static isValid(string $dimension): bool`

Check if a dimension code string is valid.

```php
DimensionService::isValid('L');       // true
DimensionService::isValid('MLT-2');   // true
DimensionService::isValid('');        // true (dimensionless)
DimensionService::isValid('X');       // false (invalid letter)
DimensionService::isValid('L2M-1');   // true
```

### Decomposition and Composition

#### `static decompose(string $dimension): array`

Decompose a dimension code into an array of codes and exponents. The result array is keyed by dimension letter, with the values equal to the exponent.

```php
$terms = DimensionService::decompose('MLT-2');
// ['M' => 1, 'L' => 1, 'T' => -2]

$terms = DimensionService::decompose('L2');
// ['L' => 2]

$terms = DimensionService::decompose('');
// [] (empty for dimensionless)
```

#### `static compose(array $dimTerms): string`

Compose an array of dimension terms into a normalized dimension code.

```php
$dim = DimensionService::compose(['M' => 1, 'L' => 1, 'T' => -2]);
// 'MLT-2'

$dim = DimensionService::compose(['L' => 2]);
// 'L2'

$dim = DimensionService::compose([]);
// '' (dimensionless)
```

### Transformation

#### `static normalize(string $dimension): string`

Normalize a dimension code to canonical form.

```php
$norm = DimensionService::normalize('TLM');
// 'MLT' (sorted to canonical order)

$norm = DimensionService::normalize('L1');
// 'L' (removes exponent of 1)
```

#### `static applyExponent(string $dimension, int $exponent): string`

Apply an exponent to a dimension code.

```php
$dim = DimensionService::applyExponent('L', 3);
// 'L3'

$dim = DimensionService::applyExponent('T-1', 2);
// 'T-2'

$dim = DimensionService::applyExponent('MLT-2', 2);
// 'M2L2T-4'
```

### Utility Methods

#### `static letterToInt(string $letter): int`

Convert a dimension letter to its position index.

**Throws:** `DomainException` if the letter is not a valid dimension code.

```php
$idx = DimensionService::letterToInt('M');  // 0
$idx = DimensionService::letterToInt('L');  // 1
DimensionService::letterToInt('X');         // throws DomainException
```

#### `static getSiUnitTermSymbol(string $code): ?string`

Get the SI unit symbol for a dimension code letter.

```php
$symbol = DimensionService::getSiUnitTermSymbol('M');  // 'kg'
$symbol = DimensionService::getSiUnitTermSymbol('L');  // 'm'
$symbol = DimensionService::getSiUnitTermSymbol('T');  // 's'
```

#### `static getSiUnitTerm(string $code): UnitTerm`

Get the SI UnitTerm for a dimension code letter.

```php
$term = DimensionService::getSiUnitTerm('M');
// UnitTerm representing 'kg'
```

---

## Canonical Ordering

Dimension codes are sorted in a specific order to match common usage:

```
M, L, A, D, C, T, I, N, H, J
```

This affects how compound dimensions are formatted:

```php
// Input in any order
$dim = DimensionService::normalize('TLM');
// Output in canonical order: 'MLT'
```

---

## Usage Examples

```php
use Galaxon\Quantities\Services\DimensionService;

// Validate user input
if (DimensionService::isValid($userDimension)) {
    $normalized = DimensionService::normalize($userDimension);
}

// Calculate result dimension for multiplication
$dim1 = 'MLT-2';  // Force
$dim2 = 'L';      // Length
$terms1 = DimensionService::decompose($dim1);
$terms2 = DimensionService::decompose($dim2);

// Add exponents for multiplication
foreach ($terms2 as $code => $exp) {
    $terms1[$code] = ($terms1[$code] ?? 0) + $exp;
}
$result = DimensionService::compose($terms1);
// 'ML2T-2' (Energy)
```

---

## See Also

- **[QuantityTypeService](../Services/QuantityTypeService.md)** - Registry using dimension codes
- **[DerivedUnit](DerivedUnit.md)** - Uses dimension codes
- **[UnitTerm](UnitTerm.md)** - Individual unit terms
