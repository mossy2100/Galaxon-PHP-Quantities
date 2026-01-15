<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\UnitData;

class Inductance extends Quantity
{
    /**
     * Unit definitions for inductance.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnits(): array
    {
        return [
            // SI named unit
            'henry' => [
                'asciiSymbol'   => 'H',
                'dimension'     => 'T-2L2MI-2',
                'system'        => 'si_named',
                'prefixGroup'   => UnitData::PREFIX_GROUP_METRIC,
                'expansionUnit' => 'kg*m2*s-2*A-2',
            ],
        ];
    }
}
