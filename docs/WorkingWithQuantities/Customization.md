# Customization

This guide explains how to work with quantities beyond what the package provides by default. Using entropy as an example, we'll cover four progressively more integrated approaches.

---

## 1. Using Derived Units Directly

You don't need a dedicated class for every quantity type. The package can work with any combination of units through derived unit expressions. For example, entropy has units of *J/K* (energy per unit temperature):

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

You can also create entropy values directly using `Quantity::create()`:

```php
use Galaxon\Quantities\Quantity;

// Create a generic Quantity with units J/K.
$s2 = Quantity::create(37.5, 'J/K');
```

This will produce a `Quantity` object since no `Entropy` class is registered. Furthermore, this code will still work if you add an `Entropy` class later.

---

## 2. Creating a Custom Quantity Type Class

If you want entropy values to have their own class, whether for code readability, type-hinting, `instanceof` checks, or adding custom methods, create one by extending `Quantity`:

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

Once the new class is registered, `Quantity::create()` and arithmetic operations will return `Entropy` objects when the result has the entropy dimension:

```php
$s = Quantity::create(37.5, 'J/K');
echo get_class($s); // "Entropy"

$energy = new Energy(150, 'J');
$temp = new Temperature(4, 'K');
$entropy = $energy->div($temp);
echo get_class($entropy); // "Entropy"
```

---

## 3. Adding a Custom Unit

You can add custom units to the `UnitService` without creating a custom class. For example, suppose you want a "chaos" unit (`ch`) for entropy, equivalent to J/K:

```php
use Galaxon\Quantities\Internal\Conversion;
use Galaxon\Quantities\Internal\Unit;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\ConversionService;
use Galaxon\Quantities\Services\UnitService;
use Galaxon\Quantities\UnitSystem;

// 1. Create the unit.
$chaosUnit = new Unit('chaos', 'ch', 'ML2T-2H-1');

// 2. Register it.
UnitService::add($chaosUnit);

// 3. Add a conversion to link the new unit to existing units.
ConversionService::add(new Conversion('ch', 'J/K', 1));

// 4. Use it.
$s = Quantity::create(37.5, 'ch');
echo $s;             // 37.5 ch
echo $s->to('J/K');  // 37.5 J/K

// Example: convert to US customary units.
UnitService::loadSystem(UnitSystem::UsCustomary);
echo $s->to('Btu/degR');  // 0.019746 Btu/°R
```

### More examples

**US legal cup** — The US legal cup (240 mL) is used for nutrition labelling in the United States. The package includes the US customary cup (236.588 mL) but not the legal cup:

```php
use Galaxon\Quantities\Internal\Conversion;
use Galaxon\Quantities\Internal\Unit;
use Galaxon\Quantities\QuantityType\Volume;
use Galaxon\Quantities\Services\ConversionService;
use Galaxon\Quantities\Services\UnitService;
use Galaxon\Quantities\UnitSystem;

$legalCup = new Unit(
    name: 'US legal cup',
    asciiSymbol: 'US legal cup',
    dimension: 'L3',
    systems: [UnitSystem::UsCustomary],
);
UnitService::add($legalCup);
ConversionService::add(new Conversion('US legal cup', 'mL', 240));

$recipe = new Volume(2, 'US legal cup');
echo $recipe->to('mL');  // 480 mL
echo $recipe->to('L');   // 0.48 L
```

**Parts per trillion** — The package includes `ppm` and `ppb` but not parts per trillion. Since `ppt` is already used for parts per thousand, we'll use `ppT`:

```php
use Galaxon\Quantities\Internal\Conversion;
use Galaxon\Quantities\Internal\Unit;
use Galaxon\Quantities\QuantityType\Dimensionless;
use Galaxon\Quantities\Services\ConversionService;
use Galaxon\Quantities\Services\UnitService;
use Galaxon\Quantities\UnitSystem;

$pptUnit = new Unit(
    name: 'parts per trillion',
    asciiSymbol: 'ppT',
    dimension: '',        // Dimensionless
    systems: [UnitSystem::Common],
);
UnitService::add($pptUnit);
ConversionService::add(new Conversion('ppb', 'ppT', 1000));

$concentration = new Dimensionless(5, 'ppT');
echo $concentration->to('ppb');  // 0.005 ppb
echo $concentration->to('ppm');  // 0.000005 ppm
```

### Unit constructor parameters

| Parameter         | Description                                                                          |
| ----------------- | ------------------------------------------------------------------------------------ |
| `name`            | The unit name (e.g. `'chaos'`).                                                      |
| `asciiSymbol`     | The ASCII symbol used for parsing and display (e.g. `'ch'`).                         |
| `dimension`       | The dimension code (e.g. `'ML2T-2H-1'`).                                             |
| `systems`         | Array of `UnitSystem` values this unit belongs to.                                   |
| `prefixGroup`     | Bitwise flags for allowed prefixes. 0 = no prefixes.                                 |
| `unicodeSymbol`   | Optional Unicode symbol for display (e.g. `'Ω'`). Null if same as ASCII.             |
| `alternateSymbol` | An additional symbol accepted by the parser. This symbol cannot accept prefixes.     |

### Key points

- The `dimension` parameter must match the quantity type you intend the unit for. Use `'L3'` for volume, `''` (empty string) for dimensionless, `'M'` for mass, etc.
- You must add at least one `Conversion` connecting your new unit to an existing unit; otherwise the conversion system won't know how to reach it.
- The conversion factor means: 1 of the source unit equals *n* of the destination unit. So `new Conversion('US legal cup', 'mL', 240)` means 1 US legal cup = 240 mL.
- Unit symbols must be unique across all registered units. `UnitService::add()` will throw a `DomainException` if a symbol conflict is detected.

---

## 4. Defining Units in a Custom Class

For a fully integrated custom quantity type, override `getUnitDefinitions()` and `getConversionDefinitions()` in your class. This is how all built-in quantity types with custom units work (e.g. `Force`, `Pressure`, `Length`):

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
                'asciiSymbol' => 'ch',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Custom],
            ],
        ];
    }

    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            // 1 ch = 1 J/K (equivalent to kg·m²·s⁻²·K⁻¹).
            ['ch', 'J/K', 1],
        ];
    }
}
```

The unit and conversion definitions will be picked up automatically when the relevant measurement system is loaded, just like the built-in types. In this case, because the prefix group is set to metric, the `chaos` unit will accept metric prefixes as specified in the definition (*kch*, *mch*, etc.), and participate in the full conversion system.

---

## Built-in Examples

The built-in quantity type classes demonstrate various patterns for extending the base `Quantity` class:

- **Minimal implementation** — [`Acceleration`](../Reference/QuantityType/Acceleration.md) and [`Density`](../Reference/QuantityType/Density.md) are empty subclasses with no custom units or conversions, included for type safety and as templates.
- **Extended functionality** — [`Time`](../Reference/QuantityType/Time.md) and [`Angle`](../Reference/QuantityType/Angle.md) add domain-specific methods such as trigonometric functions and DateInterval conversion.
- **Overriding conversion** — [`Temperature`](../Reference/QuantityType/Temperature.md) overrides `convert()` to handle offset-based conversions (e.g. Celsius to Fahrenheit).
- **Configuring parts** — [`Mass`](../Reference/QuantityType/Mass.md) demonstrates configuring part unit symbols for imperial and US customary decomposition.
- **Dynamic units and conversions** — [`Money`](../Reference/QuantityType/Money.md) loads currency units and exchange rates at runtime via the CurrencyService.

---

## See Also

- **[Quantity](../Reference/Quantity.md)** — Base class API reference.
- **[UnitService](../Reference/Services/UnitService.md)** — Unit registration and lookup.
- **[ConversionService](../Reference/Services/ConversionService.md)** — Conversion registration.
- **[QuantityTypeService](../Reference/Services/QuantityTypeService.md)** — Quantity type registration.
- **[Units](../Concepts/Units.md)** — Complete list of built-in units.
