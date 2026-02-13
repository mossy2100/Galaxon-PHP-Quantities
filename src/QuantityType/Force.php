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
     *     alternateSymbol?: string,
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
                'systems'             => [System::Si],
                'expansionUnitSymbol' => 'kg*m/s2',
            ],
            'pound force' => [
                'asciiSymbol'         => 'lbf',
                'systems'             => [System::Imperial, System::UsCustomary],
                'expansionUnitSymbol' => 'lb*ft/s2',
                // g₀ (standard gravity) = 9.80665 m/s² exactly. Convert to ft/s².
                'expansionValue'      => 9.80665 / 0.3048,
            ],
        ];
    }

    // endregion
}
