# PrefixService

Registry for SI and binary prefixes.

**Namespace:** `Galaxon\Quantities`

---

## Overview

The `PrefixService` provides access to metric prefixes (milli, kilo, mega, etc.) and binary prefixes (kibi, mebi, etc.) organized by group codes for flexible filtering.

---

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
- **[2.6_SupportedUnits](../../DeveloperGuide/2.6_SupportedUnits.md)** - Units with their prefix support
