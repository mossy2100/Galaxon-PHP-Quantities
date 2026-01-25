<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Override;

class Capacitance extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for capacitance.
     *
     * @return array<string, array<string, string|int>>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'farad' => [
                'asciiSymbol'         => 'F',
                'prefixGroup'         => PrefixRegistry::PREFIX_GROUP_METRIC,
                'expansionUnitSymbol' => 'kg-1*m-2*s4*A2',
            ],
        ];
    }

    // endregion
}
