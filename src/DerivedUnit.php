<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use Galaxon\Core\Integers;
use Stringable;
use ValueError;

class DerivedUnit implements Stringable
{
    /**
     * Array of unit terms the DerivedUnit comprises, keyed by dimension code (L, M, T, T-2LM, etc.).
     *
     * @var array<string, UnitTerm>
     */
    private(set) array $unitTerms = [];

    /**
     * Get the dimension code of the derived unit.
     *
     * @return string The dimension code.
     */
    public string $dimension {
        get {
            // Convert the unit terms to dimension codes.
            $dimCodes = [];
            foreach ($this->unitTerms as $unitTerm) {
                // Parse the dimension code terms for this unit term.
                $dims = Dimensions::parse($unitTerm->dimension);
                foreach ($dims as $dimCode => $exp) {
                    $dimCodes[$dimCode] = ($dimCodes[$dimCode] ?? 0) + ($exp * $unitTerm->exponent);
                }
            }

            // Sort the dimension codes into canonical order.
            $dimCodes = Dimensions::sort($dimCodes);

            // Generate the full dimension code.
            return Dimensions::combine($dimCodes);
        }
    }

    /**
     * Construct a new DerivedUnit instance.
     *
     * @param null|BaseUnit|UnitTerm|list<UnitTerm> $unit The base unit, unit term, or array of unit terms to add, or
     * null to create an empty unit.
     */
    public function __construct(null|BaseUnit|UnitTerm|array $unit = null)
    {
        if ($unit === null) {
            return;
        }

        // If the unit is a Unit, wrap it in a UnitTerm.
        if ($unit instanceof BaseUnit) {
            $unit = new UnitTerm($unit);
        }

        // If the unit is a UnitTerm, just add it to the unit terms array.
        if ($unit instanceof UnitTerm) {
            $this->unitTerms[$unit->base->symbol] = $unit;
        }

        // Argument must be an array of UnitTerms.
        foreach ($unit as $unitTerm) {
            $this->addUnitTerm($unitTerm);
        }
    }

    /**
     * Parse a string into a new DerivedUnit instance, if possible.
     *
     * @param string $symbol
     * @return self The new instance.
     * @throws ValueError If the symbol format is invalid or if any units are unrecognized.
     */
    public static function parse(string $symbol): self
    {
        // Replace some characters to simplify parsing.
        $replacements = array_flip(Integers::SUPERSCRIPT_CHARACTERS);
        $replacements['·'] = '*';
        $replacements['.'] = '*';
        $replacements['^'] = '';
        $symbol = strtr($symbol, $replacements);

        // Get the parts of the compound unit.
        $parts = preg_split("/([*\/])/", $symbol, -1, PREG_SPLIT_DELIM_CAPTURE);
        if (empty($parts)) {
            throw new ValueError('Invalid format for compound unit. Expected format: <unit>[/<unit>]*, ' .
                                 "e.g. 'm', 'km', 'km2', 'm/s', 'm/s2', 'kg*m/s2', etc. To show an exponent, append " .
                                 "it to the unit, e.g. 'm2'. To show 'per unit', either use a divide sign or an " .
                                 "exponent of -1, e.g. metres per second can be expressed as 'm/s' or 'ms-1'.");
        }

        // Convert the substrings to unit terms.
        $unitTerms = [];
        $nParts = count($parts);
        for ($i = 0; $i < $nParts; $i += 2) {
            // Get the operator.
            $op = $i === 0 ? '*' : $parts[$i - 1];

            // Parse the unit term. This could throw a ValueError.
            $unit = UnitTerm::parse($parts[$i]);

            // Collect the unit terms.
            if ($op === '*') {
                $unitTerms[] = $unit;
            } else {
                $unitTerms[] = $unit->invert();
            }
        }

        // Create a new derived unit.
        return new self($unitTerms);
    }

//    public function mul(UnitTerm|DerivedUnit $unit): void
//    {
//        if ($unit instanceof UnitTerm) {
//            // Let's see if we already have a unit term with the same base unit and prefix.
//            $i = $this->findUnitTerm($unit->base, $unit->prefix);
//            if ($i !== null) {
//                // Add the exponent.
//                $this->unitTerms[$i]->exponent += $unit->exponent;
//            }
//            else {
//                // Add the new unit term.
//                $this->addUnitTerm($unit);
//            }
//        } else {
//            // $unit is a derived unit. Multiply each term.
//            foreach ($unit->unitTerms as $unitTerm) {
//                $this->mul($unitTerm);
//            }
//        }
//    }

//    public function div(UnitTerm|DerivedUnit $unit): void
//    {
//        if ($unit instanceof UnitTerm) {
//            // Let's see if we already have a unit term with the same base unit and prefix.
//            $i = $this->findUnitTerm($unit->base, $unit->prefix);
//            if ($i !== null) {
//                // Subtract the exponent.
//                $this->unitTerms[$i]->exponent -= $unit->exponent;
//            }
//            else {
//                // Add the new unit term, inverted because we're dividing it.
//                $this->addUnitTerm($unit->invert());
//            }
//        } else {
//            // $unit is a derived unit. Divide each term.
//            foreach ($unit->unitTerms as $unitTerm) {
//                $this->div($unitTerm);
//            }
//        }
//    }

    /**
     * Expand the derived unit by converting named units into their equivalents, e.g. N => kg*m/s2
     *
     * @return self The expanded unit.
     */
    public function expand(): self
    {
        // Create a new DerivedUnit object with no unit terms.
        $new = new self();

        foreach ($this->unitTerms as $unitTerm) {
            // If the current term has an equivalent, expand it.
            if ($unitTerm->base->equivalent !== null) {
                $du = self::parse($unitTerm->base->equivalent);
                $new->mul($du);
            } else {
                $new->mul($unitTerm);
            }
        }

        // Return the new object.
        return $new;
    }

    /**
     * Convert the derived unit to a string representation.
     *
     * @return string The derived unit symbol.
     */
    public function __toString(): string
    {
        $fn = static fn(UnitTerm $unit) => $unit->__toString();
        return implode('·', array_map($fn, $this->unitTerms));
    }

    /**
     * Check if there are any existing terms with the same base unit and prefix.
     *
     * @param BaseUnit $base
     * @param ?string $prefix
     * @return ?int
     */
    private function findUnitTerm(BaseUnit $base, ?string $prefix): ?int
    {
        return array_find_key($this->unitTerms, fn($unitTerm) => $unitTerm->prefix === $prefix &&
                                                                 $unitTerm->base->equal($base));
    }

    public function isSi(): bool {
        foreach ($this->unitTerms as $unitTerm) {
            if (!$unitTerm->isSi()) {
                return false;
            }
        }
        return true;
    }

    public function addUnitTerm(UnitTerm $unitTerm): void {
        $this->unitTerms[] = $unitTerm;
    }

    public static function toDerivedUnit(string|BaseUnit|UnitTerm|DerivedUnit $unit): DerivedUnit {
        // If the unit is already a DerivedUnit, return it as is.
        if ($unit instanceof self) {
            return $unit;
        }

        // If the unit is a string, parse it as a DerivedUnit.
        if (is_string($unit)) {
            return DerivedUnit::parse($unit);
        }

        // Otherwise, construct a new DerivedUnit.
        return new DerivedUnit($unit);
    }
}
