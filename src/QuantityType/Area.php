<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Override;

class Area extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for area.
     *
     * @return array<string, array<string, string|int>>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'hectare' => [
                'asciiSymbol' => 'ha',
            ],
            'acre'    => [
                'asciiSymbol' => 'ac',
            ],
        ];
    }

    /**
     * Conversion factors for area units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            // Metric
            ['ha', 'm2', 10000],
            // Metric-US bridge
            ['ac', 'm2', 4046.8564224],
            // US customary
            ['ac', 'yd2', 4840],
        ];
    }

    // endregion
}
