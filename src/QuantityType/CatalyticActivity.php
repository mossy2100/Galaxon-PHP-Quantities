<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
use Override;

/**
 * Represents catalytic activity quantities.
 */
class CatalyticActivity extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for catalytic activity.
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
            'katal' => [
                'asciiSymbol' => 'kat',
                'prefixGroup' => PrefixRegistry::GROUP_METRIC,
                'systems'     => [System::Si],
            ],
        ];
    }

    /**
     * Conversion factors for catalytic activity units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            // Expansion.
            ['kat', 'mol*s-1', 1.0],
        ];
    }

    // endregion
}
