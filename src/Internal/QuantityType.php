<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Internal;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\DimensionService;
use Galaxon\Quantities\Services\UnitService;
use Galaxon\Quantities\UnitSystem;
use InvalidArgumentException;

/**
 * Represents a quantity type.
 */
class QuantityType
{
    // region Properties

    /**
     * The human-readable name of the quantity type (e.g. 'length', 'force').
     */
    public readonly string $name;

    /**
     * The normalized dimension code (e.g. 'L', 'M', 'T-2LM').
     *
     * @see DimensionService
     */
    public readonly string $dimension;

    // endregion

    // region Property hooks

    /**
     * The fully-qualified class name of the Quantity subclass for this quantity type.
     *
     * @var class-string<Quantity>
     * @throws InvalidArgumentException If the value is not a subclass of Quantity.
     */
    public string $class {
        set {
            // Validate that the value is a subclass of Quantity.
            if (!is_subclass_of($value, Quantity::class)) {
                throw new InvalidArgumentException("$value must be a subclass of " . Quantity::class . '.');
            }

            /** @var class-string<Quantity> $value */
            $this->class = $value;
        }
    }

    /**
     * The default unit symbols for part decomposition (e.g. ['deg', 'arcmin', 'arcsec'] for angles).
     *
     * @var ?list<string>
     * @throws DomainException If the array is empty.
     * @throws InvalidArgumentException If the array contains non-string values.
     */
    public ?array $partUnitSymbols = null {
        set {
            if ($value !== null) {
                // Check value is not empty.
                if (empty($value)) {
                    throw new DomainException('The array of part unit symbols must not be empty.');
                }

                // Check all the symbols are strings.
                foreach ($value as $symbol) {
                    if (!is_string($symbol)) {
                        throw new InvalidArgumentException('The array of part unit symbols must contain only strings.');
                    }
                }

                // NB: We don't check the units are valid at this time because we want to be able to set the defaults
                // before those units are loaded.

                $value = array_values(array_unique($value));
            }

            $this->partUnitSymbols = $value;
        }
    }

    /**
     * The default result unit symbol for part operations (e.g. 'deg' for angles).
     *
     * @throws DomainException If the value is an empty string.
     */
    public ?string $resultUnitSymbol = null {
        set {
            // Check the value is not an empty string.
            if ($value === '') {
                throw new DomainException('The result unit symbol must be null or a unit symbol.');
            }

            // NB: We don't check the unit is valid at this time, because we want to be able to set the default
            // before the unit is loaded.

            $this->resultUnitSymbol = $value;
        }
    }

    /**
     * The default unit definitions for this quantity type.
     *
     * @var array<string, array{
     *      asciiSymbol: string,
     *      unicodeSymbol?: string,
     *      prefixGroup?: int,
     *      alternateSymbol?: string,
     *      systems: list<UnitSystem>
     *  }>
     */
    private(set) array $unitDefinitions {
        get {
            // Return the cached value if it exists.
            if (isset($this->unitDefinitions)) {
                return $this->unitDefinitions;
            }

            // Load the unit definitions from the Quantity subclass.
            $unitDefinitions = $this->class::getUnitDefinitions();

            // Inject the dimension code into each unit definition.
            foreach ($unitDefinitions as $unitName => $definition) {
                $unitDefinitions[$unitName]['dimension'] = $this->dimension;
            }

            // Cache the value and return it.
            $this->unitDefinitions = $unitDefinitions;
            return $this->unitDefinitions;
        }
    }

    /**
     * The default conversion definitions for this quantity type.
     *
     * @var list<array{string, string, float}>
     */
    public array $conversionDefinitions {
        get => $this->class::getConversionDefinitions();
    }

    /**
     * The units compatible with this quantity type.
     *
     * @var list<Unit>
     */
    public array $units {
        get => UnitService::getByQuantityType($this);
    }

    /**
     * The converter for this quantity type.
     */
    public Converter $converter {
        get => Converter::getInstance($this->dimension);
    }

    // endregion

    // region Constructor

    /**
     * Create a new QuantityType instance.
     *
     * @param string $name The human-readable name.
     * @param string $dimension The dimension code.
     * @param class-string<Quantity> $class The Quantity subclass.
     * @param ?list<string> $partUnitSymbols The default part unit symbols.
     * @param ?string $resultUnitSymbol The default result unit symbol.
     * @throws FormatException If the dimension code is invalid.
     * @throws InvalidArgumentException If the class is not a subclass of Quantity, or partUnitSymbols contains
     * non-string values.
     * @throws DomainException If partUnitSymbols is empty, or resultUnitSymbol is an empty string.
     */
    public function __construct(
        string $name,
        string $dimension,
        string $class,
        ?array $partUnitSymbols = null,
        ?string $resultUnitSymbol = null
    ) {
        $this->name = $name;
        $this->dimension = DimensionService::normalize($dimension);
        $this->class = $class;
        $this->partUnitSymbols = $partUnitSymbols;
        $this->resultUnitSymbol = $resultUnitSymbol;
    }

    // endregion
}
