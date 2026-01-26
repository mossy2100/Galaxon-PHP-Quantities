<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
use Override;

class Conductance extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for electrical conductance.
     *
     * @return array<string, array<string, string|int>>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'siemens' => [
                'asciiSymbol'         => 'S',
                'prefixGroup'         => PrefixRegistry::GROUP_CODE_METRIC,
                'expansionUnitSymbol' => 'kg-1*m-2*s3*A2',
                'systems'             => [System::SI],
            ],
        ];
    }

    // endregion
}
