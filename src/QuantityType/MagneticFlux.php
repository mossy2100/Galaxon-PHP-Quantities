<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\System;
use Galaxon\Quantities\Utility\PrefixUtility;
use Override;

class MagneticFlux extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for magnetic flux.
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
            'weber' => [
                'asciiSymbol'         => 'Wb',
                'prefixGroup'         => PrefixUtility::GROUP_CODE_METRIC,
                'expansionUnitSymbol' => 'kg*m2*s-2*A-1',
                'systems'             => [System::SI],
            ],
        ];
    }

    // endregion
}
