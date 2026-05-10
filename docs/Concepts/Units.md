# Units

The package provides a relatively comprehensive set of units that should be sufficient for most use cases in modern programming. You can also add your own custom units.

---

## A note on spelling

US spelling is used for the units "meter" and "liter", rather than the British English spellings of "metre" and "litre", respectively. This is because US spelling is more commonly used in programming languages and open source code, and the majority of the user base is likely to be more familiar with US spelling. It doesn't actually matter; users of the package only need to know the symbols "m" and "L".

Note, however, that the prefix meaning 10 is spelled "deca" within the package, which is the official international spelling, rather than "deka", as sometimes used in US English.

---

## Unit symbols

All units can have at least one and up to three symbols, with additional symbols formed by combination with prefixes.

1. **ASCII symbol.** Every unit has one, and they must be unique. The ASCII symbol is necessary for easy typing on a regular keyboard.
2. **Unicode symbol.** In most cases this matches the ASCII symbol. It differs only for units conventionally expressed with non-ASCII characters, such as `°` or `Ω`. When formatting, the Unicode symbol is preferred; if none is specified, the ASCII symbol is used.
3. **Alternate symbol.** An additional single-character symbol accepted by `parse()` methods, but never used by `format()`.

The following table shows all units that have a Unicode and/or alternate symbol defined:

| Unit | Quantity | ASCII | Unicode | Alternate |
|------|----------|-------|---------|-----------|
| degree of arc | Angle | `deg` | `°` | |
| arcminute | Angle | `arcmin` | `′` | `'` |
| arcsecond | Angle | `arcsec` | `″` | `"` |
| degrees Celsius | Temperature | `degC` | `°C` | |
| degrees Fahrenheit | Temperature | `degF` | `°F` | |
| degrees Rankine | Temperature | `degR` | `°R` | |
| liter | Volume | `L` | | `l` |
| ohm | Resistance | `ohm` | `Ω` (U+03A9) | `Ω` (U+2126) |
| per mille | Dimensionless | `ppt` | `‰` | |

Notes:
- `deg` matches [CSS notation](https://developer.mozilla.org/en-US/docs/Web/CSS/Reference/Values/angle) for angles.
- The two ohm symbols are visually identical but distinct Unicode code points: U+03A9 (GREEK CAPITAL LETTER OMEGA) is used for formatting; U+2126 (OHM SIGN) is accepted by the parser for backward compatibility.

---

## Notes on specific symbols

In some cases a common unit symbol may not be supported. One reason is because the package relies on unit symbols being unique. Otherwise, it could be necessary for consistency, or for prefixes or conversions to work properly (e.g. `kcal` instead of `Cal`, `lbf/in2` instead of `psi`); or it could be a stylistic choice (e.g. `L` instead of `l`).

1. Use `p` (lower-case) for picas, not `pc`, which means parsec.
2. Don't use `pt` for pint, which means point, a typographical unit. For pints, use either `imp pt` for imperial pints, or `US pt` for US pints.
3. Use `ft` for feet, not `′` (the prime symbol), which means arcminutes.
4. Use `in` for inches, not `″` (the double prime symbol), which means arcseconds.
5. Use `arcmin` for arcminute, not `am`, which means attometer.
6. Use `arcsec` for arcsecond, not `as`, which means attosecond. For milliarcsecond, use `marcsec`, not `mas`.
7. Use `°C` or `degC` for degrees Celsius, not `C`, which means coulomb, the unit for electric charge.
8. Use `°F` or `degF` for degrees Fahrenheit, not `F`, which means farad, the unit for electric capacitance.
9. Use `°R` or `degR` for degrees Rankine, not `R`. This is just for consistency; `R` is not currently used for any other unit.
10. Use `kcal` for kilocalorie (a.k.a. 'large' or 'food' calorie), not `Cal`. Use `cal` for calorie, i.e. 'small' calorie.
11. Use `cm3` for cubic centimeters, not `cc`.
12. Use `lbf/in2` for pounds force per square inch, not `psi`.
13. Use `km/h` for kilometers per hour, not `kph`.
14. Use `min` for minutes, not `mi`, which means miles.
15. Use `mi/h` for miles per hour, not `mph`. 
16. Use `u` or `µ` for the 'micro' prefix, not `mc`. e.g. for microgram use `ug` or `µg`, not `mcg`. 
17. Use `ppt` for 'parts per thousand'. There is no built-in unit for 'parts per trillion'.
18. Use `mA*h` for battery capacity, not `mAh`.
19. Use `kW*h` for energy consumption, not `kWh`.

---

## Unit system codes

When it comes to volume units, certain units represent different amounts in different unit systems. For example an imperial pint is approximately equal to 568 mL, whereas a US pint is approximately 473 mL. These are disambiguated by a unit system code of either "imp" or "US" where needed, e.g. `imp pt` vs. `US pt`. See the [Volume](../Reference/QuantityType/Volume.md) documentation for details.

---

## See also

- **[Quantity Types](../Reference/QuantityType/)** — Documentation for all quantity type classes.
- **[UnitSystem](../Reference/Internal/UnitSystem.md)** - Measurement system classification
- **[Prefix](../Reference/Internal/Prefix.md)** - SI and binary prefixes
- **[Unit](../Reference/Internal/Unit.md)** - Unit class documentation
- **[Quantity](../Reference/Quantity.md)** - Quantity class documentation

