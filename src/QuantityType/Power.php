<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
use Override;

class Power extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for power.
     *
     * @return array<string, array<string, string|int>>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'watt' => [
                'asciiSymbol'         => 'W',
                'prefixGroup'         => PrefixRegistry::GROUP_CODE_METRIC,
                'expansionUnitSymbol' => 'kg*m2*s-3',
                'systems'             => [System::SI],
            ],
        ];
    }

    // endregion
}
