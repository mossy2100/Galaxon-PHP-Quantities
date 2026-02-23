# PrefixService

Registry for SI and binary prefixes.

**Namespace:** `Galaxon\Quantities`

---

## Overview

The `PrefixService` provides access to metric prefixes (milli, kilo, mega, etc.) and binary prefixes (kibi, mebi, etc.) organized by group codes for flexible filtering.

---

## Prefix Group Constants

Prefixes are organized into groups using bitwise flags:

| Constant              | Value | Description                                      |
|-----------------------|-------|--------------------------------------------------|
| `GROUP_SMALL_METRIC`  | 1     | Small metric: m, μ, n, p, f, a, z, y, r, q      |
| `GROUP_MEDIUM_METRIC` | 2     | Medium metric: c, d, da, h                       |
| `GROUP_LARGE_METRIC`  | 4     | Large metric: k, M, G, T, P, E, Z, Y, R, Q      |
| `GROUP_BINARY`        | 8     | Binary: Ki, Mi, Gi, Ti, Pi, Ei, Zi, Yi, Ri, Qi   |

### Combined Group Codes

| Constant         | Components  | Description                       |
|------------------|-------------|-----------------------------------|
| `GROUP_METRIC`      | 1 \| 2 \| 4 | All metric prefixes               |
| `GROUP_ENGINEERING` | 1 \| 4       | Engineering metric (powers of 1000) |
| `GROUP_LARGE`       | 4 \| 8       | Large metric + binary             |
| `GROUP_ALL`         | 1 \| 2 \| 4 \| 8 | All prefixes                      |

---

## Available Prefixes

### Metric Prefixes

| Name   | Symbol | Multiplier | Group  |
| ------ | ------ | ---------- | ------ |
| quecto | q      | 10⁻³⁰      | small  |
| ronto  | r      | 10⁻²⁷      | small  |
| yocto  | y      | 10⁻²⁴      | small  |
| zepto  | z      | 10⁻²¹      | small  |
| atto   | a      | 10⁻¹⁸      | small  |
| femto  | f      | 10⁻¹⁵      | small  |
| pico   | p      | 10⁻¹²      | small  |
| nano   | n      | 10⁻⁹       | small  |
| micro  | μ, u   | 10⁻⁶       | small  |
| milli  | m      | 10⁻³       | small  |
| centi  | c      | 10⁻²       | medium |
| deci   | d      | 10⁻¹       | medium |
| deca   | da     | 10¹        | medium |
| hecto  | h      | 10²        | medium |
| kilo   | k      | 10³        | large  |
| mega   | M      | 10⁶        | large  |
| giga   | G      | 10⁹        | large  |
| tera   | T      | 10¹²       | large  |
| peta   | P      | 10¹⁵       | large  |
| exa    | E      | 10¹⁸       | large  |
| zetta  | Z      | 10²¹       | large  |
| yotta  | Y      | 10²⁴       | large  |
| ronna  | R      | 10²⁷       | large  |
| quetta | Q      | 10³⁰       | large  |

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

#### `static getPrefixes(int $prefixGroup = GROUP_ALL): array`

Get prefixes matching a group code.

```php
// All prefixes
$all = PrefixService::getPrefixes();

// Only metric prefixes
$metric = PrefixService::getPrefixes(PrefixService::GROUP_METRIC);

// Only engineering prefixes (powers of 1000)
$eng = PrefixService::getPrefixes(PrefixService::GROUP_ENGINEERING);

// Binary prefixes only
$binary = PrefixService::getPrefixes(PrefixService::GROUP_BINARY);

// Large metric + binary (for data units)
$large = PrefixService::getPrefixes(PrefixService::GROUP_LARGE);
```

#### `static getBySymbol(string $symbol): ?Prefix`

Find a prefix by its symbol (ASCII or Unicode).

```php
$kilo = PrefixService::getBySymbol('k');
$micro = PrefixService::getBySymbol('μ');
$micro = PrefixService::getBySymbol('u');  // ASCII alternative
$kibi = PrefixService::getBySymbol('Ki');
```

#### `static invert(?Prefix $prefix): ?Prefix`

Get the inverse of a prefix (opposite exponent).

```php
$kilo = PrefixService::getBySymbol('k');   // 10³
$milli = PrefixService::invert($kilo);      // 10⁻³

$mega = PrefixService::getBySymbol('M');   // 10⁶
$micro = PrefixService::invert($mega);      // 10⁻⁶
```

#### `static isValidGroupCode(int $groupCode): bool`

Check if a group code is one of the base codes.

```php
$valid = PrefixService::isValidGroupCode(1);   // true (SMALL_METRIC)
$valid = PrefixService::isValidGroupCode(3);   // false (combined code)
$valid = PrefixService::isValidGroupCode(8);   // true (BINARY)
```

---

## Usage Examples

```php
use Galaxon\Quantities\Services\PrefixService;

// Get all engineering prefixes for a scientific application
$engPrefixes = PrefixService::getPrefixes(PrefixService::GROUP_ENGINEERING);
foreach ($engPrefixes as $prefix) {
    echo "{$prefix->name}: {$prefix->asciiSymbol} = {$prefix->multiplier}\n";
}

// Parse a prefixed symbol
$symbol = 'km';
$prefix = PrefixService::getBySymbol('k');
if ($prefix !== null) {
    $baseSymbol = substr($symbol, strlen($prefix->asciiSymbol));
    $multiplier = $prefix->multiplier;
}

// Find inverse for unit conversion
$source = PrefixService::getBySymbol('M');   // mega (10⁶)
$inverse = PrefixService::invert($source);    // micro (10⁻⁶)
```

---

## See Also

- **[Prefix](../Internal/Prefix.md)** - Prefix class documentation
- **[Unit](../Internal/Unit.md)** - Unit class using prefix groups
- **[Supported Units and Prefixes](Supported%20Units%20and%20Prefixes.md)** - Units with their prefix support
