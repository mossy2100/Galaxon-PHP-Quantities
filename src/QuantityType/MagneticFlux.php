<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Override;

class MagneticFlux extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for magnetic flux.
     *
     * @return array<string, array<string, string|int>>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'weber' => [
                'asciiSymbol'         => 'Wb',
                'prefixGroup'         => PrefixRegistry::PREFIX_GROUP_METRIC,
                'expansionUnitSymbol' => 'kg*m2*s-2*A-1',
            ],
        ];
    }

    // endregion
}
