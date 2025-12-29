<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use Galaxon\Core\Integers;
use LogicException;
use RuntimeException;
use Stringable;
use ValueError;

class DerivedUnit implements Stringable
{
    /** @var array<string, UnitTerm> */
    private(set) array $unitTerms = [];

    /**
     * @param null|UnitTerm|string $unit
     * @throws ValueError
     */
    public function __construct(null|UnitTerm|string $unit = null)
    {
        // No unit provided.
        if ($unit === null) {
            return;
        }

        // If the unit is a UnitTerm, just add it to the unit terms array and return.
        if ($unit instanceof UnitTerm) {
            $this->unitTerms[$unit->base] = $unit;
            return;
        }

        // The unit is a string. Parse it into unit terms.
        $unitTerms = self::parse($unit);

        // Construct the derived unit from the unit terms.
        foreach ($unitTerms as $unitTerm) {
            $this->mul($unitTerm);
        }

        // Sort the unit terms by dimension code.
        $fn = static fn (UnitTerm $a, UnitTerm $b) => $a->getDimensionCode() <=> $b->getDimensionCode();
        uasort($this->unitTerms, $fn);
    }

    /**
     * @param string $symbol
     * @return list<UnitTerm> The unit terms in the provided symbol.
     * @throws ValueError If the symbol format is invalid or if any units are unrecognized.
     */
    public static function parse(string $symbol): array
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
            }
            else {
                $unitTerms[] = $unit->invert();
            }
        }

        return $unitTerms;
    }

    public function mul(UnitTerm|DerivedUnit $unit): void
    {
        if ($unit instanceof UnitTerm) {
            $base = $unit->base;
            if (array_key_exists($base, $this->unitTerms)) {
                $this->unitTerms[$base]->exponent += $unit->exponent;
            } else {
                $this->unitTerms[$base] = $unit;
            }
        } else {
            // $unit is a derived unit. Multiply each term.
            foreach ($unit->unitTerms as $unitTerm) {
                $this->mul($unitTerm);
            }
        }
    }

    public function div(UnitTerm|DerivedUnit $unit): void
    {
        if ($unit instanceof UnitTerm) {
            $base = $unit->base;
            if (array_key_exists($base, $this->unitTerms)) {
                $this->unitTerms[$base]->exponent -= $unit->exponent;
            } else {
                $this->unitTerms[$base] = $unit->invert();
            }
        } else {
            // $unit is a derived unit. Divide each term.
            foreach ($unit->unitTerms as $unitTerm) {
                $this->div($unitTerm);
            }
        }
    }

    /**
     * Get the dimension code of the derived unit.
     *
     * @return string The dimension code.
     * @throws ValueError
     * @throws LogicException
     * @throws RuntimeException If the base is not found (should never happen).
     */
    public function getDimensionCode(): string
    {
        // Convert the unit terms to dimension codes.
        $dimCodes = [];
        foreach ($this->unitTerms as $base => $unitTerm) {
            // Look up the base unit.
            $unit = Unit::lookup($base);
            if ($unit === null) {
                throw new RuntimeException("Unknown unit '$base'.");
            }

            // The dimension code of the base unit may be complex.
            $dims = Dimensions::parse($unit->dimension);
            foreach ($dims as $dimCode => $exp) {
                $dimCodes[$dimCode] = ($dimCodes[$dimCode] ?? 0) + ($exp * $unitTerm->exponent);
            }
        }

        // Sort the dimension codes into canonical order.
        $dimCodes = Dimensions::sort($dimCodes);

        // Generate the full dimension code.
        return Dimensions::combine($dimCodes);
    }

    /**
     * Expand the derived unit by converting named units into their equivalents, e.g. N => kg*m/s2
     *
     * @return self The expanded unit.
     * @throws RuntimeException If an unknown unit is encountered (should never happen).
     */
    public function expand(): self
    {
        // Create a new object.
        $new = new self();

        foreach ($this->unitTerms as $unitTerm) {
            // Look up the base unit.
            $base = Unit::lookup($unitTerm->base);
            if ($base === null) {
                throw new RuntimeException("Error expanding unit '$this': unknown unit '$unitTerm->base'.");
            }

            // Expand if necessary.
            if ($base->equivalent !== null) {
                $du = new DerivedUnit($base->equivalent);
                foreach ($du->unitTerms as $duTerm) {
                    $new->mul($duTerm);
                }
            }
            else {
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
}
