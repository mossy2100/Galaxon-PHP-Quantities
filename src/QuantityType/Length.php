<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;

class Length extends Quantity
{
    /**
     * Unit definitions for length.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnitDefinitions(): array
    {
        return [
            // SI base unit
            'metre'             => [
                'asciiSymbol' => 'm',
                'dimension'   => 'L',
                'system'      => 'si_base',
                'prefixGroup' => PrefixRegistry::PREFIX_GROUP_METRIC,
            ],
            // Astronomical units
            'astronomical unit' => [
                'asciiSymbol' => 'au',
                'dimension'   => 'L',
                'system'      => 'metric',
            ],
            'light year'        => [
                'asciiSymbol' => 'ly',
                'dimension'   => 'L',
                'system'      => 'metric',
            ],
            'parsec'            => [
                'asciiSymbol' => 'pc',
                'dimension'   => 'L',
                'system'      => 'metric',
                'prefixGroup' => PrefixRegistry::PREFIX_GROUP_LARGE_METRIC,
            ],
            // US customary units
            'pixel'             => [
                'asciiSymbol' => 'px',
                'dimension'   => 'L',
                'system'      => 'us_customary',
            ],
            'point'             => [
                'asciiSymbol' => 'pt',
                'dimension'   => 'L',
                'system'      => 'us_customary',
            ],
            'pica'              => [
                'asciiSymbol' => 'pica',
                'dimension'   => 'L',
                'system'      => 'us_customary',
            ],
            'inch'              => [
                'asciiSymbol' => 'in',
                'dimension'   => 'L',
                'system'      => 'us_customary',
            ],
            'foot'              => [
                'asciiSymbol' => 'ft',
                'dimension'   => 'L',
                'system'      => 'us_customary',
            ],
            'yard'              => [
                'asciiSymbol' => 'yd',
                'dimension'   => 'L',
                'system'      => 'us_customary',
            ],
            'mile'              => [
                'asciiSymbol' => 'mi',
                'dimension'   => 'L',
                'system'      => 'us_customary',
            ],
            'nautical mile'     => [
                'asciiSymbol' => 'nmi',
                'dimension'   => 'L',
                'system'      => 'us_customary',
            ],
        ];
    }

    /**
     * Conversion factors for length units.
     *
     * @return list<array{string, string, float}>
     * @see https://en.wikipedia.org/wiki/International_yard_and_pound
     */
    public static function getConversions(): array
    {
        return [
            // Metric-US bridge
            ['yd', 'm', 0.9144],
            // US customary
            ['in', 'px', 96],
            ['in', 'pt', 72],
            ['in', 'pica', 6],
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
    }
}
