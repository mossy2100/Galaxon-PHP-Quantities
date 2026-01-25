<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Override;

class MagneticFluxDensity extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for magnetic flux density.
     *
     * @return array<string, array<string, string|int>>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'tesla' => [
                'asciiSymbol'         => 'T',
                'prefixGroup'         => PrefixRegistry::PREFIX_GROUP_METRIC,
                'expansionUnitSymbol' => 'kg*s-2*A-1',
            ],
        ];
    }

    // endregion
}
