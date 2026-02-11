<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
use Override;

/**
 * Represents frequency quantities.
 */
class Frequency extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for frequency.
     *
     * Note: Becquerel has the same dimension (T-1) but measures radioactivity,
     * not frequency. It's included here as they share the same dimension code.
     *
     * @return array<string, array{
     *     asciiSymbol: string,
     *     unicodeSymbol?: string,
     *     prefixGroup?: int,
     *     systems: list<System>
     * }>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'hertz'     => [
                'asciiSymbol' => 'Hz',
                'prefixGroup' => PrefixRegistry::GROUP_METRIC,
                'systems'     => [System::Si],
            ],
            'becquerel' => [
                'asciiSymbol' => 'Bq',
                'prefixGroup' => PrefixRegistry::GROUP_METRIC,
                'systems'     => [System::Si],
            ],
        ];
    }

    /**
     * Conversion definitions for frequency, including expansions.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            // Expansions.
            ['Hz', 's-1', 1.0],
            ['Bq', 's-1', 1.0],
        ];
    }

    // endregion
}
