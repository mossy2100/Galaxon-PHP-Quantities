<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Override;

class CatalyticActivity extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for catalytic activity.
     *
     * @return array<string, array<string, string|int>>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'katal' => [
                'asciiSymbol'         => 'kat',
                'prefixGroup'         => PrefixRegistry::PREFIX_GROUP_METRIC,
                'expansionUnitSymbol' => 'mol*s-1',
            ],
        ];
    }

    // endregion
}
