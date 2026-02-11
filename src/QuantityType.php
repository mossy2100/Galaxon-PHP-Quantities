<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use DomainException;

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
     * @see Dimensions
     */
    public readonly string $dimension;

    // endregion

    // region Property hooks

    /**
     * The fully-qualified Quantity subclass for this type, or null if none is registered.
     *
     * The setter validates that the value is a subclass of Quantity.
     *
     * @var ?class-string<Quantity>
     */
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
     * @param ?class-string<Quantity> $class
     */
    public function __construct(string $name, string $dimension, ?string $class = null)
    {
        $this->name = $name;
        $this->dimension = Dimensions::normalize($dimension);
        $this->class = $class;
    }

    // endregion
}
