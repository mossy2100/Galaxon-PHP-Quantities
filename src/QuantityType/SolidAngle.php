<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Internal\UnitSystem;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\PrefixService;
use Override;

/**
 * Represents solid angle quantities.
 */
class SolidAngle extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for solid angle.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'steradian' => [
                'asciiSymbol' => 'sr',
                'prefixGroup' => PrefixService::GROUP_SMALL_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
        ];
    }

    /**
     * Conversion factors for solid angle units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['sr', 'rad2', 1],
        ];
    }

    // endregion
}
