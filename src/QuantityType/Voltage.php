<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;

class Voltage extends Quantity
{
    /**
     * Unit definitions for voltage (electric potential difference).
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnitDefinitions(): array
    {
        return [
            // SI named unit
            'volt' => [
                'asciiSymbol'   => 'V',
                'dimension'     => 'T-3L2MI-1',
                'system'        => 'si_named',
                'prefixGroup'   => PrefixRegistry::PREFIX_GROUP_METRIC,
                'expansionUnit' => 'kg*m2*s-3*A-1',
            ],
        ];
    }
}
