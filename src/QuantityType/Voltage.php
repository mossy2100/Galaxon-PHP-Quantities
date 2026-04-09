<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Internal\UnitSystem;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\PrefixService;
use Override;

/**
 * Represents voltage quantities.
 */
class Voltage extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for voltage (electric potential difference).
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'volt' => [
                'asciiSymbol' => 'V',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
        ];
    }

    /**
     * Conversion factors for voltage units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['V', 'kg*m2*s-3*A-1', 1],
        ];
    }

    // endregion
}
