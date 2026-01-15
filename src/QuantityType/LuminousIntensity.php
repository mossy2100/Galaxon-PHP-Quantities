<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\UnitData;

class LuminousIntensity extends Quantity
{
    /**
     * Unit definitions for luminous intensity.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnits(): array
    {
        return [
            // SI base unit
            'candela' => [
                'asciiSymbol' => 'cd',
                'dimension'   => 'J',
                'system'      => 'si_base',
                'prefixGroup' => UnitData::PREFIX_GROUP_METRIC,
            ],
        ];
    }
}
