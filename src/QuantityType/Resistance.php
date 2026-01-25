<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Override;

class Resistance extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for electrical resistance.
     *
     * @return array<string, array<string, string|int>>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'ohm' => [
                'asciiSymbol'         => 'ohm',
                'unicodeSymbol'       => 'Ω',
                'prefixGroup'         => PrefixRegistry::PREFIX_GROUP_METRIC,
                'expansionUnitSymbol' => 'kg*m2*s-3*A-2',
            ],
        ];
    }

    // endregion
}
