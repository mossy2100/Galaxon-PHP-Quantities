<?php
declare(strict_types=1);

namespace Galaxon\Quantities;

use DomainException;
use Galaxon\Quantities\Registry\DimensionRegistry;

class QuantityType
{
    public readonly string $dimension;
    public readonly string $name;
    public readonly string $siUnitSymbol;

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

    public function __construct(string $dimension, string $name, string $siUnitSymbol, ?string $class = null)
    {
        $this->dimension = DimensionRegistry::normalize($dimension);
        $this->name = $name;
        $this->siUnitSymbol = $siUnitSymbol;
        $this->class = $class;
    }
}
