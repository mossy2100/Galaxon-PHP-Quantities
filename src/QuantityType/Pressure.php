<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
use Override;

class Pressure extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for pressure.
     *
     * @return array<string, array<string, string|int>>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'pascal'     => [
                'asciiSymbol'         => 'Pa',
                'prefixGroup'         => PrefixRegistry::GROUP_CODE_METRIC,
                'expansionUnitSymbol' => 'kg*m-1*s-2',
                'systems'             => [System::SI],
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
                'systems'     => [System::US],
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
