<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Override;

class Velocity extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for velocity.
     *
     * @return array<string, array<string, string|int>>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'knot' => [
                'asciiSymbol'         => 'kn',
                'expansionUnitSymbol' => 'nmi*h-1',
            ],
        ];
    }

    // endregion
}
