<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;

class Illuminance extends Quantity
{
    /**
     * Unit definitions for illuminance.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnitDefinitions(): array
    {
        return [
            // SI named unit
            'lux' => [
                'asciiSymbol'         => 'lx',
                'dimension'           => 'L-2JA2',
                'system'              => 'si_named',
                'prefixGroup'         => PrefixRegistry::PREFIX_GROUP_METRIC,
                'expansionUnitSymbol' => 'cd*rad2*m-2',
            ],
        ];
    }
}
