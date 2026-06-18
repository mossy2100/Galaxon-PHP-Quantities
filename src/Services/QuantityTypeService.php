<?php

declare(strict_types=1);

namespace OceanMoon\Quantities\Services;

use DomainException;
use LogicException;
use OceanMoon\Core\Exceptions\FormatException;
use OceanMoon\Quantities\Internal\QuantityType;
use OceanMoon\Quantities\Quantity;
use OceanMoon\Quantities\QuantityType\Acceleration;
use OceanMoon\Quantities\QuantityType\AmountOfSubstance;
use OceanMoon\Quantities\QuantityType\Angle;
use OceanMoon\Quantities\QuantityType\Area;
use OceanMoon\Quantities\QuantityType\Capacitance;
use OceanMoon\Quantities\QuantityType\CatalyticActivity;
use OceanMoon\Quantities\QuantityType\Conductance;
use OceanMoon\Quantities\QuantityType\Data;
use OceanMoon\Quantities\QuantityType\Density;
use OceanMoon\Quantities\QuantityType\Dimensionless;
use OceanMoon\Quantities\QuantityType\ElectricCharge;
use OceanMoon\Quantities\QuantityType\ElectricCurrent;
use OceanMoon\Quantities\QuantityType\Energy;
use OceanMoon\Quantities\QuantityType\Force;
use OceanMoon\Quantities\QuantityType\Frequency;
use OceanMoon\Quantities\QuantityType\Illuminance;
use OceanMoon\Quantities\QuantityType\Inductance;
use OceanMoon\Quantities\QuantityType\Length;
use OceanMoon\Quantities\QuantityType\LuminousFlux;
use OceanMoon\Quantities\QuantityType\LuminousIntensity;
use OceanMoon\Quantities\QuantityType\MagneticFlux;
use OceanMoon\Quantities\QuantityType\MagneticFluxDensity;
use OceanMoon\Quantities\QuantityType\Mass;
use OceanMoon\Quantities\QuantityType\Money;
use OceanMoon\Quantities\QuantityType\Power;
use OceanMoon\Quantities\QuantityType\Pressure;
use OceanMoon\Quantities\QuantityType\RadiationDose;
use OceanMoon\Quantities\QuantityType\Resistance;
use OceanMoon\Quantities\QuantityType\SolidAngle;
use OceanMoon\Quantities\QuantityType\Temperature;
use OceanMoon\Quantities\QuantityType\Time;
use OceanMoon\Quantities\QuantityType\Velocity;
use OceanMoon\Quantities\QuantityType\Voltage;
use OceanMoon\Quantities\QuantityType\Volume;

/**
 * Registry of quantity types keyed by dimension code.
 *
 * Provides mapping between dimension codes (e.g. 'L', 'M', 'L2') and their associated quantity information including
 * name and PHP class.
 */
class QuantityTypeService
{
    // region Private constants

    /**
     * Default (built-in) quantity types keyed by name (e.g. 'time', 'length').
     *
     * Each entry contains:
     * - 'dimension': The dimension of the physical quantity
     * - 'class': The QuantityType class (if one exists)
     *
     * @see DimensionService
     */
    private const array DEFAULT_QUANTITY_TYPES = [
        // Dimensionless
        'dimensionless'         => [
            'dimension' => '',
            'class'     => Dimensionless::class,
        ],

        // Base dimensions
        'length'                => [
            'dimension' => 'L',
            'class'     => Length::class,
        ],
        'mass'                  => [
            'dimension' => 'M',
            'class'     => Mass::class,
        ],
        'time'                  => [
            'dimension' => 'T',
            'class'     => Time::class,
        ],
        'electric current'      => [
            'dimension' => 'I',
            'class'     => ElectricCurrent::class,
        ],
        'temperature'           => [
            'dimension' => 'H',
            'class'     => Temperature::class,
        ],
        'amount of substance'   => [
            'dimension' => 'N',
            'class'     => AmountOfSubstance::class,
        ],
        'luminous intensity'    => [
            'dimension' => 'J',
            'class'     => LuminousIntensity::class,
        ],
        'angle'                 => [
            'dimension' => 'A',
            'class'     => Angle::class,
        ],
        'data'                  => [
            'dimension' => 'D',
            'class'     => Data::class,
        ],
        'money'                 => [
            'dimension' => 'C',
            'class'     => Money::class,
        ],

        // Compound unit dimensions
        'solid angle'           => [
            'dimension' => 'A2',
            'class'     => SolidAngle::class,
        ],
        'area'                  => [
            'dimension' => 'L2',
            'class'     => Area::class,
        ],
        'volume'                => [
            'dimension' => 'L3',
            'class'     => Volume::class,
        ],
        'velocity'              => [
            'dimension' => 'T-1L',
            'class'     => Velocity::class,
        ],
        'acceleration'          => [
            'dimension' => 'T-2L',
            'class'     => Acceleration::class,
        ],
        'density'               => [
            'dimension' => 'L-3M',
            'class'     => Density::class,
        ],
        'frequency'             => [
            'dimension' => 'T-1',
            'class'     => Frequency::class,
        ],
        'force'                 => [
            'dimension' => 'T-2LM',
            'class'     => Force::class,
        ],
        'pressure'              => [
            'dimension' => 'T-2L-1M',
            'class'     => Pressure::class,
        ],
        'energy'                => [
            'dimension' => 'T-2L2M',
            'class'     => Energy::class,
        ],
        'power'                 => [
            'dimension' => 'T-3L2M',
            'class'     => Power::class,
        ],
        'electric charge'       => [
            'dimension' => 'TI',
            'class'     => ElectricCharge::class,
        ],
        'voltage'               => [
            'dimension' => 'T-3L2MI-1',
            'class'     => Voltage::class,
        ],
        'capacitance'           => [
            'dimension' => 'T4L-2M-1I2',
            'class'     => Capacitance::class,
        ],
        'resistance'            => [
            'dimension' => 'T-3L2MI-2',
            'class'     => Resistance::class,
        ],
        'conductance'           => [
            'dimension' => 'T3L-2M-1I2',
            'class'     => Conductance::class,
        ],
        'magnetic flux'         => [
            'dimension' => 'T-2L2MI-1',
            'class'     => MagneticFlux::class,
        ],
        'magnetic flux density' => [
            'dimension' => 'T-2MI-1',
            'class'     => MagneticFluxDensity::class,
        ],
        'inductance'            => [
            'dimension' => 'T-2L2MI-2',
            'class'     => Inductance::class,
        ],
        'luminous flux'         => [
            'dimension' => 'JA2',
            'class'     => LuminousFlux::class,
        ],
        'illuminance'           => [
            'dimension' => 'L-2JA2',
            'class'     => Illuminance::class,
        ],
        'absorbed dose'         => [
            'dimension' => 'T-2L2',
            'class'     => RadiationDose::class,
        ],
        'catalytic activity'    => [
            'dimension' => 'T-1N',
            'class'     => CatalyticActivity::class,
        ],
    ];

    // endregion

    // region Private static properties

    /**
     * All known/supported quantity types, including defaults and custom. Keyed by name.
     *
     * @var ?array<string, QuantityType>
     */
    private static ?array $quantityTypes = null;

    // endregion

    // region Lookup methods

    /**
     * Get all registered quantity types.
     *
     * @return array<string, QuantityType>
     */
    public static function getAll(): array
    {
        self::init();
        assert(self::$quantityTypes !== null);

        return self::$quantityTypes;
    }

    /**
     * Get the quantity type matching a given dimension code.
     *
     * @param string $dimension The dimension code (e.g. 'L', 'M', 'L2').
     * @return ?QuantityType The quantity type, or null if not found.
     * @throws FormatException If the dimension code is invalid.
     */
    public static function getByDimension(string $dimension): ?QuantityType
    {
        self::init();
        assert(self::$quantityTypes !== null);

        $dimension = DimensionService::normalize($dimension);

        return array_find(
            self::$quantityTypes,
            static fn (QuantityType $qtyType): bool => $qtyType->dimension === $dimension
        );
    }

    /**
     * Get the quantity type matching a given name.
     *
     * @param string $name The quantity type name, e.g. 'conductance'.
     * @return ?QuantityType The quantity type, or null if not found.
     */
    public static function getByName(string $name): ?QuantityType
    {
        self::init();
        assert(self::$quantityTypes !== null);

        $name = strtolower($name);
        return self::$quantityTypes[$name] ?? null;
    }

    /**
     * Get the quantity type matching a given class.
     *
     * @param string $class The fully qualified class name.
     * @return ?QuantityType The quantity type, or null if not found.
     */
    public static function getByClass(string $class): ?QuantityType
    {
        self::init();
        assert(self::$quantityTypes !== null);

        return array_find(
            self::$quantityTypes,
            static fn (QuantityType $qtyType): bool => $qtyType->class === $class
        );
    }

    /**
     * Get all the registered quantity type classes.
     *
     * @return list<class-string<Quantity>> The list of classes.
     */
    public static function getClasses(): array
    {
        self::init();
        assert(self::$quantityTypes !== null);

        $classes = [];
        foreach (self::$quantityTypes as $quantityType) {
            $classes[] = $quantityType->class;
        }
        return $classes;
    }

    // endregion

    // region Registry methods

    /**
     * Reset the registry to its initial state.
     *
     * Clears all cached quantity types, forcing re-initialization from the constant on next access.
     * Useful for test isolation.
     */
    public static function reset(): void
    {
        self::$quantityTypes = null;
    }

    /**
     * Set or update the class for an existing quantity type.
     *
     * Use this to override the default class for a quantity type or to add a class to a quantity type that doesn't have
     * one.
     *
     * @param string $name The quantity type name.
     * @param class-string<Quantity> $class The Quantity subclass to use for this dimension.
     * @throws DomainException If the name is not registered.
     */
    public static function setClass(string $name, string $class): void
    {
        self::init();

        // Normalize argument.
        $name = strtolower($name);

        // Check we have a quantity type with the specified name.
        $qt = self::$quantityTypes[$name] ?? null;
        if ($qt === null) {
            throw new DomainException("Unknown quantity type: '$name'.");
        }

        // Update the class.
        $qt->class = $class;
    }

    /**
     * Register a new quantity type for a dimension code.
     *
     * This allows Quantity::create() to instantiate the appropriate subclass based on dimensional analysis.
     * For example, when multiplying Length * Length, create() can return an Area object.
     *
     * @param string $name The name of the physical quantity (e.g. 'length', 'velocity').
     * @param string $dimension The dimension code (e.g. 'L', 'M', 'L2', 'LT-1').
     * @param class-string<Quantity> $class The Quantity subclass to use for this dimension.
     * @throws FormatException If the dimension code is invalid.
     * @throws LogicException If a name, dimension, or class is provided that conflicts with an existing quantity type.
     */
    public static function add(string $name, string $dimension, string $class): void
    {
        self::init();

        // Normalize arguments.
        $name = strtolower($name);
        $dimension = DimensionService::normalize($dimension);

        // Check name is unique.
        $qt = self::getByName($name);
        if ($qt !== null) {
            throw new LogicException("Cannot add another quantity type with the name '$name'.");
        }

        // Check dimension is unique.
        $qt = self::getByDimension($dimension);
        if ($qt !== null) {
            throw new LogicException("Cannot add another quantity type with the dimension '$dimension'.");
        }

        // Check class is unique.
        $qt = self::getByClass($class);
        if ($qt !== null) {
            throw new LogicException("Cannot add another quantity type with the class '$class'.");
        }

        // Add the new quantity type.
        $qtyType = new QuantityType($name, $dimension, $class);
        self::$quantityTypes[$name] = $qtyType;
    }

    /**
     * Remove a quantity type from the registry.
     *
     * @param string $name The quantity type name.
     */
    public static function remove(string $name): void
    {
        // If the registry is not initialized yet, do nothing.
        if (self::$quantityTypes === null) {
            return;
        }

        unset(self::$quantityTypes[$name]);
    }

    /**
     * Remove all quantity types from the registry.
     *
     * This will NOT trigger re-initialization from the constant.
     * The array would have to be manually rebuilt using init() or add().
     */
    public static function removeAll(): void
    {
        self::$quantityTypes = [];
    }

    // endregion

    // region Helper methods

    /**
     * Initialize the quantity types array from the constant.
     *
     * This is called lazily on first access.
     */
    private static function init(): void
    {
        if (self::$quantityTypes === null) {
            self::$quantityTypes = [];

            // Convert info in constant into an array of objects.
            foreach (self::DEFAULT_QUANTITY_TYPES as $name => $info) {
                self::$quantityTypes[$name] = new QuantityType($name, $info['dimension'], $info['class']);
            }
        }
    }

    // endregion
}
