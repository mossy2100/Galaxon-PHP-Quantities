<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Registry;

use DomainException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType;
use Galaxon\Quantities\QuantityType\Acceleration;
use Galaxon\Quantities\QuantityType\AmountOfSubstance;
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\QuantityType\Area;
use Galaxon\Quantities\QuantityType\Capacitance;
use Galaxon\Quantities\QuantityType\CatalyticActivity;
use Galaxon\Quantities\QuantityType\Conductance;
use Galaxon\Quantities\QuantityType\Data;
use Galaxon\Quantities\QuantityType\Density;
use Galaxon\Quantities\QuantityType\Dimensionless;
use Galaxon\Quantities\QuantityType\ElectricCharge;
use Galaxon\Quantities\QuantityType\ElectricCurrent;
use Galaxon\Quantities\QuantityType\Energy;
use Galaxon\Quantities\QuantityType\Force;
use Galaxon\Quantities\QuantityType\Frequency;
use Galaxon\Quantities\QuantityType\Illuminance;
use Galaxon\Quantities\QuantityType\Inductance;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\LuminousFlux;
use Galaxon\Quantities\QuantityType\LuminousIntensity;
use Galaxon\Quantities\QuantityType\MagneticFlux;
use Galaxon\Quantities\QuantityType\MagneticFluxDensity;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Power;
use Galaxon\Quantities\QuantityType\Pressure;
use Galaxon\Quantities\QuantityType\RadiationDose;
use Galaxon\Quantities\QuantityType\Resistance;
use Galaxon\Quantities\QuantityType\SolidAngle;
use Galaxon\Quantities\QuantityType\Temperature;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\QuantityType\Velocity;
use Galaxon\Quantities\QuantityType\Voltage;
use Galaxon\Quantities\QuantityType\Volume;
use Galaxon\Quantities\Utility\DimensionUtility;
use LogicException;

/**
 * Registry of quantity types keyed by dimension code.
 *
 * Provides mapping between dimension codes (e.g. 'L', 'M', 'L2') and their associated quantity information including
 * name, SI unit, and (optional) PHP class.
 */
class QuantityTypeRegistry
{
    // region Constants

    /**
     * Quantity types keyed by dimension code.
     *
     * Each entry contains:
     * - 'name': The name of the physical quantity
     * - 'siUnitSymbol': The SI unit symbol for this quantity
     * - 'class': The QuantityType class (if one exists)
     *
     * @var array<string, array{dimension: string, siUnitSymbol: string, class?: class-string<Quantity>}>
     */
    private const array QUANTITY_TYPES = [
        // Dimensionless
        'dimensionless'         => [
            'dimension'    => '1',
            'siUnitSymbol' => '',
            'class'        => Dimensionless::class,
        ],

        // SI base dimensions
        'length'                => [
            'dimension'    => 'L',
            'siUnitSymbol' => 'm',
            'class'        => Length::class,
        ],
        'mass'                  => [
            'dimension'    => 'M',
            'siUnitSymbol' => 'kg',
            'class'        => Mass::class,
        ],
        'time'                  => [
            'dimension'    => 'T',
            'siUnitSymbol' => 's',
            'class'        => Time::class,
        ],
        'electric current'      => [
            'dimension'    => 'I',
            'siUnitSymbol' => 'A',
            'class'        => ElectricCurrent::class,
        ],
        'temperature'           => [
            'dimension'    => 'H',
            'siUnitSymbol' => 'K',
            'class'        => Temperature::class,
        ],
        'amount of substance'   => [
            'dimension'    => 'N',
            'siUnitSymbol' => 'mol',
            'class'        => AmountOfSubstance::class,
        ],
        'luminous intensity'    => [
            'dimension'    => 'J',
            'siUnitSymbol' => 'cd',
            'class'        => LuminousIntensity::class,
        ],

        // SI derived unit dimensions
        'angle'                 => [
            'dimension'    => 'A',
            'siUnitSymbol' => 'rad',
            'class'        => Angle::class,
        ],
        'solid angle'           => [
            'dimension'    => 'A2',
            'siUnitSymbol' => 'sr',
            'class'        => SolidAngle::class,
        ],
        'area'                  => [
            'dimension'    => 'L2',
            'siUnitSymbol' => 'm2',
            'class'        => Area::class,
        ],
        'volume'                => [
            'dimension'    => 'L3',
            'siUnitSymbol' => 'm3',
            'class'        => Volume::class,
        ],
        'velocity'              => [
            'dimension'    => 'T-1L',
            'siUnitSymbol' => 'm/s',
            'class'        => Velocity::class,
        ],
        'acceleration'          => [
            'dimension'    => 'T-2L',
            'siUnitSymbol' => 'm/s2',
            'class'        => Acceleration::class,
        ],
        'density'               => [
            'dimension'    => 'L-3M',
            'siUnitSymbol' => 'kg/m3',
            'class'        => Density::class,
        ],

        // SI named units
        'frequency'             => [
            'dimension'    => 'T-1',
            'siUnitSymbol' => 'Hz',
            'class'        => Frequency::class,
        ],
        'force'                 => [
            'dimension'    => 'T-2LM',
            'siUnitSymbol' => 'N',
            'class'        => Force::class,
        ],
        'pressure'              => [
            'dimension'    => 'T-2L-1M',
            'siUnitSymbol' => 'Pa',
            'class'        => Pressure::class,
        ],
        'energy'                => [
            'dimension'    => 'T-2L2M',
            'siUnitSymbol' => 'J',
            'class'        => Energy::class,
        ],
        'power'                 => [
            'dimension'    => 'T-3L2M',
            'siUnitSymbol' => 'W',
            'class'        => Power::class,
        ],
        'electric charge'       => [
            'dimension'    => 'TI',
            'siUnitSymbol' => 'C',
            'class'        => ElectricCharge::class,
        ],
        'voltage'               => [
            'dimension'    => 'T-3L2MI-1',
            'siUnitSymbol' => 'V',
            'class'        => Voltage::class,
        ],
        'capacitance'           => [
            'dimension'    => 'T4L-2M-1I2',
            'siUnitSymbol' => 'F',
            'class'        => Capacitance::class,
        ],
        'resistance'            => [
            'dimension'    => 'T-3L2MI-2',
            'siUnitSymbol' => 'ohm',
            'class'        => Resistance::class,
        ],
        'conductance'           => [
            'dimension'    => 'T3L-2M-1I2',
            'siUnitSymbol' => 'S',
            'class'        => Conductance::class,
        ],
        'magnetic flux'         => [
            'dimension'    => 'T-2L2MI-1',
            'siUnitSymbol' => 'Wb',
            'class'        => MagneticFlux::class,
        ],
        'magnetic flux density' => [
            'dimension'    => 'T-2MI-1',
            'siUnitSymbol' => 'T',
            'class'        => MagneticFluxDensity::class,
        ],
        'inductance'            => [
            'dimension'    => 'T-2L2MI-2',
            'siUnitSymbol' => 'H',
            'class'        => Inductance::class,
        ],
        'luminous flux'         => [
            'dimension'    => 'JA2',
            'siUnitSymbol' => 'lm',
            'class'        => LuminousFlux::class,
        ],
        'illuminance'           => [
            'dimension'    => 'L-2JA2',
            'siUnitSymbol' => 'lx',
            'class'        => Illuminance::class,
        ],
        'absorbed dose'         => [
            'dimension'    => 'T-2L2',
            'siUnitSymbol' => 'Gy',
            'class'        => RadiationDose::class,
        ],
        'catalytic activity'    => [
            'dimension'    => 'T-1N',
            'siUnitSymbol' => 'kat',
            'class'        => CatalyticActivity::class,
        ],

        // Non-SI dimensions
        'data'                  => [
            'dimension'    => 'D',
            'siUnitSymbol' => 'B',
            'class'        => Data::class,
        ],
        'currency'              => [
            'dimension'    => 'C',
            'siUnitSymbol' => 'XAU',
        ],
    ];

    // endregion

    // region Static properties

    /**
     * All known/supported quantity types, including defaults and custom.
     * Stored as an associative array with keys equal to dimension codes.
     *
     * @var ?array<string, QuantityType>
     */
    private static ?array $quantityTypes = null;

    // endregion

    // region Static public methods

    /**
     * Reset the registry to its initial state.
     *
     * Clears all cached quantity types, forcing re-initialization from the constant on next access.
     * Primarily intended for test isolation.
     */
    public static function reset(): void
    {
        self::$quantityTypes = null;
    }

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
     */
    public static function getByDimension(string $dimension): ?QuantityType
    {
        self::init();
        assert(self::$quantityTypes !== null);

        $dimension = DimensionUtility::normalize($dimension);

        return array_find(
            self::$quantityTypes,
            static fn (QuantityType $qtyType): bool => strtolower($qtyType->dimension) === strtolower($dimension)
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
            if ($quantityType->class !== null) {
                $classes[] = $quantityType->class;
            }
        }
        return $classes;
    }

    /**
     * Register a new quantity type for a dimension code.
     *
     * This allows Quantity::create() to instantiate the appropriate subclass based on dimensional analysis.
     * For example, when multiplying Length * Length, create() can return an Area object.
     *
     * @param string $name The name of the physical quantity (e.g. 'length', 'velocity').
     * @param string $dimension The dimension code (e.g. 'L', 'M', 'L2', 'LT-1').
     * @param string $siUnitSymbol The SI unit symbol for this quantity (e.g. 'm', 'm/s').
     * @param ?class-string<Quantity> $class The Quantity subclass to use for this dimension.
     */
    public static function add(string $name, string $dimension, string $siUnitSymbol, ?string $class): void
    {
        self::init();

        // Normalize arguments.
        $name = strtolower($name);
        $dimension = DimensionUtility::normalize($dimension);

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
        if ($class !== null) {
            $qt = self::getByClass($class);
            if ($qt !== null) {
                throw new LogicException("Cannot add another quantity type with the class '$class'.");
            }
        }

        // Add the new quantity type.
        self::$quantityTypes[$name] = new QuantityType($name, $dimension, $siUnitSymbol, $class);
    }

    /**
     * Set or update the class for an existing quantity type.
     *
     * Use this to override the default class for a quantity type or to add a class to a quantity type that doesn't have
     * one.
     *
     * @param string $name The quantity type name.
     * @param class-string<Quantity> $class The Quantity subclass to use for this dimension.
     * @throws DomainException If the dimension is not registered, or the class is invalid.
     */
    public static function setClass(string $name, string $class): void
    {
        self::init();

        // Normalize argument.
        $name = strtolower($name);

        // Check we have a quantity type with the specified name.
        $qt = self::$quantityTypes[$name] ?? null;
        if ($qt === null) {
            throw new DomainException("Quantity type '$name' not found. Use add() to register a new quantity type.");
        }

        // Update the class.
        $qt->class = $class;
    }

    // endregion

    // region Static private helper methods

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
            foreach (self::QUANTITY_TYPES as $name => $info) {
                $dimension = DimensionUtility::normalize($info['dimension']);
                self::$quantityTypes[$name] =
                    new QuantityType($name, $dimension, $info['siUnitSymbol'], $info['class'] ?? null);
            }
        }
    }

    // endregion
}
