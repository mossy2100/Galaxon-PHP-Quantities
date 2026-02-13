# Unit

Represents a unit of measurement.

## Overview

The `Unit` class represents a single unit of measurement such as metre, gram, or hertz. Each unit has a name, symbols (ASCII and Unicode), a dimension code, and metadata about which prefixes it accepts and which measurement systems it belongs to.

Units can be "expandable", meaning they can be decomposed into more fundamental units. For example, the newton (N) expands to kg\*m/s2. Expansion information is stored directly on the Unit via the `expansionUnitSymbol` and `expansionValue` properties.

The class implements `UnitInterface` and uses the `Equatable` trait for value-based equality comparisons.

### Key Features

- Dual symbol support (ASCII and Unicode)
- Optional alternate symbol for parser compatibility
- Configurable prefix acceptance via group codes
- Measurement system classification
- Expansion support for derived SI units
- Validation of symbol formats

## Properties

### name

```php
private(set) string $name
```

The unit name (e.g., 'metre', 'gram', 'hertz').

### asciiSymbol

```php
private(set) string $asciiSymbol
```

The ASCII unit symbol (e.g., 'm', 'g', 'Hz'). Used for parsing and code compatibility.

### unicodeSymbol

```php
private(set) string $unicodeSymbol
```

The Unicode symbol (e.g., 'Ω' for ohm, '°' for degree). Used for display purposes. Defaults to the ASCII symbol if not provided.

### alternateSymbol

```php
private(set) ?string $alternateSymbol
```

An additional symbol accepted by the parser. Cannot accept prefixes.

### dimension

```php
private(set) string $dimension
```

The dimension code (e.g., 'L', 'M', 'T-1'). See [Dimensions](Dimensions.md).

### prefixGroup

```php
private(set) int $prefixGroup
```

Bitwise flags indicating which prefixes are allowed (0 if none).

### systems

```php
private(set) array $systems
```

The measurement systems this unit belongs to (list of System enum values).

### expansionUnitSymbol

```php
private(set) ?string $expansionUnitSymbol
```

The unit symbol this unit expands to (e.g., 'kg\*m/s2' for newton). Null for non-expandable units.

### expansionValue

```php
private(set) ?float $expansionValue
```

The conversion factor for the expansion (e.g., 1.0 for newton to kg\*m/s2). Defaults to 1.0 when `expansionUnitSymbol` is set. Null for non-expandable units.

## Property Hooks

### expansionUnit

```php
public ?DerivedUnit $expansionUnit { get; }
```

The derived unit for the expansion, lazily parsed from `expansionUnitSymbol`. Returns `null` for non-expandable units.

For example, for newton this returns a DerivedUnit object representing `kg*m/s2`.

### allowedPrefixes

```php
public array $allowedPrefixes { get; }
```

List of Prefix objects allowed for this unit, based on the `prefixGroup` flags.

### symbols

```php
public array $symbols { get; }
```

All symbol variants for the unit, including ASCII, Unicode, alternate, and all prefixed versions.

## Constructor

### __construct()

```php
public function __construct(
    string $name,
    string $asciiSymbol,
    ?string $unicodeSymbol,
    string $dimension,
    int $prefixGroup = 0,
    ?string $alternateSymbol = null,
    array $systems = [],
    ?string $expansionUnitSymbol = null,
    ?float $expansionValue = null
)
```

Create a new Unit instance.

**Parameters:**
- `$name` (string) - The unit name (e.g., 'metre')
- `$asciiSymbol` (string) - The ASCII symbol (e.g., 'm')
- `$unicodeSymbol` (?string) - The Unicode symbol, or null if same as ASCII
- `$dimension` (string) - The dimension code (e.g., 'L')
- `$prefixGroup` (int) - Bitwise flags for allowed prefixes (default: 0)
- `$alternateSymbol` (?string) - Additional accepted symbol (default: null)
- `$systems` (array) - List of System enum values (default: [])
- `$expansionUnitSymbol` (?string) - The unit symbol this unit expands to (default: null)
- `$expansionValue` (?float) - The expansion conversion factor, defaults to 1.0 when expansionUnitSymbol is set (default: null)

**Throws:**
- `FormatException` - If unit symbols contain invalid characters
- `DomainException` - If the dimension code is invalid

**Examples:**

```php
use Galaxon\Quantities\Internal\Unit;use Galaxon\Quantities\Registry\PrefixRegistry;use Galaxon\Quantities\System;

// Basic SI unit
$metre = new Unit(
    name: 'metre',
    asciiSymbol: 'm',
    unicodeSymbol: null,
    dimension: 'L',
    prefixGroup: PrefixRegistry::GROUP_METRIC,
    systems: [System::Si]
);

// Expandable derived unit
$newton = new Unit(
    name: 'newton',
    asciiSymbol: 'N',
    unicodeSymbol: null,
    dimension: 'MLT-2',
    prefixGroup: PrefixRegistry::GROUP_METRIC,
    systems: [System::Si],
    expansionUnitSymbol: 'kg*m/s2'
);
```

## Inspection Methods

### belongsToSystem()

```php
public function belongsToSystem(System $system): bool
```

Check if this unit belongs to a specific measurement system.

**Parameters:**
- `$system` (System) - The system to check

**Returns:**
- `bool` - True if the unit belongs to the system

### isSi()

```php
public function isSi(): bool
```

Check if this unit belongs to the SI system.

**Returns:**
- `bool` - True if the unit is an SI unit

### isBase()

```php
public function isBase(): bool
```

Check if this unit is a base unit. A base unit has a single-character dimension code (e.g., 'L', 'M', 'T').

**Returns:**
- `bool` - True if the unit is a base unit

### isExpandable()

```php
public function isExpandable(): bool
```

Check if this unit can be expanded into base units (e.g., newton expands to kg\*m/s2).

**Returns:**
- `bool` - True if the unit has an expansion unit symbol defined

### acceptsPrefix()

```php
public function acceptsPrefix(string|Prefix $prefix): bool
```

Check if a specific prefix is allowed for this unit.

**Parameters:**
- `$prefix` (string|Prefix) - The prefix to check

**Returns:**
- `bool` - True if the prefix is allowed

**Examples:**
```php
$metre = Unit::parse('m');
$metre->acceptsPrefix('k'); // true (kilo)
$metre->acceptsPrefix('c'); // true (centi)
```

## String Methods

### regex()

```php
public static function regex(): string
```

Get the regular expression pattern for matching a unit symbol.

**Returns:**
- `string` - The regex pattern (without delimiters or anchors)

### parse()

```php
public static function parse(string $symbol): self
```

Parse a unit symbol and return the matching Unit.

**Parameters:**
- `$symbol` (string) - The unit symbol to parse

**Returns:**
- `self` - The matching Unit

**Throws:**
- `FormatException` - If the symbol contains invalid characters
- `DomainException` - If the symbol is not recognized

**Examples:**
```php
$metre = Unit::parse('m');
$newton = Unit::parse('N');
$degree = Unit::parse('deg');
```

### format()

```php
public function format(bool $ascii = false): string
```

Format the unit as a string.

**Parameters:**
- `$ascii` (bool) - If true, return ASCII symbol; if false (default), return Unicode symbol

**Returns:**
- `string` - The formatted unit symbol

### __toString()

```php
public function __toString(): string
```

Convert the unit to a string using the Unicode symbol.

**Returns:**
- `string` - The Unicode symbol

## Comparison Methods

### equal()

```php
public function equal(mixed $other): bool
```

Check if this unit equals another.

**Parameters:**
- `$other` (mixed) - The value to compare

**Returns:**
- `bool` - True if both units have the same ASCII symbol

## Static Helper Methods

### isValidNonLetterSymbol()

```php
public static function isValidNonLetterSymbol(string $symbol): bool
```

Check if a string is a single non-letter, non-digit symbol.

**Parameters:**
- `$symbol` (string) - The string to check

**Returns:**
- `bool` - True if it's a valid non-letter symbol (like %, ", ')

### isValidAsciiSymbol()

```php
public static function isValidAsciiSymbol(string $symbol): bool
```

Check if a string is a valid ASCII unit symbol (letters only, up to three words separated by spaces, or a single non-letter symbol).

**Parameters:**
- `$symbol` (string) - The string to check

**Returns:**
- `bool` - True if it's a valid ASCII symbol

### isValidUnicodeSymbol()

```php
public static function isValidUnicodeSymbol(string $symbol): bool
```

Check if a string is a valid Unicode unit symbol.

**Parameters:**
- `$symbol` (string) - The string to check

**Returns:**
- `bool` - True if it's a valid Unicode symbol

## Usage Examples

### Accessing Unit Properties

```php
use Galaxon\Quantities\Registry\UnitRegistry;

$unit = UnitRegistry::getBySymbol('N');

echo $unit->name;       // 'newton'
echo $unit->dimension;  // 'MLT-2'
echo $unit->isSi();     // true

// Check expansion
if ($unit->isExpandable()) {
    echo $unit->expansionUnit->asciiSymbol; // 'kg*m/s2'
}
```

### Working with Prefixes

```php
use Galaxon\Quantities\Registry\UnitRegistry;

$metre = UnitRegistry::getBySymbol('m');

// Get all allowed prefixes
$prefixes = $metre->allowedPrefixes;
foreach ($prefixes as $prefix) {
    echo $prefix->asciiSymbol . $metre->asciiSymbol . "\n";
    // km, mm, um, nm, etc.
}

// Get all symbol variants
$symbols = $metre->symbols;
// ['m', 'km', 'mm', 'um', 'nm', ...]
```

## See Also

- **[UnitTerm](UnitTerm.md)** - Unit with prefix and exponent
- **[DerivedUnit](DerivedUnit.md)** - Compound unit representation
- **[UnitInterface](UnitInterface.md)** - Interface for all unit types
- **[UnitRegistry](../Registry/UnitRegistry.md)** - Registry for looking up units
- **[System](../System.md)** - Measurement system classification
