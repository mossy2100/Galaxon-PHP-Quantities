# Galaxon PHP Quantities

Physical measurement types with automatic unit conversion and prefix support.

**[License](LICENSE)** | **[Changelog](CHANGELOG.md)** | **[Documentation](docs/)** | **[Supported Units](docs/SupportedUnits.md)** | **[Examples](docs/Examples.md)**

![PHP 8.4](docs/logo_php8_4.png)

## Description

This package provides strongly-typed classes for physical measurements (length, mass, time, temperature, etc.) with comprehensive unit conversion capabilities. The system uses a graph-based algorithm to automatically find conversion paths between units, supports SI metric and binary prefixes, and handles affine transformations for temperature scales.

Key capabilities include:

- **Type-safe measurements**: Each measurement type (Length, Mass, Time, etc.) is a separate class preventing accidental mixing
- **Automatic conversion**: Convert between any compatible units without manual conversion factors
- **Prefix support**: Full SI metric prefixes (quecto to quetta) and binary prefixes (Ki, Mi, Gi, etc.)
- **Arithmetic operations**: Add, subtract, multiply, and divide measurements with automatic unit handling
- **Flexible parsing**: Parse strings like "123.45 km", "90deg", or "25°C" into measurement objects
- **Part decomposition**: Break measurements into components (e.g. 12° 34′ 56″ or 1y 3mo 2d)

## Development and Quality Assurance / AI Disclosure

[Claude Chat](https://claude.ai) and [Claude Code](https://www.claude.com/product/claude-code) were used in the development of this package. The core classes were designed, coded, and commented primarily by the author, with Claude providing substantial assistance with code review, suggesting improvements, debugging, and generating tests and documentation. All code was thoroughly reviewed by the author, and validated using industry-standard tools including [PHP_Codesniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer/), [PHPStan](https://phpstan.org/) (to level 9), and [PHPUnit](https://phpunit.de/index.html) to ensure full compliance with [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards and comprehensive unit testing with 100% code coverage. This collaborative approach resulted in a high-quality, thoroughly-tested, and well-documented package delivered in significantly less time than traditional development methods.

![Code Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen)

## Requirements

- PHP ^8.4
- galaxon/core

## Installation

```bash
composer require galaxon/quantities
```

## Quick Start

```php
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Temperature;
use Galaxon\Quantities\QuantityType\Angle;

// Create measurements
$distance = new Length(5, 'km');
$temp = new Temperature(25, 'degC');
$angle = new Angle(90, 'deg');

// Convert between units
$miles = $distance->to('mi');     // 3.10686... miles
$fahrenheit = $temp->to('degF');     // 77°F
$radians = $angle->to('rad');     // 1.5707... rad

// Arithmetic operations
$total = $distance->add(new Length(500, 'm'));  // 5.5 km
$doubled = $distance->mul(2.0);                // 10 km

// Parse from strings
$length = Length::parse('123.45 km');
$temp = Temperature::parse('98.6°F');
$angle = Angle::parse("45° 30' 15\"");

// Format as parts
$angle = new Angle(45.5042, 'deg');
echo $angle->formatParts(smallestUnitSymbol: 'arcsec', precision: 1);
// "45° 30′ 15.1″"
```

## Features and Examples

### Unit Conversion

The conversion system automatically finds paths between units using a graph-based algorithm. You only need to define direct conversions; indirect paths are computed automatically.

```php
// Direct conversion
$meters = new Length(1000, 'm');
$km = $meters->to('km');  // 1 km

// Indirect conversion (found automatically)
$feet = $meters->to('ft');  // 3280.84 ft
$miles = $meters->to('mi'); // 0.621371 mi
```

### Prefix Support

Quantities can accept SI metric prefixes, binary prefixes, or sometimes even both:

```php
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Data;

// Metric prefixes (quecto to quetta)
$nano = new Length(500, 'nm');    // nanometres
$kilo = new Length(5, 'km');      // kilometres
$mega = new Length(1, 'Mm');      // megametres

// Binary prefixes for memory
$kibi = new Data(1, 'KiB');     // 1024 bytes
$gibi = new Data(1, 'GiB');     // 1073741824 bytes

// Mixed prefix support
$megabytes = new Data(1, 'MB'); // 1000000 bytes (metric)
$mebibytes = new Data(1, 'MiB'); // 1048576 bytes (binary)
```

### Temperature Conversions

Temperature uses affine transformations (y = mx + k) to handle non-proportional scales:

```php
use Galaxon\Quantities\QuantityType\Temperature;

$celsius = new Temperature(0, 'degC');
echo $celsius->to('degF');  // 32°F
echo $celsius->to('K');     // 273.15K

$fahrenheit = new Temperature(212, 'degF');
echo $fahrenheit->to('degC');  // 100°C
```

### Arithmetic Operations

Quantities support addition, subtraction, multiplication, and division:

```php
$a = new Length(100, 'm');
$b = new Length(50, 'm');

$sum = $a->add($b);        // 150 m
$diff = $a->sub($b);       // 50 m
$scaled = $a->mul(2.0);    // 200 m
$halved = $a->div(2.0);    // 50 m
$abs = $diff->neg()->abs();  // 50 m

// Add with different units (auto-converted)
$total = $a->add(new Length(1, 'km'));  // 1100 m

// Convenience syntax
$total = $a->add(500, 'cm');  // 105 m
```

### Derived Quantity Arithmetic

Multiplying or dividing quantities of different types produces the correct derived quantity:

```php
use Galaxon\Quantities\QuantityType\Acceleration;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\QuantityType\Velocity;

// Force = Mass × Acceleration (F = m·a)
$mass = new Mass(10, 'kg');
$accel = new Acceleration(9.8, 'm/s2');
$force = $mass->mul($accel);  // 98 N (Force)

// Velocity = Length / Time (v = d/t)
$distance = new Length(100, 'km');
$time = new Time(2, 'h');
$speed = $distance->div($time);  // 50 km/h (Velocity)

// Length = Velocity × Time (d = v·t)
$speed = new Velocity(60, 'km/h');
$time = new Time(1.5, 'h');
$distance = $speed->mul($time);  // 90 km (Length)
```

### Comparison and Approximate Equality

Compare measurements with exact or approximate equality:

```php
$a = new Length(1000, 'm');
$b = new Length(1, 'km');

// Exact comparison
$a->compare($b);        // 0 (equal)
$a->lessThan($b);       // false
$a->greaterThan($b);    // false

// Approximate comparison (handles floating-point precision)
$a->approxEqual($b);    // true

// Angles use radians for tolerance
$angle1 = new Angle(180, 'deg');
$angle2 = new Angle(M_PI, 'rad');
$angle1->approxEqual($angle2);  // true
```

### Part Decomposition

Break measurements into component parts:

```php
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\QuantityType\Time;

// Angle to degrees, arcminutes, arcseconds
$angle = new Angle(45.5042, 'deg');
$parts = $angle->toParts(smallestUnitSymbol: 'arcsec', precision: 2);
// ['sign' => 1, 'deg' => 45, 'arcmin' => 30, 'arcsec' => 15.12]

echo $angle->formatParts(smallestUnitSymbol: 'arcsec', precision: 1);
// "45° 30′ 15.1″"

// Create from parts
$angle = Angle::fromParts(['deg' => 45, 'arcmin' => 30, 'arcsec' => 15.12]);

// Time to years, months, days, hours, minutes, seconds
$duration = new Time(90061, 's');
echo $duration->formatParts(smallestUnitSymbol: 's', precision: 0);
// "1d 1h 1min 1s"

// Convert to DateInterval
$interval = $duration->toDateInterval();
```

### Trigonometric Functions

The Angle class provides trigonometric and hyperbolic functions:

```php
$angle = new Angle(45, 'deg');

// Trigonometric
$angle->sin();  // 0.7071...
$angle->cos();  // 0.7071...
$angle->tan();  // 1.0

// Reciprocal functions
$angle->sec();  // 1.4142...
$angle->csc();  // 1.4142...
$angle->cot();  // 1.0

// Hyperbolic
$angle->sinh();
$angle->cosh();
$angle->tanh();
```

### Physical Constants

Access fundamental physical constants as Quantity objects:

```php
use Galaxon\Quantities\PhysicalConstant;

// Speed of light
$c = PhysicalConstant::speedOfLight();
echo $c->to('km/s');  // 299792.458 km/s

// Planck constant
$h = PhysicalConstant::planckConstant();

// Gravitational constant
$G = PhysicalConstant::gravitationalConstant();

// Elementary charge
$e = PhysicalConstant::elementaryCharge();

// Get by symbol
$c = PhysicalConstant::get('c');
```

See **[PhysicalConstant](docs/PhysicalConstant.md)** for the complete list of available constants.

For more examples including real-world physics and engineering calculations, see **[Examples](docs/Examples.md)**.

## Terminology

| Term            | Definition                                                                       |
|-----------------|----------------------------------------------------------------------------------|
| Unit            | A single-symbol measurement unit, e.g. m, s, N, lbf.                             |
| Base unit       | A unit that cannot be expanded, e.g. m, s, kg, lb.                               |
| Expandable unit | A unit with an expansion (equivalent derived unit), e.g. N, J, W, lbf, kn        |
| SI base unit    | One of the 7 fundamental SI units: m, kg, s, A, K, mol, cd                       |
| SI named unit   | An SI unit named after a person, e.g. newton (N), joule (J), watt (W)            |
| Unit term       | A unit with optional prefix and/or exponent, e.g. km, KiB, m², km³               |
| Derived unit    | Zero or more unit terms combined via multiplication/division, e.g. m/s, kg·m·s⁻² |
| Metric prefix   | A decimal scaling prefix, e.g. k (10³), M (10⁶), m (10⁻³)                        |
| Binary prefix   | A binary scaling prefix for data units, e.g. Ki (2¹⁰), Mi (2²⁰)                  | 

Note: The 'kg' is referred to as an SI base unit, despite having a prefix ('k'). This is an accepted irregularity.

## About Units

Please note, in some cases a conventional unit symbol may not be supported. The main reason is because the package
relies on unit symbols being unique. It could also be necessary for prefixes to work properly (e.g. 'kcal'); or it could
be a stylistic choice (e.g. 'L').

1. Use `p` (lower-case) for points (1/72 in), not `pt`, which means pint.
2. Use `P` (upper-case) for picas (12 points or 1/6 in), not `pc`, which means parsec.
3. Use `arcsec` for arcsecond, not `as`, which means attosecond.
4. Use `ft` for feet, not `′` (the prime symbol), which means arcminutes.
5. Use `in` for inches, not `″` (the double prime symbol), which means arcseconds.
6. Use `°C` or `degC` (see below) for degrees Celsius, not `C`, which means coulomb, the unit for electric charge.
7. Use `°F` or `degF` for degrees Fahrenheit, not `F`, which means farad, the unit for electric capacitance.
8. Use `°R` or `degR` for degrees Rankine, not `R`. This is just for consistency; `R` is not currently used for any other unit.
9. Use `kcal` for kilocalorie (a.k.a. 'large' or 'food' calorie), not `Cal`. Use `cal` for calorie, i.e. 'small' calorie. 
10. Use `L` for litre, not `l`, following modern style guides, as `l` is deemed too similar to the digit `1`.
11. Use `lbf/in2` for pounds force per square inch, not `psi`.
12. Use `cm3` for cubic centimetres, not `cc`.
13. Use `km/h` for kilometres per hour, not `kph`.
14. Use `mi/h` for miles per hour, not `mph`. 
15. Use `u` as an ASCII alternative for `µ`, meaning 'micro', and not `mc`. e.g. use `ug` as the ASCII alternative to `µg`, not `mcg`. 

All units have a "primary" symbol, which uses ASCII characters only, so they are easy to type.
Therefore, you can use the following:

1. `deg` in place of `°`
2. `arcmin` in place of `′`
3. `arcsec` in place of `″`
2. `degC` in place of `°C`
2. `degF` in place of `°F`
3. `degR` in place of `°R`
4. `ohm` in place of `Ω`

## Classes

### Public API

| Class | Description |
|-------|-------------|
| [Quantity](docs/Quantity.md) | Abstract base class for all measurement types. Provides unit conversion, arithmetic operations, comparison, formatting, and part decomposition. |
| [PhysicalConstant](docs/PhysicalConstant.md) | Access to physical constants (speed of light, Planck constant, etc.) as Quantity objects. |
| [System](docs/System.md) | Enum for measurement systems (SI, Imperial, US Customary, etc.). |

### Quantity Types

All quantity type classes extend `Quantity` and define their specific units and conversions. See **[SupportedUnits](docs/SupportedUnits.md)** for a complete reference of all units organized by quantity type.

| Class | Dimension | SI Unit | Description |
|-------|-----------|---------|-------------|
| [Acceleration](docs/QuantityType/Acceleration.md) | T-2L | m/s²    | Rate of change of velocity. |
| [AmountOfSubstance](docs/QuantityType/AmountOfSubstance.md) | N | mol     | SI base quantity for counting entities. |
| [Angle](docs/QuantityType/Angle.md) | A | rad     | Angular measurements with trig functions. |
| [Area](docs/QuantityType/Area.md) | L2 | m²      | Two-dimensional extent. |
| [Capacitance](docs/QuantityType/Capacitance.md) | T4I2L-2M-1 | F       | Ability to store electric charge. |
| [CatalyticActivity](docs/QuantityType/CatalyticActivity.md) | T-1N | kat     | Rate of catalysis. |
| [Conductance](docs/QuantityType/Conductance.md) | T3I2L-2M-1 | S       | Electrical conductance. |
| [Data](docs/QuantityType/Data.md) | D | B       | Digital storage with metric and binary prefixes. |
| [Density](docs/QuantityType/Density.md) | L-3M | kg/m³   | Mass per unit volume. |
| [Dimensionless](docs/QuantityType/Dimensionless.md) | 1 | —       | Ratios, percentages, and pure numbers. |
| [ElectricCharge](docs/QuantityType/ElectricCharge.md) | TI | C       | Quantity of electricity. |
| [ElectricCurrent](docs/QuantityType/ElectricCurrent.md) | I | A       | SI base quantity for electric current. |
| [Energy](docs/QuantityType/Energy.md) | T-2L2M | J       | Capacity to do work. |
| [Force](docs/QuantityType/Force.md) | T-2LM | N       | Interaction causing acceleration. |
| [Frequency](docs/QuantityType/Frequency.md) | T-1 | Hz, Bq  | Cycles per unit time. |
| [Illuminance](docs/QuantityType/Illuminance.md) | L-2J | lx      | Luminous flux per area. |
| [Inductance](docs/QuantityType/Inductance.md) | T-2L2MI-2 | H       | Property opposing current change. |
| [Length](docs/QuantityType/Length.md) | L | m       | SI base quantity for distance. |
| [LuminousFlux](docs/QuantityType/LuminousFlux.md) | AJ | lm      | Perceived light power. |
| [LuminousIntensity](docs/QuantityType/LuminousIntensity.md) | J | cd      | SI base quantity for luminous intensity. |
| [MagneticFlux](docs/QuantityType/MagneticFlux.md) | T-2L2MI-1 | Wb      | Total magnetic field through surface. |
| [MagneticFluxDensity](docs/QuantityType/MagneticFluxDensity.md) | T-2MI-1 | T       | Magnetic field strength. |
| [Mass](docs/QuantityType/Mass.md) | M | kg      | SI base quantity for mass. |
| [Power](docs/QuantityType/Power.md) | T-3L2M | W       | Rate of energy transfer. |
| [Pressure](docs/QuantityType/Pressure.md) | T-2L-1M | Pa      | Force per unit area. |
| [RadiationDose](docs/QuantityType/RadiationDose.md) | T-2L2 | Gy, Sv  | Absorbed and equivalent radiation dose. |
| [Resistance](docs/QuantityType/Resistance.md) | T-3L2MI-2 | Ω       | Opposition to electric current. |
| [SolidAngle](docs/QuantityType/SolidAngle.md) | A2 | sr      | Three-dimensional angular extent. |
| [Temperature](docs/QuantityType/Temperature.md) | H | K       | SI base quantity with affine conversions. |
| [Time](docs/QuantityType/Time.md) | T | s       | SI base quantity for duration. |
| [Velocity](docs/QuantityType/Velocity.md) | T-1L | m/s     | Rate of change of position. |
| [Voltage](docs/QuantityType/Voltage.md) | T-3L2MI-1 | V       | Electric potential difference. |
| [Volume](docs/QuantityType/Volume.md) | L3 | m³      | Three-dimensional extent. |

### Registry Classes

| Class | Description |
|-------|-------------|
| [UnitRegistry](docs/Registry/UnitRegistry.md) | Registry of known units organized by measurement system. Provides lazy loading and lookup methods. |
| [ConversionRegistry](docs/Registry/ConversionRegistry.md) | Registry of unit conversions organized by dimension. |
| [QuantityTypeRegistry](docs/Registry/QuantityTypeRegistry.md) | Registry mapping dimension codes to quantity type classes. |
| [PrefixRegistry](docs/Registry/PrefixRegistry.md) | Registry for SI and binary prefixes (lookup, filtering by group). |

### Internal Classes

| Class | Description |
|-------|-------------|
| [Unit](docs/Internal/Unit.md) | Represents a single-symbol measurement unit with optional prefix support. |
| [UnitTerm](docs/Internal/UnitTerm.md) | A unit with optional prefix and exponent (e.g., km², ms⁻¹). |
| [DerivedUnit](docs/Internal/DerivedUnit.md) | Compound unit expression combining unit terms via multiplication/division. |
| [Prefix](docs/Internal/Prefix.md) | SI metric and binary prefixes (kilo, mega, kibi, etc.). |
| [Conversion](docs/Internal/Conversion.md) | Represents a unit conversion with factor and error tracking. |
| [Converter](docs/Internal/Converter.md) | Graph-based algorithm for finding conversion paths between units. |
| [Dimensions](docs/Internal/Dimensions.md) | Utilities for working with physical dimension codes (validation, composition, transformation). |
| [FloatWithError](docs/Internal/FloatWithError.md) | Floating-point numbers with tracked error bounds for precision monitoring. |
| [QuantityType](docs/Internal/QuantityType.md) | Data class representing a quantity type with its dimension, SI unit, and PHP class. |

## Testing

The library includes comprehensive test coverage:

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test class
vendor/bin/phpunit tests/QuantityTest.php

# Run with coverage (generates HTML report and clover.xml)
composer test
```

## License

MIT License - see [LICENSE](LICENSE) for details

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Add tests for new functionality
4. Ensure all tests pass
5. Submit a pull request

For questions or suggestions, please [open an issue](https://github.com/mossy2100/PHP-Quantities/issues).

## Support

- **Issues**: https://github.com/mossy2100/PHP-Quantities/issues
- **Documentation**: See [docs/](docs/) directory for detailed class documentation
- **Examples**: See [Examples](docs/Examples.md) for real-world physics and engineering calculations

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and changes.
