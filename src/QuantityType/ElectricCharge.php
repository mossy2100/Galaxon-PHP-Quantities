<?php

declare(strict_types=1);

namespace OceanMoon\Quantities\QuantityType;

use OceanMoon\Quantities\Internal\UnitSystem;
use OceanMoon\Quantities\Quantity;
use OceanMoon\Quantities\Services\PrefixService;
use Override;

/**
 * Represents electric charge quantities.
 */
class ElectricCharge extends Quantity
{
    #region Overridden methods

    /**
     * Unit definitions for electric charge.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'coulomb' => [
                'asciiSymbol' => 'C',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
        ];
    }

    /**
     * Conversion factors for electric charge units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['C', 's*A', 1],
        ];
    }

    #endregion
}
