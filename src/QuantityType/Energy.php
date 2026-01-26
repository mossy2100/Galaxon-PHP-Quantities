<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\System;
use Override;

class Energy extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for energy.
     *
     * @return array<string, array<string, string|int>>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'joule'        => [
                'asciiSymbol'         => 'J',
                'prefixGroup'         => PrefixRegistry::GROUP_CODE_METRIC,
                'expansionUnitSymbol' => 'kg*m2*s-2',
                'systems'             => [System::SI],
            ],
            'electronvolt' => [
                'asciiSymbol' => 'eV',
                'prefixGroup' => PrefixRegistry::GROUP_CODE_METRIC,
                'systems'     => [System::SIAccepted],
            ],
            'calorie'      => [
                'asciiSymbol' => 'cal',
                'prefixGroup' => PrefixRegistry::GROUP_CODE_LARGE_ENGINEERING_METRIC,
                'systems'     => [System::Common],
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
        ];
    }

    // endregion
}
