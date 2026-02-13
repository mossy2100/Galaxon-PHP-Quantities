<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
use Override;

/**
 * Represents inductance quantities.
 */
class Inductance extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for inductance.
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
            'henry' => [
                'asciiSymbol'         => 'H',
                'prefixGroup'         => PrefixRegistry::GROUP_METRIC,
                'systems'             => [System::Si],
                'expansionUnitSymbol' => 'kg*m2*s-2*A-2',
            ],
        ];
    }

    // endregion
}
