<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\System;
use Galaxon\Quantities\Utility\PrefixUtility;
use Override;

/**
 * Represents data quantities.
 */
class Data extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for data.
     *
     * @return array<string, array{
     *     asciiSymbol: string,
     *     unicodeSymbol?: string,
     *     prefixGroup?: int,
     *     systems: list<System>,
     *     expansionUnitSymbol?: string,
     *     expansionValue?: float
     * }>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'bit'  => [
                'asciiSymbol' => 'b',
                'prefixGroup' => PrefixUtility::GROUP_CODE_LARGE,
                'systems'     => [System::Common],
            ],
            'byte' => [
                'asciiSymbol' => 'B',
                'prefixGroup' => PrefixUtility::GROUP_CODE_LARGE,
                'systems'     => [System::Common],
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
