<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Internal\UnitSystem;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\PrefixService;
use Override;

/**
 * Represents magnetic flux density quantities.
 */
class MagneticFluxDensity extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for magnetic flux density.
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
            'tesla' => [
                'asciiSymbol' => 'T',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
        ];
    }

    /**
     * Conversion factors for magnetic flux density units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['T', 'kg*s-2*A-1', 1],
        ];
    }

    // endregion
}
