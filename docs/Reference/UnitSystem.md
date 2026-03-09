## Overview

The `UnitSystem` enum categorizes units of measurement according to the measurement system they belong to. This classification allows filtering and grouping units by their origin and typical use context.

Units can belong to multiple systems simultaneously. For example, the *foot* belongs to both Imperial and US Customary systems, and the *liter* belongs to the SI Accepted and Metric systems.

This is an unbacked enum (no string or int values).

## Cases

### Si

```php
case Si;
```

The [International System of Units](https://en.wikipedia.org/wiki/International_System_of_Units) (SI). The modern form of the metric system and the world's most widely used system of measurement. Includes the seven SI base units (meter, kilogram, second, ampere, kelvin, mole, candela) and derived units with special names (newton, pascal, joule, etc.).

### SiAccepted

```php
case SiAccepted;
```

Units officially accepted for use with SI. These are non-SI units that are commonly used alongside SI units and are accepted by the [BIPM](https://en.wikipedia.org/wiki/International_Bureau_of_Weights_and_Measures). Examples include minute, hour, day, degree, astronomical unit, liter, and tonne. All from [this list](https://en.wikipedia.org/wiki/International_System_of_Units#Non-SI_units_accepted_for_use_with_SI) are supported out of the box except for the logarithmic units (neper, bel).

### Common

```php
case Common;
```

Widely used units that don't belong to a specific measurement system. These are units with broad international usage that aren't part of the formal SI, Imperial, or US Customary systems.

### Metric

```php
case Metric;
```

Non-SI metric units. These are units defined in metric terms but not part of formal SI or SI Accepted. Includes the liter, hectare, and metric culinary units (cup, tablespoon, teaspoon). Some units in this system are also members of other systems (e.g. liter is also SI Accepted).

### Imperial

```php
case Imperial;
```

The British Imperial system of units. Established by the Weights and Measures Act 1824 in the United Kingdom. Includes units like the imperial gallon, imperial pint, and imperial fluid ounce.

### UsCustomary

```php
case UsCustomary;
```

The United States customary system of units. Derived from English units and used in the United States. While sharing many unit names with Imperial (foot, inch, pound), some units differ in size (gallon, fluid ounce).

### Scientific

```php
case Scientific;
```

Units primarily used in scientific contexts. Includes specialised units like the electron volt, dalton, atmosphere, and light year.

### Nautical

```php
case Nautical;
```

Units used in maritime and aviation navigation. Includes the nautical mile, fathom, and knot.

### Css

```php
case Css;
```

Units used in CSS, typography, and printing. Includes the point and pica for measuring font sizes and layout dimensions, and the pixel for digital displays.

### Financial

```php
case Financial;
```

Currencies. Used for monetary quantities such as USD, EUR, GBP, etc.

### Custom

```php
case Custom;
```

The default system for user-defined units.

## Cases Summary

| Case          | Description                                                   |
| ------------- | ------------------------------------------------------------- |
| `Si`          | International System of Units (metric base and derived units) |
| `SiAccepted`  | Non-SI units officially accepted for use with SI              |
| `Common`      | Widely used units without formal system classification        |
| `Metric`      | Non-SI metric units (liters, hectares, cups, etc.)            |
| `Imperial`    | British Imperial system of measurement                        |
| `UsCustomary` | United States customary units                                 |
| `Scientific`  | Units for scientific applications                             |
| `Nautical`    | Units for maritime and aviation                               |
| `Css`         | Units for typography and display layouts                      |
| `Financial`   | Currencies                                                    |
| `Custom`      | User-defined units                                            |

## See Also

- **[Unit](Internal/Unit.md)** - Units can belong to one or more systems
- **[UnitService](Services/UnitService.md)** - Load units by measurement system
