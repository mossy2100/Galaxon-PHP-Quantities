<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\System;
use Override;

class Voltage extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for voltage (electric potential difference).
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
            'volt' => [
                'asciiSymbol'         => 'V',
                'prefixGroup'         => Prefixes::GROUP_CODE_METRIC,
                'expansionUnitSymbol' => 'kg*m2*s-3*A-1',
                'systems'             => [System::SI],
            ],
        ];
    }

    // endregion
}
