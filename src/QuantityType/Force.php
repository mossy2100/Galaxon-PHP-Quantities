<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
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
                'prefixGroup'         => PrefixRegistry::GROUP_CODE_METRIC,
                'expansionUnitSymbol' => 'kg*m*s-2',
                'systems'             => [System::SI],
            ],
            'pound force' => [
                'asciiSymbol'         => 'lbf',
                'expansionUnitSymbol' => 'lb*ft/s2',
                'expansionValue'      => 9.80665 / 0.3048,
                'systems'             => [System::Imperial, System::US],
            ],
        ];
    }

    // endregion
}
