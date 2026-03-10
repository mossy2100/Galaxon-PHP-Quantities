---

## Available Prefixes

### Metric Prefixes

| Name   | Symbol | Multiplier | Group  |
| ------ | ------ | ---------- | ------ |
| quecto | q      | 10⁻³⁰      | small  |
| ronto  | r      | 10⁻²⁷      | small  |
| yocto  | y      | 10⁻²⁴      | small  |
| zepto  | z      | 10⁻²¹      | small  |
| atto   | a      | 10⁻¹⁸      | small  |
| femto  | f      | 10⁻¹⁵      | small  |
| pico   | p      | 10⁻¹²      | small  |
| nano   | n      | 10⁻⁹       | small  |
| micro  | μ, u   | 10⁻⁶       | small  |
| milli  | m      | 10⁻³       | small  |
| centi  | c      | 10⁻²       | medium |
| deci   | d      | 10⁻¹       | medium |
| deca   | da     | 10¹        | medium |
| hecto  | h      | 10²        | medium |
| kilo   | k      | 10³        | large  |
| mega   | M      | 10⁶        | large  |
| giga   | G      | 10⁹        | large  |
| tera   | T      | 10¹²       | large  |
| peta   | P      | 10¹⁵       | large  |
| exa    | E      | 10¹⁸       | large  |
| zetta  | Z      | 10²¹       | large  |
| yotta  | Y      | 10²⁴       | large  |
| ronna  | R      | 10²⁷       | large  |
| quetta | Q      | 10³⁰       | large  |

### Binary Prefixes

| Name | Symbol | Multiplier | Decimal Approx |
|------|--------|------------|----------------|
| kibi | Ki | 2¹⁰ | ~1.024 × 10³ |
| mebi | Mi | 2²⁰ | ~1.049 × 10⁶ |
| gibi | Gi | 2³⁰ | ~1.074 × 10⁹ |
| tebi | Ti | 2⁴⁰ | ~1.100 × 10¹² |
| pebi | Pi | 2⁵⁰ | ~1.126 × 10¹⁵ |
| exbi | Ei | 2⁶⁰ | ~1.153 × 10¹⁸ |
| zebi | Zi | 2⁷⁰ | ~1.181 × 10²¹ |
| yobi | Yi | 2⁸⁰ | ~1.209 × 10²⁴ |
| robi | Ri | 2⁹⁰ | ~1.238 × 10²⁷ |
| quebi | Qi | 2¹⁰⁰ | ~1.268 × 10³⁰ |

---

## Prefix Group Constants

Prefixes are organised into groups using bitwise flags:

| Constant              | Value | Description                 |
| --------------------- | ----- | --------------------------- |
| `GROUP_SMALL_METRIC`  | 1     | Small metric (q-m)          |
| `GROUP_MEDIUM_METRIC` | 2     | Medium metric (c, d, da, h) |
| `GROUP_LARGE_METRIC`  | 4     | Large metric (k-Q)          |
| `GROUP_BINARY`        | 8     | Binary                      |

### Combined Group Codes

| Constant            | Components       | Description                                                                                                                                                                                          |
| ------------------- | ---------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `GROUP_METRIC`      | 1 \| 2 \| 4      | All metric prefixes                                                                                                                                                                                  |
| `GROUP_ENGINEERING` | 1 \| 4           | Engineering metric prefixes, which represent powers of 1000. This includes all small and large metric prefixes, and excludes the medium ones. These prefixes are used by the autoprefixing function. |
| `GROUP_LARGE`       | 4 \| 8           | Large metric + binary (i.e. *kilo* and *Kibi* upwards). Used by data units.                                                                                                                          |
| `GROUP_ALL`         | 1 \| 2 \| 4 \| 8 | All prefixes                                                                                                                                                                                         |
