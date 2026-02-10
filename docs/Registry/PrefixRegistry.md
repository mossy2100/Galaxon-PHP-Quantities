# PrefixRegistry

Registry for SI and binary prefixes.

**Namespace:** `Galaxon\Quantities`

---

## Overview

The `PrefixRegistry` provides access to metric prefixes (milli, kilo, mega, etc.) and binary prefixes (kibi, mebi, etc.) organized by group codes for flexible filtering.

---

## Prefix Group Constants

Prefixes are organized into groups using bitwise flags:

| Constant                          | Value  | Description                                     |
|-----------------------------------|--------|-------------------------------------------------|
| `GROUP_CODE_SMALL_ENG_METRIC`     | 1      | Small engineering: m, μ, n, p, f, a, z, y, r, q |
| `GROUP_CODE_SMALL_NON_ENG_METRIC` | 2      | Small non-engineering: c, d                     |
| `GROUP_CODE_LARGE_NON_ENG_METRIC` | 4      | Large non-engineering: da, h                    |
| `GROUP_CODE_LARGE_ENG_METRIC`     | 8      | Large engineering: k, M, G, T, P, E, Z, Y, R, Q |
| `GROUP_CODE_BINARY`               | 16     | Binary: Ki, Mi, Gi, Ti, Pi, Ei, Zi, Yi, Ri, Qi  |

### Combined Group Codes

| Constant | Components | Description |
|----------|------------|-------------|
| `GROUP_CODE_SMALL_METRIC` | 1 \| 2 | All small metric prefixes |
| `GROUP_CODE_LARGE_METRIC` | 4 \| 8 | All large metric prefixes |
| `GROUP_CODE_ENG_METRIC` | 1 \| 8 | Engineering metric (powers of 1000) |
| `GROUP_CODE_METRIC` | 1 \| 2 \| 4 \| 8 | All metric prefixes |
| `GROUP_CODE_LARGE` | 8 \| 16 | Large metric + binary |
| `GROUP_CODE_ALL` | all | All prefixes |

---

## Available Prefixes

### Metric Prefixes

| Name | Symbol | Multiplier | Group |
|------|--------|------------|-------|
| quecto | q | 10⁻³⁰ | small eng |
| ronto | r | 10⁻²⁷ | small eng |
| yocto | y | 10⁻²⁴ | small eng |
| zepto | z | 10⁻²¹ | small eng |
| atto | a | 10⁻¹⁸ | small eng |
| femto | f | 10⁻¹⁵ | small eng |
| pico | p | 10⁻¹² | small eng |
| nano | n | 10⁻⁹ | small eng |
| micro | μ, u | 10⁻⁶ | small eng |
| milli | m | 10⁻³ | small eng |
| centi | c | 10⁻² | small non-eng |
| deci | d | 10⁻¹ | small non-eng |
| deca | da | 10¹ | large non-eng |
| hecto | h | 10² | large non-eng |
| kilo | k | 10³ | large eng |
| mega | M | 10⁶ | large eng |
| giga | G | 10⁹ | large eng |
| tera | T | 10¹² | large eng |
| peta | P | 10¹⁵ | large eng |
| exa | E | 10¹⁸ | large eng |
| zetta | Z | 10²¹ | large eng |
| yotta | Y | 10²⁴ | large eng |
| ronna | R | 10²⁷ | large eng |
| quetta | Q | 10³⁰ | large eng |

### Binary Prefixes

| Name | Symbol | Multiplier | Decimal Approx |
|------|--------|------------|----------------|
| kibi | Ki | 2¹⁰ | ~1.024 × 10³ |
| mebi | Mi | 2²⁰ | ~1.049 × 10⁶ |
| gibi | Gi | 2³⁰ | ~1.074 × 10⁹ |
| tebi | Ti | 2⁴⁰ | ~1.100 × 10¹² |
| pebi | Pi | 2⁵⁰ | ~1.126 × 10¹⁵ |
| exbi | Ei | 2⁶⁰ | ~1.153 × 10¹⁸ |
| zebi | Zi | 2⁷⁰ | ~1.181 × 10²¹ |
| yobi | Yi | 2⁸⁰ | ~1.209 × 10²⁴ |
| robi | Ri | 2⁹⁰ | ~1.238 × 10²⁷ |
| quebi | Qi | 2¹⁰⁰ | ~1.268 × 10³⁰ |

---

## Methods

#### `static getPrefixes(int $prefixGroup = GROUP_CODE_ALL): array`

Get prefixes matching a group code.

```php
// All prefixes
$all = PrefixRegistry::getPrefixes();

// Only metric prefixes
$metric = PrefixRegistry::getPrefixes(PrefixRegistry::GROUP_CODE_METRIC);

// Only engineering prefixes (powers of 1000)
$eng = PrefixRegistry::getPrefixes(PrefixRegistry::GROUP_CODE_ENG_METRIC);

// Binary prefixes only
$binary = PrefixRegistry::getPrefixes(PrefixRegistry::GROUP_CODE_BINARY);

// Large metric + binary (for data units)
$large = PrefixRegistry::getPrefixes(PrefixRegistry::GROUP_CODE_LARGE);
```

#### `static getBySymbol(string $symbol): ?Prefix`

Find a prefix by its symbol (ASCII or Unicode).

```php
$kilo = PrefixRegistry::getBySymbol('k');
$micro = PrefixRegistry::getBySymbol('μ');
$micro = PrefixRegistry::getBySymbol('u');  // ASCII alternative
$kibi = PrefixRegistry::getBySymbol('Ki');
```

#### `static invert(?Prefix $prefix): ?Prefix`

Get the inverse of a prefix (opposite exponent).

```php
$kilo = PrefixRegistry::getBySymbol('k');   // 10³
$milli = PrefixRegistry::invert($kilo);      // 10⁻³

$mega = PrefixRegistry::getBySymbol('M');   // 10⁶
$micro = PrefixRegistry::invert($mega);      // 10⁻⁶
```

#### `static isValidGroupCode(int $groupCode): bool`

Check if a group code is one of the base codes.

```php
$valid = PrefixRegistry::isValidGroupCode(1);   // true (SMALL_ENG_METRIC)
$valid = PrefixRegistry::isValidGroupCode(3);   // false (combined code)
$valid = PrefixRegistry::isValidGroupCode(16);  // true (BINARY)
```

---

## Usage Examples

```php
use Galaxon\Quantities\Registry\PrefixRegistry;

// Get all engineering prefixes for a scientific application
$engPrefixes = PrefixRegistry::getPrefixes(PrefixRegistry::GROUP_CODE_ENG_METRIC);
foreach ($engPrefixes as $prefix) {
    echo "{$prefix->name}: {$prefix->asciiSymbol} = {$prefix->multiplier}\n";
}

// Parse a prefixed symbol
$symbol = 'km';
$prefix = PrefixRegistry::getBySymbol('k');
if ($prefix !== null) {
    $baseSymbol = substr($symbol, strlen($prefix->asciiSymbol));
    $multiplier = $prefix->multiplier;
}

// Find inverse for unit conversion
$source = PrefixRegistry::getBySymbol('M');   // mega (10⁶)
$inverse = PrefixRegistry::invert($source);    // micro (10⁻⁶)
```

---

## See Also

- **[Prefix](../Prefix.md)** - Prefix class documentation
- **[Unit](../Unit.md)** - Unit class using prefix groups
- **[SupportedUnits](../SupportedUnits.md)** - Units with their prefix support
