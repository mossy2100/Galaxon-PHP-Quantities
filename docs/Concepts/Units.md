# Units

---

## Overview

The package provides a relatively comprehensive set of units that should be sufficient for the majority of use cases in modern programming. This page details the built-in units. Note, however, you can add your own.

---

## A note on spelling

US spelling is used for the units "meter" and "liter", rather than the British English spellings of "metre" and "litre", respectively. This is because US spelling is more commonly used in programming languages and open source code, and the majority of the user base is likely to be more familiar with US spelling. It doesn't actually matter; users of the package only need to know the symbols 'm' and 'L'.

Note, however, that the prefix meaning 10 is spelled "deca" within the package, which is the official international spelling, rather than "deka", as sometimes used in US English.

---

## Unit symbols

All units can have at least one and up to three symbols, with additional symbols formed by combination with prefixes.

### Symbol types

1. **ASCII symbol.** Every unit has one, and they must be unique. The ASCII symbol is necessary for easy typing in code on a regular keyboard.
2. **Unicode symbol.** This is defined for a handful of units only, the most common being the degree symbol (`°`) as used in the symbols for degrees of angle, degrees Celsius or Fahrenheit, etc.; and the ohm symbol (`Ω`). When formatting a quantity or unit the Unicode symbol will be preferred, if specified; if none is specified, the ASCII symbol will be used.
3. **Alternate symbol.** This is an additional unit symbol accepted by `parse()` methods. It can only be one character, it doesn't combine with prefixes, and it is never used by `format()` methods. The only defaults are the single and double quote characters (i.e. `'` and `"`), which may be used for arcminutes and arcseconds respectively.

All units normally expressed with non-ASCII characters are assigned an ASCII symbol so they are easier to type on a standard keyboard. Therefore, you can use the following:

1. `deg` in place of `°` (this matches [CSS notation](https://developer.mozilla.org/en-US/docs/Web/CSS/Reference/Values/angle) for angles)
2. `arcmin` in place of `′`
3. `arcsec` in place of `″`
4. `degC` in place of `°C`
5. `degF` in place of `°F`
6. `degR` in place of `°R`
7. `ohm` in place of `Ω`
8. `ppt` in place of `‰`

### Notes on specific symbols

In some cases a conventional unit symbol may not be supported. The main reason is because the package relies on unit symbols being unique. It could also be necessary for prefixes to work properly (e.g. 'kcal'); or it could be a stylistic choice (e.g. 'L').

1. Use `p` (lower-case) for picas, not `pc`, which means parsec.
2. Don't use `pt` for pint, which means point, a typographical unit. For pints, use either `imp pt` for imperial pints, or `US pt` for US pints.
3. Use `arcsec` for arcsecond, not `as`, which means attosecond.
4. Use `ft` for feet, not `′` (the prime symbol), which means arcminutes.
5. Use `in` for inches, not `″` (the double prime symbol), which means arcseconds.
6. Use `°C` or `degC` for degrees Celsius, not `C`, which means coulomb, the unit for electric charge.
7. Use `°F` or `degF` for degrees Fahrenheit, not `F`, which means farad, the unit for electric capacitance.
8. Use `°R` or `degR` for degrees Rankine, not `R`. This is just for consistency; `R` is not currently used for any other unit.
9. Use `kcal` for kilocalorie (a.k.a. 'large' or 'food' calorie), not `Cal`. Use `cal` for calorie, i.e. 'small' calorie. 
10. Use `L` for liter, not `l`, following modern style guides, as `l` is deemed too similar to the digit `1`.
11. Use `lbf/in2` for pounds force per square inch, not `psi`.
12. Use `cm3` for cubic centimeters, not `cc`.
13. Use `km/h` for kilometers per hour, not `kph`.
14. Use `mi/h` for miles per hour, not `mph`. 
15. Use `u` or `µ` for the 'micro' prefix, not `mc`. e.g. for microgram use `ug` or `µg`, not `mcg`. 
16. Use `ppt` for 'parts per thousand'. There is no built-in unit for 'parts per trillion'.
17. Use `min` for minutes, not `mi`, which means miles.
18. Use `mA*h`, not `mAh`, for battery capacity.
19. Use `kW*h`, not `kWh`, for energy consumption.

### Unit system codes

When it comes to volume units, certain units represent different amounts in different unit systems. For example an imperial pint is approximately equal to 568 mL, whereas a US pint is approximately 473 mL. These are disambiguated by a unit system code of either "imp" or "US" where needed, e.g. "imp pt" vs. "US pt". Refer to the section on Volume units, below.

---

## Built-in units by quantity type

The following tables detail the built-in units.

The **Prefixes** column indicates which prefixes are supported by a given unit:
- **all metric** - All metric prefixes (*q, r, y, z, a, f, p, n, μ, m, c, d, da, h, k, M, G, T, P, E, Z, Y, R, Q*)
- **large metric** - Large engineering prefixes (*k, M, G, T, P, E, Z, Y, R, Q*)
- **small metric** - Small engineering prefixes (*q, r, y, z, a, f, p, n, μ, m*)
- **large metric and binary** - Large engineering and binary prefixes (*Ki, Mi, Gi, Ti, Pi, Ei, Zi, Yi, Ri, Qi*)

### Length

| Name              | ASCII symbol | Unicode symbol | Prefixes     | Systems                 |
| ----------------- | ------------ | -------------- | ------------ | ----------------------- |
| meter             | `m`          |                | all metric   | SI                      |
| astronomical unit | `au`         |                |              | SI Accepted, Scientific |
| light year        | `ly`         |                |              | Scientific              |
| parsec            | `pc`         |                | large metric | Scientific              |
| pixel             | `px`         |                |              | CSS                     |
| point             | `pt`¹        |                |              | CSS                     |
| pica              | `p`²         |                |              | CSS                     |
| inch              | `in`         |                |              | Imperial, US Customary  |
| foot              | `ft`         |                |              | Imperial, US Customary  |
| yard              | `yd`         |                |              | Imperial, US Customary  |
| mile              | `mi`         |                |              | Imperial, US Customary  |
| fathom            | `ftm`        |                |              | Nautical                |
| nautical mile     | `nmi`        |                |              | Nautical                |
**Note 1:** The abbreviation `pt`, for point, can also mean pint. Unit uniqueness is maintained because the unit symbols for pints within the package are `imp pt` and `US pt`.

**Note 2:** While CSS uses `pc` for picas, the package instead uses `p` for several reasons:
1. `pc` is the symbol for parsec, and there's no obvious alternative.
2. Lower-case `p` is used for picas in design software like Adobe InDesign and QuarkXPress, so there is an established precedent.
3. Picas are not often used in CSS, so it shouldn't be a huge problem.

**See:** [Length class documentation](../Reference/QuantityType/Length.md)

---

### Mass

| Name       | ASCII symbol | Unicode symbol | Prefixes   | Systems                |
| ---------- | ------------ | -------------- | ---------- | ---------------------- |
| gram       | `g`          |                | all metric | SI                     |
| tonne      | `t`          |                |            | SI Accepted            |
| dalton     | `Da`         |                |            | SI Accepted            |
| grain      | `gr`         |                |            | Imperial, US Customary |
| ounce      | `oz`         |                |            | Imperial, US Customary |
| troy ounce | `oz t`       |                |            | Imperial, US Customary |
| pound      | `lb`         |                |            | Imperial, US Customary |
| stone      | `st`         |                |            | Imperial               |
| short ton  | `tn`         |                |            | US Customary           |
| long ton   | `LT`         |                |            | Imperial               |

**Note:** The SI base unit for mass is the kilogram (kg), not the gram.

**See:** [Mass class documentation](../Reference/QuantityType/Mass.md)

---

### Time

| Name    | ASCII symbol | Unicode symbol | Prefixes   | Systems     |
|---------|--------------|----------------|------------|-------------|
| second  | `s`          |                | all metric | SI          |
| minute  | `min`        |                |            | SI Accepted |
| hour    | `h`          |                |            | SI Accepted |
| day     | `d`          |                |            | SI Accepted |
| week    | `w`          |                |            | Common      |
| month   | `mo`         |                |            | Common      |
| year    | `y`          |                |            | Common      |
| century | `c`          |                |            | Common      |

**See:** [Time class documentation](../Reference/QuantityType/Time.md)

---

### Temperature

| Name       | ASCII symbol | Unicode symbol | Prefixes   | Systems                |
| ---------- | ------------ | -------------- | ---------- | ---------------------- |
| kelvin     | `K`          |                | all metric | SI                     |
| celsius    | `degC`       | `°C`           |            | SI                     |
| fahrenheit | `degF`       | `°F`           |            | Imperial, US Customary |
| rankine    | `degR`       | `°R`           |            | Imperial, US Customary |

**Note:** Temperature conversions between Celsius/Fahrenheit and Kelvin/Rankine include offsets and are handled specially.

**See:** [Temperature class documentation](../Reference/QuantityType/Temperature.md)

---

### Angle

| Name      | ASCII symbol | Unicode symbol | Alternate Symbol | Prefixes     | Systems     |
| --------- | ------------ | -------------- | ---------------- | ------------ | ----------- |
| radian    | `rad`        |                |                  | all metric   | SI          |
| degree    | `deg`        | `°`            |                  |              | SI Accepted |
| arcminute | `arcmin`     | `′`            | `'`              |              | SI Accepted |
| arcsecond | `arcsec`     | `″`            | `"`              | small metric | SI Accepted |
| gradian   | `grad`       |                |                  |              | Common      |
| turn      | `turn`       |                |                  |              | Common      |

**See:** [Angle class documentation](../Reference/QuantityType/Angle.md)

---

### Solid Angle

| Name       | ASCII symbol | Unicode symbol | Prefixes     | Systems |
|------------|--------------|----------------|--------------|---------|
| steradian  | `sr`         |                | small metric | SI      |

**See:** [SolidAngle class documentation](../Reference/QuantityType/SolidAngle.md)

---

### Area

| Name    | ASCII symbol | Unicode symbol | Prefixes | Systems                |
| ------- | ------------ | -------------- | -------- | ---------------------- |
| hectare | `ha`         |                |          | SI Accepted, Metric    |
| acre    | `ac`         |                |          | Imperial, US Customary |

**Note:** Square units like m², km², ft², etc. are automatically supported through unit arithmetic.

**See:** [Area class documentation](../Reference/QuantityType/Area.md)

---

### Volume

| Name                 | ASCII symbol | Unicode symbol | Prefixes   | Systems             |
| -------------------- | ------------ | -------------- | ---------- | ------------------- |
| liter                | `L`          |                | all metric | SI Accepted, Metric |
| metric cup           | `cup`        |                |            | Metric              |
| metric tablespoon    | `tbsp`       |                |            | Metric              |
| metric teaspoon      | `tsp`        |                |            | Metric              |
| imperial gallon      | `imp gal`    |                |            | Imperial            |
| imperial quart       | `imp qt`     |                |            | Imperial            |
| imperial pint        | `imp pt`     |                |            | Imperial            |
| imperial fluid ounce | `imp fl oz`  |                |            | Imperial            |
| imperial tablespoon  | `imp tbsp`   |                |            | Imperial            |
| imperial teaspoon    | `imp tsp`    |                |            | Imperial            |
| US gallon            | `US gal`     |                |            | US Customary        |
| US quart             | `US qt`      |                |            | US Customary        |
| US pint              | `US pt`      |                |            | US Customary        |
| US cup               | `US cup`     |                |            | US Customary        |
| US fluid ounce       | `US fl oz`   |                |            | US Customary        |
| US tablespoon        | `US tbsp`    |                |            | US Customary        |
| US teaspoon          | `US tsp`     |                |            | US Customary        |

**Note:** Cubic units like m³, cm³, ft³, etc. are automatically supported through unit arithmetic.

**See:** [Volume class documentation](../Reference/QuantityType/Volume.md)

---

### Velocity

| Name     | ASCII symbol | Unicode symbol | Prefixes | Systems  |
|----------|--------------|----------------|----------|----------|
| knot     | `kn`         |                |          | Nautical |

**Note:** Compound velocity units like m/s, km/h, mi/h, etc. are automatically supported through unit arithmetic.

**See:** [Velocity class documentation](../Reference/QuantityType/Velocity.md)

---

### Frequency

| Name      | ASCII symbol | Unicode symbol | Prefixes    | Systems  |
|-----------|--------------|----------------|-------------|----------|
| hertz     | `Hz`         |                | all metric  | SI       |
| becquerel | `Bq`         |                | all metric  | SI       |

**Note:** Hertz measures frequency; becquerel measures radioactivity. Both have dimension T⁻¹.

**See:** [Frequency class documentation](../Reference/QuantityType/Frequency.md)

---

### Force

| Name        | ASCII symbol | Unicode symbol | Prefixes    | Systems                |
|-------------|--------------|----------------|-------------|------------------------|
| newton      | `N`          |                | all metric  | SI                     |
| pound force | `lbf`        |                |             | Imperial, US Customary |

**Expansion:** newton = kg·m·s⁻²; pound force = lb·ft·s⁻² × g₀

**See:** [Force class documentation](../Reference/QuantityType/Force.md)

---

### Pressure

| Name         | ASCII symbol | Unicode symbol | Prefixes    | Systems      |
|--------------|--------------|----------------|-------------|--------------|
| pascal       | `Pa`         |                | all metric  | SI           |
| atmosphere   | `atm`        |                |             | Scientific   |
| mmHg         | `mmHg`       |                |             | Scientific   |
| inHg         | `inHg`       |                |             | US Customary |

**Expansion:** pascal = kg·m⁻¹·s⁻²

**See:** [Pressure class documentation](../Reference/QuantityType/Pressure.md)

---

### Energy

| Name                 | ASCII symbol | Unicode symbol | Prefixes     | Systems      |
|----------------------|--------------|----------------|--------------|--------------|
| joule                | `J`          |                | all metric   | SI           |
| electronvolt         | `eV`         |                | all metric   | SI Accepted  |
| calorie              | `cal`        |                | large metric | Common       |
| British thermal unit | `Btu`        |                |              | US Customary |

**Expansion:** joule = kg·m²·s⁻²

**See:** [Energy class documentation](../Reference/QuantityType/Energy.md)

---

### Power

| Name  | ASCII symbol | Unicode symbol | Prefixes    | Systems |
|-------|--------------|----------------|-------------|---------|
| watt  | `W`          |                | all metric  | SI      |

**Expansion:** watt = kg·m²·s⁻³

**See:** [Power class documentation](../Reference/QuantityType/Power.md)

---

### Electric Current

| Name    | ASCII symbol | Unicode symbol | Prefixes    | Systems |
|---------|--------------|----------------|-------------|---------|
| ampere  | `A`          |                | all metric  | SI      |

**See:** [ElectricCurrent class documentation](../Reference/QuantityType/ElectricCurrent.md)

---

### Electric Charge

| Name     | ASCII symbol | Unicode symbol | Prefixes    | Systems |
|----------|--------------|----------------|-------------|---------|
| coulomb  | `C`          |                | all metric  | SI      |

**Expansion:** coulomb = A·s

**See:** [ElectricCharge class documentation](../Reference/QuantityType/ElectricCharge.md)

---

### Voltage

| Name     | ASCII symbol | Unicode symbol | Prefixes    | Systems |
|----------|--------------|----------------|-------------|---------|
| volt     | `V`          |                | all metric  | SI      |

**Expansion:** volt = kg·m²·s⁻³·A⁻¹

**See:** [Voltage class documentation](../Reference/QuantityType/Voltage.md)

---

### Resistance

| Name     | ASCII symbol | Unicode symbol | Prefixes    | Systems |
|----------|--------------|----------------|-------------|---------|
| ohm      | `ohm`        | `Ω`            | all metric  | SI      |

**Expansion:** ohm = kg·m²·s⁻³·A⁻²

**See:** [Resistance class documentation](../Reference/QuantityType/Resistance.md)

---

### Conductance

| Name     | ASCII symbol | Unicode symbol | Prefixes    | Systems |
|----------|--------------|----------------|-------------|---------|
| siemens  | `S`          |                | all metric  | SI      |

**Expansion:** siemens = s³·A²·kg⁻¹·m⁻²

**See:** [Conductance class documentation](../Reference/QuantityType/Conductance.md)

---

### Capacitance

| Name     | ASCII symbol | Unicode symbol | Prefixes    | Systems |
|----------|--------------|----------------|-------------|---------|
| farad    | `F`          |                | all metric  | SI      |

**Expansion:** farad = s⁴·A²·kg⁻¹·m⁻²

**See:** [Capacitance class documentation](../Reference/QuantityType/Capacitance.md)

---

### Inductance

| Name     | ASCII symbol | Unicode symbol | Prefixes    | Systems |
|----------|--------------|----------------|-------------|---------|
| henry    | `H`          |                | all metric  | SI      |

**Expansion:** henry = kg·m²·s⁻²·A⁻²

**See:** [Inductance class documentation](../Reference/QuantityType/Inductance.md)

---

### Magnetic Flux

| Name     | ASCII symbol | Unicode symbol | Prefixes    | Systems |
|----------|--------------|----------------|-------------|---------|
| weber    | `Wb`         |                | all metric  | SI      |

**Expansion:** weber = kg·m²·s⁻²·A⁻¹

**See:** [MagneticFlux class documentation](../Reference/QuantityType/MagneticFlux.md)

---

### Magnetic Flux Density

| Name   | ASCII symbol | Unicode symbol | Prefixes    | Systems |
|--------|--------------|----------------|-------------|---------|
| tesla  | `T`          |                | all metric  | SI      |

**Expansion:** tesla = kg·s⁻²·A⁻¹

**See:** [MagneticFluxDensity class documentation](../Reference/QuantityType/MagneticFluxDensity.md)

---

### Luminous Intensity

| Name      | ASCII symbol | Unicode symbol | Prefixes    | Systems |
|-----------|--------------|----------------|-------------|---------|
| candela   | `cd`         |                | all metric  | SI      |

**See:** [LuminousIntensity class documentation](../Reference/QuantityType/LuminousIntensity.md)

---

### Luminous Flux

| Name    | ASCII symbol | Unicode symbol | Prefixes    | Systems |
|---------|--------------|----------------|-------------|---------|
| lumen   | `lm`         |                | all metric  | SI      |

**Expansion:** lumen = cd·sr

**See:** [LuminousFlux class documentation](../Reference/QuantityType/LuminousFlux.md)

---

### Illuminance

| Name  | ASCII symbol | Unicode symbol | Prefixes    | Systems |
|-------|--------------|----------------|-------------|---------|
| lux   | `lx`         |                | all metric  | SI      |

**Expansion:** lux = cd·sr·m⁻²

**See:** [Illuminance class documentation](../Reference/QuantityType/Illuminance.md)

---

### Amount of Substance

| Name  | ASCII symbol | Unicode symbol | Prefixes    | Systems |
|-------|--------------|----------------|-------------|---------|
| mole  | `mol`        |                | all metric  | SI      |

**See:** [AmountOfSubstance class documentation](../Reference/QuantityType/AmountOfSubstance.md)

---

### Catalytic Activity

| Name    | ASCII symbol | Unicode symbol | Prefixes    | Systems |
|---------|--------------|----------------|-------------|---------|
| katal   | `kat`        |                | all metric  | SI      |

**Expansion:** katal = mol·s⁻¹

**See:** [CatalyticActivity class documentation](../Reference/QuantityType/CatalyticActivity.md)

---

### Radiation Dose

| Name    | ASCII symbol | Unicode symbol | Prefixes    | Systems |
|---------|--------------|----------------|-------------|---------|
| gray    | `Gy`         |                | all metric  | SI      |
| sievert | `Sv`         |                | all metric  | SI      |

**Note:** Gray measures absorbed dose; sievert measures equivalent dose. Both have dimension L²·T⁻².

**Expansion:** gray = sievert = m²·s⁻²

**See:** [RadiationDose class documentation](../Reference/QuantityType/RadiationDose.md)

---

### Data

| Name    | ASCII symbol | Unicode symbol | Prefixes                | Systems |
|---------|--------------|----------------|-------------------------|---------|
| bit     | `b`          |                | large metric and binary | Common  |
| byte    | `B`          |                | large metric and binary | Common  |

**See:** [Data class documentation](../Reference/QuantityType/Data.md)

---

### Dimensionless

| Name               | ASCII symbol | Unicode symbol | Prefixes  | Systems  |
|--------------------|--------------|----------------|-----------|----------|
| scalar             | *(empty)*    |                |           | Common   |
| percentage         | `%`          |                |           | Common   |
| parts per thousand | `ppt`        | `‰`            |           | Common   |
| parts per million  | `ppm`        |                |           | Common   |
| parts per billion  | `ppb`        |                |           | Common   |

**See:** [Dimensionless class documentation](../Reference/QuantityType/Dimensionless.md)

---

## See Also

- **[QuantityType/](QuantityType/)** - Documentation for all quantity type classes
- **[UnitSystem](../Reference/UnitSystem.md)** - Measurement system classification
- **[Prefix](../Reference/Internal/Prefix.md)** - SI and binary prefixes
- **[Unit](../Reference/Internal/Unit.md)** - Unit class documentation
- **[Quantity](../Reference/Quantity.md)** - Working with quantities

