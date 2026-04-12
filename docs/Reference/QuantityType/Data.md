# Data

Represents data/information quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Data` class handles digital information measurements in bits and bytes, supporting both metric (decimal) and binary prefixes.

---

## Unit definitions

| Name | ASCII symbol | Prefixes                | Systems |
| ---- | ------------ | ----------------------- | ------- |
| bit  | `b`          | large metric and binary | Common  |
| byte | `B`          | large metric and binary | Common  |

---

## Conversion definitions

| From | To  | Factor |
| ---- | --- | ------ |
| *B*  | *b* | 8      |

---

## Prefixes

Data units support both large metric prefixes (*kilo* and above), and binary (IEC) prefixes (*kibi*, *mebi*, etc.). See [Prefixes](Prefixes.md) for the full list.

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

## See Also

- **[Units: Data](../../Concepts/Units.md#data)** - Complete list of data units
- **[Quantity](../Quantity.md)** - Base class documentation
