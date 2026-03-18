# Physical Constants

Fundamental physical constants can be accessed as Quantity objects via the PhysicalConstant class:

```php
use Galaxon\Quantities\PhysicalConstant;

// Speed of light
$c = PhysicalConstant::speedOfLight();
echo $c->to('km/s');  // 299792.458 km/s

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

### Supported constants

| Constant                                                                                     | ASCII symbol | Value                 | Units             | Method                 |
| -------------------------------------------------------------------------------------------- | ------------ | --------------------- | ----------------- | ---------------------- |
| [Caesium frequency](https://en.wikipedia.org/wiki/Caesium_standard)                          | deltaNuCs    | 9,192,631,770         | Hz                | `caesiumFrequency()`   |
| [Speed of light](https://en.wikipedia.org/wiki/Speed_of_light)                               | c            | 299,792,458           | m/s               | `speedOfLight()`       |
| [Planck constant](https://en.wikipedia.org/wiki/Planck_constant)                             | h            | 6.62607015 x 10-34    | J*s               | `planck()`             |
| [Reduced Planck constant](https://en.wikipedia.org/wiki/Planck_constant)                     | hbar         | h / 2pi               | J*s               | `reducedPlanck()`      |
| [Elementary charge](https://en.wikipedia.org/wiki/Elementary_charge)                         | e            | 1.602176634 x 10-19   | C                 | `elementaryCharge()`   |
| [Boltzmann constant](https://en.wikipedia.org/wiki/Boltzmann_constant)                       | k            | 1.380649 x 10-23      | J/K               | `boltzmann()`          |
| [Avogadro constant](https://en.wikipedia.org/wiki/Avogadro_constant)                         | NA           | 6.02214076 x 10+23    | mol-1             | `avogadro()`           |
| [Luminous efficacy](https://en.wikipedia.org/wiki/Luminous_efficacy)                         | Kcd          | 683                   | lm/W              | `luminousEfficacy()`   |
| [Standard gravity](https://en.wikipedia.org/wiki/Standard_gravity)                           | g            | 9.80665               | m/s2              | `earthGravity()`       |
| [Gravitational constant](https://en.wikipedia.org/wiki/Gravitational_constant)               | G            | 6.67430 x 10-11       | m3/(kg*s2)        | `gravitational()`      |
| [Vacuum permittivity](https://en.wikipedia.org/wiki/Vacuum_permittivity)                     | epsilon0     | 8.8541878128 x 10-12  | F/m               | `vacuumPermittivity()` |
| [Vacuum permeability](https://en.wikipedia.org/wiki/Vacuum_permeability)                     | mu0          | 1.25663706212 x 10-6  | H/m               | `vacuumPermeability()` |
| [Electron mass](https://en.wikipedia.org/wiki/Electron_mass)                                 | me           | 9.1093837015 x 10-31  | kg                | `electronMass()`       |
| [Proton mass](https://en.wikipedia.org/wiki/Proton)                                          | mp           | 1.67262192369 x 10-27 | kg                | `protonMass()`         |
| [Neutron mass](https://en.wikipedia.org/wiki/Neutron)                                        | mn           | 1.67492749804 x 10-27 | kg                | `neutronMass()`        |
| [Fine-structure constant](https://en.wikipedia.org/wiki/Fine-structure_constant)             | alpha        | 7.2973525693 x 10-3   | *(dimensionless)* | `fineStructure()`      |
| [Rydberg constant](https://en.wikipedia.org/wiki/Rydberg_constant)                           | Rinf         | 10,973,731.568160     | m-1               | `rydberg()`            |
| [Bohr radius](https://en.wikipedia.org/wiki/Bohr_radius)                                     | a0           | 5.29177210903 x 10-11 | m                 | `bohrRadius()`         |
| [Molar gas constant](https://en.wikipedia.org/wiki/Gas_constant)                             | R            | 8.314462618           | J/(mol*K)         | `molarGas()`           |
| [Stefan-Boltzmann constant](https://en.wikipedia.org/wiki/Stefan%E2%80%93Boltzmann_constant) | sigma        | 5.670374419 x 10-8    | W/(m2*K4)         | `stefanBoltzmann()`    |

If you have suggestions for additional constants that would be useful, let me know.

---

## See Also

- **[Units](Units.md)** - Complete unit reference
- **[Quantity](../Reference/Quantity.md)** - Quantity class documentation
- **[PhysicalConstant](../Reference/PhysicalConstant.md)** - Available physical constants
- **[Calculation Examples](../WorkingWithQuantities/CalculationExamples.md)** — Real-world physics and engineering calculations using physical constants.

