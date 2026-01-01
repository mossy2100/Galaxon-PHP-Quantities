<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\ConversionData;
use Galaxon\Quantities\Quantity;

class Mass extends Quantity
{
    /**
     * Conversion factors for mass units.
     *
     * @return list<array{string, string, float}>
     * @see https://en.wikipedia.org/wiki/International_yard_and_pound
     */
    public static function getConversions(): array
    {
        return [
            // Metric
            ['t', 'kg', 1000],
            // Metric-US bridge
            ['lb', 'kg', 0.453_592_37],
            // US customary
            ['lb', 'oz', 16],
            ['st', 'lb', 14],
            ['ton', 'lb', 2000],
        ];
    }

    // region Modification methods

    /**
     * Use British (imperial or long) ton instead of US (short) ton.
     *
     * Default:                   1 ton = 2000 lb (short ton)
     * After calling this method: 1 ton = 2240 lb (long ton)
     */
    public static function useBritishUnits(): void
    {
        // Update the conversion from ton to lb.
        ConversionData::addConversion('ton', 'lb', 2240);
    }

    // endregion
}
