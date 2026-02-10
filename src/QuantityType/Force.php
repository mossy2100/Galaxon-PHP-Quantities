<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
use Override;

/**
 * Represents force quantities.
 */
class Force extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for force.
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
            'newton'      => [
                'asciiSymbol'         => 'N',
                'prefixGroup'         => PrefixRegistry::GROUP_METRIC,
                'expansionUnitSymbol' => 'kg*m*s-2',
                'systems'             => [System::Si],
            ],
            'pound force' => [
                'asciiSymbol'         => 'lbf',
                'expansionUnitSymbol' => 'lb*ft/s2',
                // g₀ (standard gravity) = 9.80665 m/s² exactly.
                'expansionValue'      => 9.80665 / 0.3048,
                'systems'             => [System::Imperial, System::UsCustomary],
            ],
        ];
    }

    // endregion
}
