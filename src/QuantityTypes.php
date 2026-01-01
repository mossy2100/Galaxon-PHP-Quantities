<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use ValueError;

/**
 * Registry of quantity types keyed by dimension code.
 *
 * Provides mapping between dimension codes (e.g., 'L', 'M', 'L2') and their associated quantity information including
 * name, SI unit, and (optional) PHP class.
 */
class QuantityTypes
{
    /**
     * Quantity types keyed by dimension code.
     *
     * Each entry contains:
     * - 'quantity': The name of the physical quantity
     * - 'siUnit': The SI unit symbol for this quantity
     * - 'class': The QuantityType class (if one exists), or null
     *
     * @var array<string, array{quantity: string, siUnit: string, class: class-string<Quantity>|null}>
     */
    private static array $quantityTypes = [
        // SI base dimensions
        'L'           => ['quantity' => 'length', 'siUnit' => 'm', 'class' => QuantityType\Length::class],
        'M'           => ['quantity' => 'mass', 'siUnit' => 'kg', 'class' => QuantityType\Mass::class],
        'T'           => ['quantity' => 'time', 'siUnit' => 's', 'class' => QuantityType\Time::class],
        'I'           => ['quantity' => 'electric current', 'siUnit' => 'A', 'class' => null],
        'H'           => ['quantity' => 'temperature', 'siUnit' => 'K', 'class' => QuantityType\Temperature::class],
        'N'           => ['quantity' => 'amount of substance', 'siUnit' => 'mol', 'class' => null],
        'J'           => ['quantity' => 'luminous intensity', 'siUnit' => 'cd', 'class' => null],

        // SI derived dimensions (dimensionless or simple)
        'A'           => ['quantity' => 'angle', 'siUnit' => 'rad', 'class' => QuantityType\Angle::class],
        'A2'          => ['quantity' => 'solid angle', 'siUnit' => 'sr', 'class' => null],
        'L2'          => ['quantity' => 'area', 'siUnit' => 'm2', 'class' => QuantityType\Area::class],
        'L3'          => ['quantity' => 'volume', 'siUnit' => 'm3', 'class' => QuantityType\Volume::class],
        'T-1L'        => ['quantity' => 'velocity', 'siUnit' => 'm/s', 'class' => QuantityType\Velocity::class],
        'T-2L'        => ['quantity' => 'acceleration', 'siUnit' => 'm/s2', 'class' => QuantityType\Acceleration::class],
        'L-3M'        => ['quantity' => 'density', 'siUnit' => 'kg/m3', 'class' => QuantityType\Density::class],

        // SI named unit dimensions
        'T-1'         => ['quantity' => 'frequency', 'siUnit' => 'Hz', 'class' => null],
        'T-2LM'       => ['quantity' => 'force', 'siUnit' => 'N', 'class' => QuantityType\Force::class],
        'T-2L-1M'     => ['quantity' => 'pressure', 'siUnit' => 'Pa', 'class' => QuantityType\Pressure::class],
        'T-2L2M'      => ['quantity' => 'energy', 'siUnit' => 'J', 'class' => QuantityType\Energy::class],
        'T-3L2M'      => ['quantity' => 'power', 'siUnit' => 'W', 'class' => QuantityType\Power::class],
        'TI'          => ['quantity' => 'electric charge', 'siUnit' => 'C', 'class' => null],
        'T-3L2MI-1'   => ['quantity' => 'voltage', 'siUnit' => 'V', 'class' => null],
        'T4L-2M-1I2'  => ['quantity' => 'capacitance', 'siUnit' => 'F', 'class' => null],
        'T-3L2MI-2'   => ['quantity' => 'resistance', 'siUnit' => 'ohm', 'class' => null],
        'T3L-2M-1I2'  => ['quantity' => 'conductance', 'siUnit' => 'S', 'class' => null],
        'T-2L2MI-1'   => ['quantity' => 'magnetic flux', 'siUnit' => 'Wb', 'class' => null],
        'T-2MI-1'     => ['quantity' => 'magnetic flux density', 'siUnit' => 'T', 'class' => null],
        'T-2L2MI-2'   => ['quantity' => 'inductance', 'siUnit' => 'H', 'class' => null],
        'JA2'         => ['quantity' => 'luminous flux', 'siUnit' => 'lm', 'class' => null],
        'L-2JA2'      => ['quantity' => 'illuminance', 'siUnit' => 'lx', 'class' => null],
        'T-2L2'       => ['quantity' => 'absorbed dose', 'siUnit' => 'Gy', 'class' => null],
        'T-1N'        => ['quantity' => 'catalytic activity', 'siUnit' => 'kat', 'class' => null],

        // Non-SI dimensions
        'D'           => ['quantity' => 'data', 'siUnit' => 'B', 'class' => QuantityType\Data::class],
    ];

    /**
     * Get the quantity type info for a dimension code.
     *
     * @param string $dimension The dimension code (e.g., 'L', 'M', 'L2').
     * @return array{quantity: string, siUnit: string, class: class-string<Quantity>|null}|null The quantity type info,
     *     or null if not found.
     */
    public static function get(string $dimension): ?array
    {
        return self::$quantityTypes[$dimension] ?? null;
    }

    /**
     * Get all registered quantity types.
     *
     * @return array<string, array{quantity: string, siUnit: string, class: class-string<Quantity>|null}>
     */
    public static function getAll(): array
    {
        return self::$quantityTypes;
    }

    /**
     * Register a new quantity type for a dimension code.
     *
     * This allows Quantity::create() to instantiate the appropriate subclass based on dimensional analysis.
     * For example, when multiplying Length * Length, create() can return an Area object.
     *
     * @param string $dimension The dimension code (e.g., 'L', 'M', 'L2', 'LT-1').
     * @param string $name The name of the physical quantity (e.g., 'length', 'velocity').
     * @param string $siUnit The SI unit symbol for this quantity (e.g., 'm', 'm/s').
     * @param class-string<Quantity> $class The Quantity subclass to use for this dimension.
     * @throws ValueError If the class is not a subclass of Quantity.
     */
    public static function add(string $dimension, string $name, string $siUnit, string $class): void
    {
        // Validate the class.
        if (!is_subclass_of($class, Quantity::class)) {
            throw new ValueError("$class must be a subclass of " . Quantity::class . '.');
        }

        // Normalize the dimension code.
        $dimension = Dimensions::normalize($dimension);

        // Register the quantity type.
        self::$quantityTypes[$dimension] = [
            'quantity' => $name,
            'siUnit' => $siUnit,
            'class' => $class,
        ];
    }

    /**
     * Set or update the class for an existing quantity type.
     *
     * Use this to override the default class for a dimension, or to add a class to a quantity type that doesn't have
     * one.
     *
     * @param string $dimension The dimension code (e.g., 'L', 'M', 'L2').
     * @param class-string<Quantity> $class The Quantity subclass to use for this dimension.
     * @throws ValueError If the dimension is not registered, or the class is invalid.
     */
    public static function setClass(string $dimension, string $class): void
    {
        // Check the dimension exists.
        if (!array_key_exists($dimension, self::$quantityTypes)) {
            throw new ValueError("Quantity type with dimension '$dimension' not found. Use add() to register a new quantity type.");
        }

        // Validate the class.
        if (!is_subclass_of($class, Quantity::class)) {
            throw new ValueError("$class must be a subclass of " . Quantity::class . '.');
        }

        self::$quantityTypes[$dimension]['class'] = $class;
    }

    /**
     * Find the dimension code for a given Quantity subclass.
     *
     * @param class-string<Quantity> $class The Quantity subclass to look up.
     * @return string|null The dimension code, or null if the class is not registered.
     */
    public static function getDimensionByClass(string $class): ?string
    {
        return array_find_key(self::$quantityTypes, fn($data) => isset($data['class']) && $data['class'] === $class);
    }
}
