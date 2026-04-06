<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Internal\UnitSystem;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\PrefixService;
use Override;

/**
 * Represents radiation dose quantities.
 */
class RadiationDose extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for absorbed dose and equivalent dose.
     *
     * Gray measures absorbed radiation dose (energy per unit mass).
     * Sievert measures equivalent dose (biological effect of radiation).
     * Both have the same dimension but measure different aspects of radiation.
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
            'gray'    => [
                'asciiSymbol' => 'Gy',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
            'sievert' => [
                'asciiSymbol' => 'Sv',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
        ];
    }

    /**
     * Conversion factors for radiation dose units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['Gy', 'm2*s-2', 1],
            ['Sv', 'm2*s-2', 1],
        ];
    }

    // endregion
}
