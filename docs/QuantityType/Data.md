# Data

Represents data/information quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Data` class handles digital information measurements in bits and bytes, supporting both metric (decimal) and binary prefixes.

For the complete list of data units, see [Supported Units: Data](../SupportedUnits.md#data).

---

## Prefixes

Data units support two prefix systems:

### Metric (Decimal) Prefixes

| Prefix | Symbol | Factor |
|--------|--------|--------|
| kilo | k | 10³ (1,000) |
| mega | M | 10⁶ (1,000,000) |
| giga | G | 10⁹ |
| tera | T | 10¹² |
| peta | P | 10¹⁵ |

### Binary (IEC) Prefixes

| Prefix | Symbol | Factor |
|--------|--------|--------|
| kibi | Ki | 2¹⁰ (1,024) |
| mebi | Mi | 2²⁰ (1,048,576) |
| gibi | Gi | 2³⁰ |
| tebi | Ti | 2⁴⁰ |
| pebi | Pi | 2⁵⁰ |

---

## Bits vs Bytes

```php
// 1 byte = 8 bits
$byte = new Data(1, 'B');
$bits = $byte->to('b');  // 8 b

// Network speeds (typically in bits)
$speed = new Data(100, 'Mb');  // 100 megabits
$inBytes = $speed->to('MB');   // 12.5 MB

// Storage (typically in bytes)
$disk = new Data(1, 'TB');
$inGiB = $disk->to('GiB');  // 931.323 GiB
```

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\Data;

// File sizes (metric)
$file = new Data(4.7, 'GB');
$inMB = $file->to('MB');  // 4700 MB

// RAM (binary)
$ram = new Data(16, 'GiB');
$inMiB = $ram->to('MiB');  // 16,384 MiB
$inGB = $ram->to('GB');    // 17.179 GB

// Bandwidth
$bandwidth = new Data(1, 'Gb');  // 1 gigabit
$inMB = $bandwidth->to('MB');    // 125 MB

// Storage comparison
$advertised = new Data(1, 'TB');
$actual = new Data(1, 'TiB');
echo $advertised->to('GiB')->value;  // 931.323 GiB
echo $actual->to('GB')->value;       // 1099.51 GB
```

---

## Common Conversions

| Marketing | Actual (Binary) |
|-----------|-----------------|
| 1 TB | 931.32 GiB |
| 1 GB | 953.67 MiB |
| 1 MB | 976.56 KiB |

---

## See Also

- **[Supported Units: Data](../SupportedUnits.md#data)** - Complete list of data units
- **[Quantity](../Quantity.md)** - Base class documentation
