# PhysicalConstant

Provides access to fundamental physical constants as Quantity objects.

**Namespace:** `Galaxon\Quantities`

---

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
- Computed derived constants (e.g., reduced Planck constant = h / τ = h / 2π)

---

## SI Defining Constants

### caesiumFrequency()

```php
public static function caesiumFrequency(): Quantity
```

The caesium-133 hyperfine transition frequency (ΔνCs). Defines the *second*.

**Returns:**
- `Quantity` equal to 9,192,631,770 Hz

### speedOfLight()

```php
public static function speedOfLight(): Quantity
```

The speed of light in vacuum (c). Defines the *meter*.

**Returns:**
- `Quantity` equal to 299,792,458 m/s

### planck()

```php
public static function planck(): Quantity
```

The Planck constant (h). Defines the *kilogram*.

**Returns:**
- `Quantity` equal to 6.62607015×10⁻³⁴ J⋅s

### elementaryCharge()

```php
public static function elementaryCharge(): Quantity
```

The elementary charge (e). Defines the *ampere*.

**Returns:**
- `Quantity` equal to 1.602176634×10⁻¹⁹ C

### boltzmann()

```php
public static function boltzmann(): Quantity
```

The Boltzmann constant (k). Defines the *kelvin*.

**Returns:**
- `Quantity` equal to 1.380649×10⁻²³ J/K

### avogadro()

```php
public static function avogadro(): Quantity
```

The Avogadro constant (Nₐ). Defines the *mole*.

**Returns:**
- `Quantity` equal to 6.02214076×10²³ mol⁻¹

### luminousEfficacy()

```php
public static function luminousEfficacy(): Quantity
```

The luminous efficacy of 540 THz radiation (K<sub>cd</sub>). Defines the *candela*.

**Returns:**
- `Quantity` equal to 683 lm/W

---

## Gravitational constants

### earthGravity()

```php
public static function earthGravity(): Quantity
```

Standard acceleration of gravity at the surface of Earth (g).

**Returns:**
- `Quantity` equal to 9.80665 m/s²

### gravitational()

```php
public static function gravitational(): Quantity
```

The Newtonian constant of gravitation (G).

**Returns:**
- `Quantity` equal to 6.67430×10⁻¹¹ m³/(kg⋅s²)

---

## Electromagnetic constants

### vacuumPermittivity()

```php
public static function vacuumPermittivity(): Quantity
```

The vacuum electric permittivity (ε₀).

**Returns:**
- `Quantity` equal to 8.8541878128×10⁻¹² F/m

### vacuumPermeability()

```php
public static function vacuumPermeability(): Quantity
```

The vacuum magnetic permeability (μ₀).

**Returns:**
- `Quantity` equal to 1.25663706212×10⁻⁶ H/m

---

## Atomic and Nuclear Constants

### electronMass()

```php
public static function electronMass(): Quantity
```

The electron rest mass (mₑ).

**Returns:**
- `Quantity` equal to 9.1093837015×10⁻³¹ kg

### protonMass()

```php
public static function protonMass(): Quantity
```

The proton mass (mₚ).

**Returns:**
- `Quantity` equal to 1.67262192369×10⁻²⁷ kg

### neutronMass()

```php
public static function neutronMass(): Quantity
```

The neutron mass (mₙ).

**Returns:**
- `Quantity` equal to 1.67492749804×10⁻²⁷ kg

### fineStructure()

```php
public static function fineStructure(): Quantity
```

The fine-structure constant (α). Dimensionless.

**Returns:**
- `Quantity` equal to 7.2973525693×10⁻³

### rydberg()

```php
public static function rydberg(): Quantity
```

The Rydberg constant (R<sub>∞</sub>).

**Returns:**
- `Quantity` equal to 10,973,731.568160 m⁻¹

### bohrRadius()

```php
public static function bohrRadius(): Quantity
```

The Bohr radius (a₀).

**Returns:**
- `Quantity` equal to 5.29177210903×10⁻¹¹ m

---

## Thermodynamic constants

### molarGas()

```php
public static function molarGas(): Quantity
```

The molar gas constant (R = Nₐ·k).

**Returns:**
- `Quantity` equal to 8.314462618 J/(mol⋅K)

### stefanBoltzmann()

```php
public static function stefanBoltzmann(): Quantity
```

The Stefan-Boltzmann constant (σ).

**Returns:**
- `Quantity` equal to 5.670374419×10⁻⁸ W/(m²⋅K⁴)

---

## Derived constants

### reducedPlanck()

```php
public static function reducedPlanck(): Quantity
```

The reduced Planck constant (ℏ = h / 2π).

**Returns:**
- `Quantity` computed from Planck constant with full precision

---

## Lookup methods

### get()

```php
public static function get(string $symbol): Quantity
```

Get a constant by its symbol.

**Parameters:**
- `$symbol` (string) - The constant's symbol (case-sensitive)

**Returns:**
- `Quantity` equal to the constant as a Quantity object

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

### getAll()

```php
public static function getAll(): array
```

Get all physical constants as an associative array of `Quantity` objects keyed by symbol.

**Returns:**
- `array<string, Quantity>` - All constants, keyed by symbol

**Examples:**
```php
$all = PhysicalConstant::getAll();
foreach ($all as $symbol => $quantity) {
    echo "$symbol = $quantity\n";
}
```

---

## Usage examples

### Using Constants in Calculations

```php
use Galaxon\Quantities\PhysicalConstant;
use Galaxon\Quantities\Quantity;
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

---

## See also

- **[Quantity](Quantity.md)** - The base class for all physical quantities
- **[Unit](Internal/Unit.md)** - Units used by the constants
