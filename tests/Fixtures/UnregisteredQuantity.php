<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Fixtures;

use Galaxon\Quantities\Quantity;

/**
 * An unregistered Quantity subclass for testing error paths.
 *
 * This class is NOT registered in QuantityTypeRegistry, which allows testing
 * the error path in validatePartUnitSymbols() when the calling class is not found.
 *
 * Uses a compound dimension (L*M) which has no registered class.
 */
class UnregisteredQuantity extends Quantity
{
    /**
     * Override getPartsConfig to define part units.
     *
     * Note: These units are for the L*M dimension (length × mass).
     *
     * @return array{from: ?string, to: list<string>}
     */
    public static function getPartsConfig(): array
    {
        return [
            'from' => 'kg*m',
            'to'   => ['kg*m'],
        ];
    }
}
