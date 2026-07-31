<?php

declare(strict_types=1);

namespace OceanMoon\Quantities\QuantityType;

use OceanMoon\Quantities\Internal\UnitSystem;
use OceanMoon\Quantities\Quantity;
use OceanMoon\Quantities\Services\PrefixService;
use Override;

/**
 * Represents capacitance quantities.
 */
class Capacitance extends Quantity
{
    #region Overridden methods

    /**
     * Unit definitions for capacitance.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'farad' => [
                'asciiSymbol' => 'F',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
        ];
    }

    /**
     * Conversion factors for capacitance units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['F', 'kg-1*m-2*s4*A2', 1],
        ];
    }

    #endregion
}
