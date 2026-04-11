# Unit Conversion

---

## Overview

The conversion system automatically finds paths between units using a graph-based algorithm. Only a minimum number of conversions are defined by the package; others are discovered automatically. All necessary conversions for the supported units are predefined. Great care has been taken to ensure conversions between any two compatible unit pairs are as accurate as possible, within the usual constraints of floating point numbers.

```php
// Direct conversion
$meters = new Length(1000, 'm');
$feet = $meters->to('ft');  // 3280.84 ft

// Indirect conversion (found automatically)
$miles = $meters->to('mi'); // 0.621371 mi
```

---

## SI to Imperial/US

```php
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;

// Length
$height = new Length(1.83, 'm');
echo $height->to('ft');   // 6.003937... ft
echo $height->to('in');   // 72.047244... in

// Mass
$weight = new Mass(70, 'kg');
echo $weight->to('lb');   // 154.32358... lb
echo $weight->to('st');   // 11.023113... st
```

---

## Base and Derived Units

Derived units like newtons, joules, and watts are shorthand for combinations of base units. You can convert to base units with `toBase()`, or substitute derived units for base-unit combinations with `toDerived()`.

```php
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Force;

// Convert to base units
$force = new Force(100, 'N');
echo $force->toBase();  // 100 kg*m/s2

// Substitute: base units -> derived unit
$base = Quantity::create(100, 'kg*m/s2');
echo $base->toDerived();  // 100 N

// Works with English (imperial/US customary) units too
$lbf = new Force(1, 'lbf');
echo $lbf->toBase();  // 32.174049... lb*ft/s2
```

---

## Auto-Prefixing

The `autoPrefix()` method selects the best engineering SI prefix (kilo, mega, milli, etc.) to keep the numeric value readable:

```php
use Galaxon\Quantities\QuantityType\Length;

// Large value -> auto-prefix picks km
$distance = new Length(42195, 'm');
echo $distance->autoPrefix();  // 42.195 km

// Small value -> auto-prefix picks mm
$thickness = new Length(0.0025, 'm');
echo $thickness->autoPrefix();  // 2.5 mm

// Very small value -> auto-prefix picks µm
$wavelength = new Length(0.00000055, 'm');
echo $wavelength->autoPrefix();  // 550 nm
```

The `toSi()` method converts to SI units and simplifies (e.g., kg\*m/s2 becomes N). Chain with `autoPrefix()` to also apply the best engineering prefix:

```php
use Galaxon\Quantities\QuantityType\Energy;

$energy = new Energy(1, 'Btu');
echo $energy->toSi();                // 1055.06 J
echo $energy->toSi()->autoPrefix();  // 1.05506 kJ
```

---

## Temperature Conversions

Most conversions involve a simple multiplication. The built-in `Temperature` class uses affine transformations (y = mx + k) to handle offset scales:

```php
use Galaxon\Quantities\QuantityType\Temperature;

$celsius = new Temperature(0, 'degC');
echo $celsius->to('degF');  // 32 degF
echo $celsius->to('K');     // 273.15 K

$fahrenheit = new Temperature(212, 'degF');
echo $fahrenheit->to('degC');  // 100 degC
```

---

## See Also

- **[Quantity — Transformation Methods](../Reference/Quantity.md#transformation-methods)** — Full reference for `to()`, `toSi()`, `toSiBase()`, `toEnglish()`, `toEnglishBase()`, `toBase()`, `toDerived()`, `merge()`, and `autoPrefix()`.
- **[Units](../Concepts/Units.md)** — Complete list of built-in units by quantity type.
- **[Arithmetic Operations](ArithmeticOperations.md)** — Multiply and divide to create compound units.
