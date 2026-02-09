<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\System;
use Galaxon\Quantities\Utility\PrefixUtility;
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
     *     systems: list<System>,
     *     expansionUnitSymbol?: string,
     *     expansionValue?: float
     * }>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'joule'                => [
                'asciiSymbol'         => 'J',
                'prefixGroup'         => PrefixUtility::GROUP_CODE_METRIC,
                'expansionUnitSymbol' => 'kg*m2*s-2',
                'systems'             => [System::Si],
            ],
            'electronvolt'         => [
                'asciiSymbol' => 'eV',
                'prefixGroup' => PrefixUtility::GROUP_CODE_METRIC,
                'systems'     => [System::SiAccepted],
            ],
            'calorie'              => [
                'asciiSymbol' => 'cal',
                'prefixGroup' => PrefixUtility::GROUP_CODE_LARGE_ENG_METRIC,
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
            ['eV', 'J', 1.602_176_634e-19],
            ['cal', 'J', 4.184],
            ['Btu', 'J', 1055.05585262],
        ];
    }

    // endregion
}
