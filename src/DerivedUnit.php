<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Core\Traits\Equatable;
use Galaxon\Quantities\Utility\DimensionUtility;
use LogicException;

/**
 * Represents a compound unit composed of one or more unit terms.
 *
 * A derived unit like 'kg⋅m⋅s⁻²' (newton) comprises multiple UnitTerm objects.
 * Unit terms with the same base unit are automatically combined
 * (e.g. km³ * km⁻¹ = km²).
 */
class DerivedUnit implements UnitInterface
{
    use Equatable;

    // region Properties

    /**
     * Array of unit terms the DerivedUnit comprises, keyed by the unit symbol without the exponent.
     * This is done because we will automatically combine the same units with different exponents,
     * e.g. km3 * km-1 = km2.
     *
     * @var array<string, UnitTerm>
     */
    private(set) array $unitTerms = [];

    /**
     * Get the dimension code of the derived unit.
     *
     * Defaults to '1' (dimensionless).
     *
     * @return string The dimension code.
     */
    private(set) string $dimension = '1';

    // endregion

    // region Property hooks

    /**
     * The full unit symbol with prefix and exponent (e.g. 'km2', 'ms-1').
     * This property returns the ASCII symbol (e.g. 'deg').
     */
    public string $asciiSymbol {
        get => $this->format(true);
    }

    /**
     * The full unit symbol with prefix and exponent formatted as superscript (e.g. 'km²', 'ms⁻¹').
     * This property returns the Unicode symbol if set (e.g. '°').
     */
    public string $unicodeSymbol {
        get => $this->format();
    }

    /**
     * The combined multiplier from all unit term prefixes.
     *
     * This is the product of each unit term's prefix multiplier raised to its exponent.
     * For example, km²⋅ms⁻¹ would have multiplier 1000² × 0.001⁻¹ = 1e6 × 1000 = 1e9.
     */
    public float $multiplier {
        get => array_product(
            array_map(
                static fn (UnitTerm $unitTerm) => $unitTerm->multiplier,
                $this->unitTerms
            )
        );
    }

    /**
     * The first unit term in the derived unit, or null if empty.
     */
    public ?UnitTerm $firstUnitTerm {
        get {
            $firstKey = array_key_first($this->unitTerms) ?? null;
            return $firstKey === null ? null : $this->unitTerms[$firstKey];
        }
    }

    // endregion

    // region Constructor

    /**
     * Construct a new DerivedUnit instance.
     *
     * @param null|Unit|UnitTerm|list<UnitTerm> $unit The unit, unit term, or array of unit terms to add, or null to
     * create an empty unit.
     * @throws DomainException If the provided unit is invalid.
     */
    public function __construct(null|Unit|UnitTerm|array $unit = null)
    {
        // Allow empty derived units.
        if ($unit === null) {
            return;
        }

        // If the unit is a Unit, convert it to a UnitTerm.
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

    // region Static public methods

    /**
     * Convert the argument to a DerivedUnit if necessary.
     *
     * @param null|string|UnitInterface $value The value to convert.
     * @return self The equivalent DerivedUnit object.
     * @throws FormatException If a string is provided, and it cannot be parsed.
     * @throws DomainException If a string is provided, and it contains unknown units.
     */
    public static function toDerivedUnit(null|string|UnitInterface $value): self
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
        /** @var null|Unit|UnitTerm $value */
        return new self($value);
    }

    // endregion

    // region String methods

    /**
     * Get the regex pattern for matching a derived unit.
     *
     * @return string The regex pattern (without delimiters or anchors).
     */
    public static function regex(): string
    {
        $rxUnitTerm = UnitTerm::regex();
        $form1 = "$rxUnitTerm(?:" . Unit::RX_MUL_OPS_PLUS_DIV . "$rxUnitTerm)*";
        $mulTerms = "$rxUnitTerm(?:" . Unit::RX_MUL_OPS_ONLY . "$rxUnitTerm)*";
        $form2 = "(?:$mulTerms)\/\\((?:$mulTerms)\\)";
        return "(?:$form1)|(?:$form2)";
    }

    /**
     * Parse a string into a new DerivedUnit.
     *
     * @param string $symbol The unit symbol, which can be simple or complex (e.g. 'm', 'kg*m/s2', etc.).
     * @return self The new DerivedUnit instance.
     * @throws FormatException If the symbol format is invalid.
     * @throws DomainException If any units are unknown.
     * @throws LogicException If there was an error extracting unit terms from the symbol.
     */
    public static function parse(string $symbol): self
    {
        // If the symbol is empty, there are no unit terms (dimensionless).
        if ($symbol === '') {
            return new self();
        }

        $rxUnitTerm = UnitTerm::regex();

        // Check for a series of unit terms separated by multiplication and/or division operators.
        $form1 = "$rxUnitTerm(?:" . Unit::RX_MUL_OPS_PLUS_DIV . "$rxUnitTerm)*";
        if (preg_match("/^$form1$/iu", $symbol, $matches) === 1) {
            return self::parseHelper($symbol);
        }

        // Check for parentheses. The only permitted use of parentheses is "<terms>/(<terms>)", where <terms> is a
        // sequence of one or more multiplied unit terms. Examples: 'J/(mol*K)', 'W/(m2*K4)'.
        $mulTerms = "$rxUnitTerm(?:" . Unit::RX_MUL_OPS_ONLY . "$rxUnitTerm)*";
        $form2 = "(?<num>$mulTerms)\/\((?<den>$mulTerms)\)";
        if (preg_match("/^$form2$/iu", $symbol, $matches) === 1) {
            $numerator = $matches['num'];
            $denominator = $matches['den'];
            $numUnit = self::parseHelper($numerator);
            $denUnit = self::parseHelper($denominator);
            foreach ($denUnit->unitTerms as $denUnitTerm) {
                $numUnit->addUnitTerm($denUnitTerm->inv());
            }
            return $numUnit;
        }

        throw new FormatException("Invalid derived unit symbol: '$symbol'");
    }

    /**
     * Parse a sequence of unit terms separated by multiplication and/or division operators.
     *
     * @param string $symbol The derived unit symbol.
     * @return self The new DerivedUnit instance.
     * @throws DomainException If any units are unknown.
     * @throws FormatException If the symbol format is invalid.
     * @throws LogicException If there was an error extracting unit terms from the symbol.
     */
    private static function parseHelper(string $symbol): self
    {
        // Initialize a new object.
        $new = new self();

        // Get the parts of the compound unit.
        $parts = preg_split('/(' . Unit::RX_MUL_OPS_PLUS_DIV . ')/iu', $symbol, -1, PREG_SPLIT_DELIM_CAPTURE);

        // Check for error.
        if ($parts === false) {
            throw new LogicException("Error parsing unit symbol: '$symbol'");
        }

        // Convert the substrings to unit terms.
        $nParts = count($parts);
        for ($i = 0; $i < $nParts; $i += 2) {
            // Parse the unit term. This could throw a DomainException if the symbol is invalid.
            $unitTerm = UnitTerm::parse($parts[$i]);

            if ($i > 0 && $parts[$i - 1] === '/') {
                // If dividing, invert and add the unit term.
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
     * Format the derived unit as a string.
     *
     * If $ascii is false (default), Unicode symbols are used (if set), exponents are converted to superscript
     * (e.g. 'm²'), and the unit terms will be separated by a '⋅' character.
     *
     * If $ascii is true, then the primary (ASCII) symbol will be used, exponents will not be converted to superscript,
     * and the unit terms will be separated by a '*' character.
     *
     * @param bool $ascii If true, return the ASCII version; if false (default), return the Unicode version.
     * @return string The derived unit symbol.
     */
    public function format(bool $ascii = false): string
    {
        // Collection the unit terms with positive and negative exponents.
        $posTerms = [];
        $negTerms = [];
        foreach ($this->unitTerms as $unitTerm) {
            if ($unitTerm->exponent > 0) {
                $posTerms[] = $unitTerm;
            } elseif ($unitTerm->exponent < 0) {
                $negTerms[] = $unitTerm;
            }
        }

        // If there are no positive terms, don't use a divide symbol, just show exponents as negative.
        if (count($posTerms) === 0) {
            return implode(
                $ascii ? '*' : '⋅',
                array_map(static fn (UnitTerm $unitTerm) => $unitTerm->format($ascii), $negTerms)
            );
        }

        // Get the positive terms as a string.
        $posTermsStr = implode(
            $ascii ? '*' : '⋅',
            array_map(static fn (UnitTerm $unitTerm) => $unitTerm->format($ascii), $posTerms)
        );

        // Get the negative terms as a string.
        $negTermsStr = implode(
            $ascii ? '*' : '⋅',
            array_map(static fn (UnitTerm $unitTerm) => $unitTerm->inv()->format($ascii), $negTerms)
        );
        if (count($negTerms) > 1) {
            $negTermsStr = "($negTermsStr)";
        }

        // Combine the positive and negative terms.
        $result = $posTermsStr;
        if (count($negTerms) > 0) {
            $result .= '/' . $negTermsStr;
        }

        return $result;
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
     * Check if this derived unit is dimensionless (has no unit terms).
     *
     * @return bool True if dimensionless, false otherwise.
     */
    public function isDimensionless(): bool
    {
        return count($this->unitTerms) === 0;
    }

    /**
     * Check if all unit terms in this derived unit belong to the SI system.
     *
     * Returns true only if every component unit is an SI unit.
     * Dimensionless units (with no terms) are considered SI.
     *
     * @return bool True if all units are SI units.
     */
    public function isSi(): bool
    {
        return array_all($this->unitTerms, static fn ($unitTerm) => $unitTerm->isSi());
    }

    /**
     * Check if any unit term in this derived unit has a prefix.
     *
     * @return bool True if at least one unit term has a prefix, false otherwise.
     */
    public function hasPrefixes(): bool
    {
        return array_any($this->unitTerms, static fn (UnitTerm $unitTerm) => $unitTerm->prefix !== null);
    }

    /**
     * Check if any unit term in this derived unit has an expansion.
     *
     * @return bool True if at least one unit term has an expansion, false otherwise.
     */
    public function hasExpansion(): bool
    {
        return array_any($this->unitTerms, static fn (UnitTerm $unitTerm) => $unitTerm->unit->expansionUnit !== null);
    }

    /**
     * Check if any two unit terms have the same unit dimension and could be merged.
     *
     * For example, a derived unit containing both 'm' and 'ft' would return true
     * since both have dimension 'L' and could be combined.
     *
     * @return bool True if at least two unit terms share the same unit dimension.
     */
    public function hasMergeableUnits(): bool
    {
        $seenDimensions = [];

        foreach ($this->unitTerms as $unitTerm) {
            $dimension = $unitTerm->unit->dimension;
            if (isset($seenDimensions[$dimension])) {
                return true;
            }
            $seenDimensions[$dimension] = true;
        }

        return false;
    }

    // endregion

    // region Comparison methods

    /**
     * Check if this derived unit is equal to another.
     *
     * Two derived units are equal if they have the same unit terms with the same exponents.
     *
     * @param mixed $other The value to compare.
     * @return bool True if equal, false otherwise.
     */
    public function equal(mixed $other): bool
    {
        // Check for equal types.
        if (!$other instanceof self) {
            return false;
        }

        // Must have same number of unit terms.
        if (count($this->unitTerms) !== count($other->unitTerms)) {
            return false;
        }

        // Each unit term must be equal.
        // This doesn't check that the order of the unit terms matches, but that doesn't matter.
        return array_all(
            $this->unitTerms,
            static fn ($unitTerm, $symbol) => isset($other->unitTerms[$symbol]) && $unitTerm->equal(
                $other->unitTerms[$symbol]
            )
        );
    }

    // endregion

    // region Manipulation methods

    /**
     * Add a unit term, combining exponents with any existing term of the same unit.
     *
     * If a term with the same base unit already exists, their exponents are added.
     * If the resulting exponent is zero, the term is removed entirely.
     *
     * @param UnitTerm $newUnitTerm The unit term to add.
     */
    public function addUnitTerm(UnitTerm $newUnitTerm): void
    {
        $symbol = $newUnitTerm->unexponentiatedAsciiSymbol;
        $existingUnitTerm = $this->unitTerms[$symbol] ?? null;
        if ($existingUnitTerm === null) {
            // Add the new unit term.
            $this->unitTerms[$symbol] = $newUnitTerm;
        } else {
            // Add the exponent of the new unit term to that of the existing term.
            $exp = $existingUnitTerm->exponent + $newUnitTerm->exponent;
            if ($exp === 0) {
                unset($this->unitTerms[$symbol]);
            } else {
                $this->unitTerms[$symbol] = $existingUnitTerm->withExponent($exp);
            }
        }

        // Keep the unit terms sorted. This is important for multiplying quantities.
        $this->sortUnitTerms();

        // Update the dimension.
        $this->updateDimension();
    }

    /**
     * Remove a unit term.
     *
     * @param UnitTerm $unitTermToRemove The unit term to remove.
     */
    public function removeUnitTerm(UnitTerm $unitTermToRemove): void
    {
        foreach ($this->unitTerms as $symbol => $unitTerm) {
            if ($unitTerm->equal($unitTermToRemove)) {
                unset($this->unitTerms[$symbol]);
                break;
            }
        }
    }

    /**
     * Sort the unit terms into canonical order.
     *
     * @return void
     */
    public function sortUnitTerms(): void
    {
        uasort($this->unitTerms, self::compareUnitTerms(...));
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
        $this->unitTerms = array_map(static fn (UnitTerm $unitTerm) => clone $unitTerm, $this->unitTerms);
    }

    /**
     * Return a new DerivedUnit with all exponents negated.
     *
     * @return self A new instance with inverted exponents (e.g. m⋅s⁻¹ → m⁻¹⋅s).
     */
    public function inv(): self
    {
        /** @var list<UnitTerm> $unitTerms */
        $unitTerms = array_map(static fn (UnitTerm $unitTerm) => $unitTerm->inv(), $this->unitTerms);
        return new self($unitTerms);
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
        $dimTerms = DimensionUtility::decompose($this->dimension);
        foreach ($dimTerms as $code => $exp) {
            $unitTerms[] = DimensionUtility::getSiUnitTerm($code)->pow($exp);
        }
        return new self($unitTerms);
    }

    /**
     * Return a new DerivedUnit with all prefixes removed from all unit terms.
     *
     * @return self A new instance with no prefixes on any unit term.
     */
    public function removePrefixes(): self
    {
        /** @var list<UnitTerm> $unitTerms */
        $unitTerms = array_map(static fn (UnitTerm $unitTerm) => $unitTerm->removePrefix(), $this->unitTerms);
        return new self($unitTerms);
    }

    /**
     * Return a new DerivedUnit raised to a given power.
     *
     * Each unit term's exponent is multiplied by the given value, e.g. (m⋅s⁻¹)->pow(2) returns m²⋅s⁻².
     *
     * @param int $exponent The power to raise the derived unit to.
     * @return self A new instance with the exponents multiplied by the given value.
     */
    public function pow(int $exponent): self
    {
        // Get the unit terms raised to the given power.
        /** @var list<UnitTerm> $unitTerms */
        $unitTerms = array_map(static fn (UnitTerm $unitTerm) => $unitTerm->pow($exponent), $this->unitTerms);

        // Construct the new DerivedUnit object.
        return new self($unitTerms);
    }

    // endregion

    // region Private helper methods

    /**
     * Compare two unit terms for sorting purposes.
     *
     * Sorting order:
     * 1. More complex dimensions (expandable units) before simpler ones.
     * 2. By dimension code order.
     * 3. By exponent (descending - higher exponents first).
     *
     * @param UnitTerm $a The first unit term.
     * @param UnitTerm $b The second unit term.
     * @return int Negative if $a should come first, positive if $b should come first, zero if equal.
     */
    private static function compareUnitTerms(UnitTerm $a, UnitTerm $b): int
    {
        // If the dimensions are the same, the unit terms are equal for ordering.
        if ($a->dimension === $b->dimension) {
            return 0;
        }

        // Parse the dimension into dimension terms.
        $aDimTerms = DimensionUtility::decompose($a->dimension);
        $bDimTerms = DimensionUtility::decompose($b->dimension);

        // Put more complex dimensions (indicating expandable units) first.
        if (count($aDimTerms) > count($bDimTerms)) {
            return -1;
        }
        if (count($aDimTerms) < count($bDimTerms)) {
            return 1;
        }

        // The number of dimensions in the two unit terms is the same, most likely 1.
        // We'll order them by the dimension codes and exponents of the dimension terms.
        $nTerms = count($aDimTerms);
        $aDims = array_keys($aDimTerms);
        $bDims = array_keys($bDimTerms);

        // First loop: compare all letters in the order given by DimensionUtility::DIMENSION_CODES.
        for ($i = 0; $i < $nTerms; $i++) {
            $cmp = DimensionUtility::letterToInt($aDims[$i]) <=> DimensionUtility::letterToInt($bDims[$i]);
            if ($cmp !== 0) {
                return $cmp;
            }
        }

        // Second loop: compare exponents in descending order (higher first).
        // e.g. Pa (M*L-1*T-2) vs. J (M*L2*T-2) - same letters, different exponents.
        for ($i = 0; $i < $nTerms; $i++) {
            $cmp = $bDimTerms[$bDims[$i]] <=> $aDimTerms[$aDims[$i]];
            if ($cmp !== 0) {
                return $cmp;
            }
        }

        // Unit terms are equal; shouldn't happen since dimensions differ.
        return 0;
    }

    /**
     * Recalculate the dimension from the current unit terms.
     *
     * @return void
     */
    private function updateDimension(): void
    {
        // Convert the unit terms to dimension codes.
        $dimCodes = [];
        foreach ($this->unitTerms as $unitTerm) {
            // Get the dimension code terms for this unit term.
            $dims = DimensionUtility::decompose($unitTerm->dimension);

            // Accumulate the exponents for each letter in the dimension code.
            foreach ($dims as $dimCode => $exp) {
                if ($exp === 0) {
                    continue;
                }

                if (isset($dimCodes[$dimCode])) {
                    $exp += $dimCodes[$dimCode];
                    if ($exp === 0) {
                        unset($dimCodes[$dimCode]);
                    } else {
                        $dimCodes[$dimCode] = $exp;
                    }
                } else {
                    $dimCodes[$dimCode] = $exp;
                }
            }
        }

        // Generate the full dimension code.
        $this->dimension = DimensionUtility::compose($dimCodes);
    }

    // endregion
}
