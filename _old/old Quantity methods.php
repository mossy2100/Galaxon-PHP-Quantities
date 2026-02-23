<?php

use Galaxon\Quantities\Converter;
use Galaxon\Quantities\Registry\QuantityTypeRegistry;


    /**
     * Get the converter matching the quantity type.
     *
     * This method must be called from a registered subclass of Quantity.
     *
     * @return Converter The converter for this quantity type's dimension.
     * @throws LogicException If called from Quantity or an unregistered subclass.
     * @throws DomainException If the dimension is invalid.
     */
    public static function getConverter(): Converter
    {
        // This method won't work if called from Quantity.
        if (self::class === static::class) {
            throw new LogicException('This method should be called from a registered subclass of ' . self::class . '.');
        }

        // Load the dimension corresponding to the calling class.
        $dimension = QuantityTypeRegistry::getByClass(static::class)?->dimension;

        // This method won't work if called from a Quantity subclass that isn't in the QuantityTypeRegistry.
        if ($dimension === null) {
            throw new LogicException('This method should be called from a registered subclass of ' . self::class . '.');
        }

        return Converter::getByDimension($dimension);
    }
