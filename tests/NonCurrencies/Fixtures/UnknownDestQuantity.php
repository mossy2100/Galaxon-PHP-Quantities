<?php

declare(strict_types=1);

namespace OceanMoon\Quantities\Tests\NonCurrencies\Fixtures;

use OceanMoon\Quantities\Quantity;
use Override;

/**
 * Fixture with a conversion definition referencing an unknown destination unit.
 */
class UnknownDestQuantity extends Quantity
{
    /**
     * Returns a conversion where the destination unit symbol is unregistered.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['m', 'zzzdest', 1.0],
        ];
    }
}
