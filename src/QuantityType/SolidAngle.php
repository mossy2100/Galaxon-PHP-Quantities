<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
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
                'prefixGroup'         => PrefixRegistry::GROUP_CODE_SMALL_ENGINEERING_METRIC,
                'expansionUnitSymbol' => 'rad2',
                'systems'             => [System::SI],
            ],
        ];
    }

    // endregion
}
