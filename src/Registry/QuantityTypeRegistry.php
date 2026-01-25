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
     * - 'siUnit': The SI unit symbol for this quantity
     * - 'class': The QuantityType class (if one exists), or null
     *
     * @var array<string, array{quantityType: string, siUnit: string, class: class-string<Quantity>|null}>
     */
    private const array QUANTITY_TYPES = [
        // SI base dimensions
        'L'          => [
            'name'   => 'length',
            'siUnit' => 'm',
            'class'  => Length::class,
        ],
        'M'          => [
            'name'   => 'mass',
            'siUnit' => 'kg',
            'class'  => Mass::class,
        ],
        'T'          => [
            'name'   => 'time',
            'siUnit' => 's',
            'class'  => Time::class,
        ],
        'I'          => [
            'name'   => 'electric current',
            'siUnit' => 'A',
            'class'  => ElectricCurrent::class,
        ],
        'H'          => [
            'name'   => 'temperature',
            'siUnit' => 'K',
            'class'  => Temperature::class,
        ],
        'N'          => [
            'name'   => 'amount of substance',
            'siUnit' => 'mol',
            'class'  => AmountOfSubstance::class,
        ],
        'J'          => [
            'name'   => 'luminous intensity',
            'siUnit' => 'cd',
            'class'  => LuminousIntensity::class,
        ],

        // SI derived unit dimensions
        'A'          => [
            'name'   => 'angle',
            'siUnit' => 'rad',
            'class'  => Angle::class,
        ],
        'A2'         => [
            'name'   => 'solid angle',
            'siUnit' => 'sr',
            'class'  => SolidAngle::class,
        ],
        'L2'         => [
            'name'   => 'area',
            'siUnit' => 'm2',
            'class'  => Area::class,
        ],
        'L3'         => [
            'name'   => 'volume',
            'siUnit' => 'm3',
            'class'  => Volume::class,
        ],
        'T-1L'       => [
            'name'   => 'velocity',
            'siUnit' => 'm/s',
            'class'  => Velocity::class,
        ],
        'T-2L'       => [
            'name'   => 'acceleration',
            'siUnit' => 'm/s2',
            'class'  => Acceleration::class,
        ],
        'L-3M'       => [
            'name'   => 'density',
            'siUnit' => 'kg/m3',
            'class'  => Density::class,
        ],

        // SI named units
        'T-1'        => [
            'name'   => 'frequency',
            'siUnit' => 'Hz',
            'class'  => Frequency::class,
        ],
        'T-2LM'      => [
            'name'   => 'force',
            'siUnit' => 'N',
            'class'  => Force::class,
        ],
        'T-2L-1M'    => [
            'name'   => 'pressure',
            'siUnit' => 'Pa',
            'class'  => Pressure::class,
        ],
        'T-2L2M'     => [
            'name'   => 'energy',
            'siUnit' => 'J',
            'class'  => Energy::class,
        ],
        'T-3L2M'     => [
            'name'   => 'power',
            'siUnit' => 'W',
            'class'  => Power::class,
        ],
        'TI'         => [
            'name'   => 'electric charge',
            'siUnit' => 'C',
            'class'  => ElectricCharge::class,
        ],
        'T-3L2MI-1'  => [
            'name'   => 'voltage',
            'siUnit' => 'V',
            'class'  => Voltage::class,
        ],
        'T4L-2M-1I2' => [
            'name'   => 'capacitance',
            'siUnit' => 'F',
            'class'  => Capacitance::class,
        ],
        'T-3L2MI-2'  => [
            'name'   => 'resistance',
            'siUnit' => 'ohm',
            'class'  => Resistance::class,
        ],
        'T3L-2M-1I2' => [
            'name'   => 'conductance',
            'siUnit' => 'S',
            'class'  => Conductance::class,
        ],
        'T-2L2MI-1'  => [
            'name'   => 'magnetic flux',
            'siUnit' => 'Wb',
            'class'  => MagneticFlux::class,
        ],
        'T-2MI-1'    => [
            'name'   => 'magnetic flux density',
            'siUnit' => 'T',
            'class'  => MagneticFluxDensity::class,
        ],
        'T-2L2MI-2'  => [
            'name'   => 'inductance',
            'siUnit' => 'H',
            'class'  => Inductance::class,
        ],
        'JA2'        => [
            'name'   => 'luminous flux',
            'siUnit' => 'lm',
            'class'  => LuminousFlux::class,
        ],
        'L-2JA2'     => [
            'name'   => 'illuminance',
            'siUnit' => 'lx',
            'class'  => Illuminance::class,
        ],
        'T-2L2'      => [
            'name'   => 'absorbed dose',
            'siUnit' => 'Gy',
            'class'  => RadiationDose::class,
        ],
        'T-1N'       => [
            'name'   => 'catalytic activity',
            'siUnit' => 'kat',
            'class'  => CatalyticActivity::class,
        ],

        // Non-SI dimensions
        'D'          => [
            'name'   => 'data',
            'siUnit' => 'B',
            'class'  => Data::class,
        ],
    ];

    // endregion

    // region Static properties

    /**
     * All known/supported quantity types including defaults and custom.
     * Stored as an associative array with keys equal to dimension codes.
     *
     * @var ?array<string, QuantityType>
     */
    private static ?array $quantityTypes = null;

    // endregion

    // region Static methods


    /**
     * Initialize the quantity types array from the constant.
     *
     * This is called lazily on first access.
     */
    private static function init(): void
    {
        if (self::$quantityTypes === null) {
            self::$quantityTypes = [];

            // Convert info in constant into array of objects.
            foreach (self::QUANTITY_TYPES as $dimension => $info) {
                $dimension = DimensionRegistry::normalize($dimension);
                self::$quantityTypes[$dimension] =
                    new QuantityType($dimension, $info['name'], $info['siUnit'], $info['class']);
            }
        }
    }

    /**
     * Get all registered quantity types.
     *
     * @return array<string, QuantityType>
     */
    public static function getAll(): array
    {
        self::init();
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
        $dimension = DimensionRegistry::normalize($dimension);
        return self::$quantityTypes[$dimension] ?? null;
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
        return array_find(
            self::$quantityTypes,
            static fn (QuantityType $qtyType): bool => strtolower($qtyType->name) === strtolower($name)
        );
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
        return array_find(
            self::$quantityTypes,
            static fn (QuantityType $qtyType): bool => $qtyType->class === $class
        );
    }

    /**
     * Register a new quantity type for a dimension code.
     *
     * This allows Quantity::create() to instantiate the appropriate subclass based on dimensional analysis.
     * For example, when multiplying Length * Length, create() can return an Area object.
     *
     * @param string $dimension The dimension code (e.g. 'L', 'M', 'L2', 'LT-1').
     * @param string $name The name of the physical quantity (e.g. 'length', 'velocity').
     * @param string $siUnitSymbol The SI unit symbol for this quantity (e.g. 'm', 'm/s').
     * @param ?class-string<Quantity> $class The Quantity subclass to use for this dimension.
     */
    public static function add(string $dimension, string $name, string $siUnitSymbol, ?string $class): void
    {
        self::init();
        $dimension = DimensionRegistry::normalize($dimension);

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
        self::$quantityTypes[$dimension] = new QuantityType($dimension, $name, $siUnitSymbol, $class);
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
        self::init();

        // Check we have a quantity type with the specified dimension.
        $qt = self::getByDimension($dimension);
        if ($qt === null) {
            throw new DomainException(
                "Quantity type with dimension '$dimension' not found. Use add() to register a new quantity type."
            );
        }

        // Update the class.
        $qt->class = $class;
    }

    // endregion
}
