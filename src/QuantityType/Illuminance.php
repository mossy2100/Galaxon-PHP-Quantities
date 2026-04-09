<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Internal\UnitSystem;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\PrefixService;
use Override;

/**
 * Represents illuminance quantities.
 */
class Illuminance extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for illuminance.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'lux' => [
                'asciiSymbol' => 'lx',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
        ];
    }

    /**
     * Conversion factors for illuminance units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['lx', 'cd*rad2*m-2', 1],
        ];
    }

    // endregion
}
