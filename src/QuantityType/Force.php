<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;

class Force extends Quantity
{
    /**
     * Unit definitions for force.
     *
     * Note: Newton (N) is an SI named unit and will be migrated separately.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnits(): array
    {
        return [
            // US customary units
            'pound force' => [
                'asciiSymbol'    => 'lbf',
                'dimension'      => 'T-2LM',
                'system'         => 'us_customary',
                'expansionValue' => 9.80665 / 0.3048,
                'expansionUnit'  => 'ft*lb/s2',
            ],
        ];
    }

    /**
     * Conversion factors for force units.
     *
     * @return list<array{string, string, float}>
     */
    public static function getConversions(): array
    {
        return [
            ['lbf', 'N', 4.4482216152605],
        ];
    }
}
