<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\PrefixService;
use Galaxon\Quantities\UnitSystem;
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
     *     alternateSymbol?: string,
     *     systems: list<UnitSystem>
     * }>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'joule'                => [
                'asciiSymbol' => 'J',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
            'electronvolt'         => [
                'asciiSymbol' => 'eV',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::SiAccepted, UnitSystem::Scientific],
            ],
            'calorie'              => [
                'asciiSymbol' => 'cal',
                'prefixGroup' => PrefixService::GROUP_LARGE_METRIC,
                'systems'     => [UnitSystem::Common],
            ],
            'British thermal unit' => [
                'asciiSymbol' => 'Btu',
                'systems'     => [UnitSystem::UsCustomary],
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
            ['J', 'kg*m2*s-2', 1],
            ['eV', 'J', 1.602_176_634e-19],
            ['cal', 'J', 4.184],
            ['Btu', 'J', 1055.05585262],
        ];
    }

    // endregion
}
