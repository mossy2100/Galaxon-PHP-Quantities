<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\UnitData;

class RadiationDose extends Quantity
{
    /**
     * Unit definitions for absorbed dose and equivalent dose.
     *
     * Gray measures absorbed radiation dose (energy per unit mass).
     * Sievert measures equivalent dose (biological effect of radiation).
     * Both have the same dimension but measure different aspects of radiation.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnits(): array
    {
        return [
            // SI named units
            'gray'    => [
                'asciiSymbol'   => 'Gy',
                'dimension'     => 'T-2L2',
                'system'        => 'si_named',
                'prefixGroup'   => UnitData::PREFIX_GROUP_METRIC,
                'expansionUnit' => 'm2*s-2',
            ],
            'sievert' => [
                'asciiSymbol'   => 'Sv',
                'dimension'     => 'T-2L2',
                'system'        => 'si_named',
                'prefixGroup'   => UnitData::PREFIX_GROUP_METRIC,
                'expansionUnit' => 'm2*s-2',
            ],
        ];
    }
}
