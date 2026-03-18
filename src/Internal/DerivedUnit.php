<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Internal;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Core\Traits\Equatable;
use Galaxon\Quantities\Exceptions\UnknownUnitException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\ConversionService;
use Galaxon\Quantities\Services\DimensionService;
use Galaxon\Quantities\Services\QuantityTypeService;
use Galaxon\Quantities\Services\RegexService;
use LogicException;
use UnexpectedValueException;

/**
 * Represents a compound unit composed of one or more unit terms.
 *
 * A derived unit like 'kg⋅m⋅s⁻²' (newton) comprises multiple UnitTerm objects.
 * Unit terms with the same unit are automatically combined
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
     * The dimension code of the derived unit.
     *
     * Defaults to empty string '' (dimensionless).
     */
    private(set) string $dimension = '';

    /**
     * The expansion quantity, if one exists and is known.
     */
    private(set) ?Quantity $expansion = null;

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

    /**
     * The quantity type this derived unit is for, if known.
     */
    public ?QuantityType $quantityType {
        get => QuantityTypeService::getByDimension($this->dimension);
    }

    // endregion

    // region Constructor

    /**
     * Construct a new DerivedUnit instance.
     *
     * @param null|Unit|UnitTerm|list<Unit|UnitTerm> $unit The Unit, UnitTerm, or array of Unit or UnitTerm objects
     * to add, or null to create an empty unit.
     * @throws DomainException If the provided unit is invalid.
     */
    public function __construct(null|Unit|UnitTerm|array $unit = null)
    {
        // Allow empty derived units.
        if ($unit === null) {
            return;
        }

        // If the unit is a Unit or UnitTerm, convert it to an array.
        if ($unit instanceof Unit || $unit instanceof UnitTerm) {
            $unit = [$unit];
        }

        // Argument is an array of Unit and/or UnitTerm objects.
        foreach ($unit as $unitTerm) {
            // If we have a Unit, convert it to a UnitTerm.
            $this->addUnitTerm($unitTerm instanceof Unit ? new UnitTerm($unitTerm) : $unitTerm);
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
     * @throws UnknownUnitException If a string is provided, and it contains unknown units.
     * @throws DomainException If a string is provided, and an exponent is zero.
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
        assert($value === null || $value instanceof Unit || $value instanceof UnitTerm);
        return new self($value);
    }

    // endregion

    // region String methods

    /**
     * Parse a string into a new DerivedUnit.
     *
     * @param string $symbol The unit symbol, which can be simple or complex (e.g. 'm', 'kg*m/s2', etc.).
     * @return self The new DerivedUnit instance.
     * @throws FormatException If the symbol format is invalid.
     * @throws UnknownUnitException If any units are unknown.
     */
    public static function parse(string $symbol): self
    {
        // If the symbol is empty, there are no unit terms (dimensionless).
        if ($symbol === '') {
            return new self();
        }

        // Check for a series of unit terms separated by multiplication and/or division operators.
        if (RegexService::isValidDerivedUnitForm1($symbol)) {
            return self::parseHelper($symbol);
        }

        // Check for parentheses. The only permitted use of parentheses is "<terms>/(<terms>)", where <terms> is a
        // sequence of one or more multiplied unit terms. Examples: 'J/(mol*K)', 'W/(m2*K4)'.
        if (RegexService::isValidDerivedUnitForm2($symbol, $matches)) {
            assert(isset($matches['num']) && isset($matches['den']));
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
     * @throws UnknownUnitException If any units are unknown.
     * @throws FormatException If the symbol format is invalid.
     * @throws UnexpectedValueException If an unexpected error occurs.
     * @throws DomainException If an exponent is zero.
     */
    private static function parseHelper(string $symbol): self
    {
        // Initialize a new object.
        $new = new self();

        // Get the parts of the compound unit.
        $parts = preg_split(
            '/(' . RegexService::RX_CLASS_MUL_DIV_OPS . ')/iu',
            $symbol,
            flags: PREG_SPLIT_DELIM_CAPTURE
        );
        if ($parts === false) {
            // @codeCoverageIgnoreStart
            throw new UnexpectedValueException('Error splitting string into parts.');
            // @codeCoverageIgnoreEnd
        }

        // Convert the substrings to unit terms.
        $nParts = count($parts);
        for ($i = 0; $i < $nParts; $i += 2) {
            // Parse the unit term. This could throw an UnknownUnitException if the symbol is invalid.
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
     * Dimensionless units are not considered SI.
     *
     * @return bool True if all units are SI units.
     */
    public function isSi(): bool
    {
        return !$this->isDimensionless() &&
            array_all($this->unitTerms, static fn (UnitTerm $unitTerm) => $unitTerm->isSi());
    }

    /**
     * Check if this derived unit is expressed in base units.
     *
     * "Base units" are those with only 0 or 1 dimension term, i.e. not expandable units.
     *
     * @return bool True if this derived unit is expressed in base units only.
     */
    public function isBase(): bool
    {
        return array_all($this->unitTerms, static fn (UnitTerm $unitTerm) => $unitTerm->isBase());
    }

    /**
     * Check that this derived unit is expressed in SI base units.
     *
     * @return bool True if this derived unit is expressed in SI base units only.
     */
    public function isSiBase(): bool
    {
        return $this->equal($this->toSiBase());
    }

    /**
     * Check if any two unit terms have the same unit dimension and could be merged.
     *
     * For example, a derived unit containing both 'm' and 'ft' would return true
     * since both have dimension 'L' and could be combined.
     *
     * @return bool True if at least two unit terms share the same unit dimension.
     */
    public function isMergeable(): bool
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

    /**
     * Determine if a compound unit should ideally be expanded to SI or English base units.
     *
     * @return bool True if the derived unit should be expanded to SI base units, false otherwise.
     */
    public function siExpansionPreferred(): bool
    {
        // These SI units can't be used to determine SI compatibility, as they can be used with English units.
        $commonBaseUnits = ['s', 'mol', 'A', 'cd', 'rad', 'B', 'XAU'];

        // Count the number of unambiguously SI vs. English units.
        $nSiUnits = 0;
        $nEnglishUnits = 0;

        // Check each unit term.
        foreach ($this->unitTerms as $unitTerm) {
            // Check if the unit is SI (discounting those can be used with English units).
            if ($unitTerm->unit->isSi() && !in_array($unitTerm->unit->asciiSymbol, $commonBaseUnits, true)) {
                $nSiUnits++;
            }

            // Check if the unit is imperial or US customary.
            if ($unitTerm->unit->isEnglish()) {
                $nEnglishUnits++;
            }
        }

        // If there are no English units, or if there's at least one unambiguously SI unit, we would prefer to expand to
        // SI base units.
        return $nEnglishUnits === 0 || $nSiUnits > 0;
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
     * Check if the DerivedUnit includes the given unit.
     *
     * @param Unit $unit The unit to check.
     * @return bool True if the unit is included in the DerivedUnit.
     */
    public function includesUnit(Unit $unit): bool
    {
        return array_any($this->unitTerms, static fn (UnitTerm $unitTerm) => $unitTerm->unit->equal($unit));
    }

    // endregion

    // region Comparison methods

    /**
     * Check if this DerivedUnit is equal to another.
     *
     * @param mixed $other The other value to compare.
     * @return bool True if equal, false otherwise.
     */
    public function equal(mixed $other): bool
    {
        return $other instanceof self && $this->asciiSymbol === $other->asciiSymbol;
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

    // region Arithmetic methods

    /**
     * Return a new DerivedUnit with all exponents negated.
     *
     * @return self A new instance with inverted exponents (e.g. m⋅s⁻¹ → m⁻¹⋅s).
     */
    public function inv(): self
    {
        $unitTerms = array_values(array_map(static fn (UnitTerm $unitTerm) => $unitTerm->inv(), $this->unitTerms));

        /** @var list<UnitTerm> $unitTerms */
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
        $unitTerms = array_values(
            array_map(static fn (UnitTerm $unitTerm) => $unitTerm->pow($exponent), $this->unitTerms)
        );

        /** @var list<UnitTerm> $unitTerms */
        return new self($unitTerms);
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
     * Convert the DerivedUnit to its equivalent in SI base units.
     *
     * NB: This includes the special units we're designating as SI base for the purpose of this system: rad, B, and XAU.
     *
     * @return self The new DerivedUnit.
     * @throws DomainException If any of the dimension codes are invalid.
     * @throws LogicException If any of the dimension codes do not have an SI base unit defined.
     */
    public function toSiBase(): self
    {
        return DimensionService::getBaseDerivedUnit($this->dimension, true);
    }

    /**
     * Convert the DerivedUnit to its equivalent in English base units.
     *
     * @return self The new DerivedUnit.
     * @throws DomainException If any of the dimension codes are invalid.
     * @throws LogicException If any of the dimension codes do not have an English base unit defined.
     */
    public function toEnglishBase(): self
    {
        return DimensionService::getBaseDerivedUnit($this->dimension, false);
    }

    /**
     * Return a new DerivedUnit with all prefixes removed from all unit terms.
     *
     * @return self A new instance with no prefixes on any unit term.
     */
    public function removePrefixes(): self
    {
        $unitTerms = array_values(
            array_map(static fn (UnitTerm $unitTerm) => $unitTerm->removePrefix(), $this->unitTerms)
        );
        return new self($unitTerms);
    }

    /**
     * Attempt to expand this derived unit into base units.
     *
     * Expands each unit term individually and combines the results. All terms must be expandable for the expansion to
     * succeed.
     *
     * @return ?Quantity The expansion as a Quantity with base units, or null if any term cannot be expanded.
     */
    public function tryExpand(): ?Quantity
    {
        // Check if we found the expansion already.
        if ($this->expansion !== null) {
            return $this->expansion;
        }

        // Check if there's anything to do.
        if ($this->isBase()) {
            return null;
        }

        // Initialize result components.
        $resultValue = 1;
        $resultUnit = new self();

        // Expand any unit terms with expansions.
        foreach ($this->unitTerms as $unitTerm) {
            // Skip base units.
            if ($unitTerm->isBase()) {
                $resultUnit->addUnitTerm($unitTerm);
                continue;
            }

            // Try to get the expansion for this unit.
            $unitTermExpansion = $unitTerm->tryExpand();

            // If none found, the expansion for the full derived unit isn't discoverable yet either.
            if ($unitTermExpansion === null) {
                return null; // @codeCoverageIgnore
            }

            // Multiply by the conversion factor.
            $resultValue *= $unitTermExpansion->value;

            // Add the unit terms from the expansion.
            foreach ($unitTermExpansion->derivedUnit->unitTerms as $expansionUnitTerm) {
                $resultUnit->addUnitTerm($expansionUnitTerm);
            }
        }

        // Since a unit expansion can add different and new unit terms, we can end up with unmerged compatible units.
        // Merge compatible units if necessary.
        $this->expansion = Quantity::create($resultValue, $resultUnit)->merge();
        return $this->expansion;
    }

    /**
     * Merge units that have the same dimension, e.g. 'm' and 'ft', or 's' and 'h', or 'lb' and 'kg'.
     *
     * The first unit encountered of a given dimension will be the one any others are converted to.
     *
     * @return Quantity A new Quantity with the merged derived unit.
     */
    public function merge(): Quantity
    {
        // Initialize result components.
        $resultValue = 1.0;
        $resultUnit = new self();

        foreach ($this->unitTerms as $unitTerm) {
            // See if there is already a unit term with a unit with this dimension.
            $newUnitTerm1 = array_find(
                $resultUnit->unitTerms,
                static fn (UnitTerm $ut) => $ut->unit->dimension === $unitTerm->unit->dimension
            );

            // If no unit exists with this dimension, copy the existing one to the result.
            if ($newUnitTerm1 === null) {
                $resultUnit->addUnitTerm($unitTerm);
            } else {
                // If the unexponentiated units are different, convert one to the other.
                $unexponentiatedThisUnitTerm = $unitTerm->removeExponent();
                $unexponentiatedNewUnitTerm1 = $newUnitTerm1->removeExponent();
                if (!$unexponentiatedThisUnitTerm->equal($unexponentiatedNewUnitTerm1)) {
                    // Get the conversion factor from the existing to the new unit term.
                    $factor = ConversionService::findFactor($unexponentiatedThisUnitTerm, $unexponentiatedNewUnitTerm1);

                    // Multiply by the conversion factor raised to the exponent of the second unit term.
                    $resultValue *= $factor ** $unitTerm->exponent;
                }

                // Create a second term with the same unit as the first, but the exponent of the second term.
                $newUnitTerm2 = $newUnitTerm1->withExponent($unitTerm->exponent);

                // Adding the second unit term will combine it with the first because they have the same
                // unexponentiated symbol.
                $resultUnit->addUnitTerm($newUnitTerm2);
            }
        }

        // Construct a new Quantity from the merged value and unit.
        return Quantity::create($resultValue, $resultUnit);
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
        $aDimTerms = DimensionService::decompose($a->dimension);
        $bDimTerms = DimensionService::decompose($b->dimension);

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

        // First loop: compare all letters in the order given by DimensionService::DIMENSION_CODES.
        for ($i = 0; $i < $nTerms; $i++) {
            $cmp = DimensionService::letterToInt($aDims[$i]) <=> DimensionService::letterToInt($bDims[$i]);
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
        return 0; // @codeCoverageIgnore
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
            $dims = DimensionService::decompose($unitTerm->dimension);

            // Accumulate the exponents for each letter in the dimension code.
            foreach ($dims as $dimCode => $exp) {
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
        $this->dimension = DimensionService::compose($dimCodes);
    }

    // endregion
}
