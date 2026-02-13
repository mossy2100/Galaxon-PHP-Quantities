<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
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
     *      systems: list<System>,
     *      expansionUnitSymbol?: string,
     *      expansionValue?: float
     *  }>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'mole' => [
                'asciiSymbol' => 'mol',
                'prefixGroup' => PrefixRegistry::GROUP_METRIC,
                'systems'     => [System::Si],
            ],
        ];
    }

    // endregion
}
