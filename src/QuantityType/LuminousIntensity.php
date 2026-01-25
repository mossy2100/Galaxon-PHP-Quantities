<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Override;

class LuminousIntensity extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for luminous intensity.
     *
     * @return array<string, array<string, string|int>>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'candela' => [
                'asciiSymbol' => 'cd',
                'prefixGroup' => PrefixRegistry::PREFIX_GROUP_METRIC,
            ],
        ];
    }

    // endregion
}
