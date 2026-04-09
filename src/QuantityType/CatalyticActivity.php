<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Internal\UnitSystem;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\PrefixService;
use Override;

/**
 * Represents catalytic activity quantities.
 */
class CatalyticActivity extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for catalytic activity.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'katal' => [
                'asciiSymbol' => 'kat',
                'prefixGroup' => PrefixService::GROUP_METRIC,
                'systems'     => [UnitSystem::Si],
            ],
        ];
    }

    /**
     * Conversion factors for catalytic activity units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['kat', 'mol*s-1', 1],
        ];
    }

    // endregion
}
