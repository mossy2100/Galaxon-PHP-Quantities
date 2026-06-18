<?php

declare(strict_types=1);

namespace OceanMoon\Quantities\Tests\NonCurrencies\Fixtures;

use OceanMoon\Quantities\Quantity;
use Override;

/**
 * Fixture with a conversion definition referencing an unknown source unit.
 */
class UnknownSrcQuantity extends Quantity
{
    /**
     * Returns a conversion where the source unit symbol is unregistered.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['zzzsrc', 'm', 1.0],
        ];
    }
}
