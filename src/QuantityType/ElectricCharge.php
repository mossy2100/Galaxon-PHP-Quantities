<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Override;

class ElectricCharge extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for electric charge.
     *
     * @return array<string, array<string, string|int>>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'coulomb' => [
                'asciiSymbol'         => 'C',
                'prefixGroup'         => PrefixRegistry::PREFIX_GROUP_METRIC,
                'expansionUnitSymbol' => 's*A',
            ],
        ];
    }

    // endregion
}
