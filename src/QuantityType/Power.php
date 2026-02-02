<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Helpers\PrefixUtils;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\System;
use Override;

class Power extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for power.
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
            'watt' => [
                'asciiSymbol'         => 'W',
                'prefixGroup'         => PrefixUtils::GROUP_CODE_METRIC,
                'expansionUnitSymbol' => 'kg*m2*s-3',
                'systems'             => [System::SI],
            ],
        ];
    }

    // endregion
}
