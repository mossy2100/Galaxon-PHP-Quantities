<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Internal\UnitSystem;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\PrefixService;
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
                'systems'     => [UnitSystem::SiAccepted, UnitSystem::Metric],
            ],
            'metric cup'           => [
                'asciiSymbol' => 'cup',
                'systems'     => [UnitSystem::Metric],
            ],
            'metric tablespoon'    => [
                'asciiSymbol' => 'tbsp',
                'systems'     => [UnitSystem::Metric],
            ],
            'metric teaspoon'      => [
                'asciiSymbol' => 'tsp',
                'systems'     => [UnitSystem::Metric],
            ],
            // US customary volume units
            'US gallon'            => [
                'asciiSymbol' => 'US gal',
                'systems'     => [UnitSystem::UsCustomary],
            ],
            'US quart'             => [
                'asciiSymbol' => 'US qt',
                'systems'     => [UnitSystem::UsCustomary],
            ],
            'US pint'              => [
                'asciiSymbol' => 'US pt',
                'systems'     => [UnitSystem::UsCustomary],
            ],
            'US fluid ounce'       => [
                'asciiSymbol' => 'US fl oz',
                'systems'     => [UnitSystem::UsCustomary],
            ],
            'US cup'               => [
                'asciiSymbol' => 'US cup',
                'systems'     => [UnitSystem::UsCustomary],
            ],
            'US tablespoon'        => [
                'asciiSymbol' => 'US tbsp',
                'systems'     => [UnitSystem::UsCustomary],
            ],
            'US teaspoon'          => [
                'asciiSymbol' => 'US tsp',
                'systems'     => [UnitSystem::UsCustomary],
            ],
            // Imperial volume units
            'imperial gallon'      => [
                'asciiSymbol' => 'imp gal',
                'systems'     => [UnitSystem::Imperial],
            ],
            'imperial quart'       => [
                'asciiSymbol' => 'imp qt',
                'systems'     => [UnitSystem::Imperial],
            ],
            'imperial pint'        => [
                'asciiSymbol' => 'imp pt',
                'systems'     => [UnitSystem::Imperial],
            ],
            'imperial fluid ounce' => [
                'asciiSymbol' => 'imp fl oz',
                'systems'     => [UnitSystem::Imperial],
            ],
            'imperial tablespoon'  => [
                'asciiSymbol' => 'imp tbsp',
                'systems'     => [UnitSystem::Imperial],
            ],
            'imperial teaspoon'    => [
                'asciiSymbol' => 'imp tsp',
                'systems'     => [UnitSystem::Imperial],
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
            ['cup', 'mL', 250],
            ['tbsp', 'mL', 15],
            ['tsp', 'mL', 5],
            // US customary
            ['US gal', 'in3', 231],
            ['US gal', 'US qt', 4],
            ['US qt', 'US pt', 2],
            ['US pt', 'US cup', 2],
            ['US cup', 'US fl oz', 8],
            ['US fl oz', 'US tbsp', 2],
            ['US tbsp', 'US tsp', 3],
            // Imperial
            ['imp gal', 'L', 4.54609],
            ['imp gal', 'imp qt', 4],
            ['imp qt', 'imp pt', 2],
            ['imp pt', 'imp fl oz', 20],
            ['imp fl oz', 'imp tbsp', 2],
            ['imp tbsp', 'imp tsp', 4],
        ];
    }

    // endregion
}
