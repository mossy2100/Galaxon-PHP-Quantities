# Examples

Real-world physics, engineering, and science calculations using the Quantities package.

These examples are drawn from the [tests/Examples](../tests/Examples) test suite. Each one is a
runnable calculation that demonstrates derived quantity arithmetic, unit conversions, and physical
constants.

---

## Kinematics

### Velocity from distance and time: v = d/t

Usain Bolt's 100 m world record: 9.58 seconds.

```php
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Time;

$d = new Length(100, 'm');
$t = new Time(9.58, 's');
$v = $d->div($t);
// 10.44 m/s (Velocity)
```

### Acceleration: a = dv/dt

Car accelerating from 0 to 30 m/s in 10 seconds.

```php
use Galaxon\Quantities\QuantityType\Velocity;
use Galaxon\Quantities\QuantityType\Time;

$deltaV = new Velocity(30, 'm/s');
$deltaT = new Time(10, 's');
$a = $deltaV->div($deltaT);
// 3.0 m/s2 (Acceleration)
```

### Distance under constant acceleration: d = 1/2 at²

```php
use Galaxon\Quantities\QuantityType\Acceleration;
use Galaxon\Quantities\QuantityType\Time;

$a = new Acceleration(2, 'm/s2');
$t = new Time(10, 's');
$d = $a->mul($t->pow(2))->div(2);
// 100 m (Length)
```

### Free fall: d = 1/2 gt²

Object falling for 3 seconds under standard gravity.

```php
use Galaxon\Quantities\PhysicalConstant;
use Galaxon\Quantities\QuantityType\Time;

$g = PhysicalConstant::earthGravity();
$t = new Time(3, 's');
$d = $g->mul($t->pow(2))->div(2);
// 44.13 m (Length)
```

### Kinetic energy: E = 1/2 mv²

A one tonne car travelling at 100 km/h.

```php
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Velocity;

$m = new Mass(1, 't');
$v = new Velocity(100, 'km/h');
$energy = $m->mul($v->pow(2))->div(2)->toSi();
// 385.8 kJ (Energy)
```

---

## Newtonian Mechanics

### Newton's second law: F = ma

```php
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Acceleration;

$m = new Mass(10, 'kg');
$a = new Acceleration(5, 'm/s2');
$force = $m->mul($a);
// 50 N (Force)
```

### Weight: W = mg

Weight of a 75 kg person under standard gravity.

```php
use Galaxon\Quantities\PhysicalConstant;
use Galaxon\Quantities\QuantityType\Mass;

$m = new Mass(75, 'kg');
$g = PhysicalConstant::earthGravity();
$weight = $m->mul($g);
// 735.5 N (Force)
```

### Gravitational force: F = GMm/r²

Gravitational force between the Earth and the Moon.

```php
use Galaxon\Quantities\PhysicalConstant;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Length;

$G = PhysicalConstant::gravitational();
$earthMass = new Mass(5.972e24, 'kg');
$moonMass = new Mass(7.342e22, 'kg');
$distance = new Length(3.844e8, 'm');

$force = $G->mul($earthMass)->mul($moonMass)->div($distance->pow(2));
echo $force->simplify(false)->format('e', 2);
// 1.98×10²⁰ N
```

### Work: W = Fd

Pushing a box with 100 N of force over 5 metres.

```php
use Galaxon\Quantities\QuantityType\Force;
use Galaxon\Quantities\QuantityType\Length;

$force = new Force(100, 'N');
$d = new Length(5, 'm');
$work = $force->mul($d);
// 500 J (Energy)
```

### Work converted to calories

A 100 N force over 41.84 m produces exactly 1 kcal of energy.

```php
$force = new Force(100, 'N');
$d = new Length(41.84, 'm');
$work = $force->mul($d);
$kcal = $work->to('kcal');
// 1.0 kcal (Energy)
```

### Power: P = W/t

```php
use Galaxon\Quantities\QuantityType\Energy;
use Galaxon\Quantities\QuantityType\Time;

$work = new Energy(500, 'J');
$t = new Time(10, 's');
$power = $work->div($t);
// 50 W (Power)
```

---

## Astronomy

### Light travel time: t = d/c

How long sunlight takes to reach Earth.

```php
use Galaxon\Quantities\PhysicalConstant;
use Galaxon\Quantities\QuantityType\Length;

$d = new Length(1, 'au');
$c = PhysicalConstant::speedOfLight();
$t = $d->div($c);
// 499 s ~ 8.3 minutes (Time)
```

### Gravitational force between the Sun and Earth

```php
$G = PhysicalConstant::gravitational();
$sunMass = new Mass(1.989e30, 'kg');
$earthMass = new Mass(5.972e24, 'kg');
$distance = new Length(1, 'au');

$force = $G->mul($sunMass)->mul($earthMass)->div($distance->pow(2));
// 3.54 x 10²² N (Force)
```

### Surface gravity of Mars: g = GM/r²

```php
$G = PhysicalConstant::gravitational();
$marsMass = new Mass(6.417e23, 'kg');
$marsRadius = new Length(3.3895e6, 'm');

$g = $G->mul($marsMass)->div($marsRadius->pow(2));
// 3.73 m/s2 (Acceleration) — about 38% of Earth's gravity
```

---

## Light and Waves

### Photon energy: E = hf

Energy of a green light photon.

```php
use Galaxon\Quantities\PhysicalConstant;
use Galaxon\Quantities\QuantityType\Frequency;

$h = PhysicalConstant::planck();
$f = new Frequency(5.49e14, 'Hz');
$energy = $h->mul($f);
// 3.64 x 10^-19 J (Energy)
```

### Wavelength from frequency: lambda = c/f

Wavelength of an FM radio signal at 101.5 MHz.

```php
$c = PhysicalConstant::speedOfLight();
$f = new Frequency(101.5e6, 'Hz');
$lambda = $c->div($f);
// 2.95 m (Length)
```

### Mass-energy equivalence: E = mc²

Energy contained in 1 gram of matter.

```php
$m = new Mass(1, 'g');
$c = PhysicalConstant::speedOfLight();
$energy = $m->mul($c->pow(2));
// 8.99 x 10¹³ J ~ 21.5 kilotons of TNT (Energy)
```

### De Broglie wavelength: lambda = h/(mv)

Wavelength of an electron travelling at 10⁶ m/s.

```php
$h = PhysicalConstant::planck();
$me = PhysicalConstant::electronMass();
$v = new Velocity(1e6, 'm/s');

$lambda = $h->div($me->mul($v));
// 7.27 x 10^-10 m ~ 0.727 nm (Length)
```

### Photon energy from wavelength: E = hc/lambda

Energy of a green light photon in joules and electronvolts.

```php
$h = PhysicalConstant::planck();
$c = PhysicalConstant::speedOfLight();
$lambda = new Length(550, 'nm');

$energy = $h->mul($c)->div($lambda);
$j = $energy->to('J');    // 3.61 x 10^-19 J
$ev = $energy->to('eV');  // 2.25 eV
```

---

## Electromagnetism

### Ohm's law: V = IR

A current of 2 A through a 100 ohm resistor.

```php
use Galaxon\Quantities\QuantityType\ElectricCurrent;
use Galaxon\Quantities\QuantityType\Resistance;

$current = new ElectricCurrent(2, 'A');
$resistance = new Resistance(100, 'ohm');
$voltage = $current->mul($resistance);
// 200 V (Voltage)
```

### Electrical power: P = IV

A 2 A current at 120 V (typical US household appliance).

```php
use Galaxon\Quantities\QuantityType\ElectricCurrent;
use Galaxon\Quantities\QuantityType\Voltage;

$current = new ElectricCurrent(2, 'A');
$voltage = new Voltage(120, 'V');
$power = $current->mul($voltage);
// 240 W (Power)
```

### Electric charge: Q = It

A current of 0.5 A flowing for 60 seconds.

```php
$current = new ElectricCurrent(0.5, 'A');
$t = new Time(60, 's');
$charge = $current->mul($t);
// 30 C (ElectricCharge)
```

### Energy stored in a capacitor: E = 1/2 CV²

A 10 uF capacitor charged to 12 V.

```php
use Galaxon\Quantities\QuantityType\Capacitance;
use Galaxon\Quantities\QuantityType\Voltage;

$cap = new Capacitance(10e-6, 'F');
$voltage = new Voltage(12, 'V');
$energy = $cap->mul($voltage->pow(2))->div(2);
// 7.2 x 10^-4 J (Energy)
```

### AC mains period: T = 1/f

```php
use Galaxon\Quantities\QuantityType\Dimensionless;
use Galaxon\Quantities\QuantityType\Frequency;

$mains = new Frequency(50, 'Hz');
$one = new Dimensionless(1);
$period = $one->div($mains);
$ms = $period->to('ms');
// 20 ms (Time) — European/Australian mains

$mains = new Frequency(60, 'Hz');
$period = $one->div($mains);
$ms = $period->to('ms');
// 16.67 ms (Time) — North American mains
```

---

## Thermodynamics

### Ideal gas law: PV = nRT

1 mol of gas at 300 K in a 25 L (0.025 m³) container.

```php
use Galaxon\Quantities\PhysicalConstant;
use Galaxon\Quantities\QuantityType\AmountOfSubstance;
use Galaxon\Quantities\QuantityType\Temperature;
use Galaxon\Quantities\QuantityType\Volume;

$n = new AmountOfSubstance(1, 'mol');
$R = PhysicalConstant::molarGas();
$T = new Temperature(300, 'K');
$V = new Volume(0.025, 'm3');

$P = $n->mul($R)->mul($T)->div($V);
// 99,774 Pa ~ 1 atm (Pressure)
```

### Stefan-Boltzmann radiation: P = σAT⁴

Total radiant power from 1 m² of the Sun's surface (T = 5778 K).

```php
use Galaxon\Quantities\QuantityType\Area;

$sigma = PhysicalConstant::stefanBoltzmann();
$area = new Area(1, 'm2');
$temp = new Temperature(5778, 'K');

$power = $sigma->mul($area)->mul($temp->pow(4));
// 6.32 x 10⁷ W = 63.2 MW per m2 (Power)
```

### Thermal energy: E = 3/2 kT

Average thermal energy of a particle at room temperature (300 K).

```php
$k = PhysicalConstant::boltzmann();
$temp = new Temperature(300, 'K');
$energy = $k->mul($temp)->mul(1.5);
// 6.21 x 10^-21 J (Energy)
```

---

## Chemistry

### Ideal gas molar volume

Volume of 1 mol of an ideal gas at standard temperature and pressure (0 degC, 1 atm).

```php
$n = new AmountOfSubstance(1, 'mol');
$R = PhysicalConstant::molarGas();
$T = new Temperature(273.15, 'K');
$P = new Pressure(101325, 'Pa');

$V = $n->mul($R)->mul($T)->div($P);
// 0.02241 m3 ~ 22.41 L (Volume)
```

### Particle count: N = n * Na

Number of molecules in 2 moles of a substance.

```php
$n = new AmountOfSubstance(2, 'mol');
$Na = PhysicalConstant::avogadro();
$N = $n->mul($Na);
// 1.20 x 10²⁴ (dimensionless)
```

### Moles from mass and molar mass: n = m/M

How many moles in 18 grams of water (molar mass = 18.015 g/mol)?

```php
use Galaxon\Quantities\QuantityType\Mass;

$mass = new Mass(0.018, 'kg');
$molarMass = new Mass(0.018015, 'kg')->div(new AmountOfSubstance(1, 'mol'));
$n = $mass->div($molarMass);
// 0.999 mol (AmountOfSubstance) — approximately 1 mol of water
```

---

## Geometry

### Circle circumference: C = 2πr

```php
use Galaxon\Quantities\QuantityType\Length;

$r = new Length(5, 'm');
$circumference = $r->mul(2 * M_PI);
// 31.42 m (Length)
```

### Circle area: A = πr²

```php
$r = new Length(5, 'm');
$area = $r->pow(2)->mul(M_PI);
// 78.54 m2 (Area)
```

### Sphere surface area: A = 4πr²

```php
$r = new Length(5, 'm');
$area = $r->pow(2)->mul(4 * M_PI);
// 314.16 m2 (Area)
```

### Sphere volume: V = 4/3 πr³

```php
$r = new Length(5, 'm');
$volume = $r->pow(3)->mul(4 / 3 * M_PI);
// 523.60 m3 (Volume)
```

---

## Fluid Mechanics

### Pressure: P = F/A

A 1000 N force distributed over 0.5 m².

```php
use Galaxon\Quantities\QuantityType\Force;
use Galaxon\Quantities\QuantityType\Area;

$force = new Force(1000, 'N');
$area = new Area(0.5, 'm2');
$pressure = $force->div($area);
// 2000 Pa (Pressure)
```

### Hydrostatic pressure: P = ρgh

Pressure at 10 metres depth in fresh water (ρ = 1000 kg/m³).

```php
use Galaxon\Quantities\PhysicalConstant;
use Galaxon\Quantities\QuantityType\Density;
use Galaxon\Quantities\QuantityType\Length;

$rho = new Density(1000, 'kg/m3');
$g = PhysicalConstant::earthGravity();
$h = new Length(10, 'm');
$pressure = $rho->mul($g)->mul($h);
// 98,067 Pa (Pressure)
```

### Buoyancy force (Archimedes' principle): F = ρVg

A 10 litre (0.01 m³) object submerged in fresh water.

```php
use Galaxon\Quantities\QuantityType\Density;
use Galaxon\Quantities\QuantityType\Volume;

$rho = new Density(1000, 'kg/m3');
$volume = new Volume(0.01, 'm3');
$g = PhysicalConstant::earthGravity();
$force = $rho->mul($volume)->mul($g);
// 98.07 N (Force)
```

### Pressure in Imperial units

100 lbf distributed over 10 in². Requires loading Imperial units.

```php
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;

UnitRegistry::loadSystem(System::Imperial);

$force = new Force(100, 'lbf');
$area = new Area(10, 'in2');
$pressure = $force->div($area);
$psi = $pressure->to('lbf/in2');
// 10 psi (Pressure)
```

---

## Aviation

These examples use mixed Imperial, Nautical, and SI units. Load the required systems first:

```php
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;

UnitRegistry::loadSystem(System::Imperial);
UnitRegistry::loadSystem(System::UsCustomary);
UnitRegistry::loadSystem(System::Nautical);
```

### Ground speed: v = d/t

An aircraft covers 360 nautical miles in 1.5 hours.

```php
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Time;

$d = new Length(360, 'nmi');
$t = new Time(1.5, 'h');
$v = $d->div($t);
$vSi = $v->toSi();
// 123.5 m/s ~ 240 knots (Velocity)
```

### Descent time

Descending from 35,000 ft at 1,500 ft/min.

```php
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Velocity;

$altitude = new Length(35000, 'ft');
$rate = new Velocity(1500, 'ft/min');
$t = $altitude->div($rate);
// 1400 s ~ 23.3 minutes (Time)
```

### Top of descent distance

At 450 knots ground speed, descending from 35,000 ft at 1,500 ft/min. Pilots use the "3:1 rule"
as a rough approximation: 3 nm per 1,000 ft.

```php
$altitude = new Length(35000, 'ft');
$descentRate = new Velocity(1500, 'ft/min');
$descentTime = $altitude->div($descentRate);

$groundSpeed = new Velocity(450, 'kn');
$distance = $groundSpeed->mul($descentTime);
$distanceSi = $distance->toSi();
// 324,100 m ~ 175 nmi (Length)
```

### Fuel burn

An aircraft burns 850 US gallons per hour on a 3.5 hour flight.

```php
use Galaxon\Quantities\QuantityType\Volume;
use Galaxon\Quantities\QuantityType\Time;

$fuelFlow = new Volume(850, 'US gal')->div(new Time(1, 'h'));
$flightTime = new Time(3.5, 'h');
$totalFuel = $fuelFlow->mul($flightTime);
$totalFuelSi = $totalFuel->toSi();
// 11.26 m3 ~ 2,975 US gal (Volume)
```

---

## See Also

- **[README](../README.md)** - Package overview and quick start
- **[Supported Units](SupportedUnits.md)** - Complete unit reference
- **[Quantity](Quantity.md)** - Quantity class documentation
- **[PhysicalConstant](PhysicalConstant.md)** - Available physical constants
