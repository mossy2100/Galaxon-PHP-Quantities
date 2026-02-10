# UnitRegistry

Registry of known units organized by measurement system.

**Namespace:** `Galaxon\Quantities\Registry`

---

## Overview

The `UnitRegistry` manages the collection of known units in the system. It provides:

- Lazy loading of units by measurement system
- Lookup methods for finding units by symbol or dimension
- Methods for adding custom units
- Integration with `ConversionRegistry` for automatic conversion loading

By default, SI, SI Accepted, and Common units are loaded automatically on first access.

---

## Loading Systems

### Default Behavior

On first access, the registry automatically loads:
- `System::Si` - SI base and derived units
- `System::SiAccepted` - Units accepted for use with SI
- `System::Common` - Common units (percentages, data, etc.)

### Loading Additional Systems

```php
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;

// Load Imperial and US Customary units
UnitRegistry::loadSystem(System::Imperial);
UnitRegistry::loadSystem(System::UsCustomary);

// Load other systems as needed
UnitRegistry::loadSystem(System::Astronomical);
UnitRegistry::loadSystem(System::Nautical);
```

---

## Methods

### Lookup Methods

#### `static getBySymbol(string $symbol): ?Unit`

Find a unit by its symbol (ASCII, Unicode, or alternate).

```php
$metre = UnitRegistry::getBySymbol('m');
$ohm = UnitRegistry::getBySymbol('Ω');  // Unicode
$ohm = UnitRegistry::getBySymbol('ohm');  // ASCII
```

#### `static getByDimension(string $dimension): array`

Get all units matching a dimension code.

```php
$lengthUnits = UnitRegistry::getByDimension('L');
// Returns: ['metre' => Unit, 'inch' => Unit, ...]
```

#### `static getAll(): array`

Get all registered units.

```php
$allUnits = UnitRegistry::getAll();
```

#### `static getExpandable(): array`

Get all units that have an expansion (derived SI units).

```php
$expandable = UnitRegistry::getExpandable();
// Returns units like newton, joule, pascal, etc.
```

#### `static getAllSymbols(): array`

Get all valid unit symbols, including prefixed variants.

```php
$symbols = UnitRegistry::getAllSymbols();
// Includes: 'm', 'km', 'mm', 'nm', 'Hz', 'kHz', 'MHz', etc.
```

### Modification Methods

#### `static add(...): Unit`

Add a custom unit to the registry.

```php
$unit = UnitRegistry::add(
    name: 'furlong',
    asciiSymbol: 'fur',
    unicodeSymbol: null,
    quantityType: 'length',
    dimension: 'L',
    prefixGroup: 0,
    alternateSymbol: null,
    expansionUnitSymbol: null,
    expansionValue: null,
    systems: [System::Imperial]
);
```

#### `static remove(string $name): void`

Remove a unit from the registry.

```php
UnitRegistry::remove('furlong');
```

#### `static loadSystem(System $system): void`

Load all units belonging to a measurement system.

```php
UnitRegistry::loadSystem(System::Imperial);
```

#### `static reset(): void`

Reset the registry to an empty state.

```php
UnitRegistry::reset();
// Registry is now empty - use loadSystem() or add() to populate
```

### Inspection Methods

#### `static has(string $name): bool`

Check if a unit exists in the registry.

```php
if (UnitRegistry::has('metre')) {
    // Unit exists
}
```

#### `static getLoadedSystems(): array`

Get the list of measurement systems that have been loaded.

```php
$systems = UnitRegistry::getLoadedSystems();
// Returns: [System::Si, System::SiAccepted, System::Common]
```

---

## Integration with ConversionRegistry

When a system is loaded via `loadSystem()`, the `ConversionRegistry` is automatically updated with relevant conversions:

```php
// This loads both units AND conversions
UnitRegistry::loadSystem(System::Imperial);

// Conversions involving Imperial units are now available
$feet = Length::convert(1, 'm', 'ft');
```

---

## Usage Examples

```php
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;

// Check what's loaded by default
$systems = UnitRegistry::getLoadedSystems();
// [System::Si, System::SiAccepted, System::Common]

// Load Imperial for feet, pounds, etc.
UnitRegistry::loadSystem(System::Imperial);

// Find a unit
$foot = UnitRegistry::getBySymbol('ft');
echo $foot->name;  // 'foot'

// Get all length units
$lengths = UnitRegistry::getByDimension('L');

// Custom unit (not recommended - use QuantityType classes instead)
UnitRegistry::add(
    'cubit',
    'cbt',
    null,
    'length',
    'L',
    systems: [System::Common]
);
```

---

## See Also

- **[Unit](../Unit.md)** - Unit class documentation
- **[ConversionRegistry](ConversionRegistry.md)** - Conversion registry
- **[QuantityTypeRegistry](QuantityTypeRegistry.md)** - Quantity type registry
- **[System](../System.md)** - Measurement system enum
- **[SupportedUnits](../SupportedUnits.md)** - Complete unit reference
