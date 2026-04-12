# Units

The package provides a relatively comprehensive set of units that should be sufficient for the majority of use cases in modern programming. This page details the built-in units. Note, however, you can add your own.

---

## A note on spelling

US spelling is used for the units *meter* and *liter*, rather than the British English spellings of *metre* and *litre*, respectively. This is because US spelling is more commonly used in programming languages and open source code, and the majority of the user base is likely to be more familiar with US spelling. It doesn't actually matter; users of the package only need to know the symbols `m` and `L`.

Note, however, that the prefix meaning 10 is spelled *deca* within the package, which is the official international spelling, rather than *deka*, as sometimes used in US English.

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

In some cases a conventional unit symbol may not be supported. The main reason is because the package relies on unit symbols being unique. It could also be necessary for prefixes or conversions to work properly (e.g. `kcal` instead of `Cal`, `lbf/in2` instead of `psi`); or it could be a stylistic choice (e.g. `L` instead of `l`).

1. Use `p` (lower-case) for picas, not `pc`, which means parsec.
2. Don't use `pt` for pint, which means point, a typographical unit. For pints, use either `imp pt` for imperial pints, or `US pt` for US pints.
3. Use `arcsec` for arcsecond, not `as`, which means attosecond. For milliarcsecond, use `marcsec`, not `mas`.
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
18. Use `mA*h` for battery capacity, not `mAh`.
19. Use `kW*h` for energy consumption, not `kWh`.

---

## Unit system codes

When it comes to volume units, certain units represent different amounts in different unit systems. For example an imperial pint is approximately equal to 568 mL, whereas a US pint is approximately 473 mL. These are disambiguated by a unit system code of either "imp" or "US" where needed, e.g. `imp pt` vs. `US pt`. See the [Volume](../Reference/QuantityType/Volume.md) documentation for details.

---

## Prefix support

The **Prefixes** column in unit definition tables indicates which prefixes are supported by a given unit:
- **all metric** — All metric prefixes (*q, r, y, z, a, f, p, n, μ, m, c, d, da, h, k, M, G, T, P, E, Z, Y, R, Q*)
- **large metric** — Large engineering prefixes (*k, M, G, T, P, E, Z, Y, R, Q*)
- **small metric** — Small engineering prefixes (*q, r, y, z, a, f, p, n, μ, m*)
- **large metric and binary** — Large engineering and binary prefixes (*Ki, Mi, Gi, Ti, Pi, Ei, Zi, Yi, Ri, Qi*)

For the complete list of built-in units, see the "Unit definitions" section in each [quantity type](../Reference/QuantityType/) documentation page.

---

## See Also

- **[Quantity Types](../Reference/QuantityType/)** — Documentation for all quantity type classes.
- **[UnitSystem](../Reference/Internal/UnitSystem.md)** - Measurement system classification
- **[Prefix](../Reference/Internal/Prefix.md)** - SI and binary prefixes
- **[Unit](../Reference/Internal/Unit.md)** - Unit class documentation
- **[Quantity](../Reference/Quantity.md)** - Quantity class documentation

