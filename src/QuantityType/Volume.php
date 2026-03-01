<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\PrefixService;
use Galaxon\Quantities\UnitSystem;
use Override;

/**
 * Represents volume quantities.
 */
class Volume extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for volume.
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
            // Metric volume units
            'liter'                => [
                'asciiSymbol' => 'L',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [
                    UnitSystem::SiAccepted,
                ],
            ],
            // US customary volume units
            'US fluid ounce'       => [
                'asciiSymbol' => 'US fl oz',
                'systems'     => [
                    UnitSystem::UsCustomary,
                ],
            ],
            'US pint'              => [
                'asciiSymbol' => 'US pt',
                'systems'     => [
                    UnitSystem::UsCustomary,
                ],
            ],
            'US quart'             => [
                'asciiSymbol' => 'US qt',
                'systems'     => [
                    UnitSystem::UsCustomary,
                ],
            ],
            'US gallon'            => [
                'asciiSymbol' => 'US gal',
                'systems'     => [
                    UnitSystem::UsCustomary,
                ],
            ],
            // Imperial volume units
            'imperial fluid ounce' => [
                'asciiSymbol' => 'imp fl oz',
                'systems'     => [
                    UnitSystem::Imperial,
                ],
            ],
            'imperial pint'        => [
                'asciiSymbol' => 'imp pt',
                'systems'     => [
                    UnitSystem::Imperial,
                ],
            ],
            'imperial quart'       => [
                'asciiSymbol' => 'imp qt',
                'systems'     => [
                    UnitSystem::Imperial,
                ],
            ],
            'imperial gallon'      => [
                'asciiSymbol' => 'imp gal',
                'systems'     => [
                    UnitSystem::Imperial,
                ],
            ],
        ];
    }

    /**
     * Conversion factors for volume units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            // Metric
            ['m3', 'L', 1000],
            // US customary
            ['US gal', 'in3', 231],
            ['US gal', 'US qt', 4],
            ['US qt', 'US pt', 2],
            ['US pt', 'US fl oz', 16],
            // Imperial
            ['imp gal', 'L', 4.54609],
            ['imp gal', 'imp qt', 4],
            ['imp qt', 'imp pt', 2],
            ['imp pt', 'imp fl oz', 20],
        ];
    }

    // endregion
}
