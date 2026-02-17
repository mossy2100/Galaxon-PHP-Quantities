<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
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
     *     systems: list<System>
     * }>
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            // SI
            'gram'      => [
                'asciiSymbol' => 'g',
                'prefixGroup' => PrefixRegistry::GROUP_METRIC,
                'systems'     => [System::Si],
            ],
            // SI accepted
            'tonne'     => [
                'asciiSymbol' => 't',
                'systems'     => [System::SiAccepted],
            ],
            'dalton'    => [
                'asciiSymbol' => 'Da',
                'systems'     => [System::SiAccepted, System::Scientific],
            ],
            // Imperial and US customary
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
        UnitRegistry::loadSystem(System::Imperial);
        // The long ton and stone are in use, but the grain is not.
        self::setDefaultPartUnitSymbols(['LT', 'st', 'lb', 'oz']);
        self::setDefaultResultUnitSymbol('lb');
    }

    /**
     * Set the default part units for US customary mass quantities.
     */
    public static function setUsCustomaryParts(): void
    {
        UnitRegistry::loadSystem(System::UsCustomary);
        // The short ton and grain are in use, but the stone is not.
        self::setDefaultPartUnitSymbols(['tn', 'lb', 'oz', 'gr']);
        self::setDefaultResultUnitSymbol('lb');
    }

    // endregion
}
