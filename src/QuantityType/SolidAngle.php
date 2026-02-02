<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\System;
use Override;

class SolidAngle extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for solid angle.
     *
     * @return array<string, array{
     *     asciiSymbol: string,
     *     unicodeSymbol?: string,
     *     prefixGroup?: int,
     *     systems: list<System>,
     *     expansionUnitSymbol?: string,
     *     expansionValue?: float
     * }>
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
