# Introduction

The Quantities package provides strongly-typed classes for physical measurements with automatic unit conversion. It covers common quantity types — length, mass, time, temperature, angle, volume, and more — with built-in support for SI, imperial, US customary, and other unit systems.

## Quick example

```php
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Time;

$distance = new Length(42.195, 'km');
echo $distance->to('mi');  // 26.219 mi

$time = new Time(7380, 's');
echo $time->formatParts();  // 2h 3min

$speed = $distance->div($time);
echo $speed;            // 5.7175 m/s
echo $speed->to('km/h');  // 20.584 km/h
```

## What the package handles

- **Unit conversion** — Convert between any compatible units, including compound units like `km/h` or `kg*m/s2`.
- **Prefix support** — SI metric prefixes (kilo, milli, micro, …) and binary prefixes (kibi, mebi, …).
- **Arithmetic** — Add, subtract, multiply, and divide quantities with automatic unit tracking.
- **String parsing** — Parse strings like `"25 degC"`, `"45° 30′ 15″"`, or `"3.5e-6 mol"` into quantity objects.
- **Part decomposition** — Break values into components (e.g. 1d 2h 30min or 11st 3lb).
- **Physical constants** — Built-in constants like the speed of light, Planck's constant, and Avogadro's number.

## How this guide is organised

1. **Concepts** — The building blocks: dimensions, units, prefixes, and quantity types.
2. **Working with Quantities** — Creating, converting, comparing, and formatting quantities.
3. **Specific Quantity Types** — Features unique to angle, time, mass, volume, temperature, and money.
4. **Customization** — Adding your own units, conversions, and quantity type classes.

Start with [Terminology](2.1_Terminology.md) if the concepts are new, or jump to [Creating Quantities](3.1_CreatingQuantities.md) if you want to dive straight in.
