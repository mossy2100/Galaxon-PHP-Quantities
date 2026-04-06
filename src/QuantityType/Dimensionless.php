<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Internal\UnitSystem;
use Galaxon\Quantities\Quantity;
use Override;

/**
 * Represents dimensionless quantities.
 */
class Dimensionless extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for dimensionless quantities.
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
            'scalar'             => [
                'asciiSymbol' => '',
                'systems'     => [UnitSystem::Common],
            ],
            'percentage'         => [
                'asciiSymbol' => '%',
                'systems'     => [UnitSystem::Common],
            ],
            'parts per thousand' => [
                'asciiSymbol'   => 'ppt',
                'unicodeSymbol' => '‰',
                'systems'       => [UnitSystem::Common],
            ],
            'parts per million'  =>  [
                'asciiSymbol' => 'ppm',
                'systems'     => [UnitSystem::Common],
            ],
            'parts per billion'  => [
                'asciiSymbol' => 'ppb',
                'systems'     => [UnitSystem::Common],
            ],
        ];
    }

    /**
     * Conversion factors for dimensionless quantities.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['', '%', 100],
            ['%', 'ppt', 10],
            ['ppt', 'ppm', 1000],
            ['ppm', 'ppb', 1000],
        ];
    }

    // endregion
}
