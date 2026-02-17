# Physical Constants

Access fundamental physical constants as Quantity objects:

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

// Get by symbol
$c = PhysicalConstant::get('c');
```

### Calculation Example

Compute the gravitational force between the Earth and Moon using Newton's law of gravitation (F = G * m1 * m2 / r²):

```php
use Galaxon\Quantities\PhysicalConstant;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;

$G = PhysicalConstant::gravitational();
$earthMass = new Mass(5.972e24, 'kg');
$moonMass = new Mass(7.342e22, 'kg');
$distance = new Length(3.844e8, 'm');

$force = $G->mul($earthMass)->mul($moonMass)->div($distance->pow(2));
echo $force->simplify(false)->format('e', 2);
// 1.98×10²⁰ N
```

See the **[PhysicalConstant](../Reference/PhysicalConstant.md)** class reference for the complete list of available constants.
