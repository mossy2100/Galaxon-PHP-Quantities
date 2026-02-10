<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
use Override;

/**
 * Represents capacitance quantities.
 */
class Capacitance extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for capacitance.
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
            'farad' => [
                'asciiSymbol'         => 'F',
                'prefixGroup'         => PrefixRegistry::GROUP_METRIC,
                'expansionUnitSymbol' => 'kg-1*m-2*s4*A2',
                'systems'             => [System::Si],
            ],
        ];
    }

    // endregion
}
