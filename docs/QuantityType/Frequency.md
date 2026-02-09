# Frequency

Represents frequency and radioactivity quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Frequency` class handles measurements with dimension T⁻¹ (inverse time). This includes both frequency (hertz) and radioactivity (becquerel).

For the complete list of frequency units, see [Supported Units: Frequency](../SupportedUnits.md#frequency).

---

## Hertz vs Becquerel

Both units have the same dimension (s⁻¹) but measure different phenomena:

| Unit | Measures | Example |
|------|----------|---------|
| Hertz (Hz) | Periodic events | Sound waves, radio frequencies |
| Becquerel (Bq) | Radioactive decays | Nuclear decay rate |

The units are dimensionally equivalent but represent different physical concepts:

```php
$radio = new Frequency(100, 'MHz');  // 100 million cycles/second
$decay = new Frequency(1000, 'Bq');  // 1000 decays/second
```

---

## SI Unit Expansion

Both hertz and becquerel expand to the same base unit expression:

```
Hz = s⁻¹
Bq = s⁻¹
```

---

## Common Prefixed Units

| Unit | Value | Typical Use |
|------|-------|-------------|
| mHz | 0.001 Hz | Seismic waves |
| Hz | 1 Hz | Low audio |
| kHz | 1,000 Hz | Audio, AM radio |
| MHz | 1,000,000 Hz | FM radio, computers |
| GHz | 10⁹ Hz | Microwave, WiFi, CPUs |
| THz | 10¹² Hz | Infrared light |

---

## Usage Examples

```php
use Galaxon\Quantities\QuantityType\Frequency;

// Sound frequencies
$middleC = new Frequency(261.63, 'Hz');
$inKHz = $middleC->to('kHz');  // 0.26163 kHz

// Radio frequencies
$fm = new Frequency(98.7, 'MHz');
$inHz = $fm->to('Hz');  // 98,700,000 Hz

// Computer clock speeds
$cpu = new Frequency(3.5, 'GHz');
$inMHz = $cpu->to('MHz');  // 3500 MHz

// Radioactivity
$sample = new Frequency(37, 'GBq');  // 1 curie equivalent
$inMBq = $sample->to('MBq');  // 37,000 MBq

// Period (inverse of frequency)
$wave = new Frequency(1000, 'Hz');
$period = 1 / $wave->value;  // 0.001 seconds
```

---

## Frequency and Period

Frequency and period are inversely related:

```
f = 1/T
T = 1/f
```

Where f is frequency in Hz and T is period in seconds.

---

## See Also

- **[Supported Units: Frequency](../SupportedUnits.md#frequency)** - Complete list of frequency units
- **[Quantity](../Quantity.md)** - Base class documentation
- **[Time](Time.md)** - Related quantity (inverse relationship)
- **[RadiationDose](RadiationDose.md)** - Related radiation quantity
