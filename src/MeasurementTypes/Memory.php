<?php

declare(strict_types=1);

namespace Galaxon\Units\MeasurementTypes;

use Galaxon\Units\Measurement;
use Override;

class Memory extends Measurement
{
    // region Measurement methods

    /**
     * Get the units for Memory measurements.
     *
     * @return array<string, int> Array of units with allowed prefixes flags.
     */
    #[Override]
    public static function getBaseUnits(): array
    {
        return [
            'B' => self::PREFIX_SET_LARGE,  // byte
            'b' => self::PREFIX_SET_LARGE,  // bit
        ];
    }

    /**
     * Get the conversions for Memory measurements.
     *
     * @return array<int, array> Array of conversion definitions.
     */
    #[Override]
    public static function getConversions(): array
    {
        return [
            // 1 byte = 8 bits
            ['B', 'b', 8]
        ];
    }

    // endregion
}
