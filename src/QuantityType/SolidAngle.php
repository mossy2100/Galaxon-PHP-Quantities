<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Override;

class SolidAngle extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for solid angle.
     *
     * @return array<string, array<string, string|int>>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'steradian' => [
                'asciiSymbol'         => 'sr',
                'prefixGroup'         => PrefixRegistry::PREFIX_GROUP_SMALL_METRIC,
                'expansionUnitSymbol' => 'rad2',
            ],
        ];
    }

    // endregion
}
