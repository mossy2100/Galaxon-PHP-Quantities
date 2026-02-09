# QuantityType

Represents a type of physical quantity with its associated metadata.

## Overview

The `QuantityType` class defines the metadata for a category of physical quantities, such as Length, Mass, Time, or Force. Each quantity type has a unique name, a dimensional code, an SI unit symbol, and an optional associated PHP class.

Quantity types are registered with the `QuantityTypeRegistry` and are used to:
- Map dimension codes to human-readable names
- Associate dimensions with their SI base or derived units
- Link dimension codes to strongly-typed Quantity subclasses
- Provide validation for unit-dimension compatibility

The class uses PHP 8.4 property hooks for computed properties that derive values from other registered data.

## Properties

### name

```php
public readonly string $name
```

The human-readable name of the quantity type (e.g., 'Length', 'Mass', 'Force'). This name is used in error messages and for display purposes.

### dimension

```php
public readonly string $dimension
```

The dimensional code for this quantity type. Uses standard dimension letters: L (length), M (mass), T (time), I (electric current), Θ (temperature), N (amount of substance), J (luminous intensity).

Examples:
- 'L' for length
- 'M' for mass
- 'L2' for area
- 'MLT-2' for force
- 'ML2T-2' for energy

### siUnitSymbol

```php
public readonly string $siUnitSymbol
```

The symbol of the SI unit for this quantity type (e.g., 'm' for length, 'kg' for mass, 'N' for force).

### class

```php
public ?string $class
```

The fully-qualified class name of the Quantity subclass for this type, or null if no specific class is registered. When set, `Quantity::create()` will instantiate this class for quantities with matching dimensions.

This property uses a hook to retrieve the value from the registry.

### siUnit

```php
public ?Unit $siUnit { get; }
```

The SI unit object for this quantity type. Retrieved from the UnitRegistry using the `siUnitSymbol`. Returns null if the unit is not yet registered.

## Constructor

### __construct()

```php
public function __construct(
    string $name,
    string $dimension,
    string $siUnitSymbol
)
```

Create a new QuantityType instance.

**Parameters:**
- `$name` (string) - The human-readable name (e.g., 'Length')
- `$dimension` (string) - The dimensional code (e.g., 'L')
- `$siUnitSymbol` (string) - The SI unit symbol (e.g., 'm')

**Throws:**
- `FormatException` - If the dimension code format is invalid

**Examples:**
```php
// Create a quantity type for length
$length = new QuantityType('Length', 'L', 'm');

// Create a quantity type for force
$force = new QuantityType('Force', 'MLT-2', 'N');
```

## Usage Examples

### Registering Quantity Types

```php
use Galaxon\Quantities\QuantityType;
use Galaxon\Quantities\Registry\QuantityTypeRegistry;

// Register a custom quantity type
$viscosity = new QuantityType('Dynamic Viscosity', 'ML-1T-1', 'Pa*s');
QuantityTypeRegistry::add($viscosity);

// Associate a class with an existing quantity type
QuantityTypeRegistry::setClass('L', Length::class);
```

### Looking Up Quantity Types

```php
use Galaxon\Quantities\Registry\QuantityTypeRegistry;

// Get quantity type by dimension
$lengthType = QuantityTypeRegistry::getByDimension('L');
echo $lengthType->name; // 'Length'

// Get quantity type by class
$massType = QuantityTypeRegistry::getByClass(Mass::class);
echo $massType->siUnitSymbol; // 'kg'
```

### Using Quantity Types in Validation

```php
use Galaxon\Quantities\Registry\QuantityTypeRegistry;

// Check if a unit matches a quantity type
$unit = Unit::parse('km');
$qtyType = QuantityTypeRegistry::getByDimension($unit->dimension);

if ($qtyType !== null) {
    echo "This is a {$qtyType->name} unit";
}
```

## See Also

- **[Quantity](Quantity.md)** - Uses quantity types for type-safe instantiation
- **[QuantityTypeRegistry](Registry/QuantityTypeRegistry.md)** - Registry for quantity types
- **[DimensionUtility](Utility/DimensionUtility.md)** - Utilities for working with dimension codes
