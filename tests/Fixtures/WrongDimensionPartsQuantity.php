<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Fixtures;

use Galaxon\Quantities\QuantityType\Time;
use Override;

/**
 * A Time subclass with a wrong-dimension unit in its parts config.
 *
 * Used for testing the "wrong dimension" error path in validatePartUnitSymbols().
 */
class WrongDimensionPartsQuantity extends Time
{
    /**
     * Override getPartsConfig to include a unit of wrong dimension.
     *
     * @return array{from: ?string, to: list<string>}
     */
    #[Override]
    public static function getPartsConfig(): array
    {
        return [
            'from' => 's',
            'to'   => ['h', 'min', 'm'],  // 'm' is a length unit, not time
        ];
    }
}
