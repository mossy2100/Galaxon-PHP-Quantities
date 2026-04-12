# Physical Constants

Fundamental physical constants can be accessed as Quantity objects via the PhysicalConstant class:

```php
use Galaxon\Quantities\PhysicalConstant;

// Speed of light
$c = PhysicalConstant::speedOfLight();
echo $c->to('km/s')->format('f', 3);  // 299792.458 km/s

// Planck constant
$h = PhysicalConstant::planck();

// Gravitational constant
$G = PhysicalConstant::gravitational();

// Elementary charge
$e = PhysicalConstant::elementaryCharge();
```

It's also possible to access a constant using its conventional symbol. Note, however, only ASCII symbols are supported:

```php
$c = PhysicalConstant::get('c');
```

---

## Supported constants

| Constant                                                                                     | Usual symbol      | ASCII symbol | Value                 | Units             |
| -------------------------------------------------------------------------------------------- | ----------------- | ------------ | --------------------- | ----------------- |
| [Caesium frequency](https://en.wikipedia.org/wiki/Caesium_standard)                          | Δν<sub>Cs</sub>   | deltaNuCs    | 9,192,631,770         | Hz                |
| [Speed of light](https://en.wikipedia.org/wiki/Speed_of_light)                               | c                 | c            | 299,792,458           | m/s               |
| [Planck constant](https://en.wikipedia.org/wiki/Planck_constant)                             | h                 | h            | 6.62607015 × 10⁻³⁴    | J·s               |
| [Reduced Planck constant](https://en.wikipedia.org/wiki/Planck_constant)                     | ℏ                 | hbar         | h / 2π                | J·s               |
| [Elementary charge](https://en.wikipedia.org/wiki/Elementary_charge)                         | e                 | e            | 1.602176634 × 10⁻¹⁹   | C                 |
| [Boltzmann constant](https://en.wikipedia.org/wiki/Boltzmann_constant)                       | k                 | k            | 1.380649 × 10⁻²³      | J/K               |
| [Avogadro constant](https://en.wikipedia.org/wiki/Avogadro_constant)                         | N<sub>A</sub>     | NA           | 6.02214076 × 10²³     | mol⁻¹             |
| [Luminous efficacy](https://en.wikipedia.org/wiki/Luminous_efficacy)                         | K<sub>cd</sub>    | Kcd          | 683                   | lm/W              |
| [Standard gravity](https://en.wikipedia.org/wiki/Standard_gravity)                           | g                 | g            | 9.80665               | m/s²              |
| [Gravitational constant](https://en.wikipedia.org/wiki/Gravitational_constant)               | G                 | G            | 6.67430 × 10⁻¹¹       | m³/(kg·s²)        |
| [Vacuum permittivity](https://en.wikipedia.org/wiki/Vacuum_permittivity)                     | ε₀                | epsilon0     | 8.8541878128 × 10⁻¹²  | F/m               |
| [Vacuum permeability](https://en.wikipedia.org/wiki/Vacuum_permeability)                     | μ₀                | mu0          | 1.25663706212 × 10⁻⁶  | H/m               |
| [Electron mass](https://en.wikipedia.org/wiki/Electron_mass)                                 | mₑ                | me           | 9.1093837015 × 10⁻³¹  | kg                |
| [Proton mass](https://en.wikipedia.org/wiki/Proton)                                          | mₚ                | mp           | 1.67262192369 × 10⁻²⁷ | kg                |
| [Neutron mass](https://en.wikipedia.org/wiki/Neutron)                                        | mₙ                | mn           | 1.67492749804 × 10⁻²⁷ | kg                |
| [Fine-structure constant](https://en.wikipedia.org/wiki/Fine-structure_constant)             | α                 | alpha        | 7.2973525693 × 10⁻³   | *(dimensionless)* |
| [Rydberg constant](https://en.wikipedia.org/wiki/Rydberg_constant)                           | R<sub>∞</sub>     | Rinf         | 10,973,731.568160     | m⁻¹               |
| [Bohr radius](https://en.wikipedia.org/wiki/Bohr_radius)                                     | a₀                | a0           | 5.29177210903 × 10⁻¹¹ | m                 |
| [Molar gas constant](https://en.wikipedia.org/wiki/Gas_constant)                             | R                 | R            | 8.314462618           | J/(mol·K)         |
| [Stefan-Boltzmann constant](https://en.wikipedia.org/wiki/Stefan%E2%80%93Boltzmann_constant) | σ                 | sigma        | 5.670374419 × 10⁻⁸    | W/(m²·K⁴)         |

If you have suggestions for additional constants that would be useful, let me know.

---

## See Also

- **[Units](Units.md)** — Complete unit reference.
- **[Quantity](../Reference/Quantity.md)** — Quantity class documentation.
- **[PhysicalConstant](../Reference/PhysicalConstant.md)** — Available physical constants.
- **[Calculation Examples](../WorkingWithQuantities/CalculationExamples.md)** — Real-world physics and engineering calculations using physical constants.

