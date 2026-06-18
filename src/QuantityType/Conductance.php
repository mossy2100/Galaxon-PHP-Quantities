<?php

declare(strict_types=1);

namespace OceanMoon\Quantities\QuantityType;

use OceanMoon\Quantities\Internal\UnitSystem;
use OceanMoon\Quantities\Quantity;
use OceanMoon\Quantities\Services\PrefixService;
use Override;

/**
 * Represents electrical conductance quantities.
 */
class Conductance extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for electrical conductance.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'siemens' => [
                'asciiSymbol' => 'S',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
        ];
    }

    /**
     * Conversion factors for electrical conductance units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['S', 'kg-1*m-2*s3*A2', 1],
        ];
    }

    // endregion
}
