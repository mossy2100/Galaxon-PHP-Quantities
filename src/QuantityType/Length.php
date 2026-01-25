<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Override;

class Length extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for length.
     *
     * @return array<string, array<string, string|int>>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'metre'             => [
                'asciiSymbol' => 'm',
                'prefixGroup' => PrefixRegistry::PREFIX_GROUP_METRIC,
            ],
            'astronomical unit' => [
                'asciiSymbol' => 'au',
            ],
            'light year'        => [
                'asciiSymbol' => 'ly',
            ],
            'parsec'            => [
                'asciiSymbol' => 'pc',
                'prefixGroup' => PrefixRegistry::PREFIX_GROUP_LARGE_METRIC,
            ],
            'pixel'             => [
                'asciiSymbol' => 'px',
            ],
            'point'             => [
                'asciiSymbol' => 'p',
            ],
            'pica'              => [
                'asciiSymbol' => 'P',
            ],
            'inch'              => [
                'asciiSymbol' => 'in',
            ],
            'foot'              => [
                'asciiSymbol' => 'ft',
            ],
            'yard'              => [
                'asciiSymbol' => 'yd',
            ],
            'mile'              => [
                'asciiSymbol' => 'mi',
            ],
            'league'            => [
                'asciiSymbol' => 'le',
            ],
            'fathom'            => [
                'asciiSymbol' => 'ftm',
            ],
            'nautical mile'     => [
                'asciiSymbol' => 'nmi',
            ],
        ];
    }

    /**
     * Conversion factors for length units.
     *
     * @return list<array{string, string, float}>
     * @see https://en.wikipedia.org/wiki/International_yard_and_pound
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            // Metric-US bridge
            ['yd', 'm', 0.9144],
            ['ft', 'm', 0.3048],
            ['in', 'mm', 25.4],
            // US customary
            ['in', 'px', 96],
            ['in', 'p', 72],
            ['in', 'P', 6],
            ['ft', 'in', 12],
            ['yd', 'ft', 3],
            ['mi', 'yd', 1760],
            ['le', 'mi', 3],
            // Astronomical
            ['au', 'm', 149597870700],
            ['ly', 'm', 9460730472580800],
            ['pc', 'au', 648000 / M_PI],
            // Nautical
            ['ftm', 'yd', 2],
            ['nmi', 'm', 1852],
        ];
    }

    // endregion
}
