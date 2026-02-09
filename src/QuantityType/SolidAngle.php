<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\System;
use Galaxon\Quantities\Utility\PrefixUtility;
use Override;

/**
 * Represents solid angle quantities.
 */
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
                'prefixGroup'         => PrefixUtility::GROUP_CODE_SMALL_ENG_METRIC,
                'expansionUnitSymbol' => 'rad2',
                'systems'             => [System::Si],
            ],
        ];
    }

    // endregion
}
