<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
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
     *     systems: list<System>
     * }>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'gray'    => [
                'asciiSymbol' => 'Gy',
                'prefixGroup' => PrefixRegistry::GROUP_METRIC,
                'systems'     => [System::Si],
            ],
            'sievert' => [
                'asciiSymbol' => 'Sv',
                'prefixGroup' => PrefixRegistry::GROUP_METRIC,
                'systems'     => [System::Si],
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
            // Expansions.
            ['Gy', 'm2*s-2', 1.0],
            ['Sv', 'm2*s-2', 1.0],
        ];
    }

    // endregion
}
