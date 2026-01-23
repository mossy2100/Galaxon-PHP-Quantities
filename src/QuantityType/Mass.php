<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Conversion;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\ConversionRegistry;
use Galaxon\Quantities\Registry\PrefixRegistry;

class Mass extends Quantity
{
    /**
     * Unit definitions for mass.
     *
     * @return array<string, array<string, string|int>>
     */
    public static function getUnitDefinitions(): array
    {
        return [
            // SI base unit
            'gram'      => [
                'asciiSymbol' => 'g',
                'dimension'   => 'M',
                'system'      => 'si_base',
                'prefixGroup' => PrefixRegistry::PREFIX_GROUP_METRIC,
                'siPrefix'    => 'k',
            ],
            // Non-SI metric units
            'tonne'     => [
                'asciiSymbol' => 't',
                'dimension'   => 'M',
                'system'      => 'metric',
            ],
            // US customary units
            'ounce'     => [
                'asciiSymbol' => 'oz',
                'dimension'   => 'M',
                'system'      => 'us_customary',
            ],
            'pound'     => [
                'asciiSymbol' => 'lb',
                'dimension'   => 'M',
                'system'      => 'us_customary',
            ],
            'stone'     => [
                'asciiSymbol' => 'st',
                'dimension'   => 'M',
                'system'      => 'us_customary',
            ],
            'short ton' => [
                'asciiSymbol' => 'ton',
                'dimension'   => 'M',
                'system'      => 'us_customary',
            ],
        ];
    }

    /**
     * Conversion factors for mass units.
     *
     * @return list<array{string, string, float}>
     * @see https://en.wikipedia.org/wiki/International_yard_and_pound
     */
    public static function getConversions(): array
    {
        return [
            // Metric
            ['t', 'kg', 1000],
            // Metric-US bridge
            ['lb', 'kg', 0.453_592_37],
            // US customary
            ['lb', 'oz', 16],
            ['st', 'lb', 14],
            ['ton', 'lb', 2000],
        ];
    }

    // region Modification methods

    /**
     * Use British (imperial or long) ton instead of US (short) ton.
     *
     * Default:                   1 ton = 2000 lb (short ton)
     * After calling this method: 1 ton = 2240 lb (long ton)
     */
    public static function useBritishUnits(): void
    {
        $conversion = new Conversion('ton', 'lb', 2240);
        ConversionRegistry::add($conversion);
    }

    // endregion
}
