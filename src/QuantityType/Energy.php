<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;

class Energy extends Quantity
{
    /**
     * Unit definitions for energy.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnitDefinitions(): array
    {
        return [
            // SI named unit
            'joule'        => [
                'asciiSymbol'         => 'J',
                'dimension'           => 'T-2L2M',
                'system'              => 'si_named',
                'prefixGroup'         => PrefixRegistry::PREFIX_GROUP_METRIC,
                'expansionUnitSymbol' => 'kg*m2*s-2',
            ],
            // Non-SI metric units
            'electronvolt' => [
                'asciiSymbol' => 'eV',
                'dimension'   => 'T-2L2M',
                'system'      => 'metric',
                'prefixGroup' => PrefixRegistry::PREFIX_GROUP_METRIC,
            ],
            'calorie'      => [
                'asciiSymbol' => 'cal',
                'dimension'   => 'T-2L2M',
                'system'      => 'metric',
                'prefixGroup' => PrefixRegistry::PREFIX_GROUP_LARGE_METRIC,
            ],
        ];
    }

    /**
     * Conversion factors for energy units.
     *
     * @return list<array{string, string, float}>
     */
    public static function getConversions(): array
    {
        return [
            ['eV', 'J', 1.602_176_634e-19],
            ['cal', 'J', 4.184],
        ];
    }
}
