<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
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
     *     systems: list<System>,
     *     expansionUnitSymbol?: string,
     *     expansionValue?: float
     * }>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            // Metric volume units
            'liter'                => [
                'asciiSymbol' => 'L',
                'prefixGroup' => PrefixRegistry::GROUP_METRIC,
                'systems'     => [System::SiAccepted],
            ],
            // US customary volume units
            'US fluid ounce'       => [
                'asciiSymbol' => 'US fl oz',
                'systems'     => [System::UsCustomary],
            ],
            'US pint'              => [
                'asciiSymbol' => 'US pt',
                'systems'     => [System::UsCustomary],
            ],
            'US quart'             => [
                'asciiSymbol' => 'US qt',
                'systems'     => [System::UsCustomary],
            ],
            'US gallon'            => [
                'asciiSymbol' => 'US gal',
                'systems'     => [System::UsCustomary],
            ],
            // Imperial volume units
            'imperial fluid ounce' => [
                'asciiSymbol' => 'imp fl oz',
                'systems'     => [System::Imperial],
            ],
            'imperial pint'        => [
                'asciiSymbol' => 'imp pt',
                'systems'     => [System::Imperial],
            ],
            'imperial quart'       => [
                'asciiSymbol' => 'imp qt',
                'systems'     => [System::Imperial],
            ],
            'imperial gallon'      => [
                'asciiSymbol' => 'imp gal',
                'systems'     => [System::Imperial],
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
