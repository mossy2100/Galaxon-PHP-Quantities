<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Internal\UnitSystem;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\PrefixService;
use Override;

/**
 * Represents luminous intensity quantities.
 */
class LuminousIntensity extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for luminous intensity.
     *
     * @return array<string, array{
     *     asciiSymbol: string,
     *     unicodeSymbol?: string,
     *     prefixGroup?: int,
     *     alternateSymbol?: string,
     *     systems: list<UnitSystem>
     * }>
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

    // endregion
}
