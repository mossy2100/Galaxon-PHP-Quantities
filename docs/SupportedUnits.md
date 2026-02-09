# Supported Units

Complete list of units included with the Quantities package by default.

## Overview

The Quantities package includes units across multiple measurement systems:

- **SI** - International System of Units (base and derived units)
- **SI Accepted** - Non-SI units officially accepted for use with SI
- **Common** - Widely used units without formal system classification
- **Imperial** - British Imperial system
- **US Customary** - United States customary units
- **Scientific** - Units for scientific applications
- **Astronomical** - Units for astronomical distances
- **Nautical** - Units for maritime and aviation
- **Typographical** - Units for typography and printing

The Prefixes column indicates which metric prefixes are supported:
- **all** - All metric prefixes (Y, Z, E, P, T, G, M, k, h, da, d, c, m, μ, n, p, f, a, z, y)
- **large** - Large engineering prefixes (k, M, G, T, P, E)
- **small** - Small engineering prefixes (m, μ, n, p, f, a)
- **metric + binary** - Large metric (k, M, G, T) and binary (Ki, Mi, Gi, Ti) prefixes

An "engineering prefix" is one that represents a multiple of 1000 or 1/1000; or in other words, the power of 10 is a
multiple of 3. That's all of them except for c, d, da, and h.

---

## Length

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| metre | `m` | | all | SI |
| astronomical unit | `au` | | | SI Accepted, Astronomical |
| light year | `ly` | | | Astronomical |
| parsec | `pc` | | large | Astronomical |
| pixel | `px` | | | Typographical |
| point | `p` | | | Typographical |
| pica | `P` | | | Typographical |
| inch | `in` | | | Imperial, US Customary |
| foot | `ft` | | | Imperial, US Customary |
| yard | `yd` | | | Imperial, US Customary |
| mile | `mi` | | | Imperial, US Customary |
| fathom | `ftm` | | | Nautical |
| nautical mile | `nmi` | | | Nautical |

**See:** [Length class documentation](QuantityType/Length.md)

---

## Mass

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| gram | `g` | | all | SI |
| tonne | `t` | | | SI Accepted |
| dalton | `Da` | | | SI Accepted |
| grain | `gr` | | | Imperial, US Customary |
| ounce | `oz` | | | Imperial, US Customary |
| pound | `lb` | | | Imperial, US Customary |
| stone | `st` | | | Imperial |
| short ton | `tn` | | | US Customary |
| long ton | `LT` | | | Imperial |

**Note:** The SI base unit for mass is the kilogram (kg), not the gram.

**See:** [Mass class documentation](QuantityType/Mass.md)

---

## Time

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| second | `s` | | all | SI |
| minute | `min` | | | SI Accepted |
| hour | `h` | | | SI Accepted |
| day | `d` | | | SI Accepted |
| week | `w` | | | Common |
| month | `mo` | | | Common |
| year | `y` | | | Common |

**See:** [Time class documentation](QuantityType/Time.md)

---

## Temperature

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| kelvin | `K` | | all | SI |
| celsius | `degC` | `°C` | | SI |
| fahrenheit | `degF` | `°F` | | Imperial, US Customary |
| rankine | `degR` | `°R` | | Imperial, US Customary |

**Note:** Temperature conversions between Celsius/Fahrenheit and Kelvin/Rankine include offsets and are handled specially.

**See:** [Temperature class documentation](QuantityType/Temperature.md)

---

## Angle

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| radian | `rad` | | all | SI |
| degree | `deg` | `°` | | SI Accepted |
| arcminute | `arcmin` | `′` | | SI Accepted |
| arcsecond | `arcsec` | `″` | small | SI Accepted |
| gradian | `grad` | | | Common |
| turn | `turn` | | | Common |

**Alternate symbols:** arcminute also accepts `'`; arcsecond also accepts `"`

**See:** [Angle class documentation](QuantityType/Angle.md)

---

## Solid Angle

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| steradian | `sr` | | small | SI |

**See:** [SolidAngle class documentation](QuantityType/SolidAngle.md)

---

## Area

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| hectare | `ha` | | | SI Accepted |
| acre | `ac` | | | Imperial, US Customary |

**Note:** Square units like m², km², ft², etc. are automatically supported through unit arithmetic.

**See:** [Area class documentation](QuantityType/Area.md)

---

## Volume

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| litre | `L` | | all | SI Accepted |
| US fluid ounce | `US fl oz` | | | US Customary |
| US pint | `US pt` | | | US Customary |
| US quart | `US qt` | | | US Customary |
| US gallon | `US gal` | | | US Customary |
| imperial fluid ounce | `imp fl oz` | | | Imperial |
| imperial pint | `imp pt` | | | Imperial |
| imperial quart | `imp qt` | | | Imperial |
| imperial gallon | `imp gal` | | | Imperial |

**Note:** Cubic units like m³, cm³, ft³, etc. are automatically supported through unit arithmetic.

**See:** [Volume class documentation](QuantityType/Volume.md)

---

## Velocity

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| knot | `kn` | | | Nautical |

**Note:** Compound velocity units like m/s, km/h, mi/h, etc. are automatically supported through unit arithmetic.

**See:** [Velocity class documentation](QuantityType/Velocity.md)

---

## Frequency

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| hertz | `Hz` | | all | SI |
| becquerel | `Bq` | | all | SI |

**Note:** Hertz measures frequency; becquerel measures radioactivity. Both have dimension T⁻¹.

**See:** [Frequency class documentation](QuantityType/Frequency.md)

---

## Force

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| newton | `N` | | all | SI |
| pound force | `lbf` | | | Imperial, US Customary |

**Expansion:** newton = kg·m·s⁻²; pound force = lb·ft·s⁻² × g₀

**See:** [Force class documentation](QuantityType/Force.md)

---

## Pressure

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| pascal | `Pa` | | all | SI |
| atmosphere | `atm` | | | Scientific |
| mmHg | `mmHg` | | | Scientific |
| inHg | `inHg` | | | US Customary |

**Expansion:** pascal = kg·m⁻¹·s⁻²

**See:** [Pressure class documentation](QuantityType/Pressure.md)

---

## Energy

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| joule | `J` | | all | SI |
| electronvolt | `eV` | | all | SI Accepted |
| calorie | `cal` | | large | Common |
| British thermal unit | `Btu` | | | US Customary |

**Expansion:** joule = kg·m²·s⁻²

**See:** [Energy class documentation](QuantityType/Energy.md)

---

## Power

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| watt | `W` | | all | SI |

**Expansion:** watt = kg·m²·s⁻³

**See:** [Power class documentation](QuantityType/Power.md)

---

## Electric Current

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| ampere | `A` | | all | SI |

**See:** [ElectricCurrent class documentation](QuantityType/ElectricCurrent.md)

---

## Electric Charge

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| coulomb | `C` | | all | SI |

**Expansion:** coulomb = A·s

**See:** [ElectricCharge class documentation](QuantityType/ElectricCharge.md)

---

## Voltage

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| volt | `V` | | all | SI |

**Expansion:** volt = kg·m²·s⁻³·A⁻¹

**See:** [Voltage class documentation](QuantityType/Voltage.md)

---

## Resistance

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| ohm | `ohm` | `Ω` | all | SI |

**Expansion:** ohm = kg·m²·s⁻³·A⁻²

**See:** [Resistance class documentation](QuantityType/Resistance.md)

---

## Conductance

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| siemens | `S` | | all | SI |

**Expansion:** siemens = s³·A²·kg⁻¹·m⁻²

**See:** [Conductance class documentation](QuantityType/Conductance.md)

---

## Capacitance

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| farad | `F` | | all | SI |

**Expansion:** farad = s⁴·A²·kg⁻¹·m⁻²

**See:** [Capacitance class documentation](QuantityType/Capacitance.md)

---

## Inductance

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| henry | `H` | | all | SI |

**Expansion:** henry = kg·m²·s⁻²·A⁻²

**See:** [Inductance class documentation](QuantityType/Inductance.md)

---

## Magnetic Flux

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| weber | `Wb` | | all | SI |

**Expansion:** weber = kg·m²·s⁻²·A⁻¹

**See:** [MagneticFlux class documentation](QuantityType/MagneticFlux.md)

---

## Magnetic Flux Density

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| tesla | `T` | | all | SI |

**Expansion:** tesla = kg·s⁻²·A⁻¹

**See:** [MagneticFluxDensity class documentation](QuantityType/MagneticFluxDensity.md)

---

## Luminous Intensity

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| candela | `cd` | | all | SI |

**See:** [LuminousIntensity class documentation](QuantityType/LuminousIntensity.md)

---

## Luminous Flux

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| lumen | `lm` | | all | SI |

**Expansion:** lumen = cd·sr

**See:** [LuminousFlux class documentation](QuantityType/LuminousFlux.md)

---

## Illuminance

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| lux | `lx` | | all | SI |

**Expansion:** lux = cd·sr·m⁻²

**See:** [Illuminance class documentation](QuantityType/Illuminance.md)

---

## Amount of Substance

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| mole | `mol` | | all | SI |

**See:** [AmountOfSubstance class documentation](QuantityType/AmountOfSubstance.md)

---

## Catalytic Activity

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| katal | `kat` | | all | SI |

**Expansion:** katal = mol·s⁻¹

**See:** [CatalyticActivity class documentation](QuantityType/CatalyticActivity.md)

---

## Radiation Dose

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| gray | `Gy` | | all | SI |
| sievert | `Sv` | | all | SI |

**Note:** Gray measures absorbed dose; sievert measures equivalent dose. Both have dimension L²·T⁻².

**Expansion:** gray = sievert = m²·s⁻²

**See:** [RadiationDose class documentation](QuantityType/RadiationDose.md)

---

## Data

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| bit | `b` | | metric + binary | Common |
| byte | `B` | | metric + binary | Common |

**See:** [Data class documentation](QuantityType/Data.md)

---

## Dimensionless

| Name | ASCII Symbol | Unicode Symbol | Prefixes | Systems |
|------|--------------|----------------|----------|---------|
| scalar | *(empty)* | | | Common |
| percentage | `%` | | | Common |
| parts per thousand | `ppt` | `‰` | | Common |
| parts per million | `ppm` | | | Common |
| parts per billion | `ppb` | | | Common |

**See:** [Dimensionless class documentation](QuantityType/Dimensionless.md)

---

## Loading Additional Systems

By default, only SI, SI Accepted, and Common units are loaded. To use Imperial, US Customary, or other system units, load them first:

```php
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;

// Load Imperial and US Customary units
UnitRegistry::loadSystem(System::Imperial);
UnitRegistry::loadSystem(System::UsCustomary);

// Now you can use feet, pounds, gallons, etc.
$length = new Length(100, 'ft');
```

## See Also

- **[QuantityType/](QuantityType/)** - Documentation for all quantity type classes
- **[System](System.md)** - Measurement system classification
- **[Prefix](Prefix.md)** - SI and binary prefixes
- **[Unit](Unit.md)** - Unit class documentation
- **[Quantity](Quantity.md)** - Working with quantities
