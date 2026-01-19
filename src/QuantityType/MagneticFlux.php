<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;

class MagneticFlux extends Quantity
{
    /**
     * Unit definitions for magnetic flux.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnitDefinitions(): array
    {
        return [
            // SI named unit
            'weber' => [
                'asciiSymbol'   => 'Wb',
                'dimension'     => 'T-2L2MI-1',
                'system'        => 'si_named',
                'prefixGroup'   => PrefixRegistry::PREFIX_GROUP_METRIC,
                'expansionUnit' => 'kg*m2*s-2*A-1',
            ],
        ];
    }
}
