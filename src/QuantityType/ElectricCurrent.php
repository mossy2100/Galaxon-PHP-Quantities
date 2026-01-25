<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Override;

class ElectricCurrent extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for electric current.
     *
     * @return array<string, array<string, string|int>>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'ampere' => [
                'asciiSymbol' => 'A',
                'prefixGroup' => PrefixRegistry::PREFIX_GROUP_METRIC,
            ],
        ];
    }

    // endregion
}
