<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use DomainException;
use Galaxon\Quantities\Registry\DimensionRegistry;

class QuantityType
{
    // region Properties

    public readonly string $dimension;

    public readonly string $name;

    public readonly string $siUnitSymbol;

    // endregion

    // region Property hooks

    // phpcs:disable PSR2.Classes.PropertyDeclaration
    // phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact

    public ?string $class {
        set {
            // Validate the class.
            if ($value !== null && !is_subclass_of($value, Quantity::class)) {
                throw new DomainException("$value must be a subclass of " . Quantity::class . '.');
            }

            /** @var class-string $value */
            $this->class = $value;
        }
    }

    // phpcs:enable PSR2.Classes.PropertyDeclaration
    // phpcs:enable Generic.WhiteSpace.ScopeIndent.IncorrectExact

    // endregion

    // region Constructor

    public function __construct(string $dimension, string $name, string $siUnitSymbol, ?string $class = null)
    {
        $this->dimension = DimensionRegistry::normalize($dimension);
        $this->name = $name;
        $this->siUnitSymbol = $siUnitSymbol;
        $this->class = $class;
    }

    // endregion
}
