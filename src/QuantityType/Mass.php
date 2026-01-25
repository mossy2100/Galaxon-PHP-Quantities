<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Conversion;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\ConversionRegistry;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Override;

class Mass extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for mass.
     *
     * @return array<string, array<string, string|int>>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'gram'      => [
                'asciiSymbol' => 'g',
                'prefixGroup' => PrefixRegistry::PREFIX_GROUP_METRIC,
            ],
            'tonne'     => [
                'asciiSymbol' => 't',
            ],
            'grain'     => [
                'asciiSymbol' => 'gr',
            ],
            'ounce'     => [
                'asciiSymbol' => 'oz',
            ],
            'pound'     => [
                'asciiSymbol' => 'lb',
            ],
            'stone'     => [
                'asciiSymbol' => 'st',
            ],
            'short ton' => [
                'asciiSymbol' => 'ton',
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
            // Metric
            ['t', 'kg', 1000],
            // Metric-US bridge
            ['lb', 'kg', 0.453_592_37],
            ['gr', 'mg', 64.79891],
            // US customary
            ['lb', 'oz', 16],
            ['st', 'lb', 14],
            ['ton', 'lb', 2000],
        ];
    }

    // endregion

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
        ConversionRegistry::addConversion($conversion);
    }

    // endregion
}
