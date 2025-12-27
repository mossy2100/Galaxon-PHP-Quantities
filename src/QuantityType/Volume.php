<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;

class Volume extends Quantity
{
    // region Static getters

    /**
     * Get the dimension code for this quantity type. This method must be overridden in derived classes.
     *
     * @return ?string
     */
    #[Override]
    public static function getDimensionCode(): ?string
    {
        return 'L3';
    }

    // endregion
}
