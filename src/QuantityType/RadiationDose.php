<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Override;

class RadiationDose extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for absorbed dose and equivalent dose.
     *
     * Gray measures absorbed radiation dose (energy per unit mass).
     * Sievert measures equivalent dose (biological effect of radiation).
     * Both have the same dimension but measure different aspects of radiation.
     *
     * @return array<string, array<string, string|int>>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'gray'    => [
                'asciiSymbol'         => 'Gy',
                'prefixGroup'         => PrefixRegistry::PREFIX_GROUP_METRIC,
                'expansionUnitSymbol' => 'm2*s-2',
            ],
            'sievert' => [
                'asciiSymbol'         => 'Sv',
                'prefixGroup'         => PrefixRegistry::PREFIX_GROUP_METRIC,
                'expansionUnitSymbol' => 'm2*s-2',
            ],
        ];
    }

    // endregion
}
