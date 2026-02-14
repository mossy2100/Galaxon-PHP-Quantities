# Custom Quantity Types and Units

This guide explains how to work with quantities beyond what the package provides by default. Using entropy as an
example, we'll cover four progressively more integrated approaches.

## 1. Using Derived Units Directly

You don't need a dedicated class for every quantity type. The package can work with any combination of units through
derived unit expressions. For example, entropy has units of J/K (energy per temperature), and you can use this directly:

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

The result is a generic `Quantity` object with the derived unit `J/K`. All arithmetic, conversion, and formatting
operations work as normal.

You can also create entropy values directly using `new Quantity()` or `Quantity::create()`:

```php
use Galaxon\Quantities\Quantity;

// Both produce a generic Quantity with units J/K.
$s1 = new Quantity(37.5, 'J/K');
$s2 = Quantity::create(37.5, 'J/K');
```

The difference between `new Quantity()` and `Quantity::create()`:

- **`new Quantity()`** always creates a generic `Quantity` object. However, it will throw an exception if a dedicated 
  class is registered for the dimension/quantity type, prompting you to use that class instead.
- **`Quantity::create()`** checks the `QuantityTypeRegistry` and returns the appropriate subclass if one is registered
  for the dimension/quantity type. For example, `Quantity::create(10, 'm')` returns a `Length` object, not a generic
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

That's it — some built-in quantity types like `Acceleration` and `Density` are exactly this: empty classes that extend
`Quantity`. Most Quantity subclasses define units and conversions specific to the quantity type by overriding
`getUnitDefinitions()` and `getConversionDefinitions()` respectively, but this isn't required. 

The class itself needs no methods; it inherits everything from `Quantity`.

To make the package use your class automatically, register it with the `QuantityTypeRegistry`:

```php
use Galaxon\Quantities\Registry\QuantityTypeRegistry;

// Register the Entropy class for the entropy dimension (M·L²·T⁻²·K⁻¹).
QuantityTypeRegistry::add('entropy', 'ML2T-2H-1', Entropy::class);
```

If you don't know the dimension code for a quantity type, but you know the units (and they are registered and loaded),
try this:
```php
use Galaxon\Quantities\Internal\DerivedUnit;

$dimension = DerivedUnit::parse('J/K')->dimension;
```

Now `Quantity::create()` and arithmetic operations will return `Entropy` objects when the result has the entropy
dimension:

```php
$s = Quantity::create(37.5, 'J/K');
echo get_class($s); // "Entropy"

$energy = new Energy(150, 'J');
$temp = new Temperature(4, 'K');
$entropy = $energy->div($temp);
echo get_class($entropy); // "Entropy"
```

## 3. Adding a Custom Unit

You can add custom units to the `UnitRegistry` without creating a custom class. For example, suppose you want a
"chaos" unit (`ch`) that represents the same dimension as J/K:

```php
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\Quantity;

// Add the 'chaos' unit with an expansion to base SI units.
$chaosUnit = new Unit(
    name: 'chaos',
    asciiSymbol: 'ch',
    dimension: 'ML2T-2H-1',
    systems: [System::Common],
    expansionUnitSymbol: 'kg*m2*s-2*K-1'
);
UnitRegistry::add($chaosUnit);

$s = new Quantity(37.5, 'ch');
echo $s;             // "37.5 ch"
echo $s->to('J/K');  // "37.5 J/K"

UnitRegistry::loadSystem(System::UsCustomary);
echo $s->to('Btu/degR');  // "0.019746 Btu/°R"
```

The `expansionUnitSymbol` tells the conversion system how this unit relates to base units. Since `J/K` and
`kg·m²·s⁻²·K⁻¹` are equivalent, the conversion factor is 1.

### UnitRegistry::add() Parameters

| Parameter             | Description                                                                      |
|-----------------------|----------------------------------------------------------------------------------|
| `name`                | The unit name (e.g. `'chaos'`).                                                  |
| `asciiSymbol`         | The ASCII symbol used for parsing and display (e.g. `'ch'`).                     |
| `unicodeSymbol`       | Optional Unicode symbol for display (e.g. `'Ω'`). Null if same as ASCII.         |
| `dimension`           | The dimension code (e.g. `'ML2T-2H-1'`).                                         |
| `prefixGroup`         | Bitwise flags for allowed prefixes. 0 = no prefixes.                             |
| `alternateSymbol`     | An additional symbol accepted by the parser. Cannot accept prefixes.             |
| `systems`             | Array of `System` values this unit belongs to.                                   |
| `expansionUnitSymbol` | The equivalent base unit expression (e.g. `'kg*m2*s-2*K-1'`).                    |
| `expansionValue`      | The conversion factor for the expansion. Defaults to 1.0 if an expansion is set. |

## 4. Defining Units in a Custom Class

For a fully integrated custom quantity type, override `getUnitDefinitions()` in your class. This is how all built-in
quantity types with custom units work (e.g. `Force`, `Pressure`, `Length`):

```php
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
use Override;

class Entropy extends Quantity
{
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'chaos' => [
                'asciiSymbol'         => 'ch',
                'prefixGroup'         => PrefixRegistry::GROUP_METRIC,
                'systems'             => [System::Si],
                'expansionUnitSymbol' => 'kg*m2*s-2*K-1',
            ],
        ];
    }
}
```

Then register the class with the `QuantityTypeRegistry`:

```php
QuantityTypeRegistry::add('entropy', 'ML2T-2H-1', Entropy::class);
```

The unit definitions will be picked up automatically when the relevant measurement system is loaded, just like the
built-in types. The `chaos` unit will accept metric prefixes (kch, mch, etc.) and participate in the full conversion
system.

## See Also

- [Quantity](Quantity.md) — base class API reference.
- [UnitRegistry](Registry/UnitRegistry.md) — unit registration and lookup.
- [QuantityTypeRegistry](Registry/QuantityTypeRegistry.md) — quantity type registration.
- [Supported Units](SupportedUnits.md) — complete list of built-in units.
