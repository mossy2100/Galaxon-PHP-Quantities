<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
use Override;

class AmountOfSubstance extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for amount of substance.
     *
     * @return array<string, array<string, string|int>>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'mole' => [
                'asciiSymbol' => 'mol',
                'prefixGroup' => PrefixRegistry::GROUP_CODE_METRIC,
                'systems'     => [System::SI],
            ],
        ];
    }

    // endregion
}
