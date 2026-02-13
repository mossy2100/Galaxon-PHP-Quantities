<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Fixtures;

use Galaxon\Quantities\QuantityType\Time;
use Override;

/**
 * A Time subclass with an unknown unit in its parts config.
 *
 * Used for testing the "unknown unit symbol" error path in validatePartUnitSymbols().
 */
class BadUnitPartsQuantity extends Time
{
    /**
     * Override getPartsConfig to include an unknown unit.
     *
     * @return array{from: ?string, to: list<string>}
     */
    #[Override]
    public static function getPartsConfig(): array
    {
        return [
            'from' => 's',
            'to'   => ['h', 'min', 'xyz'],  // 'xyz' is unknown
        ];
    }
}
