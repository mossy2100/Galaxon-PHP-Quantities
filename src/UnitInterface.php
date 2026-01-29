<?php

declare(strict_types=1);

namespace Galaxon\Quantities;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Stringable;

/**
 * Interface for unit types (Unit, UnitTerm, DerivedUnit).
 *
 * Defines common properties and methods for all unit representations.
 */
interface UnitInterface extends Stringable
{
    // region Property hooks

    // phpcs:disable PSR2.Classes.PropertyDeclaration
    // phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact

    /**
     * The ASCII symbol for the unit (e.g. 'm', 'km2', 'kg*m/s2').
     */
    public string $asciiSymbol { get; }

    /**
     * The Unicode symbol for the unit (e.g. 'm', 'km²', 'kg⋅m/s²').
     */
    public string $unicodeSymbol { get; }

    /**
     * The dimension code (e.g. 'L', 'L2', 'MLT-2').
     */
    public string $dimension { get; }

    // phpcs:enable PSR2.Classes.PropertyDeclaration
    // phpcs:enable Generic.WhiteSpace.ScopeIndent.IncorrectExact

    // endregion

    // region String methods

    /**
     * Parse a string into the unit type.
     *
     * @param string $symbol The unit symbol to parse.
     * @return static The parsed unit.
     * @throws FormatException If the symbol has the wrong format.
     * @throws DomainException If the symbol contains unknown units.
     */
    public static function parse(string $symbol): self;

    /**
     * Format the unit as a string.
     *
     * @param bool $ascii If true, return ASCII format; if false (default), return Unicode format.
     * @return string The formatted unit.
     */
    public function format(bool $ascii = false): string;

    // __toString() is inherited from Stringable

    // endregion
}
