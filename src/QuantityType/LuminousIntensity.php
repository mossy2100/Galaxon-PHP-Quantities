<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;

class LuminousIntensity extends Quantity
{
    /**
     * Unit definitions for luminous intensity.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnitDefinitions(): array
    {
        return [
            // SI base unit
            'candela' => [
                'asciiSymbol' => 'cd',
                'dimension'   => 'J',
                'system'      => 'si_base',
                'prefixGroup' => PrefixRegistry::PREFIX_GROUP_METRIC,
            ],
        ];
    }
}
