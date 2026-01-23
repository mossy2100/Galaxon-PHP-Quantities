<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;

class Frequency extends Quantity
{
    /**
     * Unit definitions for frequency.
     *
     * Note: Becquerel has the same dimension (T-1) but measures radioactivity,
     * not frequency. It's included here as they share the same dimension code.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnitDefinitions(): array
    {
        return [
            // SI named unit
            'hertz'     => [
                'asciiSymbol'         => 'Hz',
                'dimension'           => 'T-1',
                'system'              => 'si_named',
                'prefixGroup'         => PrefixRegistry::PREFIX_GROUP_METRIC,
                'expansionUnitSymbol' => 's-1',
            ],
            'becquerel' => [
                'asciiSymbol'         => 'Bq',
                'dimension'           => 'T-1',
                'system'              => 'si_named',
                'prefixGroup'         => PrefixRegistry::PREFIX_GROUP_METRIC,
                'expansionUnitSymbol' => 's-1',
            ],
        ];
    }
}
