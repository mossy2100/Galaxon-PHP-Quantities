<?php

declare(strict_types=1);

namespace OceanMoon\Quantities\QuantityType;

use OceanMoon\Quantities\Internal\UnitSystem;
use OceanMoon\Quantities\Quantity;
use OceanMoon\Quantities\Services\PrefixService;
use Override;

/**
 * Represents luminous intensity quantities.
 */
class LuminousIntensity extends Quantity
{
    #region Overridden methods

    /**
     * Unit definitions for luminous intensity.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'candela' => [
                'asciiSymbol' => 'cd',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
        ];
    }

    #endregion
}
