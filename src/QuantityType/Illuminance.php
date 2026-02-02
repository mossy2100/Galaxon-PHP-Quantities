<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Helpers\PrefixUtils;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\System;
use Override;

class Illuminance extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for illuminance.
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
            'lux' => [
                'asciiSymbol'         => 'lx',
                'prefixGroup'         => PrefixUtils::GROUP_CODE_METRIC,
                'expansionUnitSymbol' => 'cd*rad2*m-2',
                'systems'             => [System::SI],
            ],
        ];
    }

    // endregion
}
