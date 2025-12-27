<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use Galaxon\Core\Integers;
use LogicException;
use ValueError;

class DerivedUnit
{
    /** @var array<string, UnitTerm> */
    private(set) array $unitTerms = [];

    /**
     * @param UnitTerm|string $unit
     * @throws ValueError
     */
    public function __construct(UnitTerm|string $unit)
    {
        if ($unit instanceof UnitTerm) {
            $this->unitTerms[$unit->base] = $unit;
            return;
        }

        // Parse the provided string into unit terms.
        $unitTerms = self::parse($unit);

        // Construct the derived unit from the unit terms.
        foreach ($unitTerms as $unitTerm) {
            $this->mul($unitTerm);
        }
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
     */
    public function getDimensionCode(): string
    {
        // Convert the unit terms to dimension codes.
        $dimCodes = [];
        foreach ($this->unitTerms as $base => $unit) {
            // Look up the base unit info.
            $unitInfo = UnitData::lookupBaseUnit($base)[0];

            // The dimension code of the base unit may be complex if it's a named unit.
            $dims = Dimensions::parse($unitInfo['dimension']);
            foreach ($dims as $dimCode => $exp) {
                $dimCodes[$dimCode] = ($dimCodes[$dimCode] ?? 0) + $exp;
            }
        }

        // Sort the dimension codes into canonical order.
        $dimCodes = Dimensions::sort($dimCodes);

        // Generate the full dimension code.
        return Dimensions::combine($dimCodes);
    }

    public function __toString(): string
    {
        $fn = static fn(UnitTerm $unit) => $unit->__toString();
        return implode('·', array_map($fn, $this->unitTerms));
    }
}
