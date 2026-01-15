<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;

class Area extends Quantity
{
    /**
     * Unit definitions for area.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnits(): array
    {
        return [
            // Non-SI metric units
            'hectare' => [
                'asciiSymbol' => 'ha',
                'dimension'   => 'L2',
                'system'      => 'metric',
            ],
            // US customary units
            'acre'    => [
                'asciiSymbol' => 'ac',
                'dimension'   => 'L2',
                'system'      => 'us_customary',
            ],
        ];
    }

    /**
     * Conversion factors for area units.
     *
     * @return list<array{string, string, float}>
     */
    public static function getConversions(): array
    {
        return [
            // Metric
            ['ha', 'm2', 10000],
            // Metric-US bridge
            ['ac', 'm2', 4046.8564224],
            // US customary
            ['mi2', 'ac', 640],
            ['ac', 'yd2', 4840],
        ];
    }
}
