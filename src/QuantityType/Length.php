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
    // region Static properties

    /**
     * Default part unit symbols for output methods.
     *
     * @var list<string>
     */
    protected static array $defaultPartUnitSymbols = ['mi', 'yd', 'ft', 'in'];

    /**
     * Default part unit symbols for input methods.
     *
     * @var string
     */
    protected static string $defaultResultUnitSymbol = 'ft';

    // endregion

    // region Overridden methods

    /**
     * Unit definitions for length.
     *
     * @return array<string, array{
     *     asciiSymbol: string,
     *     unicodeSymbol?: string,
     *     prefixGroup?: int,
     *     alternateSymbol?: string,
     *     systems: list<System>
     * }>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            // SI
            'meter'             => [
                'asciiSymbol' => 'm',
                'prefixGroup' => PrefixRegistry::GROUP_METRIC,
                'systems'     => [System::Si],
            ],
            // Scientific
            'astronomical unit' => [
                'asciiSymbol' => 'au',
                'systems'     => [System::SiAccepted, System::Scientific],
            ],
            'light year'        => [
                'asciiSymbol' => 'ly',
                'systems'     => [System::Scientific],
            ],
            'parsec'            => [
                'asciiSymbol' => 'pc',
                'prefixGroup' => PrefixRegistry::GROUP_LARGE_METRIC,
                'systems'     => [System::Scientific],
            ],
            // CSS
            'pixel'             => [
                'asciiSymbol' => 'px',
                'systems'     => [System::Css],
            ],
            'point'             => [
                'asciiSymbol' => 'p',
                'systems'     => [System::Css],
            ],
            'pica'              => [
                'asciiSymbol' => 'P',
                'systems'     => [System::Css],
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

    // endregion
}
