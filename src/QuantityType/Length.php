<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\PrefixService;
use Galaxon\Quantities\UnitSystem;
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
     *     systems: list<UnitSystem>
     * }>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            // SI
            'meter'             => [
                'asciiSymbol' => 'm',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
            // Scientific
            'astronomical unit' => [
                'asciiSymbol' => 'au',
                'systems'     => [UnitSystem::SiAccepted, UnitSystem::Scientific],
            ],
            'light year'        => [
                'asciiSymbol' => 'ly',
                'systems'     => [UnitSystem::Scientific],
            ],
            'parsec'            => [
                'asciiSymbol' => 'pc',
                'prefixGroup' => PrefixService::GROUP_LARGE_METRIC,
                'systems'     => [UnitSystem::Scientific],
            ],
            // CSS
            'pixel'             => [
                'asciiSymbol' => 'px',
                'systems'     => [UnitSystem::Css],
            ],
            'point'             => [
                'asciiSymbol' => 'p',
                'systems'     => [UnitSystem::Css],
            ],
            'pica'              => [
                'asciiSymbol' => 'P',
                'systems'     => [UnitSystem::Css],
            ],
            // Imperial/US
            'inch'              => [
                'asciiSymbol' => 'in',
                'systems'     => [UnitSystem::Imperial, UnitSystem::UsCustomary],
            ],
            'foot'              => [
                'asciiSymbol' => 'ft',
                'systems'     => [UnitSystem::Imperial, UnitSystem::UsCustomary],
            ],
            'yard'              => [
                'asciiSymbol' => 'yd',
                'systems'     => [UnitSystem::Imperial, UnitSystem::UsCustomary],
            ],
            'mile'              => [
                'asciiSymbol' => 'mi',
                'systems'     => [UnitSystem::Imperial, UnitSystem::UsCustomary],
            ],
            // Nautical
            'fathom'            => [
                'asciiSymbol' => 'ftm',
                'systems'     => [UnitSystem::Nautical],
            ],
            'nautical mile'     => [
                'asciiSymbol' => 'nmi',
                'systems'     => [UnitSystem::Nautical],
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
