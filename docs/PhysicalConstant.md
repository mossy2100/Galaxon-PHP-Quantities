# PhysicalConstant

Provides access to fundamental physical constants as Quantity objects.

## Overview

The `PhysicalConstant` class provides a convenient way to access well-known physical constants as properly typed `Quantity` objects with their correct units. Constants are lazily created and cached for efficient reuse.

The class includes:
- All seven SI defining constants
- Universal constants (gravitational constant, reduced Planck constant)
- Electromagnetic constants (vacuum permittivity and permeability)
- Atomic and nuclear constants (electron mass, proton mass, etc.)
- Thermodynamic constants (gas constant, Stefan-Boltzmann constant)
- Other commonly used constants (standard gravity, standard atmosphere)

### Key Features

- Type-safe access to physical constants
- Lazy instantiation with caching
- Lookup by symbol or name
- Computed derived constants (e.g., reduced Planck constant)

## Static Methods for SI Defining Constants

### hyperfineTransition()

```php
public static function hyperfineTransition(): Quantity
```

The caesium-133 hyperfine transition frequency (deltaNuCs). Defines the second.

**Returns:**
- `Quantity` - 9,192,631,770 Hz (exact)

### speedOfLight()

```php
public static function speedOfLight(): Quantity
```

The speed of light in vacuum (c). Defines the metre.

**Returns:**
- `Quantity` - 299,792,458 m/s (exact)

### planck()

```php
public static function planck(): Quantity
```

The Planck constant (h). Defines the kilogram.

**Returns:**
- `Quantity` - 6.62607015e-34 J*s (exact)

### elementaryCharge()

```php
public static function elementaryCharge(): Quantity
```

The elementary charge (e). Defines the ampere.

**Returns:**
- `Quantity` - 1.602176634e-19 C (exact)

### boltzmann()

```php
public static function boltzmann(): Quantity
```

The Boltzmann constant (k). Defines the kelvin.

**Returns:**
- `Quantity` - 1.380649e-23 J/K (exact)

### avogadro()

```php
public static function avogadro(): Quantity
```

The Avogadro constant (NA). Defines the mole.

**Returns:**
- `Quantity` - 6.02214076e23 mol-1 (exact)

### luminousEfficacy()

```php
public static function luminousEfficacy(): Quantity
```

The luminous efficacy of 540 THz radiation (Kcd). Defines the candela.

**Returns:**
- `Quantity` - 683 lm/W (exact)

## Static Methods for Derived Constants

### reducedPlanck()

```php
public static function reducedPlanck(): Quantity
```

The reduced Planck constant (hbar = h / 2*pi).

**Returns:**
- `Quantity` - Computed from Planck constant with full precision

### gravitational()

```php
public static function gravitational(): Quantity
```

The Newtonian constant of gravitation (G).

**Returns:**
- `Quantity` - 6.67430e-11 m3/(kg*s2)

### vacuumPermittivity()

```php
public static function vacuumPermittivity(): Quantity
```

The vacuum electric permittivity (epsilon0).

**Returns:**
- `Quantity` - 8.8541878128e-12 F/m

### vacuumPermeability()

```php
public static function vacuumPermeability(): Quantity
```

The vacuum magnetic permeability (mu0).

**Returns:**
- `Quantity` - 1.25663706212e-6 N/A2

### electronMass()

```php
public static function electronMass(): Quantity
```

The electron rest mass (me).

**Returns:**
- `Quantity` - 9.1093837015e-31 kg

### protonMass()

```php
public static function protonMass(): Quantity
```

The proton mass (mp).

**Returns:**
- `Quantity` - 1.67262192369e-27 kg

### neutronMass()

```php
public static function neutronMass(): Quantity
```

The neutron mass (mn).

**Returns:**
- `Quantity` - 1.67492749804e-27 kg

### fineStructure()

```php
public static function fineStructure(): Quantity
```

The fine-structure constant (alpha). Dimensionless.

**Returns:**
- `Quantity` - 7.2973525693e-3

### rydberg()

```php
public static function rydberg(): Quantity
```

The Rydberg constant (Rinf).

**Returns:**
- `Quantity` - 10,973,731.568160 m-1

### bohrRadius()

```php
public static function bohrRadius(): Quantity
```

The Bohr radius (a0).

**Returns:**
- `Quantity` - 5.29177210903e-11 m

### gasConstant()

```php
public static function gasConstant(): Quantity
```

The molar gas constant (R = NA * k).

**Returns:**
- `Quantity` - 8.314462618 J/(mol*K)

### stefanBoltzmann()

```php
public static function stefanBoltzmann(): Quantity
```

The Stefan-Boltzmann constant (sigma).

**Returns:**
- `Quantity` - 5.670374419e-8 W/(m2*K4)

### standardGravity()

```php
public static function standardGravity(): Quantity
```

Standard acceleration of gravity (g).

**Returns:**
- `Quantity` - 9.80665 m/s2 (exact by definition)

### standardAtmosphere()

```php
public static function standardAtmosphere(): Quantity
```

Standard atmospheric pressure (atm).

**Returns:**
- `Quantity` - 101,325 Pa (exact by definition)

## Lookup Methods

### get()

```php
public static function get(string $symbol): ?Quantity
```

Get a constant by its symbol.

**Parameters:**
- `$symbol` (string) - The constant's symbol (case-sensitive)

**Returns:**
- `?Quantity` - The constant, or null if not found

**Examples:**
```php
$c = PhysicalConstant::get('c');      // Speed of light
$h = PhysicalConstant::get('h');      // Planck constant
$hbar = PhysicalConstant::get('hbar'); // Reduced Planck constant
```

### getByName()

```php
public static function getByName(string $name): ?Quantity
```

Get a constant by its name.

**Parameters:**
- `$name` (string) - The constant's name (case-insensitive, partial match)

**Returns:**
- `?Quantity` - The constant, or null if not found

**Examples:**
```php
$c = PhysicalConstant::getByName('speed of light');
$G = PhysicalConstant::getByName('gravitational');
```

### getAll()

```php
public static function getAll(): array
```

Get all available physical constants.

**Returns:**
- `array<string, Quantity>` - All constants keyed by symbol

## Usage Examples

### Using Constants in Calculations

```php
use Galaxon\Quantities\PhysicalConstant;
use Galaxon\Quantities\QuantityType\Energy;
use Galaxon\Quantities\QuantityType\Frequency;

// Calculate photon energy: E = h * f
$h = PhysicalConstant::planck();
$frequency = new Frequency(5e14, 'Hz');  // Green light

$energy = $h->mul($frequency);
echo $energy->toSi(); // Energy in joules

// Calculate de Broglie wavelength: lambda = h / p
$momentum = new Quantity(1e-24, 'kg*m/s');
$wavelength = $h->div($momentum);
```

### Looking Up Constants

```php
use Galaxon\Quantities\PhysicalConstant;

// By symbol
$c = PhysicalConstant::get('c');
$NA = PhysicalConstant::get('NA');

// By name
$g = PhysicalConstant::getByName('standard gravity');

// Get all constants
$all = PhysicalConstant::getAll();
foreach ($all as $symbol => $constant) {
    echo "$symbol: $constant\n";
}
```

## See Also

- **[Quantity](Quantity.md)** - The base class for all physical quantities
- **[Unit](Unit.md)** - Units used by the constants
