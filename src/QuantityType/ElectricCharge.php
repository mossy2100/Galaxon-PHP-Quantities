<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;

class ElectricCharge extends Quantity
{
    /**
     * Unit definitions for electric charge.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnitDefinitions(): array
    {
        return [
            // SI named unit
            'coulomb' => [
                'asciiSymbol'   => 'C',
                'dimension'     => 'TI',
                'system'        => 'si_named',
                'prefixGroup'   => PrefixRegistry::PREFIX_GROUP_METRIC,
                'expansionUnit' => 's*A',
            ],
        ];
    }
}
