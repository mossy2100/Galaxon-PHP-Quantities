<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
use Override;

/**
 * Represents voltage quantities.
 */
class Voltage extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for voltage (electric potential difference).
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
            'volt' => [
                'asciiSymbol' => 'V',
                'prefixGroup' => PrefixRegistry::GROUP_METRIC,
                'systems'     => [System::Si],
            ],
        ];
    }

    /**
     * Conversion factors for voltage units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            // Expansion.
            ['V', 'kg*m2*s-3*A-1', 1.0],
        ];
    }

    // endregion
}
