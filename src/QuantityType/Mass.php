<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\System;
use Galaxon\Quantities\Utility\PrefixUtility;
use Override;

/**
 * Represents mass quantities.
 */
class Mass extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for mass.
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
            'gram'      => [
                'asciiSymbol' => 'g',
                'prefixGroup' => PrefixUtility::GROUP_CODE_METRIC,
                'systems'     => [System::Si],
            ],
            'tonne'     => [
                'asciiSymbol' => 't',
                'systems'     => [System::SiAccepted],
            ],
            'dalton'    => [
                'asciiSymbol' => 'Da',
                'systems'     => [System::SiAccepted],
            ],
            'grain'     => [
                'asciiSymbol' => 'gr',
                'systems'     => [System::Imperial, System::UsCustomary],
            ],
            'ounce'     => [
                'asciiSymbol' => 'oz',
                'systems'     => [System::Imperial, System::UsCustomary],
            ],
            'pound'     => [
                'asciiSymbol' => 'lb',
                'systems'     => [System::Imperial, System::UsCustomary],
            ],
            'stone'     => [
                'asciiSymbol' => 'st',
                'systems'     => [System::Imperial],
            ],
            'short ton' => [
                'asciiSymbol' => 'tn',
                'systems'     => [System::UsCustomary],
            ],
            'long ton'  => [
                'asciiSymbol' => 'LT',
                'systems'     => [System::Imperial],
            ],
        ];
    }

    /**
     * Conversion factors for mass units.
     *
     * @return list<array{string, string, float}>
     * @see https://en.wikipedia.org/wiki/International_yard_and_pound
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
             // SI accepted
            ['t', 'kg', 1000],
            ['Da', 'kg', 1.66053906892e-27],
            // Metric-Imperial/US bridge
            ['lb', 'kg', 0.453_592_37],
            ['gr', 'mg', 64.79891],
            // Imperial and US customary
            ['lb', 'oz', 16],
            ['st', 'lb', 14],
            ['tn', 'lb', 2000],
            ['LT', 'lb', 2240],
        ];
    }

    // endregion
}
