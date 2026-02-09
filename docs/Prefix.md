# Prefix

Represents an SI or binary prefix for units.

## Overview

The `Prefix` class represents metric (SI) and binary prefixes that can be applied to units of measurement. Prefixes allow expressing very large or very small quantities more conveniently, such as kilometre (km), microsecond (us), or gibibyte (GiB).

Prefixes are organised into groups for flexible assignment to units:
- **Metric prefixes**: Standard SI prefixes from yocto (10^-24) to yotta (10^24)
- **Binary prefixes**: IEC binary prefixes (kibi, mebi, gibi, etc.) for computing
- **Small metric**: Only the smaller metric prefixes (milli, micro, nano, etc.)

Each prefix has both ASCII and Unicode symbol representations. For example, the micro prefix has ASCII symbol 'u' and Unicode symbol 'μ'.

## Properties

### name

```php
public readonly string $name
```

The full name of the prefix (e.g., 'kilo', 'mega', 'micro').

### asciiSymbol

```php
public readonly string $asciiSymbol
```

The ASCII symbol for the prefix (e.g., 'k' for kilo, 'u' for micro). Used for parsing and code compatibility.

### unicodeSymbol

```php
public readonly string $unicodeSymbol
```

The Unicode symbol for the prefix (e.g., 'μ' for micro, 'Ω' for ohm). Used for display purposes.

### multiplier

```php
public readonly float $multiplier
```

The numeric multiplier the prefix represents. For example:
- kilo: 1000 (10^3)
- milli: 0.001 (10^-3)
- kibi: 1024 (2^10)

### groupCode

```php
public readonly int $groupCode
```

Bitwise flag indicating which prefix group(s) this prefix belongs to. Used for determining which prefixes a unit accepts.

## Constructor

### __construct()

```php
public function __construct(
    string $name,
    string $asciiSymbol,
    ?string $unicodeSymbol,
    float $multiplier,
    int $groupCode
)
```

Create a new Prefix instance.

**Parameters:**
- `$name` (string) - The full name of the prefix (e.g., 'kilo')
- `$asciiSymbol` (string) - The ASCII symbol (e.g., 'k')
- `$unicodeSymbol` (?string) - The Unicode symbol, or null if same as ASCII
- `$multiplier` (float) - The numeric multiplier (e.g., 1000)
- `$groupCode` (int) - Bitwise flag for prefix group membership

**Examples:**
```php
// Create the kilo prefix
$kilo = new Prefix('kilo', 'k', null, 1000, Prefix::GROUP_CODE_METRIC);

// Create the micro prefix with different ASCII/Unicode symbols
$micro = new Prefix('micro', 'u', 'μ', 1e-6, Prefix::GROUP_CODE_METRIC);
```

## Inspection Methods

### isEngineering()

```php
public function isEngineering(): bool
```

Check if this prefix is an "engineering" prefix (powers of 1000).

Engineering prefixes are those commonly used in engineering notation: kilo, mega, giga, etc., and milli, micro, nano, etc. Non-engineering prefixes like centi, deci, deca, and hecto return false.

**Returns:**
- `bool` - True if the prefix represents a power of 1000 (or 1/1000)

**Examples:**
```php
$kilo = PrefixUtility::getBySymbol('k');
$kilo->isEngineering(); // true

$centi = PrefixUtility::getBySymbol('c');
$centi->isEngineering(); // false
```

## Comparison Methods

### equal()

```php
public function equal(mixed $other): bool
```

Check if this prefix equals another.

**Parameters:**
- `$other` (mixed) - The value to compare with

**Returns:**
- `bool` - True if both prefixes have the same ASCII symbol

**Examples:**
```php
$kilo1 = PrefixUtility::getBySymbol('k');
$kilo2 = PrefixUtility::getBySymbol('k');
$kilo1->equal($kilo2); // true
```

## String Methods

### format()

```php
public function format(bool $ascii = false): string
```

Format the prefix as a string.

**Parameters:**
- `$ascii` (bool) - If true, return ASCII symbol; if false (default), return Unicode symbol

**Returns:**
- `string` - The formatted prefix symbol

**Examples:**
```php
$micro = PrefixUtility::getBySymbol('u');
$micro->format();      // 'μ'
$micro->format(true);  // 'u'
```

### __toString()

```php
public function __toString(): string
```

Convert the prefix to a string using the Unicode symbol.

**Returns:**
- `string` - The Unicode symbol

## Constants

The class defines group code constants for prefix categorisation:

| Constant | Value | Description |
|----------|-------|-------------|
| `GROUP_CODE_METRIC` | 1 | Full range of metric (SI) prefixes |
| `GROUP_CODE_BINARY` | 2 | Binary (IEC) prefixes for computing |
| `GROUP_CODE_SMALL_METRIC` | 4 | Only small metric prefixes (milli, micro, etc.) |

## Usage Examples

### Working with Prefix Groups

```php
use Galaxon\Quantities\Prefix;
use Galaxon\Quantities\Utility\PrefixUtility;

// Get all metric prefixes
$metricPrefixes = PrefixUtility::getPrefixes(Prefix::GROUP_CODE_METRIC);

// Get prefixes that work for both metric and binary
$bothGroups = Prefix::GROUP_CODE_METRIC | Prefix::GROUP_CODE_BINARY;
$allPrefixes = PrefixUtility::getPrefixes($bothGroups);
```

### Creating Prefixed Units

```php
use Galaxon\Quantities\UnitTerm;
use Galaxon\Quantities\Utility\PrefixUtility;

// Create a kilometre
$kilo = PrefixUtility::getBySymbol('k');
$km = new UnitTerm('m', $kilo);

// The multiplier accounts for the prefix
echo $km->multiplier; // 1000
```

## See Also

- **[UnitTerm](UnitTerm.md)** - Uses prefixes when representing prefixed units
- **[Unit](Unit.md)** - Defines which prefixes a unit accepts
- **[PrefixUtility](Utility/PrefixUtility.md)** - Helper functions for working with prefixes
