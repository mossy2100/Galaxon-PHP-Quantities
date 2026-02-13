<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
use Override;

/**
 * Represents length quantities.
 */
class Length extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for length.
     *
     * @return array<string, array{
     *     asciiSymbol: string,
     *     unicodeSymbol?: string,
     *     prefixGroup?: int,
     *     alternateSymbol?: string,
     *     systems: list<System>,
     *     expansionUnitSymbol?: string,
     *     expansionValue?: float
     * }>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            // SI
            'metre'             => [
                'asciiSymbol' => 'm',
                'prefixGroup' => PrefixRegistry::GROUP_METRIC,
                'systems'     => [System::Si],
            ],
            // Astronomical
            'astronomical unit' => [
                'asciiSymbol' => 'au',
                'systems'     => [System::SiAccepted, System::Astronomical],
            ],
            'light year'        => [
                'asciiSymbol' => 'ly',
                'systems'     => [System::Astronomical],
            ],
            'parsec'            => [
                'asciiSymbol' => 'pc',
                'prefixGroup' => PrefixRegistry::GROUP_LARGE_METRIC,
                'systems'     => [System::Astronomical],
            ],
            // Typography/CSS
            'pixel'             => [
                'asciiSymbol' => 'px',
                'systems'     => [System::Typographical],
            ],
            'point'             => [
                'asciiSymbol' => 'p',
                'systems'     => [System::Typographical],
            ],
            'pica'              => [
                'asciiSymbol' => 'P',
                'systems'     => [System::Typographical],
            ],
            // Imperial/US
            'inch'              => [
                'asciiSymbol' => 'in',
                'systems'     => [System::Imperial, System::UsCustomary],
            ],
            'foot'              => [
                'asciiSymbol' => 'ft',
                'systems'     => [System::Imperial, System::UsCustomary],
            ],
            'yard'              => [
                'asciiSymbol' => 'yd',
                'systems'     => [System::Imperial, System::UsCustomary],
            ],
            'mile'              => [
                'asciiSymbol' => 'mi',
                'systems'     => [System::Imperial, System::UsCustomary],
            ],
            // Nautical
            'fathom'            => [
                'asciiSymbol' => 'ftm',
                'systems'     => [System::Nautical],
            ],
            'nautical mile'     => [
                'asciiSymbol' => 'nmi',
                'systems'     => [System::Nautical],
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
            // Astronomical
            ['au', 'm', 149597870700],
            ['ly', 'm', 9460730472580800],
            ['pc', 'au', 648000 / M_PI],
            // Nautical
            ['ftm', 'yd', 2],
            ['nmi', 'm', 1852],
        ];
    }

    /**
     * Configuration for parts-related methods.
     *
     * @return array{from: ?string, to: list<string>}
     */
    #[Override]
    public static function getPartsConfig(): array
    {
        return [
            'from' => 'ft',
            'to'   => ['mi', 'yd', 'ft', 'in'],
        ];
    }

    // endregion
}
