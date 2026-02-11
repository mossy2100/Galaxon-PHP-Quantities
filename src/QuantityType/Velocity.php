<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\System;
use Override;

/**
 * Represents velocity quantities.
 */
class Velocity extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for velocity.
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
            'knot' => [
                'asciiSymbol' => 'kn',
                'systems'     => [System::Nautical],
            ],
        ];
    }

    /**
     * Conversion factors for velocity units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            // Expansion.
            ['kn', 'nmi*h-1', 1.0],
        ];
    }

    // endregion
}
