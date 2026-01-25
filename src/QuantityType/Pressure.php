<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
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
                'prefixGroup'         => PrefixRegistry::PREFIX_GROUP_METRIC,
                'expansionUnitSymbol' => 'kg*m-1*s-2',
            ],
            'bar'        => [
                'asciiSymbol' => 'bar',
            ],
            'mmHg'       => [
                'asciiSymbol' => 'mmHg',
            ],
            'atmosphere' => [
                'asciiSymbol' => 'atm',
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
            ['bar', 'Pa', 100000],
            ['mmHg', 'Pa', 133.322387415],
            ['atm', 'Pa', 101325],
        ];
    }

    // endregion
}
