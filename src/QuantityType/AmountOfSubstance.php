<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\PrefixService;
use Galaxon\Quantities\UnitSystem;
use Override;

/**
 * Represents amount of substance quantities.
 */
class AmountOfSubstance extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for amount of substance.
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
            'mole' => [
                'asciiSymbol' => 'mol',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [
                    UnitSystem::Si,
                ],
            ],
        ];
    }

    // endregion
}
