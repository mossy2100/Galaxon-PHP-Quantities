# QuantityType

Represents a type of physical quantity with its associated metadata.

---

## Overview

The `QuantityType` class defines the metadata for a category of physical quantities, such as Length, Mass, Time, or Force. Each quantity type has a unique name, a dimensional code, and an associated Quantity subclass.

Quantity types are registered with the `QuantityTypeService` and are used to:
- Map dimension codes to human-readable names
- Link dimension codes to strongly-typed Quantity subclasses
- Load unit and conversion definitions from the associated class
- Provide access to the converter for this dimension

The class uses PHP 8.4 property hooks for lazy-loaded and computed properties.

---

## Properties

### name

```php
public readonly string $name
```

The human-readable name of the quantity type (e.g., `'length'`, `'mass'`, `'force'`).

### dimension

```php
public readonly string $dimension
```

The normalized dimension code for this quantity type. Normalized via `DimensionService::normalize()` at construction time.

Examples: `'L'` (length), `'M'` (mass), `'L2'` (area), `'MLT-2'` (force), `'ML2T-2'` (energy).

### class

```php
public string $class { set; }
```

The fully-qualified class name of the Quantity subclass for this quantity type.

Type: `class-string<Quantity>`

The setter validates that the value is a subclass of `Quantity`.

**Throws (on set):**
- `InvalidArgumentException` - If the value is not a subclass of `Quantity`.

### unitDefinitions

```php
private(set) array $unitDefinitions { get; }
```

The unit definitions for this quantity type, lazy-loaded from the associated Quantity subclass via `getUnitDefinitions()`. The dimension code is injected into each definition. Results are cached after first access.

Type: `array<string, array{asciiSymbol: string, unicodeSymbol?: string, prefixGroup?: int, alternateSymbol?: string, systems: list<UnitSystem>, dimension: string}>`

### conversionDefinitions

```php
public array $conversionDefinitions { get; }
```

The conversion definitions for this quantity type, loaded from the associated Quantity subclass via `getConversionDefinitions()`.

Type: `list<array{string, string, float}>`

### units

```php
public array $units { get; }
```

The units compatible with this quantity type, retrieved from `UnitService::getByQuantityType()`.

Type: `list<Unit>`

### converter

```php
public Converter $converter { get; }
```

The converter for this quantity type's dimension, retrieved via `Converter::getInstance()`.

---

## Constructor

### \_\_construct()

```php
public function __construct(
    string $name,
    string $dimension,
    string $class
)
```

Create a new QuantityType instance.

**Parameters:**
- `$name` (string) - The human-readable name (e.g., `'length'`).
- `$dimension` (string) - The dimension code (e.g., `'L'`). Normalized via `DimensionService::normalize()`.
- `$class` (class-string\<Quantity\>) - The fully-qualified Quantity subclass name.

**Throws:**
- [`FormatException`](https://github.com/mossy2100/Galaxon-PHP-Core/blob/main/docs/Exceptions/FormatException.md) - If the dimension code is invalid.
- `InvalidArgumentException` - If the class is not a subclass of `Quantity`.

**Examples:**
```php
use Galaxon\Quantities\Internal\QuantityType;
use Galaxon\Quantities\QuantityType\Length;

$length = new QuantityType('length', 'L', Length::class);
```

---

## Usage examples

### Registering Quantity Types

```php
use Galaxon\Quantities\Services\QuantityTypeService;
use Galaxon\Quantities\QuantityType\DynamicViscosity;

// Register a custom quantity type
QuantityTypeService::add('dynamic viscosity', 'ML-1T-1', DynamicViscosity::class);

// Update the class for an existing quantity type
QuantityTypeService::setClass('currency', MyCurrencyClass::class);
```

### Looking Up Quantity Types

```php
use Galaxon\Quantities\Services\QuantityTypeService;

// Get quantity type by dimension
$lengthType = QuantityTypeService::getByDimension('L');
echo $lengthType->name; // 'length'

// Get quantity type by name
$massType = QuantityTypeService::getByName('mass');
echo $massType->dimension; // 'M'
```

---

## See also

- **[Quantity](../Quantity.md)** - Uses quantity types for type-safe instantiation.
- **[QuantityTypeService](../Services/QuantityTypeService.md)** - Registry for quantity types.
- **[DimensionService](../Services/DimensionService.md)** - Utilities for working with dimension codes.
- **[Converter](Converter.md)** - Manages conversions for a dimension.
