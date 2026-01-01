<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;

class Volume extends Quantity
{
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
