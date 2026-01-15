<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\UnitData;

class Data extends Quantity
{
    /**
     * Unit definitions for data.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnits(): array
    {
        return [
            'bit'  => [
                'asciiSymbol' => 'b',
                'dimension'   => 'D',
                'system'      => 'metric',
                'prefixGroup' => UnitData::PREFIX_GROUP_LARGE,
            ],
            'byte' => [
                'asciiSymbol' => 'B',
                'dimension'   => 'D',
                'system'      => 'metric',
                'prefixGroup' => UnitData::PREFIX_GROUP_LARGE,
            ],
        ];
    }

    /**
     * Conversion factors for data units.
     *
     * @return list<array{string, string, float}>
     */
    public static function getConversions(): array
    {
        return [
            // 1 byte = 8 bits
            ['B', 'b', 8],
        ];
    }
}
