<?php

declare(strict_types=1);

namespace OceanMoon\Quantities\QuantityType;

use OceanMoon\Quantities\Internal\UnitSystem;
use OceanMoon\Quantities\Quantity;
use OceanMoon\Quantities\Services\PrefixService;
use Override;

/**
 * Represents amount of substance quantities.
 */
class AmountOfSubstance extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for amount of substance.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'mole' => [
                'asciiSymbol' => 'mol',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
        ];
    }

    // endregion
}
