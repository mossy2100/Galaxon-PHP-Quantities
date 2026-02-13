# PhysicalConstant

Provides access to fundamental physical constants as Quantity objects.

## Overview

The `PhysicalConstant` class provides a convenient way to access well-known physical constants as properly typed `Quantity` objects with their correct units. Constants are lazily created and cached for efficient reuse.

The class includes:
- All seven SI defining constants
- Gravitational constants (standard gravity, gravitational constant)
- Electromagnetic constants (vacuum permittivity and permeability)
- Atomic and nuclear constants (electron mass, proton mass, etc.)
- Thermodynamic constants (molar gas constant, Stefan-Boltzmann constant)
- Derived constants (reduced Planck constant)

### Key Features

- Type-safe access to physical constants
- Lazy instantiation with caching
- Lookup by symbol via `get()`
- Computed derived constants (e.g., reduced Planck constant = h / 2pi)

## SI Defining Constants

### caesiumFrequency()

```php
public static function caesiumFrequency(): Quantity
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
- `Quantity` - 6.62607015e-34 J\*s (exact)

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
- `Quantity` - 6.02214076e23 mol⁻¹ (exact)

### luminousEfficacy()

```php
public static function luminousEfficacy(): Quantity
```

The luminous efficacy of 540 THz radiation (Kcd). Defines the candela.

**Returns:**
- `Quantity` - 683 lm/W (exact)

## Gravitational Constants

### earthGravity()

```php
public static function earthGravity(): Quantity
```

Standard acceleration of gravity at the surface of Earth (g).

**Returns:**
- `Quantity` - 9.80665 m/s² (exact by definition)

### gravitational()

```php
public static function gravitational(): Quantity
```

The Newtonian constant of gravitation (G).

**Returns:**
- `Quantity` - 6.67430e-11 m³/(kg\*s²)

## Electromagnetic Constants

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
- `Quantity` - 1.25663706212e-6 H/m

## Atomic and Nuclear Constants

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
- `Quantity` - 10,973,731.568160 m⁻¹

### bohrRadius()

```php
public static function bohrRadius(): Quantity
```

The Bohr radius (a0).

**Returns:**
- `Quantity` - 5.29177210903e-11 m

## Thermodynamic Constants

### molarGas()

```php
public static function molarGas(): Quantity
```

The molar gas constant (R = NA \* k).

**Returns:**
- `Quantity` - 8.314462618 J/(mol\*K)

### stefanBoltzmann()

```php
public static function stefanBoltzmann(): Quantity
```

The Stefan-Boltzmann constant (sigma).

**Returns:**
- `Quantity` - 5.670374419e-8 W/(m²\*K⁴)

## Derived Constants

### reducedPlanck()

```php
public static function reducedPlanck(): Quantity
```

The reduced Planck constant (hbar = h / 2pi).

**Returns:**
- `Quantity` - Computed from Planck constant with full precision

## Lookup Methods

### get()

```php
public static function get(string $symbol): Quantity
```

Get a constant by its symbol.

**Parameters:**
- `$symbol` (string) - The constant's symbol (case-sensitive)

**Returns:**
- `Quantity` - The constant as a Quantity object

**Throws:**
- `DomainException` - If the symbol is unknown

**Examples:**
```php
$c = PhysicalConstant::get('c');         // Speed of light
$h = PhysicalConstant::get('h');         // Planck constant
$hbar = PhysicalConstant::get('hbar');   // Reduced Planck constant
$g = PhysicalConstant::get('g');         // Earth gravity
$R = PhysicalConstant::get('R');         // Molar gas constant
```

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
$momentum = Quantity::create(1e-24, 'kg*m/s');
$wavelength = $h->div($momentum);
```

### Looking Up Constants by Symbol

```php
use Galaxon\Quantities\PhysicalConstant;

$c = PhysicalConstant::get('c');
$NA = PhysicalConstant::get('NA');
$G = PhysicalConstant::get('G');
```

## See Also

- **[Quantity](Quantity.md)** - The base class for all physical quantities
- **[Unit](Internal/Unit.md)** - Units used by the constants
