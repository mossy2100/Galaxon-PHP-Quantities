<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Override;

class Data extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for data.
     *
     * @return array<string, array<string, string|int>>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'bit'  => [
                'asciiSymbol' => 'b',
                'prefixGroup' => PrefixRegistry::PREFIX_GROUP_LARGE,
            ],
            'byte' => [
                'asciiSymbol' => 'B',
                'prefixGroup' => PrefixRegistry::PREFIX_GROUP_LARGE,
            ],
        ];
    }

    /**
     * Conversion factors for data units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            // 1 byte = 8 bits
            ['B', 'b', 8],
        ];
    }

    // endregion
}
