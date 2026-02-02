<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Helpers\PrefixUtils;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\System;
use Override;

class ElectricCurrent extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for electric current.
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
            'ampere' => [
                'asciiSymbol' => 'A',
                'prefixGroup' => PrefixUtils::GROUP_CODE_METRIC,
                'systems'     => [System::SI],
            ],
        ];
    }

    // endregion
}
