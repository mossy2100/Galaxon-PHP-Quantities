<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Internal;

use DomainException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\DimensionService;
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
     */
    public ?array $partUnitSymbols = null {
        set {
            if ($value !== null) {
                if (empty($value)) {
                    throw new DomainException('The array of part unit symbols must not be empty.');
                }

                foreach ($value as $symbol) {
                    if (!is_string($symbol)) {
                        throw new InvalidArgumentException(
                            'The array of part unit symbols must contain only strings.'
                        );
                    }
                }

                $value = array_values(array_unique($value));
            }

            $this->partUnitSymbols = $value;
        }
    }

    /**
     * The default result unit symbol for part operations (e.g. 'deg' for angles).
     */
    public ?string $resultUnitSymbol = null {
        set {
            if ($value !== null && $value === '') {
                throw new DomainException('The result unit symbol must not be empty.');
            }

            $this->resultUnitSymbol = $value;
        }
    }

    // endregion

    // region Constructor

    /**
     * @param string $name The human-readable name.
     * @param string $dimension The dimension code.
     * @param class-string<Quantity> $class The Quantity subclass.
     * @param ?list<string> $partUnitSymbols The default part unit symbols.
     * @param ?string $resultUnitSymbol The default result unit symbol.
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
