<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Core\Traits\Equatable;
use Galaxon\Quantities\Registry\DimensionRegistry;
use Galaxon\Quantities\Registry\UnitRegistry;
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

    // region Constants

    /**
     * Regular expression character class with multiply and divide characters.
     * Allow dots for multiply.
     *     . = Period (full stop) character.
     *     · = Middle dot (U+00B7) - used in typography, Catalan, etc.
     *     ⋅ = Dot operator (U+22C5) - mathematical multiplication symbol.
     */
    public const string UNIT_TERM_SEPARATORS = "[*.\x{00B7}\x{22C5}\/]";

    // endregion

    // region Properties

    /**
     * Array of unit terms the DerivedUnit comprises, keyed by the unit symbol without the exponent.
     * This is done because we will automatically combine same units with different exponents, e.g. km3 * km-1 = km2.
     *
     * @var array<string, UnitTerm>
     */
    private(set) array $unitTerms = [];

    /**
     * Get the dimension code of the derived unit.
     *
     * @return string The dimension code.
     */
    private(set) string $dimension = '';

    // endregion

    // region Property hooks

    // phpcs:disable PSR2.Classes.PropertyDeclaration
    // phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact

    /**
     * The full unit symbol with prefix and exponent (e.g. 'km2', 'ms-1').
     * This property returns the ASCII symbol (e.g. 'deg').
     */
    public string $asciiSymbol {
        get => $this->format(true);
    }

    /**
     * The full unit symbol with prefix and exponent formatted as superscript (e.g. 'km²', 'ms⁻¹').
     * This property returns the Unicode symbol, if set (e.g. '°').
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

    // phpcs:enable PSR2.Classes.PropertyDeclaration
    // phpcs:enable Generic.WhiteSpace.ScopeIndent.IncorrectExact

    // endregion

    // region Constructor

    /**
     * Construct a new DerivedUnit instance.
     *
     * @param null|Unit|UnitTerm|list<UnitTerm> $unit The unit, unit term, or array of unit terms to add, or null to
     * create an empty unit.
     * @throws DomainException Never.
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

    // region Static methods

    /**
     * Parse a string into a new DerivedUnit.
     *
     * @param string $symbol The unit symbol, which can be simple or complex (e.g. 'm', 'kg*m/s2', etc.).
     * @return static The new DerivedUnit instance.
     * @throws FormatException If the symbol format is invalid.
     * @throws DomainException If any units are unknown.
     */
    public static function parse(string $symbol): static
    {
        // Get the parts of the compound unit.
        $parts = preg_split('/(' . self::UNIT_TERM_SEPARATORS . ')/u', $symbol, -1, PREG_SPLIT_DELIM_CAPTURE);
        if (empty($parts)) {
            throw new FormatException(
                "Invalid unit format. The expected format is one or more unit terms separated by '*' or " .
                "'/' operators, e.g. 'm', 'km', 'km2', 'm/s', 'm/s2', 'kg*m/s2', etc. To show an exponent, append it " .
                "to the unit, e.g. 'm2'. To show 'per unit', either use a divide sign or an exponent of -1, e.g. " .
                "'metres per second' can be expressed as 'm/s' or 'm*s-1'. The parser will also accept '·' (U+00B7), " .
                "'⋅' (U+22C5), or '.' in place of '*', and superscript exponents."
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
     * @param null|string|UnitInterface $value The value to convert.
     * @return self The equivalent DerivedUnit object.
     * @throws FormatException If a string is provided and it cannot be parsed.
     * @throws DomainException If a string is provided and it contains unknown units.
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
        return "$rxUnitTerm(" . self::UNIT_TERM_SEPARATORS . "$rxUnitTerm)*";
    }

    // endregion

    // region Formatting methods

    /**
     * Format the derived unit as a string.
     *
     * If $ascii is false (default), Unicode symbols are used (if set), exponents are converted to superscript
     * (e.g. 'm²'), and the unit terms will be separated by a '⋅' character.
     *
     * If $ascii is true, then the primary (ASCII) symbol will be used, exponents will not be converted to superscript,
     * and the unit terms will be separated by a '*' character.
     *
     * @param bool $ascii If true, return ASCII format; if false (default), return Unicode format.
     * @return string The derived unit symbol.
     */
    public function format(bool $ascii = false): string
    {
        return implode(
            $ascii ? '*' : '⋅',
            array_map(static fn (UnitTerm $unitTerm) => $unitTerm->format($ascii), $this->unitTerms)
        );
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

    public function isDimensionless(): bool
    {
        return count($this->unitTerms) === 0;
    }

    /**
     * Check if any unit term in this derived unit has a prefix.
     *
     * @return bool True if at least one unit term has a prefix, false otherwise.
     */
    public function hasPrefixes(): bool
    {
        return array_any($this->unitTerms, static fn (UnitTerm $unitTerm) => $unitTerm->hasPrefix());
    }

    /**
     * Get the maximum absolute value of exponents in this derived unit.
     *
     * @return int The maximum absolute exponent, or 0 if there are no unit terms.
     */
    public function maxAbsExp(): int
    {
        if (empty($this->unitTerms)) {
            return 0;
        }

        return max(array_map(
            static fn (UnitTerm $unitTerm) => abs($unitTerm->exponent),
            $this->unitTerms
        ));
    }

    /**
     * Find a unit term by unit.
     *
     * @param string|Unit $unit The unit to search for.
     * @return ?UnitTerm The matching unit term, or null if not found.
     */
    public function getUnitTermByUnit(string|Unit $unit): ?UnitTerm
    {
        // Get the unit as an object.
        if (is_string($unit)) {
            $unit = UnitRegistry::getBySymbol($unit);
            if ($unit === null) {
                return null;
            }
        }

        return array_find($this->unitTerms, static fn (UnitTerm $ut) => $ut->unit->equal($unit));
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
     * @param UnitTerm $unitTerm The unit term to add.
     * @throws DomainException Never.
     */
    public function addUnitTerm(UnitTerm $unitTerm): void
    {
        $symbol = $unitTerm->unexponentiatedAsciiSymbol;
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

        // Update the dimension.
        $this->updateDimension();
    }

    /**
     * Remove a unit term.
     *
     * @param string|UnitTerm $unitTerm The unit term to remove.
     * @throws DomainException Never.
     */
    public function removeUnitTerm(string|UnitTerm $unitTerm): void
    {
        // Get the symbol of the unit term to remove.
        $symbol = is_string($unitTerm) ? $unitTerm : $unitTerm->unexponentiatedAsciiSymbol;

        // Remove the unit term.
        unset($this->unitTerms[$symbol]);

        // Update the dimension.
        $this->updateDimension();
    }

    /**
     * Sort the unit terms into canonical order.
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
     * @throws DomainException Never.
     */
    public function inv(): self
    {
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
        $dimTerms = DimensionRegistry::explode($this->dimension);
        foreach ($dimTerms as $code => $exp) {
            $unitTerms[] = DimensionRegistry::getSiBaseUnitTerm($code)->pow($exp);
        }
        return new self($unitTerms);
    }

    /**
     * Return a new DerivedUnit with all prefixes removed from all unit terms.
     *
     * @return self A new instance with no prefixes on any unit term.
     * @throws DomainException Never.
     */
    public function removePrefixes(): self
    {
        $newUnitTerms = array_map(
            static fn (UnitTerm $unitTerm) => $unitTerm->removePrefix(),
            $this->unitTerms
        );
        return new self($newUnitTerms);
    }

    /**
     * Return a new DerivedUnit with the given prefix applied to the first unit term.
     *
     * @param ?string $prefix The prefix symbol to apply, or null for no prefix.
     * @return self A new instance with the prefix applied to the first unit term.
     * @throws DomainException If the prefix is invalid for the first unit term's unit.
     */
    public function withPrefix(?string $prefix): self
    {
        // If there are no unit terms, do nothing.
        if (empty($this->unitTerms)) {
            return clone $this;
        }

        // Create a list of UnitTerms for the result DerivedUnit. Only the first one will be altered.
        $first = true;
        $newUnitTerms = [];
        foreach ($this->unitTerms as $unitTerm) {
            if ($first) {
                $newUnitTerms[] = $unitTerm->withPrefix($prefix);
                $first = false;
            } else {
                $newUnitTerms[] = $unitTerm;
            }
        }

        // Construct the new DerivedUnit object.
        return new self($newUnitTerms);
    }

    /**
     * Return a new DerivedUnit raised to a given power.
     *
     * Each unit term's exponent is multiplied by the given value.
     * For example, (m⋅s⁻¹)->pow(2) returns m²⋅s⁻².
     *
     * @param int $exponent The power to raise the derived unit to.
     * @return self A new instance with the exponents multiplied by the given value.
     * @throws DomainException Never.
     */
    public function pow(int $exponent): self
    {
        // Get the unit terms raised to the given power.
        $newUnitTerms = array_map(
            static fn (UnitTerm $unitTerm) => $unitTerm->pow($exponent),
            $this->unitTerms
        );

        // Construct the new DerivedUnit object.
        return new self($newUnitTerms);
    }

    /**
     * Return a new DerivedUnit by taking the root (i.e. square root, cube root, etc.).
     *
     * Each unit term's exponent is divided by the given value.
     * For example, (m²⋅s⁻²)->root(2) returns m⋅s⁻¹.
     *
     * @param int $index The index of the root to calculate.
     * @return self A new instance with the exponents divided by the given value.
     * @throws DomainException If the root is not a positive integer.
     */
    public function root(int $index): self
    {
        // Check the index is positive.
        if ($index < 1) {
            throw new DomainException('Index must be a positive integer.');
        }

        // Calculate the root of each unit term.
        $newUnitTerms = array_map(
            static fn (UnitTerm $unitTerm) => $unitTerm->root($index),
            $this->unitTerms
        );

        // Construct the new DerivedUnit object.
        return new self($newUnitTerms);
    }

    // endregion

    // region Private helper methods

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
     * @throws DomainException Never.
     */
    private static function compareUnitTerms(UnitTerm $a, UnitTerm $b): int
    {
        // Parse the dimension into dimension terms.
        $aDimTerms = DimensionRegistry::explode($a->dimension);
        $bDimTerms = DimensionRegistry::explode($b->dimension);

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
        return DimensionRegistry::letterToInt($aFirstKey) <=> DimensionRegistry::letterToInt($bFirstKey);
    }

    /**
     * Recalculate the dimension from the current unit terms.
     *
     * @return void
     * @throws DomainException Never.
     */
    private function updateDimension(): void
    {
        // Convert the unit terms to dimension codes.
        $dimCodes = [];
        foreach ($this->unitTerms as $unitTerm) {
            // Get the dimension code terms for this unit term.
            $dims = DimensionRegistry::explode($unitTerm->dimension);

            // Accumulate the exponents for each letter in the dimension code.
            foreach ($dims as $dimCode => $exp) {
                $dimCodes[$dimCode] = ($dimCodes[$dimCode] ?? 0) + $exp;
            }
        }

        // Generate the full dimension code.
        $this->dimension = DimensionRegistry::implode($dimCodes);
    }
}
