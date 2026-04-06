<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Internal\UnitSystem;
use Galaxon\Quantities\Quantity;
use Override;

/**
 * Represents area quantities.
 */
class Area extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for area.
     *
     * @return array<string, array{
     *      asciiSymbol: string,
     *      unicodeSymbol?: string,
     *      prefixGroup?: int,
     *      alternateSymbol?: string,
     *      systems: list<UnitSystem>
     *  }>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'hectare' => [
                'asciiSymbol' => 'ha',
                'systems'     => [UnitSystem::SiAccepted, UnitSystem::Metric],
            ],
            'acre'    => [
                'asciiSymbol' => 'ac',
                'systems'     => [UnitSystem::Imperial, UnitSystem::UsCustomary],
            ],
        ];
    }

    /**
     * Conversion factors for area units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            // Metric
            ['ha', 'm2', 10000],
            // US customary
            ['ac', 'yd2', 4840],
        ];
    }

    // endregion
}
