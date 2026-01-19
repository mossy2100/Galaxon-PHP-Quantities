<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;

class AmountOfSubstance extends Quantity
{
    /**
     * Unit definitions for amount of substance.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnitDefinitions(): array
    {
        return [
            // SI base unit
            'mole' => [
                'asciiSymbol' => 'mol',
                'dimension'   => 'N',
                'system'      => 'si_base',
                'prefixGroup' => PrefixRegistry::PREFIX_GROUP_METRIC,
            ],
        ];
    }
}
