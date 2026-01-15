<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\UnitData;

class Resistance extends Quantity
{
    /**
     * Unit definitions for electrical resistance.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnits(): array
    {
        return [
            // SI named unit
            'ohm' => [
                'asciiSymbol'   => 'ohm',
                'unicodeSymbol' => 'Ω',
                'dimension'     => 'T-3L2MI-2',
                'system'        => 'si_named',
                'prefixGroup'   => UnitData::PREFIX_GROUP_METRIC,
                'expansionUnit' => 'kg*m2*s-3*A-2',
            ],
        ];
    }
}
