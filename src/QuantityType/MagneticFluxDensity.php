<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;

class MagneticFluxDensity extends Quantity
{
    /**
     * Unit definitions for magnetic flux density.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnitDefinitions(): array
    {
        return [
            // SI named unit
            'tesla' => [
                'asciiSymbol'         => 'T',
                'dimension'           => 'T-2MI-1',
                'system'              => 'si_named',
                'prefixGroup'         => PrefixRegistry::PREFIX_GROUP_METRIC,
                'expansionUnitSymbol' => 'kg*s-2*A-1',
            ],
        ];
    }
}
