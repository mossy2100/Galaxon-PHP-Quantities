<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;

class Force extends Quantity
{
    /**
     * Conversion factors for force units.
     *
     * @return list<array{string, string, float}>
     */
    public static function getConversions(): array
    {
        return [
            ['lbf', 'N', 4.4482216152605],
            ['lbf', 'ft*lb/s2', 9.80665 / 0.3048],
        ];
    }
}
