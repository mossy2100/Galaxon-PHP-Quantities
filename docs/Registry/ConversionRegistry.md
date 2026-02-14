# ConversionRegistry

Registry for unit conversions organized by dimension.

**Namespace:** `Galaxon\Quantities\Registry`

---

## Overview

The `ConversionRegistry` stores and retrieves conversions between units. Conversions are:

- Organized by dimension code (e.g., 'L' for length, 'M' for mass)
- Loaded automatically when measurement systems are loaded via `UnitRegistry`
- Used by the `Converter` class to find conversion paths

---

## Methods

### Lookup Methods

#### `static get(string $dimension, string $srcSymbol, string $destSymbol): ?Conversion`

Get a specific conversion between two units.

```php
$conversion = ConversionRegistry::get('L', 'm', 'ft');
if ($conversion !== null) {
    $feet = 10 * $conversion->factor->value;  // Convert 10 meters to feet
}
```

#### `static getByDimension(string $dimension): array`

Get all conversions for a dimension.

```php
$lengthConversions = ConversionRegistry::getByDimension('L');
// Returns nested array: [$srcSymbol][$destSymbol] => Conversion
```

### Modification Methods

#### `static add(Conversion $conversion): void`

Add a Conversion object to the registry. If either unit has prefixes, the unprefixed conversion is also added automatically.

```php
$conversion = new Conversion($srcUnit, $destUnit, $factor);
ConversionRegistry::add($conversion);
```

#### `static remove(Conversion $conversion): void`

Remove a conversion from the registry.

```php
ConversionRegistry::remove($conversion);
```

#### `static loadSystem(System $system): void`

Load conversions for a measurement system. Iterates through all conversion definitions and adds any where at least one unit belongs to the specified system. Also loads expansion conversions for expandable units in the system.

```php
ConversionRegistry::loadSystem(System::Imperial);
```

#### `static reset(): void`

Reset the registry to its default initial state. Triggers re-initialization on next access.

```php
ConversionRegistry::reset();
```

#### `static clear(): void`

Remove all conversions. Does not trigger re-initialization on next access.

```php
ConversionRegistry::clear();
```

#### `static clearByDimension(string $dimension): void`

Remove all conversions for a specific dimension.

```php
ConversionRegistry::clearByDimension('L');
```

### Inspection Methods

#### `static has(string $dimension, string $srcSymbol, string $destSymbol): bool`

Check if a conversion exists.

```php
if (ConversionRegistry::has('L', 'm', 'ft')) {
    // Direct conversion available
}
```

---

## How Conversions Are Stored

Conversions are stored in a three-dimensional array:

```
$conversions[$dimension][$srcSymbol][$destSymbol] = Conversion
```

For example:
- `$conversions['L']['m']['ft']` = conversion from meters to feet
- `$conversions['M']['kg']['lb']` = conversion from kilograms to pounds

---

## Automatic Prefix Handling

When adding a conversion with prefixed units, the unprefixed conversion is also added:

```php
// Adding km → mi also adds m → mi (adjusted for prefix)
ConversionRegistry::add('km', 'mi', 0.621371);
```

---

## Usage Examples

```php
use Galaxon\Quantities\Registry\ConversionRegistry;

// Check for a direct conversion
if (ConversionRegistry::has('L', 'm', 'in')) {
    $conv = ConversionRegistry::get('L', 'm', 'in');
    echo "1 m = {$conv->factor->value} in";
}

// Get all length conversions
$lengthConv = ConversionRegistry::getByDimension('L');
foreach ($lengthConv as $src => $destinations) {
    foreach ($destinations as $dest => $conv) {
        echo "$src → $dest: {$conv->factor->value}\n";
    }
}

// Add custom conversion
ConversionRegistry::add(new Conversion('league', 'mi', 3));
```

---

## See Also

- **[Conversion](../Internal/Conversion.md)** - Conversion class documentation
- **[Converter](../Internal/Converter.md)** - Converter class that uses this registry
- **[UnitRegistry](UnitRegistry.md)** - Unit registry
- **[System](../System.md)** - Measurement system enum
