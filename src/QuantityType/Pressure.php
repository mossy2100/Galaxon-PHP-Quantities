<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
use Override;

/**
 * Represents pressure quantities.
 */
class Pressure extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for pressure.
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
            'pascal'     => [
                'asciiSymbol'         => 'Pa',
                'prefixGroup'         => PrefixRegistry::GROUP_METRIC,
                'systems'             => [System::Si],
                'expansionUnitSymbol' => 'kg*m-1*s-2',
            ],
            'atmosphere' => [
                'asciiSymbol' => 'atm',
                'systems'     => [System::Scientific],
            ],
            'mmHg'       => [
                'asciiSymbol' => 'mmHg',
                'systems'     => [System::Scientific],
            ],
            'inHg'       => [
                'asciiSymbol' => 'inHg',
                'systems'     => [System::UsCustomary],
            ],
        ];
    }

    /**
     * Conversion factors for pressure units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['mmHg', 'Pa', 133.322387415],
            ['atm', 'Pa', 101325],
            ['inHg', 'mmHg', 25.4],
        ];
    }

    // endregion
}
