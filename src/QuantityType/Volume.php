<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Override;

class Volume extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for volume.
     *
     * @return array<string, array<string, string|int>>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'litre'       => [
                'asciiSymbol' => 'L',
                'prefixGroup' => PrefixRegistry::PREFIX_GROUP_METRIC,
            ],
            'teaspoon'    => [
                'asciiSymbol' => 'tsp',
            ],
            'tablespoon'  => [
                'asciiSymbol' => 'tbsp',
            ],
            'fluid ounce' => [
                'asciiSymbol' => 'fl oz',
            ],
            'cup'         => [
                'asciiSymbol' => 'cup',
            ],
            'pint'        => [
                'asciiSymbol' => 'pt',
            ],
            'quart'       => [
                'asciiSymbol' => 'qt',
            ],
            'gallon'      => [
                'asciiSymbol' => 'gal',
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
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            // Metric
            ['m3', 'L', 1000],
            // Metric-US bridge
            ['in3', 'mL', 16.387064],
            // US customary
            ['gal', 'in3', 231],
            ['gal', 'qt', 4],
            ['qt', 'pt', 2],
            ['pt', 'cup', 2],
            ['cup', 'fl oz', 8],
            ['fl oz', 'tbsp', 2],
            ['tbsp', 'tsp', 3],
        ];
    }

    // endregion
}
