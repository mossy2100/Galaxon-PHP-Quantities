<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;

class Conductance extends Quantity
{
    /**
     * Unit definitions for electrical conductance.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnitDefinitions(): array
    {
        return [
            // SI named unit
            'siemens' => [
                'asciiSymbol'   => 'S',
                'dimension'     => 'T3L-2M-1I2',
                'system'        => 'si_named',
                'prefixGroup'   => PrefixRegistry::PREFIX_GROUP_METRIC,
                'expansionUnit' => 'kg-1*m-2*s3*A2',
            ],
        ];
    }
}
