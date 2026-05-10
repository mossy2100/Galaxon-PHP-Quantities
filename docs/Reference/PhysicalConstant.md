# PhysicalConstant

Provides access to fundamental physical constants as Quantity objects.

**Namespace:** `Galaxon\Quantities`

---

## Overview

The `PhysicalConstant` class provides access to well-known physical constants in two forms:

- **As a raw `float`** — via a public class constant (e.g. `PhysicalConstant::PLANCK`). Use this when you need the numeric value for a plain PHP computation.
- **As a `Quantity` object** — via a static method (e.g. `PhysicalConstant::planck()`). Use this when you need a typed value with units for use with the Quantities API.

```php
// Raw float value — no unit information:
$hValue = PhysicalConstant::PLANCK;        // 6.62607015e-34

// Quantity object — carries its unit (J⋅s):
$hQuantity = PhysicalConstant::planck();   // Quantity(6.62607015e-34, 'J*s')
```

`Quantity` objects are lazily instantiated and cached for efficient reuse.

The class covers:
- All seven SI defining constants
- Gravitational constants (standard gravity, gravitational constant)
- Electromagnetic constants (vacuum permittivity and permeability)
- Atomic and nuclear constants (electron mass, proton mass, etc.)
- Thermodynamic constants (molar gas constant, Stefan-Boltzmann constant)
- The reduced Planck constant (derived as ℏ = h / 2π)

---

## Quick reference

| ASCII symbol | Constant              | Method                 | Value               | Unit              |
| ------------ | --------------------- | ---------------------- | ------------------- | ----------------- |
| deltaNuCs    | `CAESIUM_FREQUENCY`   | `caesiumFrequency()`   | 9,192,631,770       | *Hz*              |
| c            | `SPEED_OF_LIGHT`      | `speedOfLight()`       | 299,792,458         | *m/s*             |
| h            | `PLANCK`              | `planck()`             | 6.62607015×10⁻³⁴    | *J⋅s*             |
| hbar         | `REDUCED_PLANCK`      | `reducedPlanck()`      | 1.054571817×10⁻³⁴   | *J⋅s*             |
| e            | `ELEMENTARY_CHARGE`   | `elementaryCharge()`   | 1.602176634×10⁻¹⁹   | *C*               |
| k            | `BOLTZMANN`           | `boltzmann()`          | 1.380649×10⁻²³      | *J/K*             |
| NA           | `AVOGADRO`            | `avogadro()`           | 6.02214076×10²³     | *mol⁻¹*           |
| Kcd          | `LUMINOUS_EFFICACY`   | `luminousEfficacy()`   | 683                 | *lm/W*            |
| g            | `EARTH_GRAVITY`       | `earthGravity()`       | 9.80665             | *m/s²*            |
| G            | `GRAVITATIONAL`       | `gravitational()`      | 6.67430×10⁻¹¹       | *m³/(kg⋅s²)*      |
| epsilon0     | `VACUUM_PERMITTIVITY` | `vacuumPermittivity()` | 8.8541878128×10⁻¹²  | *F/m*             |
| mu0          | `VACUUM_PERMEABILITY` | `vacuumPermeability()` | 1.25663706212×10⁻⁶  | *H/m*             |
| me           | `ELECTRON_MASS`       | `electronMass()`       | 9.1093837015×10⁻³¹  | *kg*              |
| mp           | `PROTON_MASS`         | `protonMass()`         | 1.67262192369×10⁻²⁷ | *kg*              |
| mn           | `NEUTRON_MASS`        | `neutronMass()`        | 1.67492749804×10⁻²⁷ | *kg*              |
| alpha        | `FINE_STRUCTURE`      | `fineStructure()`      | 7.2973525693×10⁻³   | *(dimensionless)* |
| Rinf         | `RYDBERG`             | `rydberg()`            | 10,973,731.568      | *m⁻¹*             |
| a0           | `BOHR_RADIUS`         | `bohrRadius()`         | 5.29177210903×10⁻¹¹ | *m*               |
| R            | `MOLAR_GAS`           | `molarGas()`           | 8.314462618         | *J/(mol⋅K)*       |
| sigma        | `STEFAN_BOLTZMANN`    | `stefanBoltzmann()`    | 5.670374419×10⁻⁸    | *W/(m²⋅K⁴)*       |

*See also: [Supported constants](../Concepts/PhysicalConstants.md#supported-constants) — includes usual symbols and Wikipedia links.*

---

## SI defining constants

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

The Avogadro constant (Nᴀ). Defines the *mole*.

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

## Atomic and nuclear constants

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

The molar gas constant (R = Nᴀ·k).

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

### Using constants in calculations

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
