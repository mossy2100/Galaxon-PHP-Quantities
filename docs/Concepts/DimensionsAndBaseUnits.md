# Dimensions and Base Units

Every quantity has a dimension — an abstract description of what it measures (length, mass, time, etc.) independent of any particular unit. The Quantities package uses single-letter dimension codes to track these, and defines a set of base units that serve as the foundation for all unit conversions.

---

## Dimensions

Dimension codes represent the fundamental physical dimensions of a quantity without specifying actual units. Single-letter codes represent base dimensions. Zero or more of these can be combined, including with optional exponents, to represent various quantity types. Every quantity type has a unique dimension code. The page on [Quantity Types](QuantityTypes.md) shows dimension codes for all the built-in quantity types.

The standard base dimension codes, as defined by the [International System of Quantities](https://en.wikipedia.org/wiki/International_System_of_Quantities) (ISQ), are as follows:

| Code | Name                | SI Base Unit |
| ---- | ------------------- | ------------ |
| `L`  | length              | *m*          |
| `M`  | mass                | *kg*         |
| `T`  | time                | *s*          |
| `I`  | electric current    | *A*          |
| `Θ`  | temperature         | *K*          |
| `N`  | amount of substance | *mol*        |
| `J`  | luminous intensity  | *cd*         |

Additionally, a code of `1` indicates a dimensionless quantity, for example a concentration expressed in *parts per million* (`ppm`) or an interest rate expressed as a *percentage* (`%`).

The Quantities package takes some liberties with the official system, tweaking and expanding it as follows:

1. The code `H` is used for temperature instead of `Θ` (theta), for ease of typing on a standard keyboard. `H` was chosen because it suggests the word "heat", and also because capital theta has a little 'H' inside it.
2. The code `A` has been added for Angle quantities. In SI and ISQ, an angle is viewed as dimensionless because an angle in radians is equal to the ratio of arc length to radius (i.e. L/L = 1). However, for the purpose of this package it was useful to have a dedicated dimension for angles along with a dedicated `Angle` class.
3. The code `D` has been added to support data quantities.
4. The code `C` has been added to support currency quantities (note, however, that "currency" refers to the *unit* type, and "money" refers to the *quantity* type).
5. Rather than a code of `1` to indicate a dimensionless quantity, the package uses an empty string. This solved a problem related to PHP itself. (If you're interested, it was because PHP coerces numeric strings to integers when used as array keys, and dimension codes are used as array keys in some places. This behaviour of PHP complicated type safety and static analysis.)

This gives 10 base dimension codes, as shown in the next section.

---

## Base units

Base units are fundamental; they map to a single-letter dimension code. Furthermore, there is a single canonical base unit for each dimension in SI; these are known as SI base units, as shown above.

The package diverges slightly from SI in that *radians* (*rad*) are used as an SI base unit for angles. Technically, they are an SI *derived* unit, representing a dimensionless quantity.

The package also refers to "English" base units, being those used within the imperial and US customary systems of units. These are shown below.

Additionally, the notion of *common* base units is introduced, which belong to neither SI nor the English systems, but serve both:
1. Bytes (*B*) are used as the common base unit for data quantities. Although technically the *bit* (*b*) is more fundamental, *bytes* are much more common in practice.
2. Troy ounces of gold (*XAU*) are used as the common base currency, being the most internationally traded commodity currency.

The resulting dimension codes and base units used within the package are thus:

| Code | Name                | Common base unit | SI base unit | English base unit |
| ---- | ------------------- | ---------------- | ------------ | ----------------- |
| `L`  | length              |                  | *m*          | *ft*              |
| `M`  | mass                |                  | *kg*         | *lb*              |
| `T`  | time                |                  | *s*          |                   |
| `I`  | electric current    |                  | *A*          |                   |
| `H`  | temperature         |                  | *K*          | *°R*              |
| `N`  | amount of substance |                  | *mol*        |                   |
| `J`  | luminous intensity  |                  | *cd*         |                   |
| `A`  | angle               |                  | *rad*        | *°*               |
| `D`  | data                | *B*              |              |                   |
| `C`  | currency            | *XAU*            |              |                   |

Whenever converting a Quantity to SI base units, if none is defined for a given dimension, the algorithm will fall back to the common base unit.

Whenever converting a Quantity to English base units:
1. If none is defined for a given dimension, the algorithm will fall back to the SI base unit.
2. If there's also no SI base unit defined for that dimension, it will fall back to the common base unit.

---

## See also

- **[Terminology](Terminology.md)** — Key terms including base units, compound units, and dimensions.
- **[Quantity Types](QuantityTypes.md)** — Typed quantity classes and their dimension codes.
- **[Units](Units.md)** — Unit symbol conventions, notation rules, and supported non-ASCII and alternate symbols.
- **[DimensionService](../Reference/Services/DimensionService.md)** — Service for validating, decomposing, composing, and transforming dimension codes.
