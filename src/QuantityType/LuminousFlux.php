<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
use Override;

class LuminousFlux extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for luminous flux.
     *
     * @return array<string, array<string, string|int>>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'lumen' => [
                'asciiSymbol'         => 'lm',
                'prefixGroup'         => PrefixRegistry::GROUP_CODE_METRIC,
                'expansionUnitSymbol' => 'cd*rad2',
                'systems'             => [System::SI],
            ],
        ];
    }

    // endregion
}
