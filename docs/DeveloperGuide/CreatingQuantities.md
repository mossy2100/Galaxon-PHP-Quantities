You can create quantities using either the base `Quantity` class or a dedicated subclass. Both approaches give you full access to arithmetic, conversion, comparison, and formatting.

---

## Using the base Quantity class

The `Quantity` class works with any valid unit expression, making it ideal for ad-hoc or uncommon measurements:

```php
use Galaxon\Quantities\Quantity;

$entropy = new Quantity(37.5, 'J/K');
$torque = new Quantity(120, 'N*m');
$viscosity = Quantity::create(1.002e-3, 'Pa*s');
```

As a rule, calling `Quantity::create()` is preferable to `new Quantity()`.

The `Quantity::create()` factory method infers the quantity type from the unit and checks the `QuantityTypeService` for an appropriate subclass. For example, `Quantity::create(10, 'm')` returns a `Length` object. If no dedicated subclass is registered for the quantity type, then the result will be a `Quantity` object.

However, when `new Quantity()` is called, the quantity type is inferred from the unit, and if there is a preferable subclass, such as `Angle` or `Length`, an exception will be thrown.

---

## Using subclasses

Dedicated subclasses exist for quantity types that have their own units, add extra features, or are commonly used. Using them gives you type-hinting, `instanceof` checks, and IDE autocompletion:

```php
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Angle;

$distance = new Length(42.195, 'km');
$heading = new Angle(127.5, 'deg');

// Angle adds trigonometry methods.
$sine = $heading->sin();
```

### Why subclasses are provided

Subclasses are provided when at least one of the following applies:

- **Specific units** — The quantity type has its own named units (e.g. Force has newtons and pound-force, Pressure has pascals and bar).
- **Added features** — The class offers methods beyond the base `Quantity` API. For example, `Angle` provides `sin()`, `cos()`, and `tan()`.
- **Common use** — The quantity type is frequently used in calculations, so a dedicated class improves convenience even without custom units (e.g. `Acceleration`, `Density`, `Velocity`).

---

## Which Quantity Types Have Classes?

### SI Base Units

All seven [SI base units](https://en.wikipedia.org/wiki/SI_base_unit) have a dedicated class:

| Class               | Base Unit |
|---------------------|-----------|
| `Length`            | metre     |
| `Mass`             | kilogram  |
| `Time`             | second    |
| `ElectricCurrent`  | ampere    |
| `Temperature`      | kelvin    |
| `AmountOfSubstance`| mole      |
| `LuminousIntensity`| candela   |

### Named SI Derived Units

All 22 [named SI derived units](https://en.wikipedia.org/wiki/SI_derived_unit#Special_names) have a dedicated class, including:

Angle, SolidAngle, Frequency, Force, Pressure, Energy, Power, ElectricCharge, Voltage, Capacitance, Resistance, Conductance, MagneticFlux, MagneticFluxDensity, Inductance, Temperature (Celsius), LuminousFlux, Illuminance, RadiationDose, and CatalyticActivity.

In addition, these commonly used derived quantities have dedicated classes even though they don't have special named units:

- `Area` (m²)
- `Volume` (m³)
- `Velocity` (m/s)
- `Acceleration` (m/s²)
- `Density` (kg/m³)

The package also includes `Data` (bytes, bits) and `Dimensionless` for dimensionless ratios.

### Other Derived Units

Most [SI derived units defined by field of application](https://en.wikipedia.org/wiki/SI_derived_unit#By_field_of_application) — such as viscosity, entropy, specific heat capacity, and surface tension — do not have dedicated classes. They can be created directly using the base `Quantity` class with compound unit expressions (e.g. `'Pa*s'`, `'J/K'`, `'J/(kg*K)'`).

If you need a dedicated class for one of these, see [Adding Quantity Types](AddingQuantityTypes.md).
