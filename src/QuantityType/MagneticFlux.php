<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
use Override;

/**
 * Represents magnetic flux quantities.
 */
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
     *     systems: list<System>
     * }>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'weber' => [
                'asciiSymbol' => 'Wb',
                'prefixGroup' => PrefixRegistry::GROUP_METRIC,
                'systems'     => [System::Si],
            ],
        ];
    }

    /**
     * Conversion factors for magnetic flux units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            // Expansion.
            ['Wb', 'kg*m2*s-2*A-1', 1.0],
        ];
    }

    // endregion
}
