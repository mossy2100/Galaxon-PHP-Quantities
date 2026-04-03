# Unit

Represents a unit of measurement.

---

## Overview

The `Unit` class represents a single unit of measurement such as meter, gram, or hertz. Each unit has a name, symbols (ASCII and Unicode), a dimension code, and metadata about which prefixes it accepts and which measurement systems it belongs to.

The class implements `UnitInterface` and uses the `Equatable` trait for value-based equality comparisons.

### Key Features

- Dual symbol support (ASCII and Unicode)
- Optional alternate symbol for parser compatibility
- Configurable prefix acceptance via group codes
- Measurement system classification
- Validation of symbol formats

---

## Properties

### name

```php
private(set) string $name
```

The unit name (e.g., `'meter'`, `'gram'`, `'hertz'`).

### asciiSymbol

```php
private(set) string $asciiSymbol
```

The ASCII unit symbol (e.g., `'m'`, `'g'`, `'Hz'`). Used for parsing and code compatibility.

### unicodeSymbol

```php
private(set) string $unicodeSymbol
```

The Unicode symbol (e.g., `'Ω'` for ohm, `'°'` for degree). Used for display purposes. Defaults to the ASCII symbol if not provided.

### alternateSymbol

```php
private(set) ?string $alternateSymbol
```

An additional symbol accepted by the parser. This can only be a single ASCII letter or special character, and cannot accept prefixes. Examples: `'` for arcminutes (alternate to the prime symbol `′`) and `"` for arcseconds (alternate to the double-prime symbol `″`). Not used for output. Default: `null`.

### dimension

```php
private(set) string $dimension
```

The dimension code (e.g., `'L'`, `'M'`, `'T-1'`). Normalized via `DimensionService::normalize()` at construction time.

### prefixGroup

```php
private(set) int $prefixGroup
```

Bitwise flags indicating which prefixes are allowed (0 if none). See `PrefixService::GROUP_*` constants.

### systems

```php
private(set) array $systems
```

The measurement systems this unit belongs to.

Type: `list<UnitSystem>`

### allowedPrefixes

```php
public array $allowedPrefixes { get; }
```

List of `Prefix` objects allowed for this unit, based on the `prefixGroup` flags. Retrieved via `PrefixService::getPrefixes()`.

Type: `list<Prefix>`

### symbols

```php
private(set) array $symbols { get; }
```

All symbol variants for the unit, including ASCII, Unicode, alternate, and all prefixed versions. Lazy-loaded and cached on first access.

Keyed by the full symbol string (e.g., `'km'`, `'μm'`, `'°C'`). Each value is a tuple of `[unitSymbol, prefixSymbol]` where `prefixSymbol` is `null` for unprefixed variants.

Type: `array<string, array{string, ?string}>`

### quantityType

```php
public ?QuantityType $quantityType { get; }
```

The quantity type this unit is for (e.g., the `QuantityType` for length), or `null` if the dimension has no registered quantity type. Resolved via `QuantityTypeService::getByDimension()`.

---

## Constructor

### \_\_construct()

```php
public function __construct(
    string $name,
    string $asciiSymbol,
    string $dimension,
    array $systems = [UnitSystem::Custom],
    int $prefixGroup = 0,
    ?string $unicodeSymbol = null,
    ?string $alternateSymbol = null
)
```

Create a new Unit instance.

**Parameters:**
- `$name` (string) - The unit name (e.g., `'meter'`). Must be 1-3 words of ASCII letters.
- `$asciiSymbol` (string) - The ASCII symbol (e.g., `'m'`). Must be ASCII letters only (empty allowed for dimensionless).
- `$dimension` (string) - The dimension code (e.g., `'L'`). Normalized via `DimensionService::normalize()`.
- `$systems` (list\<UnitSystem\>) - The measurement systems. Default: `[UnitSystem::Custom]`.
- `$prefixGroup` (int) - Bitwise flags for allowed prefixes. Default: `0`.
- `$unicodeSymbol` (?string) - The Unicode symbol, or `null` to use the ASCII symbol. Default: `null`.
- `$alternateSymbol` (?string) - Additional accepted symbol. Default: `null`.

**Throws:**
- [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) - If any symbol or name contains invalid characters.
- `DomainException` - If the systems array is empty or the prefix group is out of range.
- `InvalidArgumentException` - If the systems array contains non-UnitSystem values.

**Examples:**
```php
use Galaxon\Quantities\Internal\Unit;
use Galaxon\Quantities\Services\PrefixService;
use Galaxon\Quantities\UnitSystem;

// Basic SI unit
$meter = new Unit(
    name: 'meter',
    asciiSymbol: 'm',
    dimension: 'L',
    systems: [UnitSystem::Si],
    prefixGroup: PrefixService::GROUP_METRIC
);

// Unit with Unicode symbol
$ohm = new Unit(
    name: 'ohm',
    asciiSymbol: 'ohm',
    dimension: 'T-3L2MI-2',
    systems: [UnitSystem::Si],
    prefixGroup: PrefixService::GROUP_METRIC,
    unicodeSymbol: 'Ω'
);
```

---

## Factory Methods

### parse()

```php
public static function parse(string $symbol): self
```

Parse a unit symbol and return the matching Unit from the registry.

**Parameters:**
- `$symbol` (string) - The unit symbol to parse (e.g., `'m'`, `'kg'`, `'Hz'`, `'Ω'`).

**Returns:** `Unit`

**Throws:**
- [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) - If the symbol contains invalid characters.
- [`UnknownUnitException`](../Exceptions/UnknownUnitException.md) - If the symbol is not recognized.

---

## Inspection Methods

### belongsToSystem()

```php
public function belongsToSystem(UnitSystem $system): bool
```

Check if this unit belongs to a specific measurement system.

**Parameters:**
- `$system` (UnitSystem) - The system to check.

### isSi()

```php
public function isSi(): bool
```

Check if this unit belongs to the SI system.

### isBase()

```php
public function isBase(): bool
```

Check if this unit is a base unit. A base unit has a dimension code of at most one character (e.g., `'L'`, `'M'`, `'T'`, or `''` for dimensionless).

### acceptsPrefix()

```php
public function acceptsPrefix(string|Prefix $prefix): bool
```

Check if a specific prefix is allowed for this unit.

**Parameters:**
- `$prefix` (string|Prefix) - The prefix symbol or object to check.

**Examples:**
```php
$meter = Unit::parse('m');
$meter->acceptsPrefix('k');  // true (kilo)
$meter->acceptsPrefix('Ki'); // false (binary prefix)
```

---

## Comparison Methods

### equal()

```php
public function equal(mixed $other): bool
```

Check if this unit equals another. Compares by ASCII symbol.

**Parameters:**
- `$other` (mixed) - The value to compare.

**Returns:** `bool` - True if both are `Unit` instances with the same ASCII symbol.

---

## Conversion Methods

### format()

```php
public function format(bool $ascii = false): string
```

Format the unit as a string.

**Parameters:**
- `$ascii` (bool) - If `true`, return ASCII symbol; if `false` (default), return Unicode symbol.

**Returns:** `string` - The formatted unit symbol.

### \_\_toString()

```php
public function __toString(): string
```

Convert the unit to a string using the Unicode symbol.

---

## Usage Examples

### Accessing Unit Properties

```php
use Galaxon\Quantities\Services\UnitService;

$unit = UnitService::getBySymbol('N');

echo $unit->name;        // 'newton'
echo $unit->asciiSymbol; // 'N'
echo $unit->dimension;   // 'MLT-2'
```

### Working with Prefixes

```php
use Galaxon\Quantities\Services\UnitService;

$meter = UnitService::getBySymbol('m');

// Get all allowed prefixes
$prefixes = $meter->allowedPrefixes;
foreach ($prefixes as $prefix) {
    echo $prefix->asciiSymbol . $meter->asciiSymbol . "\n";
    // km, mm, um, nm, etc.
}

// Get all symbol variants
$symbols = $meter->symbols;
// ['m' => [...], 'km' => [...], 'mm' => [...], ...]
```

---

## See Also

- **[UnitTerm](UnitTerm.md)** - Unit with prefix and exponent.
- **[DerivedUnit](DerivedUnit.md)** - Compound unit representation.
- **[UnitInterface](UnitInterface.md)** - Interface for all unit types.
- **[Prefix](Prefix.md)** - SI and binary prefix representation.
- **[UnitService](../Services/UnitService.md)** - Registry for looking up units.
- **[DimensionService](../Services/DimensionService.md)** - Utilities for working with dimension codes.
- **[UnitSystem](../UnitSystem.md)** - Measurement system classification.
