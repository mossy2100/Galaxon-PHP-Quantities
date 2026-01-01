<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;

class Pressure extends Quantity
{
    /**
     * Conversion factors for pressure units.
     *
     * @return list<array{string, string, float}>
     */
    public static function getConversions(): array
    {
        return [
            ['bar', 'Pa', 100000],
            ['mmHg', 'Pa', 133.322387415],
            ['atm', 'Pa', 101325],
        ];
    }
}
