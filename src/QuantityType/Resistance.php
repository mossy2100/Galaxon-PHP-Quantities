<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Internal\UnitSystem;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\PrefixService;
use Override;

/**
 * Represents electrical resistance quantities.
 */
class Resistance extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for electrical resistance.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'ohm' => [
                'asciiSymbol'   => 'ohm',
                'unicodeSymbol' => 'Ω',
                'prefixGroup'   => PrefixService::GROUP_METRIC,
                'systems'       => [UnitSystem::Si],
            ],
        ];
    }

    /**
     * Conversion factors for electrical resistance units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['ohm', 'kg*m2*s-3*A-2', 1],
        ];
    }

    // endregion
}
