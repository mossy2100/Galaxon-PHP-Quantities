<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;

class Length extends Quantity
{
    // region Factory methods

    /**
     * Get the Planck length.
     *
     * @return self Length object representing the Planck length (1.616255×10⁻³⁵ m).
     */
    public static function planckLength(): self
    {
        return new self(1.616255e-35, 'm');
    }

    // endregion

    // region Static getters

    /**
     * Get the dimension code for this quantity type. This method must be overridden in derived classes.
     *
     * @return ?string
     */
    #[Override]
    public static function getDimensionCode(): ?string
    {
        return 'L';
    }

    // endregion
}
