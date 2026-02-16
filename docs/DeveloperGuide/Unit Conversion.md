CLAUDE TODO: Need a lot more examples here, including:
1. SI to imperial/US
2. Expansion of named units. Can use force units, e.g. newtons, lbf. Show Quantity::expand() method.
3. Contraction of named units, show examples of Quantity::simplify() method.
4. Auto-prefixing, show calculation that produces metres, then use auto-prefixing to get km.


# Unit Conversion

The conversion system automatically finds paths between units using a graph-based algorithm. You only need to define a minimum number of conversions; others will be discovered automatically. Many common conversions are built-in.

```php
// Direct conversion
$meters = new Length(1000, 'm');
$feet = $meters->to('ft');  // 3280.84 ft

// Indirect conversion (found automatically)
$miles = $meters->to('mi'); // 0.621371 mi
```

### Temperature Conversions

Most conversions involve a simple multiplication. The built-in `Temperature` class uses affine transformations (y = mx + k) to handle offset scales:

```php
use Galaxon\Quantities\QuantityType\Temperature;

$celsius = new Temperature(0, 'degC');
echo $celsius->to('degF');  // 32°F
echo $celsius->to('K');     // 273.15K

$fahrenheit = new Temperature(212, 'degF');
echo $fahrenheit->to('degC');  // 100°C
```

