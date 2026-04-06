<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Internal\UnitSystem;
use Galaxon\Quantities\Quantity;
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
     *     alternateSymbol?: string,
     *     systems: list<UnitSystem>
     * }>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'knot' => [
                'asciiSymbol' => 'kn',
                'systems'     => [UnitSystem::Nautical],
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
            ['kn', 'nmi*h-1', 1],
        ];
    }

    // endregion
}
