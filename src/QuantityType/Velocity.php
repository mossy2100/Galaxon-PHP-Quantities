<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;

class Velocity extends Quantity
{
    /**
     * Unit definitions for velocity.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnitDefinitions(): array
    {
        return [
            // US named unit
            'knot' => [
                'asciiSymbol'   => 'kn',
                'dimension'     => 'T-1L',
                'system'        => 'us_named',
                'expansionUnit' => 'nmi*h-1',
            ],
        ];
    }
}
