<?php

declare(strict_types=1);

namespace Galaxon\Quantities\QuantityType;

use Galaxon\Quantities\Internal\UnitSystem;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\Services\PrefixService;
use Override;

/**
 * Represents electrical resistance quantities.
 */
class Resistance extends Quantity
{
    // region Overridden methods

    /**
     * Unit definitions for electrical resistance.
     *
     * The ohm has three canonical symbol representations. The choice of which to use in each context is driven by
     * the following references.
     *
     * From Wikipedia:
     * "Where the character set is limited to ASCII, the IEEE 260.1 standard recommends using the unit name "ohm"
     * as a symbol instead of Ω."
     *
     * "Unicode encodes the symbol as U+2126 Ω OHM SIGN, distinct from Greek omega among letterlike symbols, but
     * it is only included for backward compatibility and the Greek uppercase omega character U+03A9 Ω GREEK
     * CAPITAL LETTER OMEGA is preferred."
     *
     * From The Unicode Standard:
     * "The ohm sign is canonically equivalent to the capital omega, and normalization would remove any distinction. Its
     * use is therefore discouraged in favor of capital omega."
     *
     * This is why:
     * 1. 'ohm' is used for the ASCII symbol.
     * 2. U+03A9 (GREEK CAPITAL LETTER OMEGA) is used for formatting.
     * 3. U+2126 (OHM SIGN) is accepted by the parser for backwards compatibility.
     */
    #[Override]
    public static function getUnitDefinitions(): array
    {
        return [
            'ohm' => [
                'asciiSymbol'     => 'ohm',
                'unicodeSymbol'   => 'Ω',
                'alternateSymbol' => 'Ω',
                'prefixGroup'     => PrefixService::GROUP_METRIC,
                'systems'         => [UnitSystem::Si],
            ],
        ];
    }

    /**
     * Conversion factors for electrical resistance units.
     *
     * @return list<array{string, string, float}>
     */
    #[Override]
    public static function getConversionDefinitions(): array
    {
        return [
            ['ohm', 'kg*m2*s-3*A-2', 1],
        ];
    }

    // endregion
}
