<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Fixtures;

use Galaxon\Quantities\QuantityType\Time;

/**
 * A Time subclass with an unknown unit in its default part unit symbols.
 *
 * Used for testing the "unknown unit symbol" error path in validatePartUnitSymbols().
 */
class BadUnitPartsQuantity extends Time
{
    /** @var list<string> Part unit symbols including an unknown unit. */
    protected static array $defaultPartUnitSymbols = ['h', 'min', 'xyz'];

    /** @var string Default result unit symbol. */
    protected static string $defaultResultUnitSymbol = 's';
}
