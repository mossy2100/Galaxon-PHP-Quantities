<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
use Override;

/**
 * Represents electrical resistance quantities.
 */
class Resistance extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for electrical resistance.
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
            'ohm' => [
                'asciiSymbol'   => 'ohm',
                'unicodeSymbol' => 'Ω',
                'prefixGroup'   => PrefixRegistry::GROUP_METRIC,
                'systems'       => [System::Si],
            ],
        ];
    }

    /**
     * Conversion factors for electrical resistance units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            // Expansion.
            ['ohm', 'kg*m2*s-3*A-2', 1.0],
        ];
    }

    // endregion
}
