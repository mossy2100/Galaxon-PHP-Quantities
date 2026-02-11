<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
use Override;

/**
 * Represents energy quantities.
 */
class Energy extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for energy.
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
            'joule'                => [
                'asciiSymbol' => 'J',
                'prefixGroup' => PrefixRegistry::GROUP_METRIC,
                'systems'     => [System::Si],
            ],
            'electronvolt'         => [
                'asciiSymbol' => 'eV',
                'prefixGroup' => PrefixRegistry::GROUP_METRIC,
                'systems'     => [System::SiAccepted],
            ],
            'calorie'              => [
                'asciiSymbol' => 'cal',
                'prefixGroup' => PrefixRegistry::GROUP_LARGE_ENG_METRIC,
                'systems'     => [System::Common],
            ],
            'British thermal unit' => [
                'asciiSymbol' => 'Btu',
                'systems'     => [System::UsCustomary],
            ],
        ];
    }

    /**
     * Conversion factors for energy units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            // Expansion.
            ['J', 'kg*m2*s-2', 1.0],
            // Conversions.
            ['eV', 'J', 1.602_176_634e-19],
            ['cal', 'J', 4.184],
            ['Btu', 'J', 1055.05585262],
        ];
    }

    // endregion
}
