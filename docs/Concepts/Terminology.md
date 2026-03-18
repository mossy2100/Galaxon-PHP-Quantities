# Terminology

Understanding certain terms used by the 

| Term               | Definition                                                                                                                                                                                                                                      |
| ------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Unit               | A single-symbol measurement unit, e.g. *m*, *s*, *N*, *lbf*.                                                                                                                                                                                    |
| Base unit          | A unit that cannot be expanded into more fundamental units, e.g. *m*, *s*, *kg*, *lb*.                                                                                                                                                          |
| SI base unit       | One of the 7 fundamental SI units: *m*, *kg*, *s*, *A*, *K*, *mol*, *cd*.                                                                                                                                                                       |
| English base unit  | A base unit within the imperial and US customary systems of units: *lb*, *ft*, *°R* (degrees Rankine), or *°* (degrees of angle).                                                                                                               |
| Common base unit   | Additional base units for non-physical quantities: *B* (bytes) and *XAU* (troy ounces of gold).                                                                                                                                                 |
| SI named unit      | An SI unit named after a person, e.g. newton (*N*), joule (*J*), watt (*W*).                                                                                                                                                                    |
| Prefix             | A code of 1-2 letters prepended to a unit symbol indicating a multiplication factor, e.g. *m* (10⁻³), *da* (10), *k* (10³), *Ki* (2¹⁰).                                                                                                         |
| Metric prefix      | A decimal scaling prefix, e.g. *c* (10⁻²), *h* (10²), *M* (10⁶).                                                                                                                                                                                |
| Engineering prefix | A metric prefix that represents a power of 1000, e.g. *k* (10³), *M* (10⁶), *m* (10⁻³).                                                                                                                                                         |
| Binary prefix      | A binary scaling prefix, as used for data units, e.g. *Ki* (2¹⁰), *Mi* (2²⁰).                                                                                                                                                                   |
| Unit term          | A unit with optional prefix and/or exponent, e.g. *km*, *KiB*, *m²*, *km³*.                                                                                                                                                                     |
| Derived unit       | A unit formed by combining zero or more unit terms via multiplication/division, e.g. *m/s*, *kg·m/s²*.                                                                                                                                          |
| Expandable unit    | A unit that substitutes for a derived unit, e.g. *N* can be expanded to *kg·m/s²*. This includes all SI named units plus a few others like *lbf*, *cal*, *eV*, and *kn*. Also, any derived unit that includes an expandable, unit, e.g. *N/m2*. |
| Dimension          | A code that expresses a quantity or unit type without specifying actual units, e.g. *L* for length, *M* for mass, *LT-1* for velocity, etc.                                                                                                     |
| Unit system        | A system of units, such as SI, imperial, US customary, financial, etc.                                                                                                                                                                          |

---

## See Also

- **[Dimensions and Base Units](DimensionsAndBaseUnits.md)** — Dimension codes, base units, and how the library tracks physical dimensions.
- **[Quantity Types](QuantityTypes.md)** — Typed quantity classes and how they map to dimensions.
- **[Prefixes](Prefixes.md)** — Metric, engineering, and binary prefixes for scaling units.
- **[Systems of Units](SystemsOfUnits.md)** — SI, Imperial, US Customary, and other unit systems.
- **[Units](Units.md)** — Complete reference of all built-in units.
