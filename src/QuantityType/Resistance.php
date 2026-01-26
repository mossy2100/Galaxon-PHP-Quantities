<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
use Override;

class Resistance extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for electrical resistance.
     *
     * @return array<string, array<string, string|int>>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'ohm' => [
                'asciiSymbol'         => 'ohm',
                'unicodeSymbol'       => 'Ω',
                'prefixGroup'         => PrefixRegistry::GROUP_CODE_METRIC,
                'expansionUnitSymbol' => 'kg*m2*s-3*A-2',
                'systems'             => [System::SI],
            ],
        ];
    }

    // endregion
}
