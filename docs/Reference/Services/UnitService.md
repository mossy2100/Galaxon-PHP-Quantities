# UnitService

Registry of known units organized by measurement system.

**Namespace:** `Galaxon\Quantities\Services`

---

## Overview

The `UnitService` manages the collection of known units in the system. It provides:

- Creating units from definitions.
- Lookup methods for finding units by symbol or dimension.
- Methods for adding custom units.

---

## Lookup Methods

### getBySymbol()

```php
public static function getBySymbol(string $symbol): ?Unit
```

Find a unit by its symbol (ASCII, Unicode, or alternate).

```php
$meter = UnitService::getBySymbol('m');
$ohm = UnitService::getBySymbol('Ω');  // Unicode
$ohm = UnitService::getBySymbol('ohm');  // ASCII
```

### getBySystem()

```php
public static function getBySystem(UnitSystem $system): array
```

Get all units belonging to a given measurement system.

```php
use Galaxon\Quantities\UnitSystem;

$siUnits = UnitService::getBySystem(UnitSystem::Si);
// Returns all SI units (meter, kilogram, second, etc.)

$imperialUnits = UnitService::getBySystem(UnitSystem::Imperial);
// Returns all Imperial units (foot, pound, etc.)
```

### getAll()

```php
public static function getAll(): array
```

Get all registered units.

```php
$allUnits = UnitService::getAll();
```

### getAllSymbols()

```php
public static function getAllSymbols(): array
```

Get all valid unit symbols, including prefixed variants.

```php
$symbols = UnitService::getAllSymbols();
// Includes: 'm', 'km', 'mm', 'nm', 'Hz', 'kHz', 'MHz', etc.
```

---

## Modification Methods

### add()

```php
public static function add(...): Unit
```

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

### remove()

```php
public static function remove(string $name): void
```

Remove a unit from the registry.

```php
UnitService::remove('furlong');
```

### loadSystem()

```php
public static function loadSystem(UnitSystem $system): void
```

Load all units belonging to a measurement system.

```php
UnitService::loadSystem(UnitSystem::Imperial);
```

### reset()

```php
public static function reset(): void
```

Reset the registry to an empty state.

```php
UnitService::reset();
// Services is now empty - use loadSystem() or add() to populate
```

---

## Inspection Methods

### has()

```php
public static function has(string $name): bool
```

Check if a unit exists in the registry.

```php
if (UnitService::has('meter')) {
    // Unit exists
}
```

### getLoadedSystems()

```php
public static function getLoadedSystems(): array
```

Get the list of measurement systems that have been loaded.

```php
$systems = UnitService::getLoadedSystems();
// Returns: [UnitSystem::Si, UnitSystem::SiAccepted, UnitSystem::Common]
```

---

## Usage Examples

```php
use Galaxon\Quantities\Services\UnitService;
use Galaxon\Quantities\UnitSystem;

// Check what's loaded by default
$systems = UnitService::getLoadedSystems();
// [UnitSystem::Si, UnitSystem::SiAccepted, UnitSystem::Common]

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
- **[Units](../../Concepts/Units.md)** - Complete unit reference
