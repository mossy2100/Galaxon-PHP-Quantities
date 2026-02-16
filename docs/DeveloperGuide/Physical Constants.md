
Access fundamental physical constants as Quantity objects:

```php
use Galaxon\Quantities\PhysicalConstant;

// Speed of light
$c = PhysicalConstant::speedOfLight();
echo $c->to('km/s');  // 299792.458 km/s

// Planck constant
$h = PhysicalConstant::planckConstant();

// Gravitational constant
$G = PhysicalConstant::gravitationalConstant();

// Elementary charge
$e = PhysicalConstant::elementaryCharge();

// Get by symbol
$c = PhysicalConstant::get('c');
```

CLAUDE TODO: Show example of a calculation that uses a physical constant.

See the **[PhysicalConstant](docs/Reference/PhysicalConstant.md)** class reference for the complete list of available constants.


