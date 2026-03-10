# QuantityType

Represents a type of physical quantity with its associated metadata.

---

## Overview

The `QuantityType` class defines the metadata for a category of physical quantities, such as Length, Mass, Time, or Force. Each quantity type has a unique name, a dimensional code, and an optional associated PHP class.

Quantity types are registered with the `QuantityTypeService` and are used to:
- Map dimension codes to human-readable names
- Link dimension codes to strongly-typed Quantity subclasses
- Provide validation for unit-dimension compatibility

The class uses a PHP 8.4 property hook on `$class` to validate that only valid `Quantity` subclasses can be assigned.

---

## Properties

### name

```php
public readonly string $name
```

The human-readable name of the quantity type (e.g., 'length', 'mass', 'force'). This name is used in error messages and for display purposes.

### dimension

```php
public readonly string $dimension
```

The dimensional code for this quantity type. Uses dimension letters: L (length), M (mass), T (time), I (electric current), H (temperature), N (amount of substance), J (luminous intensity), A (angle), D (data), C (currency).

Examples:
- 'L' for length
- 'M' for mass
- 'L2' for area
- 'T-2LM' for force
- 'T-2L2M' for energy

See [DimensionService](DimensionService.md).

### class

```php
/** @var ?class-string<Quantity> */
public ?string $class
```

The fully-qualified class name of the Quantity subclass for this type, or null if no specific class is registered. When set, `Quantity::create()` will instantiate this class for quantities with matching dimensions.

This property has a setter hook that validates the value is a subclass of `Quantity`.

**Throws (on set):**
- `DomainException` - If the value is not null and not a subclass of `Quantity`

---

## Constructor

### \_\_construct()

```php
public function __construct(
    string $name,
    string $dimension,
    ?string $class = null
)
```

Create a new QuantityType instance.

**Parameters:**
- `$name` (string) - The human-readable name (e.g., 'length')
- `$dimension` (string) - The dimensional code (e.g., 'L'). Normalized via `DimensionService::normalize()`.
- `$class` (?string) - The fully-qualified Quantity subclass name, or null (default: null)

**Examples:**
```php
// Create a quantity type for length
$length = new QuantityType('length', 'L', 'm', Length::class);

// Create a quantity type for force (without a class)
$force = new QuantityType('force', 'MLT-2', 'N');
```

---

## Usage Examples

### Registering Quantity Types

```php
use Galaxon\Quantities\Services\QuantityTypeService;
use Galaxon\Quantities\QuantityType\DynamicViscosity;

// Register a custom quantity type
QuantityTypeService::add('dynamic viscosity', 'ML-1T-1', DynamicViscosity::class);

// Set or update the class for an existing quantity type
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

// Get quantity type by class
$forceType = QuantityTypeService::getByClass(Force::class);
echo $forceType->dimension; // 'T-2LM'
```

### Using Quantity Types in Validation

```php
use Galaxon\Quantities\Internal\Unit;
use Galaxon\Quantities\Services\QuantityTypeService;

// Check if a unit matches a quantity type
$unit = Unit::parse('km');
$qtyType = QuantityTypeService::getByDimension($unit->dimension);

if ($qtyType !== null) {
    echo "This is a {$qtyType->name} unit";
}
```

---

## See Also

- **[Quantity](../Quantity.md)** - Uses quantity types for type-safe instantiation
- **[QuantityTypeService](../Services/QuantityTypeService.md)** - Registry for quantity types
- **[DimensionService](DimensionService.md)** - Utilities for working with dimension codes
