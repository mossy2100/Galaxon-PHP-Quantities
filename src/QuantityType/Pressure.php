<?php

declare(strict_types=1);

namespace OceanMoon\Quantities\QuantityType;

use OceanMoon\Quantities\Internal\UnitSystem;
use OceanMoon\Quantities\Quantity;
use OceanMoon\Quantities\Services\PrefixService;
use Override;

/**
 * Represents pressure quantities.
 */
class Pressure extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for pressure.
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
