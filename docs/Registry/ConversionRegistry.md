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

## Constants

| Constant | Value | Description |
|----------|-------|-------------|
| `ON_MISSING_UNIT_IGNORE` | 1 | Skip conversions with unknown units silently |
| `ON_MISSING_UNIT_THROW` | 2 | Throw exception for unknown units |

---

## Methods

### Lookup Methods

#### `static get(string $dimension, string $srcSymbol, string $destSymbol): ?Conversion`

Get a specific conversion between two units.

```php
$conversion = ConversionRegistry::get('L', 'm', 'ft');
if ($conversion !== null) {
    $feet = 10 * $conversion->factor->value;  // Convert 10 metres to feet
}
```

#### `static getByDimension(string $dimension): array`

Get all conversions for a dimension.

```php
$lengthConversions = ConversionRegistry::getByDimension('L');
// Returns nested array: [$srcSymbol][$destSymbol] => Conversion
```

#### `static getAllConversionDefinitions(): array`

Get all conversion definitions from all QuantityType classes.

```php
$definitions = ConversionRegistry::getAllConversionDefinitions();
// Returns: [['m', 'ft', 0.3048], ['kg', 'lb', 0.45359237], ...]
```

### Modification Methods

#### `static add(string|UnitInterface $srcUnit, string|UnitInterface $destUnit, float $factor, int $onMissingUnit = ON_MISSING_UNIT_THROW): void`

Add a conversion between two units.

```php
// Add a custom conversion
ConversionRegistry::add('fur', 'yd', 220);  // 1 furlong = 220 yards
```

#### `static addConversion(Conversion $conversion): void`

Add a Conversion object directly.

```php
$conversion = new Conversion($srcUnit, $destUnit, $factor);
ConversionRegistry::addConversion($conversion);
```

#### `static removeConversion(Conversion $conversion): void`

Remove a conversion from the registry.

```php
ConversionRegistry::removeConversion($conversion);
```

#### `static loadConversions(System $system): void`

Load conversions for a measurement system.

```php
ConversionRegistry::loadConversions(System::Imperial);
```

#### `static reset(): void`

Reset all conversions.

```php
ConversionRegistry::reset();
```

#### `static resetByDimension(string $dimension): void`

Reset conversions for a specific dimension.

```php
ConversionRegistry::resetByDimension('L');
```

### Inspection Methods

#### `static has(string $dimension, string $srcSymbol, string $destSymbol): bool`

Check if a conversion exists.

```php
if (ConversionRegistry::has('L', 'm', 'ft')) {
    // Direct conversion available
}
```

#### `static hasConversion(Conversion $conversion): bool`

Check if a Conversion object exists in the registry.

```php
if (ConversionRegistry::hasConversion($conversion)) {
    // Conversion is registered
}
```

---

## How Conversions Are Stored

Conversions are stored in a three-dimensional array:

```
$conversions[$dimension][$srcSymbol][$destSymbol] = Conversion
```

For example:
- `$conversions['L']['m']['ft']` = conversion from metres to feet
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
use Galaxon\Quantities\System;

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
ConversionRegistry::add('league', 'mi', 3);
```

---

## See Also

- **[Conversion](../Conversion.md)** - Conversion class documentation
- **[Converter](../Converter.md)** - Converter class that uses this registry
- **[UnitRegistry](UnitRegistry.md)** - Unit registry
- **[System](../System.md)** - Measurement system enum
