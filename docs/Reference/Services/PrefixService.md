# PrefixService

Utility class for working with SI and binary prefixes.

**Namespace:** `Galaxon\Quantities\Services`

---

## Overview

The `PrefixService` provides access to metric prefixes (milli, kilo, mega, etc.) and binary prefixes (kibi, mebi, etc.) organized by group codes for flexible filtering.

All methods are static. The class uses lazy initialization to build the prefix list on first access.

---

## Constants

### Group codes

Base group codes identify individual prefix groups. These can be combined with bitwise OR for flexible filtering.

| Constant | Value | Description |
|---|---|---|
| `GROUP_SMALL_METRIC` | 1 | Small metric prefixes (quecto through milli). |
| `GROUP_MEDIUM_METRIC` | 2 | Medium metric prefixes (centi, deci, deca, hecto). |
| `GROUP_LARGE_METRIC` | 4 | Large metric prefixes (kilo through quetta). |
| `GROUP_BINARY` | 8 | Binary prefixes (kibi through quebi). |

### Combined group codes

| Constant | Value | Description |
|---|---|---|
| `GROUP_METRIC` | 7 | All metric prefixes (small + medium + large). |
| `GROUP_ENGINEERING` | 5 | Engineering prefixes (small + large metric, i.e. powers of 1000). |
| `GROUP_LARGE` | 12 | Large metric + binary. |
| `GROUP_ALL` | 15 | All prefixes (metric + binary). |

---

## Lookup methods

### getPrefixes()

```php
public static function getPrefixes(int $prefixGroup = self::GROUP_ALL): array
```

Return an array of prefixes matching a group code comprising bitwise flags. Results are sorted by multiplier in ascending order.

**Returns:** `list<Prefix>`

```php
// All prefixes.
$all = PrefixService::getPrefixes();

// Only metric prefixes.
$metric = PrefixService::getPrefixes(PrefixService::GROUP_METRIC);

// Only engineering prefixes (powers of 1000).
$eng = PrefixService::getPrefixes(PrefixService::GROUP_ENGINEERING);

// Binary prefixes only.
$binary = PrefixService::getPrefixes(PrefixService::GROUP_BINARY);

// Large metric + binary (for data units).
$large = PrefixService::getPrefixes(PrefixService::GROUP_LARGE);

// No prefixes.
$none = PrefixService::getPrefixes(0);  // []
```

### getBySymbol()

```php
public static function getBySymbol(string $symbol): ?Prefix
```

Get a prefix by its symbol. Supports both ASCII and Unicode symbols. Case-sensitive.

**Returns:** `?Prefix` — The matching prefix, or null if not found.

```php
$kilo = PrefixService::getBySymbol('k');    // kilo (1e3)
$micro = PrefixService::getBySymbol('μ');   // micro (1e-6)
$micro = PrefixService::getBySymbol('u');   // micro (ASCII alternative)
$kibi = PrefixService::getBySymbol('Ki');   // kibi (2^10)
$mega = PrefixService::getBySymbol('M');    // mega (1e6)
$milli = PrefixService::getBySymbol('m');   // milli (1e-3)
PrefixService::getBySymbol('X');            // null (not found)
```

---

## Registry methods

### reset()

```php
public static function reset(): void
```

Reset the prefixes cache. The next access will trigger re-initialization. Primarily useful for testing.

### removeAll()

```php
public static function removeAll(): void
```

Clear the prefixes cache. Unlike `reset()`, the next access will NOT trigger re-initialization — `init()` must be called manually. Used internally during initialization.

---

## Transformation methods

### invert()

```php
public static function invert(?Prefix $prefix): ?Prefix
```

Get the inverse of a prefix (the prefix with the reciprocal multiplier). Returns null if null is passed.

**Returns:** `?Prefix`

**Throws:** `DomainException` if no inverse could be found (e.g. binary prefixes have no inverse).

```php
$kilo = PrefixService::getBySymbol('k');    // 10^3
$milli = PrefixService::invert($kilo);       // 10^-3

$mega = PrefixService::getBySymbol('M');    // 10^6
$micro = PrefixService::invert($mega);       // 10^-6

PrefixService::invert(null);                 // null

$kibi = PrefixService::getBySymbol('Ki');
PrefixService::invert($kibi);               // throws DomainException
```

---

## Usage examples

```php
use Galaxon\Quantities\Services\PrefixService;

// Get all prefixes (metric + binary).
$allPrefixes = PrefixService::getPrefixes(PrefixService::GROUP_ALL);

// Get all metric prefixes.
$metricPrefixes = PrefixService::getPrefixes(PrefixService::GROUP_METRIC);

// Get all engineering prefixes for a scientific application, and list them.
$engPrefixes = PrefixService::getPrefixes(PrefixService::GROUP_ENGINEERING);
foreach ($engPrefixes as $prefix) {
    echo "{$prefix->name}: {$prefix->asciiSymbol} = {$prefix->multiplier}\n";
}

// Parse a prefixed symbol.
$symbol = 'km';
$prefix = PrefixService::getBySymbol('k');
if ($prefix !== null) {
    $baseSymbol = substr($symbol, strlen($prefix->asciiSymbol));
    $multiplier = $prefix->multiplier;
}

// Find inverse for unit conversion.
$source = PrefixService::getBySymbol('M');   // mega (10^6)
$inverse = PrefixService::invert($source);    // micro (10^-6)
```

---

## See also

- **[Prefix](../Internal/Prefix.md)** - Prefix class documentation
- **[Unit](../Internal/Unit.md)** - Unit class using prefix groups
- **[Units](../../Concepts/Units.md)** - Units with their prefix support
