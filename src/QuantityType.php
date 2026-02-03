<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use DomainException;
use Galaxon\Quantities\Utility\DimensionUtility;

class QuantityType
{
    // region Properties

    public readonly string $name;

    public readonly string $dimension;

    public readonly string $siUnitSymbol;

    // endregion

    // region Property hooks

    /** @var ?class-string<Quantity> */
    public ?string $class {
        set {
            // Validate the class.
            if ($value !== null && !is_subclass_of($value, Quantity::class)) {
                throw new DomainException("$value must be a subclass of " . Quantity::class . '.');
            }

            /** @var class-string<Quantity> $value */
            $this->class = $value;
        }
    }

    // endregion

    // region Constructor

    /**
     * @param string $name
     * @param string $dimension
     * @param string $siUnitSymbol
     * @param ?class-string<Quantity> $class
     * @throws DomainException
     */
    public function __construct(string $name, string $dimension, string $siUnitSymbol, ?string $class = null)
    {
        $this->name = $name;
        $this->dimension = DimensionUtility::normalize($dimension);
        $this->siUnitSymbol = $siUnitSymbol;
        $this->class = $class;
    }

    // endregion
}
