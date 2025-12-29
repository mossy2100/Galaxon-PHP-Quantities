<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use Galaxon\Core\Floats;
use ValueError;

class UnitData
{
    /**
     * Constants for prefix set codes.
     */

    // 1 = Small metric (quecto to deci)
    public const int PREFIX_CODE_SMALL_METRIC = 1;

    // 2 = Large metric (deca to quetta)
    public const int PREFIX_CODE_LARGE_METRIC = 2;

    // 3 = All metric (1|2)
    public const int PREFIX_CODE_METRIC = self::PREFIX_CODE_SMALL_METRIC | self::PREFIX_CODE_LARGE_METRIC;

    // 4 = Binary (Ki, Mi, Gi, etc.)
    public const int PREFIX_CODE_BINARY = 4;

    // 6 = Large metric + binary (2|4)
    public const int PREFIX_CODE_LARGE = self::PREFIX_CODE_LARGE_METRIC | self::PREFIX_CODE_BINARY;

    // 7 = All (1|2|4)
    public const int PREFIX_CODE_ALL = self::PREFIX_CODE_METRIC | self::PREFIX_CODE_BINARY;

    /**
     * Standard metric prefixes down to quecto (10^-30).
     *
     * Includes both standard symbols and alternatives (e.g., 'u' for micro).
     *
     * @var array<string, float>
     */
    public const array PREFIXES_SMALL_METRIC = [
        'q' => 1e-30,  // quecto
        'r' => 1e-27,  // ronto
        'y' => 1e-24,  // yocto
        'z' => 1e-21,  // zepto
        'a' => 1e-18,  // atto
        'f' => 1e-15,  // femto
        'p' => 1e-12,  // pico
        'n' => 1e-9,   // nano
        'μ' => 1e-6,   // micro
        'u' => 1e-6,   // micro (alias)
        'm' => 1e-3,   // milli
        'c' => 1e-2,   // centi
        'd' => 1e-1,   // deci
    ];

    /**
     * Standard metric prefixes up to quetta (10^30).
     *
     * @var array<string, float>
     */
    public const array PREFIXES_LARGE_METRIC = [
        'da' => 1e1,    // deca
        'h'  => 1e2,    // hecto
        'k'  => 1e3,    // kilo
        'M'  => 1e6,    // mega
        'G'  => 1e9,    // giga
        'T'  => 1e12,   // tera
        'P'  => 1e15,   // peta
        'E'  => 1e18,   // exa
        'Z'  => 1e21,   // zetta
        'Y'  => 1e24,   // yotta
        'R'  => 1e27,   // ronna
        'Q'  => 1e30,   // quetta
    ];

    /**
     * Binary prefixes for memory measurements.
     *
     * @var array<string, float>
     */
    public const array PREFIXES_BINARY = [
        'Ki' => 2 ** 10, // kibi
        'Mi' => 2 ** 20, // mebi
        'Gi' => 2 ** 30, // gibi
        'Ti' => 2 ** 40, // tebi
        'Pi' => 2 ** 50, // pebi
        'Ei' => 2 ** 60, // exbi
        'Zi' => 2 ** 70, // zebi
        'Yi' => 2 ** 80, // yobi
    ];

    /**
     * Known/supported base units.
     *
     * Parsing should accept the symbol and the format string. Formatting should use the format string.
     *
     * Exponents in dimension codes are written as suffixes: L2 = L², T-1 = T⁻¹, MLT-2 = M·L·T⁻²
     */
    public const array UNITS = [
        // SI base units
        'm'      => [
            'name'      => 'metre',
            'quantity'  => 'length',
            'dimension' => 'L',
            'system'    => 'si_base',
            'prefixes'  => self::PREFIX_CODE_METRIC,
        ],
        'g'      => [
            'name'      => 'gram',
            'quantity'  => 'mass',
            'dimension' => 'M',
            'system'    => 'si_base',
            'prefixes'  => self::PREFIX_CODE_METRIC,
            'si_prefix' => 'k',
        ],
        's'      => [
            'name'      => 'second',
            'quantity'  => 'time',
            'dimension' => 'T',
            'system'    => 'si_base',
            'prefixes'  => self::PREFIX_CODE_METRIC,
        ],
        'A'      => [
            'name'      => 'ampere',
            'quantity'  => 'electric current',
            'dimension' => 'I',
            'system'    => 'si_base',
            'prefixes'  => self::PREFIX_CODE_METRIC,
        ],
        'K'      => [
            'name'      => 'kelvin',
            'quantity'  => 'temperature',
            'dimension' => 'H',
            'system'    => 'si_base',
            'prefixes'  => self::PREFIX_CODE_METRIC,
        ],
        'mol'    => [
            'name'      => 'mole',
            'quantity'  => 'amount of substance',
            'dimension' => 'N',
            'system'    => 'si_base',
            'prefixes'  => self::PREFIX_CODE_METRIC,
        ],
        'cd'     => [
            'name'      => 'candela',
            'quantity'  => 'luminous intensity',
            'dimension' => 'J',
            'system'    => 'si_base',
            'prefixes'  => self::PREFIX_CODE_METRIC,
        ],

        // SI derived units
        'rad'    => [
            'name'      => 'radian',
            'quantity'  => 'angle',
            'dimension' => 'A',
            'system'    => 'si_derived',
            'prefixes'  => self::PREFIX_CODE_SMALL_METRIC,
        ],
        'sr'     => [
            'name'      => 'steradian',
            'quantity'  => 'solid angle',
            'dimension' => 'A2',
            'system'    => 'si_derived',
            'prefixes'  => self::PREFIX_CODE_SMALL_METRIC,
        ],

        // SI named units
        'Hz'     => [
            'name'       => 'hertz',
            'quantity'   => 'frequency',
            'dimension'  => 'T-1',
            'system'     => 'si_named',
            'prefixes'   => self::PREFIX_CODE_METRIC,
            'equivalent' => 's-1',
        ],
        'N'      => [
            'name'       => 'newton',
            'quantity'   => 'force',
            'dimension'  => 'MLT-2',
            'system'     => 'si_named',
            'prefixes'   => self::PREFIX_CODE_METRIC,
            'equivalent' => 'kg*m*s-2',
        ],
        'Pa'     => [
            'name'       => 'pascal',
            'quantity'   => 'pressure',
            'dimension'  => 'ML-1T-2',
            'system'     => 'si_named',
            'prefixes'   => self::PREFIX_CODE_METRIC,
            'equivalent' => 'kg*m-1*s-2',
        ],
        'J'      => [
            'name'       => 'joule',
            'quantity'   => 'energy',
            'dimension'  => 'ML2T-2',
            'system'     => 'si_named',
            'prefixes'   => self::PREFIX_CODE_METRIC,
            'equivalent' => 'kg*m2*s-2',
        ],
        'W'      => [
            'name'       => 'watt',
            'quantity'   => 'power',
            'dimension'  => 'ML2T-3',
            'system'     => 'si_named',
            'prefixes'   => self::PREFIX_CODE_METRIC,
            'equivalent' => 'kg*m2*s-3',
        ],
        'C'      => [
            'name'       => 'coulomb',
            'quantity'   => 'electric charge',
            'dimension'  => 'TI',
            'system'     => 'si_named',
            'prefixes'   => self::PREFIX_CODE_METRIC,
            'equivalent' => 's*A',
        ],
        'V'      => [
            'name'       => 'volt',
            'quantity'   => 'voltage',
            'dimension'  => 'ML2T-3I-1',
            'system'     => 'si_named',
            'prefixes'   => self::PREFIX_CODE_METRIC,
            'equivalent' => 'kg*m2*s-3*A-1',
        ],
        'F'      => [
            'name'       => 'farad',
            'quantity'   => 'capacitance',
            'dimension'  => 'M-1L-2T4I2',
            'system'     => 'si_named',
            'prefixes'   => self::PREFIX_CODE_METRIC,
            'equivalent' => 'kg-1*m-2*s4*A2',
        ],
        'ohm'    => [
            'name'       => 'ohm',
            'format'     => 'Ω',
            'quantity'   => 'resistance',
            'dimension'  => 'ML2T-3I-2',
            'system'     => 'si_named',
            'prefixes'   => self::PREFIX_CODE_METRIC,
            'equivalent' => 'kg*m2*s-3*A-2',
        ],
        'S'      => [
            'name'       => 'siemens',
            'quantity'   => 'conductance',
            'dimension'  => 'M-1L-2T3I2',
            'system'     => 'si_named',
            'prefixes'   => self::PREFIX_CODE_METRIC,
            'equivalent' => 'kg-1*m-2*s3*A2',
        ],
        'Wb'     => [
            'name'       => 'weber',
            'quantity'   => 'magnetic flux',
            'dimension'  => 'ML2T-2I-1',
            'system'     => 'si_named',
            'prefixes'   => self::PREFIX_CODE_METRIC,
            'equivalent' => 'kg*m2*s-2*A-1',
        ],
        'T'      => [
            'name'       => 'tesla',
            'quantity'   => 'magnetic flux density',
            'dimension'  => 'MT-2I-1',
            'system'     => 'si_named',
            'prefixes'   => self::PREFIX_CODE_METRIC,
            'equivalent' => 'kg*s-2*A-1',
        ],
        'H'      => [
            'name'       => 'henry',
            'quantity'   => 'inductance',
            'dimension'  => 'ML2T-2I-2',
            'system'     => 'si_named',
            'prefixes'   => self::PREFIX_CODE_METRIC,
            'equivalent' => 'kg*m2*s-2*A-2',
        ],
        'lm'     => [
            'name'       => 'lumen',
            'quantity'   => 'luminous flux',
            'dimension'  => 'JA2',
            'system'     => 'si_named',
            'prefixes'   => self::PREFIX_CODE_METRIC,
            'equivalent' => 'cd*sr',
        ],
        'lx'     => [
            'name'       => 'lux',
            'quantity'   => 'illuminance',
            'dimension'  => 'JA2L-2',
            'system'     => 'si_named',
            'prefixes'   => self::PREFIX_CODE_METRIC,
            'equivalent' => 'cd*sr*m-2',
        ],
        'Bq'     => [
            'name'       => 'becquerel',
            'quantity'   => 'radioactivity',
            'dimension'  => 'T-1',
            'system'     => 'si_named',
            'prefixes'   => self::PREFIX_CODE_METRIC,
            'equivalent' => 's-1',
        ],
        'Gy'     => [
            'name'       => 'gray',
            'quantity'   => 'absorbed dose',
            'dimension'  => 'L2T-2',
            'system'     => 'si_named',
            'prefixes'   => self::PREFIX_CODE_METRIC,
            'equivalent' => 'm2*s-2',
        ],
        'Sv'     => [
            'name'       => 'sievert',
            'quantity'   => 'equivalent dose',
            'dimension'  => 'L2T-2',
            'system'     => 'si_named',
            'prefixes'   => self::PREFIX_CODE_METRIC,
            'equivalent' => 'm2*s-2',
        ],
        'kat'    => [
            'name'       => 'katal',
            'quantity'   => 'catalytic activity',
            'dimension'  => 'NT-1',
            'system'     => 'si_named',
            'prefixes'   => self::PREFIX_CODE_METRIC,
            'equivalent' => 'mol*s-1',
        ],

        // US named units
        'kn'     => [
            'name'       => 'knot',
            'quantity'   => 'velocity',
            'dimension'  => 'LT-1',
            'system'     => 'us_named',
            'equivalent' => 'nmi*h-1',
        ],

        // Non-SI metric units
        'l'      => [
            'name'      => 'litre',
            'format'    => 'L',
            'quantity'  => 'volume',
            'dimension' => 'L3',
            'system'    => 'metric',
            'prefixes'  => self::PREFIX_CODE_METRIC,
        ],
        'ha'     => [
            'name'      => 'hectare',
            'quantity'  => 'area',
            'dimension' => 'L2',
            'system'    => 'metric',
        ],
        't'      => [
            'name'      => 'tonne',
            'quantity'  => 'mass',
            'dimension' => 'M',
            'system'    => 'metric',
        ],
        'bar'    => [
            'name'      => 'bar',
            'quantity'  => 'pressure',
            'dimension' => 'ML-1T-2',
            'system'    => 'metric',
        ],
        'eV'     => [
            'name'      => 'electronvolt',
            'quantity'  => 'energy',
            'dimension' => 'ML2T-2',
            'system'    => 'metric',
            'prefixes'  => self::PREFIX_CODE_METRIC,
        ],

        // Non-SI angle units
        'deg'    => [
            'name'      => 'degree',
            'format'    => '°',
            'quantity'  => 'angle',
            'dimension' => 'A',
            'system'    => 'metric',
        ],
        'arcmin' => [
            'name'      => 'arcminute',
            'format'    => '′',
            'quantity'  => 'angle',
            'dimension' => 'A',
            'system'    => 'metric',
        ],
        'arcsec' => [
            'name'      => 'arcsecond',
            'format'    => '″',
            'quantity'  => 'angle',
            'dimension' => 'A',
            'system'    => 'metric',
        ],
        'grad'   => [
            'name'      => 'gradian',
            'quantity'  => 'angle',
            'dimension' => 'A',
            'system'    => 'metric',
        ],
        'turn'   => [
            'name'      => 'turn',
            'quantity'  => 'angle',
            'dimension' => 'A',
            'system'    => 'metric',
        ],

        // Non-SI temperature units
        'degC'   => [
            'name'      => 'celsius',
            'format'    => '°C',
            'quantity'  => 'temperature',
            'dimension' => 'H',
            'system'    => 'metric',
        ],
        'degF'   => [
            'name'      => 'fahrenheit',
            'format'    => '°F',
            'quantity'  => 'temperature',
            'dimension' => 'H',
            'system'    => 'us_customary',
        ],
        'degR'   => [
            'name'      => 'rankine',
            'format'    => '°R',
            'quantity'  => 'temperature',
            'dimension' => 'H',
            'system'    => 'us_customary',
        ],

        // Non-SI time units
        'min'    => [
            'name'      => 'minute',
            'quantity'  => 'time',
            'dimension' => 'T',
            'system'    => 'metric',
        ],
        'h'      => [
            'name'      => 'hour',
            'quantity'  => 'time',
            'dimension' => 'T',
            'system'    => 'metric',
        ],
        'd'      => [
            'name'      => 'day',
            'quantity'  => 'time',
            'dimension' => 'T',
            'system'    => 'metric',
        ],
        'w'      => [
            'name'      => 'week',
            'quantity'  => 'time',
            'dimension' => 'T',
            'system'    => 'metric',
        ],
        'mo'     => [
            'name'      => 'month',
            'quantity'  => 'time',
            'dimension' => 'T',
            'system'    => 'metric',
        ],
        'y'      => [
            'name'      => 'year',
            'quantity'  => 'time',
            'dimension' => 'T',
            'system'    => 'metric',
        ],
        'c'      => [
            'name'      => 'century',
            'quantity'  => 'time',
            'dimension' => 'T',
            'system'    => 'metric',
        ],

        // Astronomical length units
        'au'     => [
            'name'      => 'astronomical unit',
            'quantity'  => 'length',
            'dimension' => 'L',
            'system'    => 'metric',
        ],
        'ly'     => [
            'name'      => 'light year',
            'quantity'  => 'length',
            'dimension' => 'L',
            'system'    => 'metric',
        ],
        'pc'     => [
            'name'      => 'parsec',
            'quantity'  => 'length',
            'dimension' => 'L',
            'system'    => 'metric',
            'prefixes'  => self::PREFIX_CODE_LARGE_METRIC,
        ],

        // US customary length units
        'px'     => [
            'name'      => 'pixel',
            'quantity'  => 'length',
            'dimension' => 'L',
            'system'    => 'us_customary',
        ],
        'pt'  => [
            'name'      => 'point',
            'quantity'  => 'length',
            'dimension' => 'L',
            'system'    => 'us_customary',
        ],
        'in'     => [
            'name'      => 'inch',
            'quantity'  => 'length',
            'dimension' => 'L',
            'system'    => 'us_customary',
        ],
        'ft'     => [
            'name'      => 'foot',
            'quantity'  => 'length',
            'dimension' => 'L',
            'system'    => 'us_customary',
        ],
        'yd'     => [
            'name'      => 'yard',
            'quantity'  => 'length',
            'dimension' => 'L',
            'system'    => 'us_customary',
        ],
        'mi'     => [
            'name'      => 'mile',
            'quantity'  => 'length',
            'dimension' => 'L',
            'system'    => 'us_customary',
        ],
        'nmi'    => [
            'name'      => 'nautical mile',
            'quantity'  => 'length',
            'dimension' => 'L',
            'system'    => 'us_customary',
        ],

        // US customary area units
        'ac'     => [
            'name'      => 'acre',
            'quantity'  => 'area',
            'dimension' => 'L2',
            'system'    => 'us_customary',
        ],

        // US customary volume units
        'tsp'    => [
            'name'      => 'teaspoon',
            'quantity'  => 'volume',
            'dimension' => 'L3',
            'system'    => 'us_customary',
        ],
        'tbsp'   => [
            'name'      => 'tablespoon',
            'quantity'  => 'volume',
            'dimension' => 'L3',
            'system'    => 'us_customary',
        ],
        'floz'   => [
            'name'      => 'fluid ounce',
            'quantity'  => 'volume',
            'dimension' => 'L3',
            'system'    => 'us_customary',
        ],
        'cup'    => [
            'name'      => 'cup',
            'quantity'  => 'volume',
            'dimension' => 'L3',
            'system'    => 'us_customary',
        ],
        'pt'     => [
            'name'      => 'pint',
            'quantity'  => 'volume',
            'dimension' => 'L3',
            'system'    => 'us_customary',
        ],
        'qt'     => [
            'name'      => 'quart',
            'quantity'  => 'volume',
            'dimension' => 'L3',
            'system'    => 'us_customary',
        ],
        'gal'    => [
            'name'      => 'gallon',
            'quantity'  => 'volume',
            'dimension' => 'L3',
            'system'    => 'us_customary',
        ],

        // US customary mass units
        'oz'     => [
            'name'      => 'ounce',
            'quantity'  => 'mass',
            'dimension' => 'M',
            'system'    => 'us_customary',
        ],
        'lb'     => [
            'name'      => 'pound',
            'quantity'  => 'mass',
            'dimension' => 'M',
            'system'    => 'us_customary',
        ],
        'st'     => [
            'name'      => 'stone',
            'quantity'  => 'mass',
            'dimension' => 'M',
            'system'    => 'us_customary',
        ],
        'ton'    => [
            'name'      => 'short ton',
            'quantity'  => 'mass',
            'dimension' => 'M',
            'system'    => 'us_customary',
        ],

        // Data units
        'b'      => [
            'name'      => 'bit',
            'quantity'  => 'data',
            'dimension' => 'D',
            'system'    => 'metric',
            'prefixes'  => self::PREFIX_CODE_LARGE,
        ],
        'B'      => [
            'name'      => 'byte',
            'quantity'  => 'data',
            'dimension' => 'D',
            'system'    => 'metric',
            'prefixes'  => self::PREFIX_CODE_LARGE,
        ],
    ];

    public const array ANGLE_CONVERSIONS = [
        ['turn', 'rad', Floats::TAU],
        ['turn', 'deg', 360],
        ['deg', 'arcmin', 60],
        ['arcmin', 'arcsec', 60],
        ['turn', 'grad', 400],
    ];

    public const array LENGTH_CONVERSIONS = [
        // Metric-US bridge
        ['in', 'mm', 25.4],
        // US customary
        ['in', 'px', 96],
        ['in', 'pt', 72],
        ['ft', 'in', 12],
        ['yd', 'ft', 3],
        ['mi', 'yd', 1760],
        // Astronomical
        ['au', 'm', 149597870700],
        ['ly', 'm', 9460730472580800],
        ['pc', 'au', 648000 / M_PI],
        // Nautical
        ['nmi', 'm', 1852],
    ];

    public const array AREA_CONVERSIONS = [
        // Metric
        ['ha', 'm2', 10000],
        // Metric-US bridge
        ['ac', 'm2', 4046.8564224],
        // US customary
        ['mi2', 'ac', 640],
        ['ac', 'yd2', 4840],
    ];

    /**
     * Volume conversions
     *
     * US customary units are used here, not imperial.
     */
    public const array VOLUME_CONVERSIONS = [
        // Metric
        ['m3', 'L', 1000],
        // Metric-US bridge
        ['in3', 'mL', 16.387064],
        // US customary
        ['gal', 'in3', 231],
        ['gal', 'qt', 4],
        ['qt', 'pt', 2],
        ['pt', 'cup', 2],
        ['cup', 'floz', 8],
        ['floz', 'tbsp', 2],
        ['tbsp', 'tsp', 3]
    ];

    public const array MASS_CONVERSIONS = [
        // Metric
        ['t', 'kg', 1000],
        // Metric-US bridge
        ['lb', 'g', 453.59237],
        // US customary
        ['lb', 'oz', 16],
        ['st', 'lb', 14],
        ['ton', 'lb', 2000],
    ];

    public const array TEMPERATURE_CONVERSIONS = [
        ['degC', 'K', 1, 273.15],
        ['degF', 'degR', 1, 491.67],
        ['K', 'degR', 1.8],
    ];

    public const array PRESSURE_CONVERSIONS = [
        ['bar', 'Pa', 100000],
        ['mmHg', 'Pa', 133.322387415],
        ['atm', 'Pa', 101325],
    ];

    public const array ENERGY_CONVERSIONS = [
        ['eV', 'J', 1.602_176_634e-19],
    ];

    public const array TIME_CONVERSIONS = [
        ['min', 's', 60],
        ['h', 'min', 60],
        ['d', 'h', 24],
        ['w', 'd', 7],
        ['y', 'mo', 12],
        ['y', 'd', 365.2425],
        ['c', 'y', 100]
    ];

    public const array DATA_CONVERSIONS = [
        // 1 byte = 8 bits
        ['B', 'b', 8]
    ];

    /**
     * Full array of conversions, keyed by dimension code.
     *
     * @var array<string, array>
     */
    public const array CONVERSIONS = [
        'A' => self::ANGLE_CONVERSIONS,
        'L' => self::LENGTH_CONVERSIONS,
        'L2' => self::AREA_CONVERSIONS,
        'L3' => self::VOLUME_CONVERSIONS,
        'M' => self::MASS_CONVERSIONS,
        'H' => self::TEMPERATURE_CONVERSIONS,
        'ML-1T-2' => self::PRESSURE_CONVERSIONS,
        'ML2T-2' => self::ENERGY_CONVERSIONS,
        'T' => self::TIME_CONVERSIONS,
        'D' => self::DATA_CONVERSIONS,
    ];
}
