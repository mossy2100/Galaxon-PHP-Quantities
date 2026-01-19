<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use DomainException;
use Galaxon\Core\Traits\Equatable;
use Galaxon\Core\Types;
use Galaxon\Quantities\Registry\DimensionRegistry;
use Galaxon\Quantities\Registry\PrefixRegistry;
use Override;
use Stringable;

/**
 * Represents a unit of measurement.
 */
class Unit implements Stringable
{
    use Equatable;

    // region Properties

    /**
     * The unit name (e.g. 'metre', 'gram', 'hertz').
     */
    public string $name;

    /**
     * The ASCII unit symbol (e.g. 'm', 'g', 'Hz').
     * This symbol is mainly for parsing from code, and must be ASCII.
     */
    public string $asciiSymbol;

    /**
     * The Unicode symbol (e.g. 'Ω' for ohm, '°' for degree), or null to use ASCII symbol.
     * This symbol is used for formatted output and can contain Unicode characters.
     */
    public ?string $unicodeSymbol;

    /**
     * The quantity type, e.g. 'length'.
     */
    public string $quantityType;

    /**
     * The dimension code (e.g. 'L', 'M', 'T-1').
     */
    public string $dimension;

    /**
     * The measurement system (e.g. 'si_base', 'si_named', 'metric', 'us_customary').
     */
    public string $system;

    /**
     * Bitwise flags indicating which prefixes are allowed (0 if none).
     */
    public int $prefixGroup;

    /**
     * The SI prefix for this unit (e.g. 'k' for kilogram), or null if none.
     */
    public ?string $siPrefix;

    /**
     * For named units, the expansion quantity's unit value. Defaults to 1.
     */
    public float $expansionValue;

    /**
     * For named units, the expansion quantity's unit symbol. Null if not applicable.
     */
    public ?string $expansionUnit;

    // endregion

    // region Property hooks

    // PHPCS doesn't know property hooks yet.
    // phpcs:disable PSR2.Classes.PropertyDeclaration
    // phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact

    public ?Quantity $expansion {
        get {
            if ($this->expansionUnit === null) {
                return null;
            }

            // Construct the equivalent quantity if not already done.
            $this->expansion ??= Quantity::create($this->expansionValue, $this->expansionUnit);
            return $this->expansion;
        }
    }

    // phpcs:enable PSR2.Classes.PropertyDeclaration
    // phpcs:enable Generic.WhiteSpace.ScopeIndent.IncorrectExact

    // endregion

    // region Constructor

    /**
     * Constructor.
     *
     * @param string $name The unit name.
     * @param array<string, mixed> $data The unit details.
     */
    public function __construct(string $name, array $data)
    {
        // Check ASCII symbol contains ASCII letters only.
        if (!self::isAsciiLetters($data['asciiSymbol'])) {
            throw new DomainException("Unit symbol '{$data['asciiSymbol']}' must contain only ASCII letters.");
        }

        // Set the properties.
        $this->name = $name;
        $this->asciiSymbol = $data['asciiSymbol'];
        $this->unicodeSymbol = $data['unicodeSymbol'] ?? null;
        $this->quantityType = $data['quantityType'];
        $this->dimension = DimensionRegistry::normalize($data['dimension']);
        $this->system = $data['system'];
        $this->prefixGroup = $data['prefixGroup'] ?? 0;
        $this->siPrefix = $data['siPrefix'] ?? null;
        $this->expansionValue = $data['expansionValue'] ?? 1;
        $this->expansionUnit = $data['expansionUnit'] ?? null;
    }

    // endregion

    // region Prefix methods

    /**
     * Check if this unit accepts prefixes.
     *
     * @return bool True if prefixes are allowed.
     */
    public function acceptsPrefixes(): bool
    {
        return $this->prefixGroup > 0;
    }

    /**
     * Check if a specific prefix is allowed for this unit.
     *
     * @param string $prefix The prefix to check.
     * @return bool True if the prefix is allowed.
     */
    public function acceptsPrefix(string $prefix): bool
    {
        $allowedPrefixes = $this->getAllowedPrefixes();
        return isset($allowedPrefixes[$prefix]);
    }

    /**
     * Get all allowed prefixes for this unit.
     *
     * @return array<string, float> Map of prefix symbols to multipliers.
     */
    public function getAllowedPrefixes(): array
    {
        return PrefixRegistry::getPrefixes($this->prefixGroup);
    }

    // endregion

    // region Inspection methods

    /**
     * Check if this is an SI base unit.
     *
     * @return bool True if this is an SI base unit.
     */
    public function isSiBase(): bool
    {
        return $this->system === 'si_base';
    }

    /**
     * Check if this is an SI derived unit.
     *
     * @return bool True if this is an SI derived unit.
     */
    public function isSiDerived(): bool
    {
        return $this->system === 'si_derived';
    }

    /**
     * Check if this is an SI named unit.
     *
     * @return bool True if this is an SI named unit.
     */
    public function isSiNamed(): bool
    {
        return $this->system === 'si_named';
    }

    /**
     * Check if this is any kind of SI unit.
     *
     * @return bool True if this is an SI unit.
     */
    public function isSi(): bool
    {
        return str_starts_with($this->system, 'si_');
    }

    /**
     * Check if this is a metric unit (SI or non-SI metric).
     *
     * @return bool True if this is a metric unit.
     */
    public function isMetric(): bool
    {
        return $this->isSi() || $this->system === 'metric';
    }

    // endregion

    // region Formatting methods

    /**
     * Format the unit as a string.
     *
     * If $ascii is false (default), returns the Unicode symbol if available, otherwise the ASCII symbol.
     * If $ascii is true, returns the ASCII symbol.
     *
     * @param bool $ascii If true, return ASCII symbol; if false (default), return Unicode symbol if available.
     * @return string The formatted unit.
     */
    public function format(bool $ascii = false): string
    {
        return $ascii ? $this->asciiSymbol : ($this->unicodeSymbol ?? $this->asciiSymbol);
    }

    /**
     * Convert the unit to a string. This will use the format version, which may include non-ASCII characters.
     * For the ASCII version, use format(true).
     *
     * @return string The unit as a string.
     */
    public function __toString(): string
    {
        return $this->format();
    }

    // endregion

    // region Comparison methods
    #[Override]
    public function equal(mixed $other): bool
    {
        // Check for same types.
        if (!Types::same($this, $other)) {
            return false;
        }

        return $this->name === $other->name;
    }

    // endregion

    // region Helper methods

    /**
     * Check if a string contains only ASCII letters (a-z, A-Z).
     *
     * @param string $symbol The string to check.
     * @return bool True if the string contains only ASCII letters.
     */
    private static function isAsciiLetters(string $symbol): bool
    {
        return (bool)preg_match('/^[a-z]+$/i', $symbol);
    }

    // endregion
}
