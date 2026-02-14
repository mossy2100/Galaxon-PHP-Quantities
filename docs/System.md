# System

Measurement system classification for units.

## Overview

The `System` enum categorizes units of measurement according to the measurement system they belong to. This classification allows filtering and grouping units by their origin and typical use context.

Units can belong to multiple systems simultaneously. For example, the foot belongs to both Imperial and US Customary systems, while the meter belongs to SI.

This is an unbacked enum (no string or int values).

## Cases

### Si

```php
case Si;
```

The International System of Units (SI). The modern form of the metric system and the world's most widely used system of measurement. Includes the seven SI base units (meter, kilogram, second, ampere, kelvin, mole, candela) and derived units with special names (newton, pascal, joule, etc.).

### SiAccepted

```php
case SiAccepted;
```

Units officially accepted for use with SI. These are non-SI units that are commonly used alongside SI units and are accepted by the BIPM. Examples include minute, hour, day, degree (angle), liter, and tonne.

### Common

```php
case Common;
```

Widely used units that don't belong to a specific measurement system. These are units with broad international usage that aren't part of the formal SI, Imperial, or US Customary systems.

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

Units primarily used in scientific contexts. Includes specialised units like the electron volt, atomic mass unit, and astronomical unit.

### Nautical

```php
case Nautical;
```

Units used in maritime and aviation navigation. Includes the nautical mile and knot.

### Typographical

```php
case Typographical;
```

Units used in typography and printing. Includes the point and pica for measuring font sizes and layout dimensions.

## Cases Summary

| Case            | Description                                                   |
|-----------------|---------------------------------------------------------------|
| `Si`            | International System of Units (metric base and derived units) |
| `SiAccepted`    | Non-SI units officially accepted for use with SI              |
| `Common`        | Widely used units without formal system classification        |
| `Imperial`      | British Imperial system of measurement                        |
| `UsCustomary`   | United States customary units                                 |
| `Scientific`    | Units for scientific applications                             |
| `Nautical`      | Units for maritime and aviation                               |
| `Typographical` | Units for typography and printing                             |

## Usage Examples

### Basic Usage

```php
use Galaxon\Quantities\System;

// Get a specific case
$system = System::Si;

// Matching on cases
$description = match ($system) {
    System::Si => 'International System of Units',
    System::Imperial => 'British Imperial',
    System::UsCustomary => 'US Customary',
    default => 'Other system',
};
```

### Checking Unit Systems

```php
use Galaxon\Quantities\Internal\Unit;use Galaxon\Quantities\System;

// Check if a unit belongs to a specific system
$unit = Unit::parse('m');
if ($unit->belongsToSystem(System::Si)) {
    echo "This is an SI unit";
}
```

### Loading Units by System

```php
use Galaxon\Quantities\Registry\UnitRegistry;use Galaxon\Quantities\System;

// Load Imperial and US Customary units
UnitRegistry::loadSystem(System::Imperial);
UnitRegistry::loadSystem(System::UsCustomary);
```

## Working with Cases

### Getting All Cases

```php
// Get all cases as an array
$cases = System::cases();
// Returns array of all 9 System cases
```

## See Also

- **[Unit](Internal/Unit.md)** - Units can belong to one or more systems
- **[UnitRegistry](Registry/UnitRegistry.md)** - Load units by measurement system
