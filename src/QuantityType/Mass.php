<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\PrefixService;
use Galaxon\Quantities\Services\UnitService;
use Galaxon\Quantities\UnitSystem;
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
     *     alternateSymbol?: string,
     *     systems: list<UnitSystem>
     * }>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            // SI
            'gram'      => [
                'asciiSymbol' => 'g',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
            // SI accepted
            'tonne'     => [
                'asciiSymbol' => 't',
                'systems'     => [UnitSystem::SiAccepted],
            ],
            'dalton'    => [
                'asciiSymbol' => 'Da',
                'systems'     => [UnitSystem::SiAccepted, UnitSystem::Scientific],
            ],
            // Imperial and US customary
            'grain'     => [
                'asciiSymbol' => 'gr',
                'systems'     => [UnitSystem::Imperial, UnitSystem::UsCustomary],
            ],
            'ounce'     => [
                'asciiSymbol' => 'oz',
                'systems'     => [UnitSystem::Imperial, UnitSystem::UsCustomary],
            ],
            'pound'     => [
                'asciiSymbol' => 'lb',
                'systems'     => [UnitSystem::Imperial, UnitSystem::UsCustomary],
            ],
            'stone'     => [
                'asciiSymbol' => 'st',
                'systems'     => [UnitSystem::Imperial],
            ],
            'short ton' => [
                'asciiSymbol' => 'tn',
                'systems'     => [UnitSystem::UsCustomary],
            ],
            'long ton'  => [
                'asciiSymbol' => 'LT',
                'systems'     => [UnitSystem::Imperial],
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
            // Imperial and US customary
            ['lb', 'gr', 7000],
            ['lb', 'oz', 16],
            ['st', 'lb', 14],
            ['tn', 'lb', 2000],
            ['LT', 'lb', 2240],
        ];
    }

    // endregion

    // region Part-related methods

    /**
     * Set the default part units for imperial mass quantities.
     */
    public static function setImperialParts(): void
    {
        UnitService::loadBySystem(UnitSystem::Imperial);

        // The long ton and stone are in use, but the grain is not.
        self::setDefaultPartUnitSymbols(['LT', 'st', 'lb', 'oz']);
        self::setDefaultResultUnitSymbol('lb');
    }

    /**
     * Set the default part units for US customary mass quantities.
     */
    public static function setUsCustomaryParts(): void
    {
        UnitService::loadBySystem(UnitSystem::UsCustomary);

        // The short ton and grain are in use, but the stone is not.
        self::setDefaultPartUnitSymbols(['tn', 'lb', 'oz', 'gr']);
        self::setDefaultResultUnitSymbol('lb');
    }

    // endregion
}
