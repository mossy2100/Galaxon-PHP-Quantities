<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Internal\UnitSystem;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\PrefixService;
use Override;

/**
 * Represents pressure quantities.
 */
class Pressure extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for pressure.
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
            'pascal'     => [
                'asciiSymbol' => 'Pa',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
            'atmosphere' => [
                'asciiSymbol' => 'atm',
                'systems'     => [UnitSystem::Scientific],
            ],
            'mmHg'       => [
                'asciiSymbol' => 'mmHg',
                'systems'     => [UnitSystem::Scientific],
            ],
            'inHg'       => [
                'asciiSymbol' => 'inHg',
                'systems'     => [UnitSystem::UsCustomary],
            ],
        ];
    }

    /**
     * Conversion factors for pressure units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['Pa', 'kg*m-1*s-2', 1],
            ['mmHg', 'Pa', 133.322387415],
            ['atm', 'Pa', 101325],
            ['inHg', 'mmHg', 25.4],
        ];
    }

    // endregion
}
