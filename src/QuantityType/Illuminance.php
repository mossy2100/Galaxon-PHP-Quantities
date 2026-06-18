<?php

declare(strict_types=1);

namespace OceanMoon\Quantities\QuantityType;

use OceanMoon\Quantities\Internal\UnitSystem;
use OceanMoon\Quantities\Quantity;
use OceanMoon\Quantities\Services\PrefixService;
use Override;

/**
 * Represents illuminance quantities.
 */
class Illuminance extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for illuminance.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'lux' => [
                'asciiSymbol' => 'lx',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
        ];
    }

    /**
     * Conversion factors for illuminance units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['lx', 'cd*rad2*m-2', 1],
        ];
    }

    // endregion
}
