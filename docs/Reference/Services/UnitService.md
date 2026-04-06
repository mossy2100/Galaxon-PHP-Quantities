# UnitService

Registry of units with lookup, filtering, and loading by system.

**Namespace:** `Galaxon\Quantities\Services`

---

## Overview

The `UnitService` manages the collection of known units. It provides methods to look up units by name, symbol, system, or quantity type; register custom units; load units from built-in systems; and inspect the registry state.

All methods are static. The class uses lazy initialization to build the registry on first access, loading all default units from all systems.

---

## Lookup Methods

### getByName()

```php
public static function getByName(string $name): ?Unit
```

Get a unit by its name (case-sensitive). Only base unit names are registered — prefixed forms like `'kilogram'` or `'millimeter'` are not resolved by this method. Use `getBySymbol()` to look up prefixed units.

**Returns:** `?Unit` — The matching unit, or null if not found.

```php
$meter = UnitService::getByName('meter');
$gram = UnitService::getByName('gram');
UnitService::getByName('kilogram');     // null — use getBySymbol('kg') instead
UnitService::getByName('nonexistent');  // null
```

### getBySymbol()

```php
public static function getBySymbol(string $symbol): ?Unit
```

Get a unit by its symbol. Supports ASCII, Unicode, and alternate symbols, with or without prefixes. Since unit symbols are unique, this returns at most one unit.

Note: prefixed symbols resolve to the underlying base `Unit` — the prefix information is discarded. For example, `getBySymbol('km')` returns the `Unit` object for meter. To work with a prefixed unit as a distinct entity (retaining the prefix and multiplier), use `UnitTerm::parse()`.

**Returns:** `?Unit` — The matching unit, or null if not found.

```php
$meter = UnitService::getBySymbol('m');
$meter = UnitService::getBySymbol('km');      // also returns the meter Unit
$ohm = UnitService::getBySymbol('Ω');         // Unicode
$ohm = UnitService::getBySymbol('ohm');       // ASCII alternative
```

### getBySystem()

```php
public static function getBySystem(UnitSystem $system): array
```

Get all units belonging to the given measurement system.

**Returns:** `list<Unit>`

```php
use Galaxon\Quantities\Internal\UnitSystem;

$siUnits = UnitService::getBySystem(UnitSystem::SI);
$imperialUnits = UnitService::getBySystem(UnitSystem::Imperial);
```

### getByQuantityType()

```php
public static function getByQuantityType(QuantityType $quantityType): array
```

Get all units compatible with the given quantity type.

**Returns:** `list<Unit>`

```php
$lengthType = QuantityTypeService::getByName('length');
$lengthUnits = UnitService::getByQuantityType($lengthType);
```

### getAll()

```php
public static function getAll(): array
```

Get all registered units, keyed by unit name.

**Returns:** `array<string, Unit>`

```php
$allUnits = UnitService::getAll();
foreach ($allUnits as $name => $unit) {
    echo "$name: {$unit->asciiSymbol}\n";
}
```

### getAllSymbols()

```php
public static function getAllSymbols(): array
```

Get all valid unit symbols across all registered units, including base, prefixed, Unicode, and alternate variants.

**Returns:** `list<string>`

```php
$symbols = UnitService::getAllSymbols();
// Includes: 'm', 'km', 'mm', 'nm', 'Hz', 'kHz', 'MHz', 'Ω', 'ohm', etc.
```

---

## Registry Methods

### add()

```php
public static function add(Unit $unit, bool $replaceExisting = false): bool
```

Add a unit to the registry. By default, returns `false` if a unit with the same name already exists. Pass `$replaceExisting = true` to overwrite.

**Returns:** `bool` — True if the unit was added, false if it already existed and was not replaced.

**Throws:** `DomainException` if any of the new unit's symbols conflict with an existing unit's symbols.

```php
use Galaxon\Quantities\Internal\Unit;use Galaxon\Quantities\Internal\UnitSystem;

$furlong = new Unit(
    name: 'furlong',
    asciiSymbol: 'fur',
    dimension: 'L',
    systems: [UnitSystem::Imperial]
);

UnitService::add($furlong);
```

### addFromDefinition()

```php
public static function addFromDefinition(
    string $name,
    array $definition,
    bool $replaceExisting = false
): void
```

Add a unit from an associative array definition (the same format used by built-in quantity types).

**Throws:** `DomainException` if any of the new unit's symbols conflict with an existing unit's symbols.

```php
UnitService::addFromDefinition('furlong', [
    'asciiSymbol' => 'fur',
    'dimension'   => 'L',
    'systems'     => [UnitSystem::Imperial],
]);
```

### remove()

```php
public static function remove(string|Unit $unit): void
```

Remove a unit from the registry by name or by Unit object. Does nothing if the registry is not initialized or the unit doesn't exist.

```php
UnitService::remove('furlong');
UnitService::remove($furlong);  // also works with Unit object
```

### removeAll()

```php
public static function removeAll(): void
```

Remove all units from the registry and clear the loaded-systems list. Unlike `reset()`, the next access will NOT trigger re-initialization.

### reset()

```php
public static function reset(): void
```

Reset the registry to its default initial state. The next access will trigger re-initialization with all default units from all systems.

---

## Loading Methods

### loadSystem()

```php
public static function loadSystem(UnitSystem $system, bool $replaceExisting = false): void
```

Load all units belonging to a specific system of units.

```php
UnitService::loadSystem(UnitSystem::Imperial);
```

### loadAll()

```php
public static function loadAll(bool $replaceExisting = false): void
```

Load all units from all systems.

**Throws:** `DomainException` if any symbol conflicts arise.

```php
UnitService::loadAll();
```

---

## Inspection Methods

### has()

```php
public static function has(string|Unit $unit): bool
```

Check if a unit is in the registry by name or by Unit object.

```php
UnitService::has('meter');   // true
UnitService::has($furlong);  // true/false
```

### count()

```php
public static function count(): int
```

Get the number of units in the registry.

```php
$n = UnitService::count();
```

---

## See Also

- **[Unit](../Internal/Unit.md)** — Unit class documentation
- **[ConversionService](ConversionService.md)** — Conversion registry
- **[QuantityTypeService](QuantityTypeService.md)** — Quantity type registry
- **[UnitSystem](../Internal/UnitSystem.md)** — Measurement system enum
- **[Units](../../Concepts/Units.md)** — Complete unit reference
