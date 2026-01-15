<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\UnitData;

class CatalyticActivity extends Quantity
{
    /**
     * Unit definitions for catalytic activity.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnits(): array
    {
        return [
            // SI named unit
            'katal' => [
                'asciiSymbol'   => 'kat',
                'dimension'     => 'T-1N',
                'system'        => 'si_named',
                'prefixGroup'   => UnitData::PREFIX_GROUP_METRIC,
                'expansionUnit' => 'mol*s-1',
            ],
        ];
    }
}
