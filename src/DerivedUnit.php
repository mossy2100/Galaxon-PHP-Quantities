<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use DomainException;
use LogicException;
use Stringable;

/**
 * Represents a compound unit composed of one or more unit terms.
 *
 * A derived unit like 'kg·m·s⁻²' (newton) comprises multiple UnitTerm objects.
 * Unit terms with the same base unit are automatically combined
 * (e.g. km³ * km⁻¹ = km²).
 */
class DerivedUnit implements Stringable
{
    // region Properties

    /**
     * Array of unit terms the DerivedUnit comprises, keyed by the unit symbol without the exponent.
     * This is done because we will automatically combine same units with different exponents, e.g. km3 * km-1 = km2.
     *
     * @var array<string, UnitTerm>
     */
    private(set) array $unitTerms = [];

    // PHPCS doesn't know property hooks yet.
    // phpcs:disable PSR2.Classes.PropertyDeclaration
    // phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact

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
                // Get the dimension code terms for this unit term.
                $dims = Dimensions::explode($unitTerm->dimension);

                // Accumulate the exponents for each letter in the dimension code.
                foreach ($dims as $dimCode => $exp) {
                    $dimCodes[$dimCode] = ($dimCodes[$dimCode] ?? 0) + $exp;
                }
            }

            // Generate the full dimension code.
            return Dimensions::implode($dimCodes);
        }
    }

    // phpcs:enable PSR2.Classes.PropertyDeclaration
    // phpcs:enable Generic.WhiteSpace.ScopeIndent.IncorrectExact

    // endregion

    // region Constructor

    /**
     * Construct a new DerivedUnit instance.
     *
     * @param null|Unit|UnitTerm|list<UnitTerm> $unit The unit, unit term, or array of unit terms to add, or null to
     * create an empty unit.
     */
    public function __construct(null|Unit|UnitTerm|array $unit = null)
    {
        if ($unit === null) {
            return;
        }

        // If the unit is a Unit, wrap it in a UnitTerm.
        if ($unit instanceof Unit) {
            $unit = new UnitTerm($unit);
        }

        // If the unit is a UnitTerm, add it to the unit terms array.
        if ($unit instanceof UnitTerm) {
            $this->addUnitTerm($unit);
            return;
        }

        // Argument is an array of UnitTerms.
        foreach ($unit as $unitTerm) {
            $this->addUnitTerm($unitTerm);
        }
    }

    // endregion

    // region Static methods

    /**
     * Parse a string into a new DerivedUnit.
     *
     * @param string $symbol The unit symbol, which can be simple or complex (e.g. 'm', 'kg*m/s2', etc.).
     * @return self The new instance.
     * @throws DomainException If the symbol format is invalid or if any units are unrecognized.
     */
    public static function parse(string $symbol): self
    {
        // Get the parts of the compound unit.
        $parts = preg_split('/([*·.\/])/u', $symbol, -1, PREG_SPLIT_DELIM_CAPTURE);
        if (empty($parts)) {
            throw new DomainException(
                "Invalid unit format. The expected format is one or more unit terms separated by '*' or " .
                "'/' operators, e.g. 'm', 'km', 'km2', 'm/s', 'm/s2', 'kg*m/s2', etc. To show an exponent, append it " .
                "to the unit, e.g. 'm2'. To show 'per unit', either use a divide sign or an exponent of -1, e.g. " .
                "'metres per second' can be expressed as 'm/s' or 'ms-1'. The parser will also accept '·' or '.' in " .
                "place of '*', and superscript exponents."
            );
        }

        // Initialize new object.
        $new = new self();

        // Convert the substrings to unit terms.
        $nParts = count($parts);
        for ($i = 0; $i < $nParts; $i += 2) {
            // Parse the unit term. This could throw a DomainException if the symbol is invalid.
            $unitTerm = UnitTerm::parse($parts[$i]);

            if ($i > 0 && $parts[$i - 1] === '/') {
                // If dividing, add the unit term, inverted.
                $new->addUnitTerm($unitTerm->inv());
            } else {
                // If multiplying, add the unit term.
                $new->addUnitTerm($unitTerm);
            }
        }

        // Return the new object.
        return $new;
    }

    /**
     * Convert the argument to a DerivedUnit if necessary.
     *
     * @param null|string|Unit|UnitTerm|self $value The value to convert.
     * @return self The equivalent DerivedUnit object.
     * @throws DomainException If a string is provided and it cannot be parsed.
     */
    public static function toDerivedUnit(null|string|Unit|UnitTerm|self $value): self
    {
        // If the value is already a DerivedUnit, return it as is.
        if ($value instanceof self) {
            return $value;
        }

        // If the value is a string, parse it.
        if (is_string($value)) {
            return self::parse($value);
        }

        // Otherwise, construct a new DerivedUnit.
        return new self($value);
    }

    /**
     * Get the regex pattern for matching a derived unit.
     *
     * @return string The regex pattern (without delimiters or anchors).
     */
    public static function regex(): string
    {
        $rxUnitTerm = UnitTerm::regex();
        return "$rxUnitTerm([*·.\/]$rxUnitTerm)*";
    }

    // endregion

    // region Formatting methods

    /**
     * Format the derived unit as a string.
     *
     * If $ascii is false (default), Unicode symbols are used (if set), exponents are converted to superscript
     * (e.g. 'm²'), and the unit terms will be separated by a '·' character.
     *
     * If $ascii is true, then the primary (ASCII) symbol will be used, exponents will not be converted to superscript,
     * and the unit terms will be separated by a '*' character.
     *
     * @param bool $ascii If true, return ASCII format; if false (default), return Unicode format.
     * @return string The derived unit symbol.
     */
    public function format(bool $ascii = false): string
    {
        $fn = static fn (UnitTerm $unitTerm) => $unitTerm->format($ascii);
        $multiplyChar = $ascii ? '*' : '·';
        return implode($multiplyChar, array_map($fn, $this->unitTerms));
    }

    /**
     * Convert the derived unit to a string. This will use the Unicode format, which may include non-ASCII characters.
     * For the ASCII version, use format(true).
     *
     * @return string The derived unit as a string.
     */
    public function __toString(): string
    {
        return $this->format();
    }

    // endregion

    // region Inspection methods

    /**
     * Check if all unit terms in this derived unit are SI units.
     *
     * @return bool True if all unit terms are SI units, false otherwise.
     */
    public function isSi(): bool
    {
        return array_all($this->unitTerms, static fn (UnitTerm $unitTerm) => $unitTerm->isSi());
    }

    /**
     * Find a unit term by its symbol (prefixed unit, no exponent).
     *
     * @param string $symbol The prefixed unit to search for (e.g. 'km', 'mi').
     * @return ?UnitTerm The matching unit term, or null if not found.
     */
    public function getUnitTermBySymbol(string $symbol): ?UnitTerm
    {
        return $this->unitTerms[$symbol] ?? null;
    }

    /**
     * Find a unit term by its dimension code.
     *
     * @param string $dimension The dimension code to search for (e.g. 'L', 'T-1').
     * @return ?UnitTerm The matching unit term, or null if not found.
     */
    public function getUnitTermByDimension(string $dimension): ?UnitTerm
    {
        return array_find($this->unitTerms, static fn (UnitTerm $unitTerm) => $unitTerm->dimension === $dimension);
    }

    // endregion

    // region Manipulation methods

    /**
     * Set a unit term directly by its symbol key.
     *
     * @param string $symbol The symbol key (without exponent) to use.
     * @param UnitTerm $unitTerm The unit term to set.
     */
    public function setUnitTerm(string $symbol, UnitTerm $unitTerm): void
    {
        $this->unitTerms[$symbol] = $unitTerm;
        $this->sortUnitTerms();
    }

    /**
     * Add a unit term, combining exponents with any existing term of the same unit.
     *
     * If a term with the same base unit already exists, their exponents are added.
     * If the resulting exponent is zero, the term is removed entirely.
     *
     * @param string|UnitTerm $unitTerm The unit term to add.
     */
    public function addUnitTerm(string|UnitTerm $unitTerm): void
    {
        if (is_string($unitTerm)) {
            $unitTerm = UnitTerm::parse($unitTerm);
        }

        $symbol = $unitTerm->unexponentiatedSymbol;
        $existingUnitTerm = $this->unitTerms[$symbol] ?? null;
        if ($existingUnitTerm === null) {
            // Add the new unit term.
            $this->unitTerms[$symbol] = $unitTerm;
        } else {
            // Add the exponent of the new unit term to that of the existing term.
            $exp = $existingUnitTerm->exponent + $unitTerm->exponent;
            if ($exp === 0) {
                unset($this->unitTerms[$symbol]);
            } else {
                $this->unitTerms[$symbol] = $existingUnitTerm->withExponent($exp);
            }
        }

        // Keep the unit terms sorted. This is important for multiplying quantities.
        $this->sortUnitTerms();
    }

    /**
     * Remove a unit term.
     *
     * @param string|UnitTerm $unitTerm The unit term to remove.
     */
    public function removeUnitTerm(string|UnitTerm $unitTerm): void
    {
        if (is_string($unitTerm)) {
            $unitTerm = UnitTerm::parse($unitTerm);
        }

        unset($this->unitTerms[$unitTerm->unexponentiatedSymbol]);
    }

    // endregion

    // region Transformation methods

    /**
     * Clone the DerivedUnit, including deep cloning of unit terms.
     *
     * The underlying Unit objects are not cloned as they are fixed/immutable.
     */
    public function __clone(): void
    {
        $clonedTerms = array_map(static fn ($unitTerm) => clone $unitTerm, $this->unitTerms);
        $this->unitTerms = $clonedTerms;
    }

    /**
     * Return a new DerivedUnit with all exponents negated.
     *
     * @return self A new instance with inverted exponents (e.g. m·s⁻¹ → m⁻¹·s).
     */
    public function inv(): self
    {
        $du = new self();
        foreach ($this->unitTerms as $unitTerm) {
            $du->addUnitTerm($unitTerm->inv());
        }
        return $du;
    }

    /**
     * Convert the DerivedUnit to its SI equivalent.
     *
     * @return self The new DerivedUnit.
     * @throws DomainException If any of the dimension codes are invalid.
     * @throws LogicException If any of the dimension codes do not have an SI base unit defined.
     */
    public function toSi(): self
    {
        $unitTerms = [];
        $dimTerms = Dimensions::explode($this->dimension);
        foreach ($dimTerms as $code => $exp) {
            $unitTerms[] = Dimensions::getSiUnitTerm($code)->applyExponent($exp);
        }
        return new self($unitTerms);
    }

    // endregion

    // region Sorting methods

    /**
     * Compare two unit terms for sorting purposes.
     *
     * Sorting order:
     * 1. More complex dimensions (named units) before simpler ones.
     * 2. By dimension code order.
     *
     * @param UnitTerm $a The first unit term.
     * @param UnitTerm $b The second unit term.
     * @return int Negative if $a should come first, positive if $b should come first, zero if equal.
     */
    private static function compareUnitTerms(UnitTerm $a, UnitTerm $b): int
    {
        // Parse the dimension into dimension terms.
        $aDimTerms = Dimensions::explode($a->dimension);
        $bDimTerms = Dimensions::explode($b->dimension);

        // Put more complex dimensions (indicating named units) first.
        if (count($aDimTerms) > count($bDimTerms)) {
            return -1;
        }
        if (count($aDimTerms) < count($bDimTerms)) {
            return 1;
        }

        // If the unit terms have the same number of dimension terms, they most likely have 1 each.
        // We can assume as much, anyway, and, compare the only/first terms.
        // Order unit terms to match order in the Dimensions::DIMENSION_CODES array.
        $aFirstKey = array_key_first($aDimTerms);
        $bFirstKey = array_key_first($bDimTerms);
        return Dimensions::letterToInt($aFirstKey) <=> Dimensions::letterToInt($bFirstKey);
    }

    /**
     * Sort the unit terms into canonical order.
     */
    public function sortUnitTerms(): void
    {
        uasort($this->unitTerms, self::compareUnitTerms(...));
    }

    // endregion
}
