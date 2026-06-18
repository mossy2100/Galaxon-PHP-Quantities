<?php

declare(strict_types=1);

namespace OceanMoon\Quantities\QuantityType;

use OceanMoon\Quantities\Internal\UnitSystem;
use OceanMoon\Quantities\Quantity;
use OceanMoon\Quantities\Services\PrefixService;
use Override;

/**
 * Represents electric current quantities.
 */
class ElectricCurrent extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for electric current.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'ampere' => [
                'asciiSymbol' => 'A',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
        ];
    }

    // endregion
}
