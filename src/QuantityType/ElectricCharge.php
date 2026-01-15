<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\UnitData;

class ElectricCharge extends Quantity
{
    /**
     * Unit definitions for electric charge.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnits(): array
    {
        return [
            // SI named unit
            'coulomb' => [
                'asciiSymbol'   => 'C',
                'dimension'     => 'TI',
                'system'        => 'si_named',
                'prefixGroup'   => UnitData::PREFIX_GROUP_METRIC,
                'expansionUnit' => 's*A',
            ],
        ];
    }
}
