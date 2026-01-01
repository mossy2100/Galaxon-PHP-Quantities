<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;

class Energy extends Quantity
{
    /**
     * Conversion factors for energy units.
     *
     * @return list<array{string, string, float}>
     */
    public static function getConversions(): array
    {
        return [
            ['eV', 'J', 1.602_176_634e-19],
            ['cal', 'J', 4.184],
        ];
    }
}
