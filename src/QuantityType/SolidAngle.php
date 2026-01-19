<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;

class SolidAngle extends Quantity
{
    /**
     * Unit definitions for solid angle.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnitDefinitions(): array
    {
        return [
            // SI derived unit
            'steradian' => [
                'asciiSymbol'   => 'sr',
                'dimension'     => 'A2',
                'system'        => 'si_derived',
                'prefixGroup'   => PrefixRegistry::PREFIX_GROUP_SMALL_METRIC,
                'expansionUnit' => 'rad2',
            ],
        ];
    }
}
