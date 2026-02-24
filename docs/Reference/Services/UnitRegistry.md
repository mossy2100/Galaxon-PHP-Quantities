# UnitService

Registry of known units organized by measurement system.

**Namespace:** `Galaxon\Quantities\Services`

---

## Overview

The `UnitService` manages the collection of known units in the system. It provides:

- Lazy loading of units by measurement system
- Lookup methods for finding units by symbol or dimension
- Methods for adding custom units
- Integration with `ConversionService` for automatic conversion loading

By default, SI, SI Accepted, and Common units are loaded automatically on first access.

---

## Loading Systems

### Default Behavior

On first access, the registry automatically loads:
- `UnitSystem::Si` - SI base and derived units
- `UnitSystem::SiAccepted` - Units accepted for use with SI
- `UnitSystem::Common` - Common units (percentages, data, etc.)

### Loading Additional Systems

```php
use Galaxon\Quantities\Services\UnitService;
use Galaxon\Quantities\UnitSystem;

// Load Imperial and US Customary units
UnitService::loadBySystem(UnitSystem::Imperial);
UnitService::loadBySystem(UnitSystem::UsCustomary);

// Load other systems as needed
UnitService::loadBySystem(UnitSystem::Scientific);
UnitService::loadBySystem(UnitSystem::Nautical);
```

---

## Methods

### Lookup Methods

#### `static getBySymbol(string $symbol): ?Unit`

Find a unit by its symbol (ASCII, Unicode, or alternate).

```php
$meter = UnitService::getBySymbol('m');
$ohm = UnitService::getBySymbol('Ω');  // Unicode
$ohm = UnitService::getBySymbol('ohm');  // ASCII
```

#### `static getBySystem(UnitSystem $system): array`

Get all units belonging to a given measurement system.

```php
use Galaxon\Quantities\UnitSystem;

$siUnits = UnitService::getBySystem(UnitSystem::Si);
// Returns all SI units (meter, kilogram, second, etc.)

UnitService::loadSystem(UnitSystem::Imperial);
$imperialUnits = UnitService::getBySystem(UnitSystem::Imperial);
// Returns all Imperial units (foot, pound, etc.)
```

#### `static getAll(): array`

Get all registered units.

```php
$allUnits = UnitService::getAll();
```

#### `static getAllSymbols(): array`

Get all valid unit symbols, including prefixed variants.

```php
$symbols = UnitService::getAllSymbols();
// Includes: 'm', 'km', 'mm', 'nm', 'Hz', 'kHz', 'MHz', etc.
```

### Modification Methods

#### `static add(...): Unit`

Add a custom unit to the registry.

```php
$unit = UnitService::add(
    name: 'furlong',
    asciiSymbol: 'fur',
    unicodeSymbol: null,
    dimension: 'L',
    systems: [UnitSystem::Imperial]
);
```

#### `static remove(string $name): void`

Remove a unit from the registry.

```php
UnitService::remove('furlong');
```

#### `static loadSystem(UnitSystem $system): void`

Load all units belonging to a measurement system.

```php
UnitService::loadSystem(UnitSystem::Imperial);
```

#### `static reset(): void`

Reset the registry to an empty state.

```php
UnitService::reset();
// Services is now empty - use loadSystem() or add() to populate
```

### Inspection Methods

#### `static has(string $name): bool`

Check if a unit exists in the registry.

```php
if (UnitService::has('meter')) {
    // Unit exists
}
```

#### `static getLoadedSystems(): array`

Get the list of measurement systems that have been loaded.

```php
$systems = UnitService::getLoadedSystems();
// Returns: [UnitSystem::Si, UnitSystem::SiAccepted, UnitSystem::Common]
```

---

## Integration with ConversionService

When a system is loaded via `loadSystem()`, the `ConversionService` is automatically updated with relevant conversions:

```php
// This loads both units AND conversions
UnitService::loadSystem(UnitSystem::Imperial);

// Conversions involving Imperial units are now available
$feet = Length::convert(1, 'm', 'ft');
```

---

## Usage Examples

```php
use Galaxon\Quantities\Services\UnitService;
use Galaxon\Quantities\UnitSystem;

// Check what's loaded by default
$systems = UnitService::getLoadedSystems();
// [UnitSystem::Si, UnitSystem::SiAccepted, UnitSystem::Common]

// Load Imperial for feet, pounds, etc.
UnitService::loadBySystem(UnitSystem::Imperial);

// Find a unit
$foot = UnitService::getBySymbol('ft');
echo $foot->name;  // 'foot'

// Custom unit
UnitService::add(new Unit('cubit', 'cbt', 'L', [UnitSystem::Common]));
```

---

## See Also

- **[Unit](../Internal/Unit.md)** - Unit class documentation
- **[ConversionService](ConversionService.md)** - Conversion registry
- **[QuantityTypeService](QuantityTypeService.md)** - Quantity type registry
- **[UnitSystem](../UnitSystem.md)** - Measurement system enum
- **[Supported Units and Prefixes](Supported%20Units%20and%20Prefixes.md)** - Complete unit reference
