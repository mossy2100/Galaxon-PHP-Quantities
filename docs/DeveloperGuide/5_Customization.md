# Custom Quantity Types and Units

This guide explains how to work with quantities beyond what the package provides by default. Using entropy as an example, we'll cover four progressively more integrated approaches.

## 1. Using Derived Units Directly

You don't need a dedicated class for every quantity type. The package can work with any combination of units through derived unit expressions. For example, entropy has units of J/K (energy per temperature), and you can use this directly:

```php
use Galaxon\Quantities\QuantityType\Energy;
use Galaxon\Quantities\QuantityType\Temperature;

// Arithmetic automatically produces the correct derived units.
$energy = new Energy(150, 'J');
$temp = new Temperature(4, 'K');
$entropy = $energy->div($temp);

echo $entropy;           // "37.5 J/K"
echo get_class($entropy); // "Galaxon\Quantities\Quantity"
```

The result is a generic `Quantity` object with the derived unit `J/K`. All arithmetic, conversion, and formatting operations work as normal.

You can also create entropy values directly using `new Quantity()` or `Quantity::create()`:

```php
use Galaxon\Quantities\Quantity;

// Both produce a generic Quantity with units J/K.
$s1 = new Quantity(37.5, 'J/K');
$s2 = Quantity::create(37.5, 'J/K');
```

The difference between `new Quantity()` and `Quantity::create()`:

- **`new Quantity()`** always creates a generic `Quantity` object. However, it will throw an exception if a dedicated class is registered for the dimension/quantity type, prompting you to use that class instead.
- **`Quantity::create()`** checks the `QuantityTypeService` and returns the appropriate subclass if one is registered for the dimension/quantity type. For example, `Quantity::create(10, 'm')` returns a `Length` object, not a generic
  `Quantity`.

For this example, both produce the same result since no `Entropy` class is registered.

## 2. Creating a Custom Quantity Type Class

If you want entropy values to have their own class (for type-hinting, instanceof checks, or adding custom methods),
create one by extending `Quantity`:

```php
use Galaxon\Quantities\Quantity;

class Entropy extends Quantity
{
}
```

That's it — some built-in quantity types like `Acceleration` and `Density` are exactly this: empty classes that extend `Quantity`. Most Quantity subclasses define units and conversions specific to the quantity type by overriding `getUnitDefinitions()` and `getConversionDefinitions()` respectively, but this isn't required. 

The class itself needs no methods; it inherits everything from `Quantity`.

To make the package use your class automatically, register it with the `QuantityTypeService`:

```php
use Galaxon\Quantities\Services\QuantityTypeService;

// Register the Entropy class for the entropy dimension (M·L²·T⁻²·K⁻¹).
QuantityTypeService::add('entropy', 'ML2T-2H-1', Entropy::class);
```

If you don't know the dimension code for a quantity type, but you know the units (and they are registered and loaded), try this:

```php
use Galaxon\Quantities\Internal\DerivedUnit;

$dimension = DerivedUnit::parse('J/K')->dimension;
```

Now `Quantity::create()` and arithmetic operations will return `Entropy` objects when the result has the entropy dimension:

```php
$s = Quantity::create(37.5, 'J/K');
echo get_class($s); // "Entropy"

$energy = new Energy(150, 'J');
$temp = new Temperature(4, 'K');
$entropy = $energy->div($temp);
echo get_class($entropy); // "Entropy"
```

## 3. Adding a Custom Unit

You can add custom units to the `UnitService` without creating a custom class. For example, suppose you want a "chaos" unit (`ch`) for entropy, equivalent to J/K:

```php
use Galaxon\Quantities\Services\UnitService;
use Galaxon\Quantities\Quantity;

// Add the 'chaos' unit with an expansion to base SI units.
$chaosUnit = new Unit(
    name: 'chaos',
    asciiSymbol: 'ch',
    dimension: 'ML2T-2H-1',
    systems: [UnitSystem::Common],
    expansionUnitSymbol: 'kg*m2/(s2*K)'
);
UnitService::add($chaosUnit);

$s = new Quantity(37.5, 'ch');
echo $s;             // "37.5 ch"
echo $s->to('J/K');  // "37.5 J/K"

UnitService::loadSystem(UnitSystem::UsCustomary);
echo $s->to('Btu/degR');  // "0.019746 Btu/°R"
```

The `expansionUnitSymbol` tells the conversion system how this unit relates to base units. Since `J/K` and `kg·m²/(s²·K)` are equivalent, the conversion factor is 1.

### Unit Constructor Parameters

| Parameter             | Description                                                                                                              |
| --------------------- |--------------------------------------------------------------------------------------------------------------------------|
| `name`                | The unit name (e.g. `'chaos'`).                                                                                          |
| `asciiSymbol`         | The ASCII symbol used for parsing and display (e.g. `'ch'`).                                                             |
| `dimension`           | The dimension code (e.g. `'ML2T-2H-1'`).                                                                                 |
| `systems`             | Array of `UnitSystem` values this unit belongs to.                                                                       |
| `prefixGroup`         | Bitwise flags for allowed prefixes. 0 = no prefixes.                                                                     |
| `unicodeSymbol`       | Optional Unicode symbol for display (e.g. `'Ω'`). Null if same as ASCII.                                                 |
| `alternateSymbol`     | An additional symbol accepted by the parser. This symbol cannot accept prefixes.                                         |
| `expansionUnitSymbol` | The equivalent base unit expression (e.g. `'kg*m2*s-2*K-1'`), or null if the unit is not expandable.                     |
| `expansionValue`      | The conversion factor for the expansion. If not provided, defaults to 1.0 if expansionUnitSymbol is set, otherwise null. |

## 4. Defining Units in a Custom Class

For a fully integrated custom quantity type, override `getUnitDefinitions()` in your class. This is how all built-in
quantity types with custom units work (e.g. `Force`, `Pressure`, `Length`):

```php
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\PrefixService;
use Galaxon\Quantities\UnitSystem;
use Override;

class Entropy extends Quantity
{
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'chaos' => [
                'asciiSymbol'         => 'ch',
                'prefixGroup'         => PrefixService::GROUP_METRIC,
                'systems'             => [UnitSystem::Si],
                'expansionUnitSymbol' => 'kg*m2*s-2*K-1',
            ],
        ];
    }
}
```

Then register the class with the `QuantityTypeService`:

```php
QuantityTypeService::add('entropy', 'ML2T-2H-1', Entropy::class);
```

The unit definitions will be picked up automatically when the relevant measurement system is loaded, just like the built-in types. The `chaos` unit will accept metric prefixes (kch, mch, etc.) and participate in the full conversion system.

## See Also

- [Quantity](../Reference/Quantity.md) — base class API reference.
- [UnitService](../Reference/Services/UnitService.md) — unit registration and lookup.
- [QuantityTypeService](../Reference/Services/QuantityTypeService.md) — quantity type registration.
- [2.6_SupportedUnits](2.6_SupportedUnits.md) — complete list of built-in units.
