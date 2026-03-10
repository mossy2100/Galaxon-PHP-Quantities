# API Reference Index

Complete API reference for the Galaxon Quantities package.

---

## Core Classes

| Class | Description |
|-------|-------------|
| [Quantity](Quantity.md) | Base class for all physical quantities with unit conversion and arithmetic |
| [PhysicalConstant](PhysicalConstant.md) | Predefined physical constants (speed of light, Planck constant, etc.) |
| [UnitSystem](UnitSystem.md) | Enum categorizing units by measurement system (SI, Imperial, etc.) |

---

## Quantity Types

All quantity type classes extend [`Quantity`](Quantity.md) and inherit its full API for unit conversion, arithmetic, comparison, and formatting.

| Class                                                      | SI base unit |
| ---------------------------------------------------------- | ------------ |
| [Acceleration](QuantityType/Acceleration.md)               | m/s²         |
| [AmountOfSubstance](QuantityType/AmountOfSubstance.md)     | mol          |
| [Angle](QuantityType/Angle.md)                             | rad          |
| [Area](QuantityType/Area.md)                               | m²           |
| [Capacitance](QuantityType/Capacitance.md)                 | F            |
| [CatalyticActivity](QuantityType/CatalyticActivity.md)     | kat          |
| [Conductance](QuantityType/Conductance.md)                 | S            |
| [Data](QuantityType/Data.md)                               | B            |
| [Density](QuantityType/Density.md)                         | kg/m³        |
| [Dimensionless](QuantityType/Dimensionless.md)             | _(none)_     |
| [ElectricCharge](QuantityType/ElectricCharge.md)           | C            |
| [ElectricCurrent](QuantityType/ElectricCurrent.md)         | A            |
| [Energy](QuantityType/Energy.md)                           | J            |
| [Force](QuantityType/Force.md)                             | N            |
| [Frequency](QuantityType/Frequency.md)                     | Hz           |
| [Illuminance](QuantityType/Illuminance.md)                 | lx           |
| [Inductance](QuantityType/Inductance.md)                   | H            |
| [Length](QuantityType/Length.md)                           | m            |
| [LuminousFlux](QuantityType/LuminousFlux.md)               | lm           |
| [LuminousIntensity](QuantityType/LuminousIntensity.md)     | cd           |
| [MagneticFlux](QuantityType/MagneticFlux.md)               | Wb           |
| [MagneticFluxDensity](QuantityType/MagneticFluxDensity.md) | T            |
| [Mass](QuantityType/Mass.md)                               | kg           |
| [Money](QuantityType/Money.md)                             | XAU          |
| [Power](QuantityType/Power.md)                             | W            |
| [Pressure](QuantityType/Pressure.md)                       | Pa           |
| [RadiationDose](QuantityType/RadiationDose.md)             | Sv           |
| [Resistance](QuantityType/Resistance.md)                   | Ω            |
| [SolidAngle](QuantityType/SolidAngle.md)                   | sr           |
| [Temperature](QuantityType/Temperature.md)                 | K            |
| [Time](QuantityType/Time.md)                               | s            |
| [Velocity](QuantityType/Velocity.md)                       | m/s          |
| [Voltage](QuantityType/Voltage.md)                         | V            |
| [Volume](QuantityType/Volume.md)                           | m³           |

---

## Internal Classes

These classes form the internal architecture of the package. They are used by the public API but are not typically used directly.

| Class | Description |
|-------|-------------|
| [Conversion](Internal/Conversion.md) | A conversion factor between two units |
| [Converter](Internal/Converter.md) | Builds and executes multi-step unit conversion chains |
| [DerivedUnit](Internal/DerivedUnit.md) | A compound unit composed of unit terms (e.g. kg·m/s²) |
| [FloatWithError](Internal/FloatWithError.md) | Float with tracked absolute error for precision-aware arithmetic |
| [Prefix](Internal/Prefix.md) | A metric or binary prefix (e.g. kilo, mega, kibi) |
| [QuantityType](Internal/QuantityType.md) | Represents a registered quantity type with dimension and class binding |
| [Unit](Internal/Unit.md) | A single unit of measurement (e.g. meter, kilogram, second) |
| [UnitInterface](Internal/UnitInterface.md) | Interface implemented by Unit, UnitTerm, and DerivedUnit |
| [UnitTerm](Internal/UnitTerm.md) | A unit with optional prefix and exponent (e.g. km², µs⁻¹) |

---

## Service Classes

Static service classes that manage registries, parsing, and utility operations.

| Class | Description |
|-------|-------------|
| [ConversionService](Services/ConversionService.md) | Registry of unit conversion factors |
| [DimensionService](Services/DimensionService.md) | Validation, composition, and manipulation of dimension codes |
| [PrefixService](Services/PrefixService.md) | Registry of metric and binary prefixes |
| [QuantityPartsService](Services/QuantityPartsService.md) | Decompose quantities into parts (e.g. hours, minutes, seconds) |
| [QuantityTypeService](Services/QuantityTypeService.md) | Registry of quantity types and their class bindings |
| [RegexService](Services/RegexService.md) | Regex patterns and validation for unit symbols and quantities |
| [UnitService](Services/UnitService.md) | Registry of units, with system loading |

---

## Currency Classes

Classes for currency data management and exchange rate integration.

| Class | Description |
|-------|-------------|
| [CurrencyService](Currencies/CurrencyService.md) | Manages currency unit data and exchange rate conversions |
| [ExchangeRateServiceInterface](Currencies/ExchangeRateServices/ExchangeRateServiceInterface.md) | Contract for exchange rate providers |
| [CurrencyLayerService](Currencies/ExchangeRateServices/CurrencyLayerService.md) | CurrencyLayer API (USD-based, 1,000 req/month free) |
| [ExchangeRateApiService](Currencies/ExchangeRateServices/ExchangeRateApiService.md) | ExchangeRate-API (any base currency, 1,500 req/month free) |
| [FixerService](Currencies/ExchangeRateServices/FixerService.md) | Fixer.io API (EUR-based, 10,000 req/month free) |
| [FrankfurterService](Currencies/ExchangeRateServices/FrankfurterService.md) | Frankfurter API (ECB data, completely free, no API key) |
| [OpenExchangeRatesService](Currencies/ExchangeRateServices/OpenExchangeRatesService.md) | Open Exchange Rates API (USD-based, free tier available) |
