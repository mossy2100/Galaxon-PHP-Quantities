<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Internal\UnitSystem;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\PrefixService;
use Override;

/**
 * Represents data quantities.
 */
class Data extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for data.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'bit'  => [
                'asciiSymbol' => 'b',
                'prefixGroup' => PrefixService::GROUP_LARGE,
                'systems'     => [UnitSystem::Common],
            ],
            'byte' => [
                'asciiSymbol' => 'B',
                'prefixGroup' => PrefixService::GROUP_LARGE,
                'systems'     => [UnitSystem::Common],
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
