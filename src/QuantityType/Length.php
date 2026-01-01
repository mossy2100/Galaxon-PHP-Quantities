<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;

class Length extends Quantity
{
    /**
     * Conversion factors for length units.
     *
     * @return list<array{string, string, float}>
     * @see https://en.wikipedia.org/wiki/International_yard_and_pound
     */
    public static function getConversions(): array
    {
        return [
            // Metric-US bridge
            ['yd', 'm', 0.9144],
            // US customary
            ['in', 'px', 96],
            ['in', 'pt', 72],
            ['ft', 'in', 12],
            ['yd', 'ft', 3],
            ['mi', 'yd', 1760],
            // Astronomical
            ['au', 'm', 149597870700],
            ['ly', 'm', 9460730472580800],
            ['pc', 'au', 648000 / M_PI],
            // Nautical
            ['nmi', 'm', 1852],
        ];
    }
}
