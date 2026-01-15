<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\UnitData;

class Capacitance extends Quantity
{
    /**
     * Unit definitions for capacitance.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnits(): array
    {
        return [
            // SI named unit
            'farad' => [
                'asciiSymbol'   => 'F',
                'dimension'     => 'T4L-2M-1I2',
                'system'        => 'si_named',
                'prefixGroup'   => UnitData::PREFIX_GROUP_METRIC,
                'expansionUnit' => 'kg-1*m-2*s4*A2',
            ],
        ];
    }
}
