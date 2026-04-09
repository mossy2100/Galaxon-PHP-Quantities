<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Internal\UnitSystem;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\PrefixService;
use Override;

/**
 * Represents magnetic flux quantities.
 */
class MagneticFlux extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for magnetic flux.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'weber' => [
                'asciiSymbol' => 'Wb',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
        ];
    }

    /**
     * Conversion factors for magnetic flux units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['Wb', 'kg*m2*s-2*A-1', 1],
        ];
    }

    // endregion
}
