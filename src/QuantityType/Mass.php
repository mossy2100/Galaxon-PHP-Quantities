<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Internal\UnitSystem;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\PrefixService;
use Override;

/**
 * Represents mass quantities.
 */
class Mass extends Quantity
{
    // region Public constants

    /**
     * Imperial units for mass parts.
     *
     * @var list<string>
     */
    public const array IMP_PART_UNITS = ['LT', 'st', 'lb', 'oz'];

    /**
     * US customary units for mass parts.
     *
     * @var list<string>
     */
    public const array US_PART_UNITS = ['tn', 'lb', 'oz', 'gr'];

    // endregion

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
            'gram'       => [
                'asciiSymbol' => 'g',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
            // SI accepted
            'tonne'      => [
                'asciiSymbol' => 't',
                'systems'     => [UnitSystem::SiAccepted],
            ],
            'dalton'     => [
                'asciiSymbol' => 'Da',
                'systems'     => [UnitSystem::SiAccepted, UnitSystem::Scientific],
            ],
            // Imperial and US customary
            'long ton'   => [
                'asciiSymbol' => 'LT',
                'systems'     => [UnitSystem::Imperial],
            ],
            'short ton'  => [
                'asciiSymbol' => 'tn',
                'systems'     => [UnitSystem::UsCustomary],
            ],
            'stone'      => [
                'asciiSymbol' => 'st',
                'systems'     => [UnitSystem::Imperial],
            ],
            'pound'      => [
                'asciiSymbol' => 'lb',
                'systems'     => [UnitSystem::Imperial, UnitSystem::UsCustomary],
            ],
            'ounce'      => [
                'asciiSymbol' => 'oz',
                'systems'     => [UnitSystem::Imperial, UnitSystem::UsCustomary],
            ],
            'troy ounce' => [
                'asciiSymbol' => 'oz t',
                'systems'     => [UnitSystem::Imperial, UnitSystem::UsCustomary],
            ],
            'grain'      => [
                'asciiSymbol' => 'gr',
                'systems'     => [UnitSystem::Imperial, UnitSystem::UsCustomary],
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
            ['LT', 'lb', 2240],
            ['tn', 'lb', 2000],
            ['st', 'lb', 14],
            ['lb', 'oz', 16],
            ['lb', 'gr', 7000],
            ['oz t', 'gr', 480],
        ];
    }

    /**
     * @inheritdoc
     */
    #[Override]
    public static function getResultUnitSymbol(): ?string
    {
        return 'lb';
    }

    // endregion
}
