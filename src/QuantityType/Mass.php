<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Quantity;

class Mass extends Quantity
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
        return 'M';
    }

    // endregion

    // region Factory methods

    /**
     * Get the mass of an electron.
     *
     * @return self Mass object representing the electron rest mass (9.1093837015×10⁻³¹ kg).
     */
    public static function electronMass(): self
    {
        return new self(9.1093837015e-31, 'kg');
    }

    /**
     * Get the mass of a proton.
     *
     * @return self Mass object representing the proton rest mass (1.67262192369×10⁻²⁷ kg).
     */
    public static function protonMass(): self
    {
        return new self(1.67262192369e-27, 'kg');
    }

    /**
     * Get the mass of a neutron.
     *
     * @return self Mass object representing the neutron rest mass (1.67492749804×10⁻²⁷ kg).
     */
    public static function neutronMass(): self
    {
        return new self(1.67492749804e-27, 'kg');
    }

    // endregion

    // region Modification methods

    /**
     * Use British (imperial) ton instead of US ton.
     *
     * Default:                   1 ton = 2000 lb (short ton)
     * After calling this method: 1 ton = 2240 lb (long ton)
     */
    public static function useBritishUnits(): void
    {
        // Update the conversion from ton to lb.
        self::getUnitConverter()->addConversion('ton', 'lb', 2240);
    }

    // endregion
}
