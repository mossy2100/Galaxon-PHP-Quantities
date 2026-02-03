<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\System;
use Galaxon\Quantities\Utility\PrefixUtility;
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
            'hertz'     => [
                'asciiSymbol'         => 'Hz',
                'prefixGroup'         => PrefixUtility::GROUP_CODE_METRIC,
                'expansionUnitSymbol' => 's-1',
                'systems'             => [System::SI],
            ],
            'becquerel' => [
                'asciiSymbol'         => 'Bq',
                'prefixGroup'         => PrefixUtility::GROUP_CODE_METRIC,
                'expansionUnitSymbol' => 's-1',
                'systems'             => [System::SI],
            ],
        ];
    }

    // endregion
}
