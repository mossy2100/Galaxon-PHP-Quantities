<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;

class Volume extends Quantity
{
    /**
     * Unit definitions for volume.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnitDefinitions(): array
    {
        return [
            // Non-SI metric units
            'litre'       => [
                'asciiSymbol' => 'L',
                'dimension'   => 'L3',
                'system'      => 'metric',
                'prefixGroup' => PrefixRegistry::PREFIX_GROUP_METRIC,
            ],
            // US customary units
            'teaspoon'    => [
                'asciiSymbol' => 'tsp',
                'dimension'   => 'L3',
                'system'      => 'us_customary',
            ],
            'tablespoon'  => [
                'asciiSymbol' => 'tbsp',
                'dimension'   => 'L3',
                'system'      => 'us_customary',
            ],
            'fluid ounce' => [
                'asciiSymbol' => 'floz',
                'dimension'   => 'L3',
                'system'      => 'us_customary',
            ],
            'cup'         => [
                'asciiSymbol' => 'cup',
                'dimension'   => 'L3',
                'system'      => 'us_customary',
            ],
            'pint'        => [
                'asciiSymbol' => 'pint',
                'dimension'   => 'L3',
                'system'      => 'us_customary',
            ],
            'quart'       => [
                'asciiSymbol' => 'qt',
                'dimension'   => 'L3',
                'system'      => 'us_customary',
            ],
            'gallon'      => [
                'asciiSymbol' => 'gal',
                'dimension'   => 'L3',
                'system'      => 'us_customary',
            ],
        ];
    }

    /**
     * Conversion factors for volume units.
     *
     * US customary units are used here, not imperial.
     *
     * @return list<array{string, string, float}>
     */
    public static function getConversions(): array
    {
        return [
            // Metric
            ['m3', 'L', 1000],
            // Metric-US bridge
            ['in3', 'mL', 16.387064],
            // US customary
            ['gal', 'in3', 231],
            ['gal', 'qt', 4],
            ['qt', 'pint', 2],
            ['pint', 'cup', 2],
            ['cup', 'floz', 8],
            ['floz', 'tbsp', 2],
            ['tbsp', 'tsp', 3],
        ];
    }
}
