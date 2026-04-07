<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\NonCurrencies\Fixtures;

use Galaxon\Quantities\Quantity;

/**
 * An unregistered Quantity subclass for testing error paths.
 *
 * This class is NOT registered in QuantityTypeService, which allows testing
 * the error path when the calling class has default part unit symbols but is
 * not a registered quantity type.
 *
 * Uses a compound dimension (L*M) which has no registered class.
 */
class UnregisteredQuantity extends Quantity
{
    /** @var list<string> Default part unit symbols. */
    protected static array $defaultPartUnitSymbols = ['kg*m'];

    /** @var string Default result unit symbol. */
    protected static string $defaultResultUnitSymbol = 'kg*m';
}
