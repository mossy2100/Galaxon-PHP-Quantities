<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Internal;

use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\DimensionService;
use Galaxon\Quantities\Services\QuantityPartsService;
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
     * Delegates to QuantityPartsService.
     *
     * @var ?list<string>
     */
    public ?array $partUnitSymbols {
        get => QuantityPartsService::getPartUnitSymbols($this->name);
        set => QuantityPartsService::setPartUnitSymbols($this->name, $value);
    }

    /**
     * The default result unit symbol for part operations (e.g. 'deg' for angles).
     *
     * Delegates to QuantityPartsService.
     */
    public ?string $resultUnitSymbol {
        get => QuantityPartsService::getResultUnitSymbol($this->name);
        set => QuantityPartsService::setResultUnitSymbol($this->name, $value);
    }

    /**
     * The default unit definitions for this quantity type.
     *
     * @var array<string, array{
     *      asciiSymbol: string,
     *      unicodeSymbol?: string,
     *      prefixGroup?: int,
     *      alternateSymbol?: string,
     *      systems: list<UnitSystem>,
     *      dimension: string
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
     * @throws FormatException If the dimension code is invalid.
     * @throws InvalidArgumentException If the class is not a subclass of Quantity.
     */
    public function __construct(
        string $name,
        string $dimension,
        string $class,
    ) {
        $this->name = $name;
        $this->dimension = DimensionService::normalize($dimension);
        $this->class = $class;
    }

    // endregion
}
