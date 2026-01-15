<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use DomainException;

/**
 * Registry of quantity types keyed by dimension code.
 *
 * Provides mapping between dimension codes (e.g. 'L', 'M', 'L2') and their associated quantity information including
 * name, SI unit, and (optional) PHP class.
 */
class QuantityTypes
{
    /**
     * Quantity types keyed by dimension code.
     *
     * Each entry contains:
     * - 'quantityType': The name of the physical quantity
     * - 'siUnit': The SI unit symbol for this quantity
     * - 'class': The QuantityType class (if one exists), or null
     *
     * @var array<string, array{quantityType: string, siUnit: string, class: class-string<Quantity>|null}>
     */
    private static array $quantityTypes = [
        // SI base dimensions
        'L'          => [
            'quantityType' => 'length',
            'siUnit'       => 'm',
            'class'        => QuantityType\Length::class,
        ],
        'M'          => [
            'quantityType' => 'mass',
            'siUnit'       => 'kg',
            'class'        => QuantityType\Mass::class,
        ],
        'T'          => [
            'quantityType' => 'time',
            'siUnit'       => 's',
            'class'        => QuantityType\Time::class,
        ],
        'I'          => [
            'quantityType' => 'electric current',
            'siUnit'       => 'A',
            'class'        => QuantityType\ElectricCurrent::class,
        ],
        'H'          => [
            'quantityType' => 'temperature',
            'siUnit'       => 'K',
            'class'        => QuantityType\Temperature::class,
        ],
        'N'          => [
            'quantityType' => 'amount of substance',
            'siUnit'       => 'mol',
            'class'        => QuantityType\AmountOfSubstance::class,
        ],
        'J'          => [
            'quantityType' => 'luminous intensity',
            'siUnit'       => 'cd',
            'class'        => QuantityType\LuminousIntensity::class,
        ],

        // SI derived unit dimensions
        'A'          => [
            'quantityType' => 'angle',
            'siUnit'       => 'rad',
            'class'        => QuantityType\Angle::class,
        ],
        'A2'         => [
            'quantityType' => 'solid angle',
            'siUnit'       => 'sr',
            'class'        => QuantityType\SolidAngle::class,
        ],
        'L2'         => [
            'quantityType' => 'area',
            'siUnit'       => 'm2',
            'class'        => QuantityType\Area::class,
        ],
        'L3'         => [
            'quantityType' => 'volume',
            'siUnit'       => 'm3',
            'class'        => QuantityType\Volume::class,
        ],
        'T-1L'       => [
            'quantityType' => 'velocity',
            'siUnit'       => 'm/s',
            'class'        => QuantityType\Velocity::class,
        ],
        'T-2L'       => [
            'quantityType' => 'acceleration',
            'siUnit'       => 'm/s2',
            'class'        => QuantityType\Acceleration::class,
        ],
        'L-3M'       => [
            'quantityType' => 'density',
            'siUnit'       => 'kg/m3',
            'class'        => QuantityType\Density::class,
        ],

        // SI named unit dimensions
        'T-1'        => [
            'quantityType' => 'frequency',
            'siUnit'       => 'Hz',
            'class'        => QuantityType\Frequency::class,
        ],
        'T-2LM'      => [
            'quantityType' => 'force',
            'siUnit'       => 'N',
            'class'        => QuantityType\Force::class,
        ],
        'T-2L-1M'    => [
            'quantityType' => 'pressure',
            'siUnit'       => 'Pa',
            'class'        => QuantityType\Pressure::class,
        ],
        'T-2L2M'     => [
            'quantityType' => 'energy',
            'siUnit'       => 'J',
            'class'        => QuantityType\Energy::class,
        ],
        'T-3L2M'     => [
            'quantityType' => 'power',
            'siUnit'       => 'W',
            'class'        => QuantityType\Power::class,
        ],
        'TI'         => [
            'quantityType' => 'electric charge',
            'siUnit'       => 'C',
            'class'        => QuantityType\ElectricCharge::class,
        ],
        'T-3L2MI-1'  => [
            'quantityType' => 'voltage',
            'siUnit'       => 'V',
            'class'        => QuantityType\Voltage::class,
        ],
        'T4L-2M-1I2' => [
            'quantityType' => 'capacitance',
            'siUnit'       => 'F',
            'class'        => QuantityType\Capacitance::class,
        ],
        'T-3L2MI-2'  => [
            'quantityType' => 'resistance',
            'siUnit'       => 'ohm',
            'class'        => QuantityType\Resistance::class,
        ],
        'T3L-2M-1I2' => [
            'quantityType' => 'conductance',
            'siUnit'       => 'S',
            'class'        => QuantityType\Conductance::class,
        ],
        'T-2L2MI-1'  => [
            'quantityType' => 'magnetic flux',
            'siUnit'       => 'Wb',
            'class'        => QuantityType\MagneticFlux::class,
        ],
        'T-2MI-1'    => [
            'quantityType' => 'magnetic flux density',
            'siUnit'       => 'T',
            'class'        => QuantityType\MagneticFluxDensity::class,
        ],
        'T-2L2MI-2'  => [
            'quantityType' => 'inductance',
            'siUnit'       => 'H',
            'class'        => QuantityType\Inductance::class,
        ],
        'JA2'        => [
            'quantityType' => 'luminous flux',
            'siUnit'       => 'lm',
            'class'        => QuantityType\LuminousFlux::class,
        ],
        'L-2JA2'     => [
            'quantityType' => 'illuminance',
            'siUnit'       => 'lx',
            'class'        => QuantityType\Illuminance::class,
        ],
        'T-2L2'      => [
            'quantityType' => 'absorbed dose',
            'siUnit'       => 'Gy',
            'class'        => QuantityType\RadiationDose::class,
        ],
        'T-1N'       => [
            'quantityType' => 'catalytic activity',
            'siUnit'       => 'kat',
            'class'        => QuantityType\CatalyticActivity::class,
        ],

        // Non-SI dimensions
        'D'          => [
            'quantityType' => 'data',
            'siUnit'       => 'B',
            'class'        => QuantityType\Data::class,
        ],
    ];

    /**
     * Get the quantity type info for a dimension code.
     *
     * @param string $dimension The dimension code (e.g. 'L', 'M', 'L2').
     * @return array{quantityType: string, siUnit: string, class: class-string<Quantity>|null}|null The quantity type
     * info, or null if not found.
     */
    public static function get(string $dimension): ?array
    {
        return self::$quantityTypes[$dimension] ?? null;
    }

    /**
     * Get all registered quantity types.
     *
     * @return array<string, array{quantityType: string, siUnit: string, class: class-string<Quantity>|null}>
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
     * @param string $dimension The dimension code (e.g. 'L', 'M', 'L2', 'LT-1').
     * @param string $name The name of the physical quantity (e.g. 'length', 'velocity').
     * @param string $siUnit The SI unit symbol for this quantity (e.g. 'm', 'm/s').
     * @param class-string<Quantity> $class The Quantity subclass to use for this dimension.
     * @throws DomainException If the class is not a subclass of Quantity.
     */
    public static function add(string $dimension, string $name, string $siUnit, string $class): void
    {
        // Validate the class.
        if (!is_subclass_of($class, Quantity::class)) {
            throw new DomainException("$class must be a subclass of " . Quantity::class . '.');
        }

        // Normalize the dimension code.
        $dimension = Dimensions::normalize($dimension);

        // Register the quantity type.
        self::$quantityTypes[$dimension] = [
            'quantityType' => $name,
            'siUnit'       => $siUnit,
            'class'        => $class,
        ];
    }

    /**
     * Set or update the class for an existing quantity type.
     *
     * Use this to override the default class for a dimension, or to add a class to a quantity type that doesn't have
     * one.
     *
     * @param string $dimension The dimension code (e.g. 'L', 'M', 'L2').
     * @param class-string<Quantity> $class The Quantity subclass to use for this dimension.
     * @throws DomainException If the dimension is not registered, or the class is invalid.
     */
    public static function setClass(string $dimension, string $class): void
    {
        // Check the dimension exists.
        if (!array_key_exists($dimension, self::$quantityTypes)) {
            throw new DomainException(
                "Quantity type with dimension '$dimension' not found. Use add() to register a new quantity type."
            );
        }

        // Validate the class.
        if (!is_subclass_of($class, Quantity::class)) {
            throw new DomainException("$class must be a subclass of " . Quantity::class . '.');
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
        return array_find_key(
            self::$quantityTypes,
            static fn ($data) => isset($data['class']) && $data['class'] === $class
        );
    }
}
