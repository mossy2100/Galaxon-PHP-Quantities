# Frequency

Represents frequency and radioactivity quantities.

**Namespace:** `Galaxon\Quantities\QuantityType`
**Extends:** [`Quantity`](../Quantity.md)

---

## Overview

The `Frequency` class handles measurements with dimension T⁻¹ (inverse time). This includes both frequency (*hertz*) and radioactivity (*becquerel*).

---

## Unit definitions

| Name      | ASCII symbol | Prefixes   | Systems |
| --------- | ------------ | ---------- | ------- |
| hertz     | `Hz`         | all metric | SI      |
| becquerel | `Bq`         | all metric | SI      |

**Note:** *Hertz* measures frequency; *becquerel* measures radioactivity. Both have dimension T⁻¹.

---

## Conversion definitions

| From | To    | Factor |
| ---- | ----- | ------ |
| `Hz` | `s-1` | 1      |
| `Bq` | `s-1` | 1      |

---

## Hertz vs becquerel

Both units have the same dimension (s⁻¹) but measure different phenomena:

| Unit           | Measures           | Example                        |
|----------------|--------------------|--------------------------------|
| hertz (Hz)     | Periodic events    | Sound waves, radio frequencies |
| becquerel (Bq) | Radioactive decays | Nuclear decay rate             |

The units are dimensionally equivalent but represent different physical concepts:

```php
$radio = new Frequency(100, 'MHz');  // 100 million cycles/second
$decay = new Frequency(1000, 'Bq');  // 1000 decays/second
```

---

## SI unit

Both *hertz* and *becquerel* expand to the same base unit expression:

```
Hz = s⁻¹
Bq = s⁻¹
```

---

## Usage examples

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

## Frequency and period

Frequency and period are inversely related:

```
f = 1/T
T = 1/f
```

Where f is frequency in Hz and T is period in seconds.

---

## Physical constants

The following physical constants have this quantity type. See [`PhysicalConstant`](../PhysicalConstant.md) for the full list.

- **`PhysicalConstant::caesiumFrequency()`** (ΔνCs) — Hyperfine transition frequency of caesium, 9,192,631,770 Hz.

---

## See also

- **[Quantity](../Quantity.md)** - Base class documentation
- **[Time](Time.md)** - Related quantity (inverse relationship)
- **[RadiationDose](RadiationDose.md)** - Related radiation quantity
