<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;

class LuminousFlux extends Quantity
{
    /**
     * Unit definitions for luminous flux.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnitDefinitions(): array
    {
        return [
            // SI named unit
            'lumen' => [
                'asciiSymbol'         => 'lm',
                'dimension'           => 'JA2',
                'system'              => 'si_named',
                'prefixGroup'         => PrefixRegistry::PREFIX_GROUP_METRIC,
                'expansionUnitSymbol' => 'cd*rad2',
            ],
        ];
    }
}
