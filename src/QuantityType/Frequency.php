<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
use Override;

class Frequency extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for frequency.
     *
     * Note: Becquerel has the same dimension (T-1) but measures radioactivity,
     * not frequency. It's included here as they share the same dimension code.
     *
     * @return array<string, array<string, string|int>>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'hertz'     => [
                'asciiSymbol'         => 'Hz',
                'prefixGroup'         => PrefixRegistry::GROUP_CODE_METRIC,
                'expansionUnitSymbol' => 's-1',
                'systems'             => [System::SI],
            ],
            'becquerel' => [
                'asciiSymbol'         => 'Bq',
                'prefixGroup'         => PrefixRegistry::GROUP_CODE_METRIC,
                'expansionUnitSymbol' => 's-1',
                'systems'             => [System::SI],
            ],
        ];
    }

    // endregion
}
