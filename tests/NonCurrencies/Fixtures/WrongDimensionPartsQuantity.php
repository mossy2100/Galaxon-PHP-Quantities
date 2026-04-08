<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\NonCurrencies\Fixtures;

use Galaxon\Quantities\QuantityType\Time;

/**
 * A Time subclass with a wrong-dimension unit in its default part unit symbols.
 *
 * Used for testing the "wrong dimension" error path in validatePartUnits().
 */
class WrongDimensionPartsQuantity extends Time
{
    /** @var list<string> Part unit symbols including a length unit (wrong dimension). */
    protected static array $defaultPartUnitSymbols = ['h', 'min', 'm'];

    /** @var string Default result unit symbol. */
    protected static string $defaultResultUnitSymbol = 's';
}
