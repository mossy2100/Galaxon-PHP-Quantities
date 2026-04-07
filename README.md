# Galaxon PHP Quantities

This package enables calculations and conversions with physical and non-physical quantities, including support for SI and other systems of units (including data and currencies), metric and binary prefixes, parsing, and formatting.

**[License](LICENSE)** | **[Changelog](CHANGELOG.md)** | **[Documentation](docs/)** | **[Examples](docs/WorkingWithQuantities/CalculationExamples.md)**

![PHP 8.4](docs/logo_php8_4.png)

---
## Description

This package provides strongly-typed classes for physical quantities (length, mass, time, temperature, etc.) and some non-physical quantities (data, money), with comprehensive unit conversion capabilities. The system automatically finds conversion factors between compatible units, supports metric and binary prefixes, and handles offsets for temperature scales.

Key capabilities include:

- **Type-safe measurements**: Each measurement type (Length, Mass, Time, etc.) is a separate class. You can easily create your own custom quantity type classes.
- **Automatic conversion**: Easily convert between any compatible units, with most common units built-in, including SI, imperial, US customary, scientific, nautical, CSS, and more.
- **Prefix support**: Full support for SI metric and binary prefixes.
- **Arithmetic operations**: Add, subtract, multiply, and divide quantities with automatic unit handling.
- **Flexible parsing**: Parse strings like "123.45 km", "90deg", or "25°C" into Quantity objects.
- **String formatting**: Format quantities as ASCII or Unicode, with configurable decimal places and locale-specific currency formatting.
- **Part decomposition**: Break measurements into components (e.g. 12° 34′ 56″ or 1y 3mo 2d).
- **Physical constants**: Built-in constants like the speed of light, Planck's constant, and Avogadro's number as Quantity objects.
- **Up-to-date exchange rates**: Updated automatically as needed using the exchange rate API of your choice.

---
## Development and Quality Assurance / AI Disclosure

[Claude Chat](https://claude.ai) and [Claude Code](https://www.claude.com/product/claude-code) were used in the development of this package. The core classes were designed, coded, and commented primarily by the author, with Claude providing substantial assistance with code review, suggesting improvements, debugging, and generating tests and documentation. All code was thoroughly reviewed by the author, and validated using industry-standard tools including [PHP_Codesniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer/), [PHPStan](https://phpstan.org/) (to level 9), and [PHPUnit](https://phpunit.de/index.html) to ensure full compliance with [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards and comprehensive unit testing with 100% code coverage. This collaborative approach resulted in a well-designed, production-quality, thoroughly-tested, and well-documented package delivered in significantly less time than with traditional development methods.

![Code Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen)

---
## Requirements

- PHP ^8.4
- galaxon/core

---
## Installation

```bash
composer require galaxon/quantities
```

---
## Quick Start

```php
use Galaxon\Quantities\PhysicalConstant;
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\QuantityType\Force;
use Galaxon\Quantities\QuantityType\Frequency;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Money;
use Galaxon\Quantities\QuantityType\Temperature;

// Create measurements
$distance = new Length(5, 'km');
$temp = new Temperature(25, 'degC');
$angle = new Angle(90, 'deg');
$price = new Money(4269, 'EUR');

// Convert between units
$miles = $distance->to('mi');        // 3.10686... miles
$fahrenheit = $temp->to('degF');     // 77°F
$radians = $angle->to('rad');        // 1.5707... rad
$dollars = $price->to('USD');        // e.g. 4621 USD

// Convert to SI units
$force = new Force(28000, 'lbf');
$force = $force->toSi();      // 124... kN
$force = $force->toSiBase();  // 124550... kg⋅m/s²

// Arithmetic operations
$total = $distance->add(new Length(500, 'm'));      // 5.5 km
$doubled = $distance->mul(2.0);                     // 10 km
$period = new Frequency(2.4, 'GHz')->inv()->toSi(); // 416.7... ps

// Physical constants
$h = PhysicalConstant::planck();
$c = PhysicalConstant::speedOfLight();

// Scientific and engineering calculations
$G = PhysicalConstant::gravitational();
$earthMass = new Mass(5.972e24, 'kg');
$moonMass = new Mass(7.342e22, 'kg');
$earthMoonDistance = new Length(3.844e8, 'm');
$gravity = $G->mul($earthMass)->mul($moonMass)->div($earthMoonDistance->sqr());
echo $gravity->to('N')->format('e', 2), "\n"; // 1.98×10²⁰ N

// Parse from strings
$length = Length::parse('123.45 km');
$temp = Temperature::parse('98.6°F');
$angle = Angle::parse("45° 30' 15\"");

// Format as parts
$angle = new Angle(45.5042, 'deg');
echo $angle->formatParts(precision: 1);
// "45° 30′ 15.1″"
```

---
## Concepts

Background on the domain model and how the library represents physical measurements.

1. **[Terminology](docs/Concepts/Terminology.md)** — Key terms and definitions used throughout the library.
2. **[Dimensions and Base Units](docs/Concepts/DimensionsAndBaseUnits.md)** — Dimension codes, base units, and how the library tracks physical dimensions.
3. **[Quantity Types](docs/Concepts/QuantityTypes.md)** — Typed quantity classes like Length, Mass, and Force, and how they map to dimensions.
4. **[Prefixes](docs/Concepts/Prefixes.md)** — Metric, engineering, and binary prefixes for scaling units.
5. **[Units](docs/Concepts/Units.md)** — Complete reference of all built-in units organised by quantity type.
6. **[Physical Constants](docs/Concepts/PhysicalConstants.md)** — Built-in physical constants as Quantity objects.

---

## Working with Quantities

Practical guides for using the library in your code.

1. **[Creating Quantities](docs/WorkingWithQuantities/CreatingQuantities.md)** — Creating new quantities with constructors and the factory method.
2. **[Unit Conversion](docs/WorkingWithQuantities/UnitConversion.md)** — Converting between units, expansion, simplification, and auto-prefixing.
3. **[Arithmetic Operations](docs/WorkingWithQuantities/ArithmeticOperations.md)** — Add, subtract, multiply, and divide quantities.
4. **[Calculation Examples](docs/WorkingWithQuantities/CalculationExamples.md)** — Real-world physics and engineering calculations.
5. **[Currency Calculations](docs/WorkingWithQuantities/CurrencyCalculations.md)** — Example conversions and calculations involving currencies.
6. **[Comparison Functions](docs/WorkingWithQuantities/ComparisonFunctions.md)** — Exact and approximate equality, ordering, and tolerances.
7. **[String Functions](docs/WorkingWithQuantities/StringFunctions.md)** — Parsing strings into quantities and formatting output (ASCII and Unicode).
8. **[Part Decomposition](docs/WorkingWithQuantities/PartDecomposition.md)** — Working with quantities as parts (e.g. 45° 30′ 15″ or 1h 30min 45s).
9. **[Customization](docs/WorkingWithQuantities/Customization.md)** — Adding custom units, conversions, and quantity type classes.

---
## Reference

### Main Public API

Other than the quantity type classes (below), these are the main classes you'll use.

| Class                                                  | Description                                                                                                                                     |
| ------------------------------------------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------- |
| [Quantity](docs/Reference/Quantity.md)                 | Base class for all measurement types. Provides unit conversion, arithmetic operations, comparison, formatting, and part decomposition.          |
| [PhysicalConstant](docs/Reference/PhysicalConstant.md) | Access to physical constants (speed of light, Planck constant, etc.) as Quantity objects.                                                       |

### Quantity Types

All quantity type classes extend `Quantity` and define their specific units and conversions. See **[Units](docs/Concepts/Units.md)** for a complete reference of all built-in units organized by quantity type.

| Class                                                                     | Dimension  | SI or common base unit | Description                                      |
| ------------------------------------------------------------------------- | ---------- | ---------------------- | ------------------------------------------------ |
| [Acceleration](docs/Reference/QuantityType/Acceleration.md)               | T-2L       | m/s²                   | Rate of change of velocity.                      |
| [AmountOfSubstance](docs/Reference/QuantityType/AmountOfSubstance.md)     | N          | mol                    | SI base quantity for counting entities.          |
| [Angle](docs/Reference/QuantityType/Angle.md)                             | A          | rad                    | Angular measurements with trig functions.        |
| [Area](docs/Reference/QuantityType/Area.md)                               | L2         | m²                     | Two-dimensional extent.                          |
| [Capacitance](docs/Reference/QuantityType/Capacitance.md)                 | T4I2L-2M-1 | F                      | Ability to store electric charge.                |
| [CatalyticActivity](docs/Reference/QuantityType/CatalyticActivity.md)     | T-1N       | kat                    | Rate of catalysis.                               |
| [Conductance](docs/Reference/QuantityType/Conductance.md)                 | T3I2L-2M-1 | S                      | Electrical conductance.                          |
| [Data](docs/Reference/QuantityType/Data.md)                               | D          | B                      | Digital storage with metric and binary prefixes. |
| [Density](docs/Reference/QuantityType/Density.md)                         | L-3M       | kg/m³                  | Mass per unit volume.                            |
| [Dimensionless](docs/Reference/QuantityType/Dimensionless.md)             | *empty*    | *empty*                | Ratios, percentages, and pure numbers.           |
| [ElectricCharge](docs/Reference/QuantityType/ElectricCharge.md)           | TI         | C                      | Quantity of electricity.                         |
| [ElectricCurrent](docs/Reference/QuantityType/ElectricCurrent.md)         | I          | A                      | SI base quantity for electric current.           |
| [Energy](docs/Reference/QuantityType/Energy.md)                           | T-2L2M     | J                      | Capacity to do work.                             |
| [Force](docs/Reference/QuantityType/Force.md)                             | T-2LM      | N                      | Interaction causing acceleration.                |
| [Frequency](docs/Reference/QuantityType/Frequency.md)                     | T-1        | Hz, Bq                 | Cycles per unit time.                            |
| [Illuminance](docs/Reference/QuantityType/Illuminance.md)                 | L-2J       | lx                     | Luminous flux per area.                          |
| [Inductance](docs/Reference/QuantityType/Inductance.md)                   | T-2L2MI-2  | H                      | Property opposing current change.                |
| [Length](docs/Reference/QuantityType/Length.md)                           | L          | m                      | SI base quantity for distance.                   |
| [LuminousFlux](docs/Reference/QuantityType/LuminousFlux.md)               | AJ         | lm                     | Perceived light power.                           |
| [LuminousIntensity](docs/Reference/QuantityType/LuminousIntensity.md)     | J          | cd                     | SI base quantity for luminous intensity.         |
| [MagneticFlux](docs/Reference/QuantityType/MagneticFlux.md)               | T-2L2MI-1  | Wb                     | Total magnetic field through surface.            |
| [MagneticFluxDensity](docs/Reference/QuantityType/MagneticFluxDensity.md) | T-2MI-1    | T                      | Magnetic field strength.                         |
| [Mass](docs/Reference/QuantityType/Mass.md)                               | M          | kg                     | SI base quantity for mass.                       |
| [Money](docs/Reference/QuantityType/Money.md)                             | C          | XAU                    | Currency conversions and calculations.           |
| [Power](docs/Reference/QuantityType/Power.md)                             | T-3L2M     | W                      | Rate of energy transfer.                         |
| [Pressure](docs/Reference/QuantityType/Pressure.md)                       | T-2L-1M    | Pa                     | Force per unit area.                             |
| [RadiationDose](docs/Reference/QuantityType/RadiationDose.md)             | T-2L2      | Gy, Sv                 | Absorbed and equivalent radiation dose.          |
| [Resistance](docs/Reference/QuantityType/Resistance.md)                   | T-3L2MI-2  | Ω                      | Opposition to electric current.                  |
| [SolidAngle](docs/Reference/QuantityType/SolidAngle.md)                   | A2         | sr                     | Three-dimensional angular extent.                |
| [Temperature](docs/Reference/QuantityType/Temperature.md)                 | H          | K                      | SI base quantity with affine conversions.        |
| [Time](docs/Reference/QuantityType/Time.md)                               | T          | s                      | SI base quantity for duration.                   |
| [Velocity](docs/Reference/QuantityType/Velocity.md)                       | T-1L       | m/s                    | Rate of change of position.                      |
| [Voltage](docs/Reference/QuantityType/Voltage.md)                         | T-3L2MI-1  | V                      | Electric potential difference.                   |
| [Volume](docs/Reference/QuantityType/Volume.md)                           | L3         | m³                     | Three-dimensional extent.                        |

### Services

These classes are predominantly internal, except for adding custom units via `UnitService::add()`, or registering new quantity types via `QuantityTypeService::add()`.

| Class                                                                   | Description                                                                                    |
|-------------------------------------------------------------------------|------------------------------------------------------------------------------------------------|
| [ConversionService](docs/Reference/Services/ConversionService.md)       | Manages unit conversions and converters.                                                       |
| [DimensionService](docs/Reference/Services/DimensionService.md)         | Utilities for working with physical dimension codes (validation, composition, transformation). |
| [PrefixService](docs/Reference/Services/PrefixService.md)               | Manages SI and binary prefixes (lookup, filtering by group).                                   |
| [QuantityPartsService](docs/Reference/Services/QuantityPartsService.md) | Decomposes quantities into parts (e.g. hours, minutes, seconds).                               |
| [QuantityTypeService](docs/Reference/Services/QuantityTypeService.md)   | Registry of quantity types keyed by dimension code.                                            |
| [UnitService](docs/Reference/Services/UnitService.md)                   | Registry of units with lookup, filtering, and loading by system.                               |

### Internal Types

These types provide the core functionality of the library and will typically not be used directly by end-users.

#### Classes

| Name                                                        | Description                                                                         |
|-------------------------------------------------------------|-------------------------------------------------------------------------------------|
| [Unit](docs/Reference/Internal/Unit.md)                     | Represents a single-symbol measurement unit with optional prefix support.           |
| [UnitTerm](docs/Reference/Internal/UnitTerm.md)             | A unit with optional prefix and exponent (e.g., km², ms⁻¹).                         |
| [CompoundUnit](docs/Reference/Internal/CompoundUnit.md)       | Compound unit expression combining unit terms via multiplication/division.          |
| [Prefix](docs/Reference/Internal/Prefix.md)                 | SI metric and binary prefixes (kilo, mega, kibi, etc.).                             |
| [Conversion](docs/Reference/Internal/Conversion.md)         | Represents a unit conversion with factor and error tracking.                        |
| [Converter](docs/Reference/Internal/Converter.md)           | Graph-based algorithm for finding conversion paths between units.                   |
| [FloatWithError](docs/Reference/Internal/FloatWithError.md) | Floating-point numbers with tracked error bounds for precision monitoring.          |
| [QuantityType](docs/Reference/Internal/QuantityType.md)     | Data class representing a quantity type with its dimension, SI unit, and PHP class. |

#### Interfaces

| Name | Description                                                      |
|------|------------------------------------------------------------------|
| [UnitInterface](docs/Reference/Internal/UnitInterface.md)   | Interface implemented by Unit, UnitTerm, and CompoundUnit.        |

#### Enums

| Name                                                 | Description                                                                         |
|------------------------------------------------------|-------------------------------------------------------------------------------------|
| [UnitSystem](docs/Reference/Internal/UnitSystem.md) | Enum for systems of units (SI, Imperial, US Customary, etc.).                       |

### Exceptions

Custom exception types to provide additional context when needed. Both extend `DomainException`, as they relate to provided values being outside a valid domain.

| Class                                                                                 | Description                                      |
|---------------------------------------------------------------------------------------|--------------------------------------------------|
| [UnknownUnitException](docs/Reference/Exceptions/UnknownUnitException.md)             | Thrown when a unit symbol cannot be resolved.     |
| [DimensionMismatchException](docs/Reference/Exceptions/DimensionMismatchException.md) | Thrown when units have incompatible dimensions.   |

### Currency Classes

Classes for currency data management and exchange rate integration.

| Class                                                                                                | Description                                                  |
|------------------------------------------------------------------------------------------------------|--------------------------------------------------------------|
| [CurrencyService](docs/Reference/Currencies/CurrencyService.md)                                     | Manages currency unit data and exchange rate conversions.     |
| [ExchangeRateServiceInterface](docs/Reference/Currencies/ExchangeRateServices/ExchangeRateServiceInterface.md) | Contract for exchange rate providers.               |
| [CurrencyLayerService](docs/Reference/Currencies/ExchangeRateServices/CurrencyLayerService.md)      | CurrencyLayer API (USD-based, 1,000 req/month free).         |
| [ExchangeRateApiService](docs/Reference/Currencies/ExchangeRateServices/ExchangeRateApiService.md)  | ExchangeRate-API (any base currency, 1,500 req/month free).  |
| [FixerService](docs/Reference/Currencies/ExchangeRateServices/FixerService.md)                      | Fixer.io API (EUR-based, 10,000 req/month free).             |
| [FrankfurterService](docs/Reference/Currencies/ExchangeRateServices/FrankfurterService.md)          | Frankfurter API (ECB data, completely free, no API key).      |
| [OpenExchangeRatesService](docs/Reference/Currencies/ExchangeRateServices/OpenExchangeRatesService.md) | Open Exchange Rates API (USD-based, free tier available).  |

---

## Testing

The library includes comprehensive test coverage:

```bash
# Run all tests
vendor/bin/phpunit

# Run a specific test file
vendor/bin/phpunit tests/Quantity/QuantityArithmeticTest.php

# Run tests matching a filter
vendor/bin/phpunit --filter=testCompare

# Run with coverage (generates HTML report and clover.xml)
composer test
```

---

## License

MIT License - see [LICENSE](LICENSE) for details

---

## Support

- **Issues**: https://github.com/mossy2100/PHP-Quantities/issues
- **Documentation**: See [docs/](docs/) directory for detailed class documentation

For questions or suggestions, please [open an issue](https://github.com/mossy2100/PHP-Quantities/issues).

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and changes.
