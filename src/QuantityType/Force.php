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
     *     systems: list<System>
     * }>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'newton'      => [
                'asciiSymbol' => 'N',
                'prefixGroup' => PrefixRegistry::GROUP_METRIC,
                'systems'     => [System::Si],
            ],
            'pound force' => [
                'asciiSymbol' => 'lbf',
                'systems'     => [System::Imperial, System::UsCustomary],
            ],
        ];
    }

    /**
     * Conversion factors for force units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            // Expansions.
            ['N', 'kg*m*s-2', 1.0],
            // g₀ (standard gravity) = 9.80665 m/s² exactly.
            ['lbf', 'lb*ft/s2', 9.80665 / 0.3048],
        ];
    }

    // endregion
}
