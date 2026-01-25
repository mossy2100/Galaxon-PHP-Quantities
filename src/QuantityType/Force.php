<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Override;

class Force extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for force.
     *
     * @return array<string, array<string, string|int>>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'newton'      => [
                'asciiSymbol'         => 'N',
                'prefixGroup'         => PrefixRegistry::PREFIX_GROUP_METRIC,
                'expansionUnitSymbol' => 'kg*m*s-2',
            ],
            'pound force' => [
                'asciiSymbol'         => 'lbf',
                'expansionUnitSymbol' => 'lb*ft/s2',
                'expansionValue'      => 9.80665 / 0.3048,
            ],
        ];
    }

    // endregion
}
