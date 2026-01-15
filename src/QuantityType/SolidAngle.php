<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\UnitData;

class SolidAngle extends Quantity
{
    /**
     * Unit definitions for solid angle.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnits(): array
    {
        return [
            // SI derived unit
            'steradian' => [
                'asciiSymbol'   => 'sr',
                'dimension'     => 'A2',
                'system'        => 'si_derived',
                'prefixGroup'   => UnitData::PREFIX_GROUP_SMALL_METRIC,
                'expansionUnit' => 'rad2',
            ],
        ];
    }
}
