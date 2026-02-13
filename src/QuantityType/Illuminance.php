<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
use Override;

/**
 * Represents illuminance quantities.
 */
class Illuminance extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for illuminance.
     *
     * @return array<string, array{
     *     asciiSymbol: string,
     *     unicodeSymbol?: string,
     *     prefixGroup?: int,
     *     alternateSymbol?: string,
     *     systems: list<System>,
     *     expansionUnitSymbol?: string,
     *     expansionValue?: float
     * }>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'lux' => [
                'asciiSymbol'         => 'lx',
                'prefixGroup'         => PrefixRegistry::GROUP_METRIC,
                'systems'             => [System::Si],
                'expansionUnitSymbol' => 'cd*rad2*m-2',
            ],
        ];
    }

    // endregion
}
