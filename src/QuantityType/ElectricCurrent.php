<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;

class ElectricCurrent extends Quantity
{
    /**
     * Unit definitions for electric current.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnitDefinitions(): array
    {
        return [
            // SI base unit
            'ampere' => [
                'asciiSymbol' => 'A',
                'dimension'   => 'I',
                'system'      => 'si_base',
                'prefixGroup' => PrefixRegistry::PREFIX_GROUP_METRIC,
            ],
        ];
    }
}
