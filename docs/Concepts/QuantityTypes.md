# Quantity Types

A [quantity type](https://en.wikipedia.org/wiki/Quantity) is a category of measurable property — such as length, mass, or time — that defines what kind of thing is being measured.

The package provides several classes to support quantity types:

1. The `Quantity` base class, which provides most of the functionality for quantity conversion, arithmetic, comparison, etc.
2. The `Quantity` subclasses (`Time`, `Length`, `Force`, etc.), each of which represents a quantity type. See below.
3. The `QuantityType` class, which encapsulates a quantity type.
4. The `QuantityTypeService`, which functions as a registry linking dimensions to quantity type classes (i.e. `Quantity` subclasses).

---

## SI base quantity types

All seven [SI base units](https://en.wikipedia.org/wiki/SI_base_unit) have a dedicated class:

| Name                | Dimension | Class                                                                 | SI Base Unit |
| ------------------- | --------- | --------------------------------------------------------------------- | ------------ |
| length              | L         | [`Length`](../Reference/QuantityType/Length.md)                       | *m*          |
| mass                | M         | [`Mass`](../Reference/QuantityType/Mass.md)                           | *kg*         |
| time                | T         | [`Time`](../Reference/QuantityType/Time.md)                           | *s*          |
| electric current    | I         | [`ElectricCurrent`](../Reference/QuantityType/ElectricCurrent.md)     | *A*          |
| temperature         | H         | [`Temperature`](../Reference/QuantityType/Temperature.md)             | *K*          |
| amount of substance | N         | [`AmountOfSubstance`](../Reference/QuantityType/AmountOfSubstance.md) | *mol*        |
| luminous intensity  | J         | [`LuminousIntensity`](../Reference/QuantityType/LuminousIntensity.md) | *cd*         |

---

## Quantity types for SI named units

The quantity types corresponding to all 22 [named SI derived units](https://en.wikipedia.org/wiki/SI_derived_unit#Special_names) have a dedicated class:

| Name                  | Dimension  | Class                                                                     | SI named unit |
| --------------------- | ---------- | ------------------------------------------------------------------------- | ------------- |
| angle                 | A          | [`Angle`](../Reference/QuantityType/Angle.md)                             | *rad*         |
| solid angle           | A2         | [`SolidAngle`](../Reference/QuantityType/SolidAngle.md)                   | *sr*          |
| frequency             | T-1        | [`Frequency`](../Reference/QuantityType/Frequency.md)                     | *Hz*          |
| force                 | T-2LM      | [`Force`](../Reference/QuantityType/Force.md)                             | *N*           |
| pressure              | T-2L-1M    | [`Pressure`](../Reference/QuantityType/Pressure.md)                       | *Pa*          |
| energy                | T-2L2M     | [`Energy`](../Reference/QuantityType/Energy.md)                           | *J*           |
| power                 | T-3L2M     | [`Power`](../Reference/QuantityType/Power.md)                             | *W*           |
| electric charge       | TI         | [`ElectricCharge`](../Reference/QuantityType/ElectricCharge.md)           | *C*           |
| voltage               | T-3L2MI-1  | [`Voltage`](../Reference/QuantityType/Voltage.md)                         | *V*           |
| capacitance           | T4L-2M-1I2 | [`Capacitance`](../Reference/QuantityType/Capacitance.md)                 | *F*           |
| resistance            | T-3L2MI-2  | [`Resistance`](../Reference/QuantityType/Resistance.md)                   | *ohm*         |
| conductance           | T3L-2M-1I2 | [`Conductance`](../Reference/QuantityType/Conductance.md)                 | *S*           |
| magnetic flux         | T-2L2MI-1  | [`MagneticFlux`](../Reference/QuantityType/MagneticFlux.md)               | *Wb*          |
| magnetic flux density | T-2MI-1    | [`MagneticFluxDensity`](../Reference/QuantityType/MagneticFluxDensity.md) | *T*           |
| inductance            | T-2L2MI-2  | [`Inductance`](../Reference/QuantityType/Inductance.md)                   | *H*           |
| luminous flux         | JA2        | [`LuminousFlux`](../Reference/QuantityType/LuminousFlux.md)               | *lm*          |
| illuminance           | L-2JA2     | [`Illuminance`](../Reference/QuantityType/Illuminance.md)                 | *lx*          |
| absorbed dose         | T-2L2      | [`RadiationDose`](../Reference/QuantityType/RadiationDose.md)             | *Gy*          |
| catalytic activity    | T-1N       | [`CatalyticActivity`](../Reference/QuantityType/CatalyticActivity.md)     | *kat*         |

---

## Additional built-in quantity type classes

These commonly used quantity types also have dedicated classes:

| Name         | Dimension  | Class                                                         |
| ------------ | ---------- | ------------------------------------------------------------- |
| dimensionless| *(empty)*  | [`Dimensionless`](../Reference/QuantityType/Dimensionless.md) |
| data         | D          | [`Data`](../Reference/QuantityType/Data.md)                   |
| money        | C          | [`Money`](../Reference/QuantityType/Money.md)                 |
| area         | L2         | [`Area`](../Reference/QuantityType/Area.md)                   |
| volume       | L3         | [`Volume`](../Reference/QuantityType/Volume.md)               |
| velocity     | T-1L       | [`Velocity`](../Reference/QuantityType/Velocity.md)           |
| acceleration | T-2L       | [`Acceleration`](../Reference/QuantityType/Acceleration.md)   |
| density      | L-3M       | [`Density`](../Reference/QuantityType/Density.md)             |

---

## Quantity types without classes

There are many other quantity types for which no dedicated subclass is provided, as doing so would unnecessarily bloat the package.

Examples include: momentum, torque, viscosity, electric field strength, and heat capacity. Many more can be found at the [list of physical quantities](https://en.wikipedia.org/wiki/List_of_physical_quantities) on Wikipedia.

Only two quantity type classes (`Acceleration` and `Density`) are included that add no additional functionality, as they have no dedicated units or conversions. They're included because they're reasonably common, and serve as examples of how to add your own custom quantity types.

Using a dedicated subclass for a quantity type improves code readability and type safety, and can be a form of future-proofing in case additional functionality needs to be added later.

---

## See Also

- **[Creating Quantities](../WorkingWithQuantities/CreatingQuantities.md)** — How to instantiate quantities using constructors and the factory method.
- **[Customization](../WorkingWithQuantities/Customization.md)** — How to create your own custom quantity type classes.
- **[Units](Units.md)** — Complete list of built-in units for each quantity type.
- **[QuantityTypeService](../Reference/Services/QuantityTypeService.md)** — Service for registering and looking up quantity types.
- **[QuantityType](../Reference/Internal/QuantityType.md)** — Internal metadata class representing a quantity type.

