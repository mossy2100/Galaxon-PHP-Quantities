<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;

class Pressure extends Quantity
{
    /**
     * Unit definitions for pressure.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnitDefinitions(): array
    {
        return [
            // SI named unit
            'pascal'     => [
                'asciiSymbol'         => 'Pa',
                'dimension'           => 'T-2L-1M',
                'system'              => 'si_named',
                'prefixGroup'         => PrefixRegistry::PREFIX_GROUP_METRIC,
                'expansionUnitSymbol' => 'kg*m-1*s-2',
            ],
            // Non-SI metric units
            'bar'        => [
                'asciiSymbol' => 'bar',
                'dimension'   => 'T-2L-1M',
                'system'      => 'metric',
            ],
            // US customary units
            'mmHg'       => [
                'asciiSymbol' => 'mmHg',
                'dimension'   => 'T-2L-1M',
                'system'      => 'us_customary',
            ],
            'atmosphere' => [
                'asciiSymbol' => 'atm',
                'dimension'   => 'T-2L-1M',
                'system'      => 'us_customary',
            ],
        ];
    }

    /**
     * Conversion factors for pressure units.
     *
     * @return list<array{string, string, float}>
     */
    public static function getConversions(): array
    {
        return [
            ['bar', 'Pa', 100000],
            ['mmHg', 'Pa', 133.322387415],
            ['atm', 'Pa', 101325],
        ];
    }
}
