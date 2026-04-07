<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\NonCurrencies\Services;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Core\Exceptions\NullArgumentException;
use Galaxon\Quantities\Exceptions\DimensionMismatchException;
use Galaxon\Quantities\Exceptions\UnknownUnitException;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Angle;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\QuantityType\Volume;
use Galaxon\Quantities\Services\QuantityPartsService;
use InvalidArgumentException;
use LengthException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for QuantityPartsService class.
 */
#[CoversClass(QuantityPartsService::class)]
final class QuantityPartsServiceTest extends TestCase
{
    // region Setup

    protected function setUp(): void
    {
        QuantityPartsService::reset();
    }

    // endregion

    // region fromParts() tests

    /**
     * Test fromParts() creates a quantity from time parts.
     */
    public function testFromPartsCreatesTimeQuantity(): void
    {
        $qty = QuantityPartsService::fromParts(Time::getQuantityType(), [
            'h'   => 1,
            'min' => 30,
            's'   => 45,
        ]);

        $this->assertInstanceOf(Time::class, $qty);
        $this->assertSame('s', $qty->compoundUnit->asciiSymbol);
        $this->assertEqualsWithDelta(5445.0, $qty->value, 1e-10);
    }

    /**
     * Test fromParts() with negative sign.
     */
    public function testFromPartsWithNegativeSign(): void
    {
        $qty = QuantityPartsService::fromParts(Time::getQuantityType(), [
            'h'    => 1,
            'min'  => 30,
            'sign' => -1,
        ]);

        $this->assertTrue($qty->value < 0);
        $this->assertEqualsWithDelta(-5400.0, $qty->value, 1e-10);
    }

    /**
     * Test fromParts() with angle parts.
     */
    public function testFromPartsCreatesAngleQuantity(): void
    {
        $qty = QuantityPartsService::fromParts(Angle::getQuantityType(), [
            'deg'    => 45,
            'arcmin' => 30,
            'arcsec' => 0,
        ]);

        $this->assertInstanceOf(Angle::class, $qty);
        $this->assertEqualsWithDelta(45.5, $qty->value, 1e-10);
    }

    /**
     * Test fromParts() with length parts.
     */
    public function testFromPartsCreatesLengthQuantity(): void
    {
        $qty = QuantityPartsService::fromParts(Length::getQuantityType(), [
            'ft' => 5,
            'in' => 11,
        ]);

        $this->assertInstanceOf(Length::class, $qty);
        $this->assertSame('ft', $qty->compoundUnit->asciiSymbol);
        // 5 ft + 11 in = 5 + 11/12 ft = 5.916666... ft.
        $this->assertEqualsWithDelta(5.916666666, $qty->value, 1e-6);
    }

    /**
     * Test fromParts() throws for invalid sign value.
     */
    public function testFromPartsThrowsForInvalidSign(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid sign: 2.');

        QuantityPartsService::fromParts(Time::getQuantityType(), [
            'h'    => 1,
            'sign' => 2,
        ]);
    }

    // endregion

    // region toParts() tests

    /**
     * Test toParts() throws for a quantity without a registered quantity type.
     */
    public function testToPartsThrowsForUnregisteredQuantityType(): void
    {
        $this->expectException(NullArgumentException::class);
        $this->expectExceptionMessage('must not be null');

        $entropy = Quantity::create(42, 'J/K');
        QuantityPartsService::toParts($entropy);
    }

    /**
     * Test toParts() decomposes a time quantity into parts.
     */
    public function testToPartsDecomposesTime(): void
    {
        $qty = new Time(5445, 's');
        $parts = QuantityPartsService::toParts($qty);

        $this->assertSame(1, $parts['sign']);
        $this->assertSame(1, $parts['h']);
        $this->assertSame(30, $parts['min']);
        $this->assertSame(45.0, $parts['s']);
    }

    /**
     * Test toParts() with negative value.
     */
    public function testToPartsWithNegativeValue(): void
    {
        $qty = new Time(-3661, 's');
        $parts = QuantityPartsService::toParts($qty);

        $this->assertSame(-1, $parts['sign']);
        $this->assertSame(1, $parts['h']);
        $this->assertSame(1, $parts['min']);
        $this->assertSame(1.0, $parts['s']);
    }

    /**
     * Test toParts() with precision rounding.
     */
    public function testToPartsWithPrecisionRoundsUp(): void
    {
        // 3661.567s = 1h 1min 1.567s. Rounding to 1dp gives 1.6s (rounds up, triggers rebuild).
        $qty = new Time(3661.567, 's');
        $parts = QuantityPartsService::toParts($qty, 1);

        $this->assertSame(1, $parts['h']);
        $this->assertSame(1, $parts['min']);
        $this->assertSame(1.6, $parts['s']);
    }

    /**
     * Test toParts() with precision that rounds down the smallest unit.
     */
    public function testToPartsWithPrecisionRoundsDown(): void
    {
        // 3661.3s = 1h 1min 1.3s. Rounding to 0dp gives 1.0s (rounds down, no rebuild needed).
        $qty = new Time(3661.3, 's');
        $parts = QuantityPartsService::toParts($qty, 0);

        $this->assertSame(1, $parts['h']);
        $this->assertSame(1, $parts['min']);
        $this->assertSame(1.0, $parts['s']);
    }

    /**
     * Test toParts() uses class defaults when no symbols provided.
     */
    public function testToPartsUsesClassDefaults(): void
    {
        $qty = new Angle(45.508333333, 'deg');
        $parts = QuantityPartsService::toParts($qty, precision: 0);

        $this->assertSame(45, $parts['deg']);
        $this->assertSame(30, $parts['arcmin']);
        $this->assertSame(30.0, $parts['arcsec']);
    }

    /**
     * Test toParts() with zero value.
     */
    public function testToPartsWithZeroValue(): void
    {
        $qty = new Time(0, 's');
        $parts = QuantityPartsService::toParts($qty);

        $this->assertSame(1, $parts['sign']);
        $this->assertSame(0, $parts['h']);
        $this->assertSame(0, $parts['min']);
        $this->assertSame(0.0, $parts['s']);
    }

    /**
     * Test toParts() rounding carries over to larger units.
     */
    public function testToPartsRoundingCarriesOver(): void
    {
        // 59 min 59.9 s → rounds to 1h 0min 0s at precision 0.
        $qty = new Time(3599.9, 's');
        $parts = QuantityPartsService::toParts($qty, 0);

        $this->assertSame(1, $parts['h']);
        $this->assertSame(0, $parts['min']);
        $this->assertSame(0.0, $parts['s']);
    }

    /**
     * Test toParts() throws for negative precision.
     */
    public function testToPartsThrowsForNegativePrecision(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid precision');

        $qty = new Time(3661, 's');
        QuantityPartsService::toParts($qty, -1);
    }

    // endregion

    // region parseParts() tests

    /**
     * Test parseParts() parses a time parts string.
     */
    public function testParsePartsTime(): void
    {
        $qty = QuantityPartsService::parseParts(Time::getQuantityType(), '1h 30min 45s');

        $this->assertInstanceOf(Time::class, $qty);
        $this->assertEqualsWithDelta(5445.0, $qty->value, 1e-10);
    }

    /**
     * Test parseParts() parses a negative time parts string.
     */
    public function testParsePartsNegativeTime(): void
    {
        $qty = QuantityPartsService::parseParts(Time::getQuantityType(), '-2h 15min');

        $this->assertTrue($qty->value < 0);
        $this->assertEqualsWithDelta(-8100.0, $qty->value, 1e-10);
    }

    /**
     * Test parseParts() with angle parts.
     */
    public function testParsePartsAngle(): void
    {
        $qty = QuantityPartsService::parseParts(Angle::getQuantityType(), '45deg 30arcmin');

        $this->assertInstanceOf(Angle::class, $qty);
        $this->assertEqualsWithDelta(45.5, $qty->value, 1e-6);
    }

    /**
     * Test parseParts() trims whitespace.
     */
    public function testParsePartsTrimsWhitespace(): void
    {
        $qty = QuantityPartsService::parseParts(Time::getQuantityType(), '  90min  ');

        $this->assertEqualsWithDelta(5400.0, $qty->value, 1e-10);
    }

    /**
     * Test parseParts() throws for empty input.
     */
    public function testParsePartsThrowsForEmptyInput(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('empty');

        QuantityPartsService::parseParts(Time::getQuantityType(), '');
    }

    /**
     * Test parseParts() throws for whitespace-only input.
     */
    public function testParsePartsThrowsForWhitespaceInput(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('empty');

        QuantityPartsService::parseParts(Time::getQuantityType(), '   ');
    }

    /**
     * Test parseParts() throws for invalid format.
     */
    public function testParsePartsThrowsForInvalidFormat(): void
    {
        $this->expectException(FormatException::class);

        QuantityPartsService::parseParts(Time::getQuantityType(), 'not a quantity');
    }

    /**
     * Test parseParts() throws for negative non-first part.
     */
    public function testParsePartsThrowsForNegativeNonFirstPart(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('only the first may be negative');

        QuantityPartsService::parseParts(Time::getQuantityType(), '1h -30min');
    }

    /**
     * Test parseParts() throws for dimensionless part (number without unit).
     */
    public function testParsePartsThrowsForDimensionlessPart(): void
    {
        $this->expectException(FormatException::class);

        QuantityPartsService::parseParts(Time::getQuantityType(), '42');
    }

    /**
     * Test parseParts() throws for multi-word unit with space.
     */
    public function testParsePartsThrowsForMultiWordUnit(): void
    {
        $this->expectException(FormatException::class);

        $volumeType = Volume::getQuantityType();
        $originalResult = QuantityPartsService::getResultUnitSymbol($volumeType);
        QuantityPartsService::setResultUnitSymbol($volumeType, 'L');
        try {
            // "10imp" parses as value=10 unit="imp", then "gal" fails as a quantity.
            QuantityPartsService::parseParts($volumeType, '10imp gal');
        } finally {
            QuantityPartsService::setResultUnitSymbol($volumeType, $originalResult);
        }
    }

    /**
     * Test parseParts() throws UnknownUnitException for unrecognised unit.
     */
    public function testParsePartsThrowsForUnknownUnit(): void
    {
        $this->expectException(UnknownUnitException::class);

        QuantityPartsService::parseParts(Time::getQuantityType(), '10xyz');
    }

    /**
     * Test parseParts() throws DimensionMismatchException for wrong dimension.
     */
    public function testParsePartsThrowsForDimensionMismatch(): void
    {
        $this->expectException(DimensionMismatchException::class);

        QuantityPartsService::parseParts(Time::getQuantityType(), '10mi 25ft');
    }

    /**
     * Test parseParts() throws for space between value and unit.
     */
    public function testParsePartsThrowsForSpaceBetweenValueAndUnit(): void
    {
        $this->expectException(FormatException::class);

        QuantityPartsService::parseParts(Time::getQuantityType(), '1 h 30 min');
    }

    // endregion

    // region formatParts() tests

    /**
     * Test formatParts() formats a time quantity.
     */
    public function testFormatPartsTime(): void
    {
        $qty = new Time(5445, 's');
        $result = QuantityPartsService::formatParts($qty);

        $this->assertStringContainsString('1h', $result);
        $this->assertStringContainsString('30min', $result);
        $this->assertStringContainsString('45s', $result);
    }

    /**
     * Test formatParts() with negative value.
     */
    public function testFormatPartsNegative(): void
    {
        $qty = new Time(-3661, 's');
        $result = QuantityPartsService::formatParts($qty);

        $this->assertStringStartsWith('-', $result);
        $this->assertStringContainsString('1h', $result);
        $this->assertStringContainsString('1min', $result);
        $this->assertStringContainsString('1s', $result);
    }

    /**
     * Test formatParts() with showZeros flag.
     */
    public function testFormatPartsShowZeros(): void
    {
        $qty = new Time(3600, 's');
        $result = QuantityPartsService::formatParts($qty, showZeros: true);

        $this->assertStringContainsString('1h', $result);
        $this->assertStringContainsString('0min', $result);
        $this->assertStringContainsString('0s', $result);
    }

    /**
     * Test formatParts() without showZeros skips zero parts.
     */
    public function testFormatPartsSkipsZeros(): void
    {
        $qty = new Time(3600, 's');
        $result = QuantityPartsService::formatParts($qty, showZeros: false);

        // Non-zero parts should be present, zero parts should be skipped.
        $this->assertStringContainsString('1h', $result);
        $this->assertStringNotContainsString('0min', $result);
    }

    /**
     * Test formatParts() with zero value shows the smallest unit.
     */
    public function testFormatPartsZeroValue(): void
    {
        $qty = new Time(0, 's');
        $result = QuantityPartsService::formatParts($qty);

        $this->assertSame('0s', $result);
    }

    /**
     * Test formatParts() with precision.
     */
    public function testFormatPartsWithPrecision(): void
    {
        $qty = new Time(3661.567, 's');
        $result = QuantityPartsService::formatParts($qty, precision: 2);

        $this->assertSame('1h 1min 1.57s', $result);
    }

    /**
     * Test formatParts() with ascii flag.
     */
    public function testFormatPartsAscii(): void
    {
        $qty = new Angle(45.508333333, 'deg');
        $resultUnicode = QuantityPartsService::formatParts($qty, precision: 0);
        $resultAscii = QuantityPartsService::formatParts($qty, precision: 0, ascii: true);

        // Unicode uses ° ′ ″ symbols.
        $this->assertStringContainsString('°', $resultUnicode);
        // ASCII uses deg, arcmin, arcsec.
        $this->assertStringContainsString('deg', $resultAscii);
    }

    // endregion

    // region Configuration methods tests

    /**
     * Test getPartUnitSymbols() returns configured symbols.
     */
    public function testGetPartUnitSymbolsReturnsConfigured(): void
    {
        $symbols = QuantityPartsService::getPartUnitSymbols(
            Time::getQuantityType()
        );

        $this->assertSame(['y', 'mo', 'w', 'd', 'h', 'min', 's'], $symbols);
    }

    /**
     * Test getPartUnitSymbols() returns null for unconfigured type.
     */
    public function testGetPartUnitSymbolsReturnsNullForUnconfigured(): void
    {
        // Mass has a resultUnitSymbol but no partUnitSymbols configured.
        $symbols = QuantityPartsService::getPartUnitSymbols(
            Mass::getQuantityType()
        );

        $this->assertNull($symbols);
    }

    /**
     * Test setPartUnitSymbols() changes the symbols.
     */
    public function testSetPartUnitSymbolsChangesSymbols(): void
    {
        $qtyType = Time::getQuantityType();

        $original = QuantityPartsService::getPartUnitSymbols($qtyType);

        try {
            QuantityPartsService::setPartUnitSymbols($qtyType, ['h', 'min', 's']);
            $this->assertSame(['h', 'min', 's'], QuantityPartsService::getPartUnitSymbols($qtyType));
        } finally {
            QuantityPartsService::setPartUnitSymbols($qtyType, $original);
        }
    }

    /**
     * Test setPartUnitSymbols() deduplicates symbols.
     */
    public function testSetPartUnitSymbolsDeduplicates(): void
    {
        $qtyType = Time::getQuantityType();

        $original = QuantityPartsService::getPartUnitSymbols($qtyType);

        try {
            QuantityPartsService::setPartUnitSymbols($qtyType, ['h', 'min', 'h', 's']);
            $this->assertSame(['h', 'min', 's'], QuantityPartsService::getPartUnitSymbols($qtyType));
        } finally {
            QuantityPartsService::setPartUnitSymbols($qtyType, $original);
        }
    }

    /**
     * Test setPartUnitSymbols(null) clears the symbols.
     */
    public function testSetPartUnitSymbolsNullClearsSymbols(): void
    {
        $qtyType = Time::getQuantityType();

        $original = QuantityPartsService::getPartUnitSymbols($qtyType);

        try {
            QuantityPartsService::setPartUnitSymbols($qtyType, null);
            $this->assertNull(QuantityPartsService::getPartUnitSymbols($qtyType));
        } finally {
            QuantityPartsService::setPartUnitSymbols($qtyType, $original);
        }
    }

    /**
     * Test setPartUnitSymbols() throws for empty array.
     */
    public function testSetPartUnitSymbolsThrowsForEmptyArray(): void
    {
        $this->expectException(LengthException::class);
        $this->expectExceptionMessage('Cannot set an empty array');

        QuantityPartsService::setPartUnitSymbols(
            Time::getQuantityType(),
            []
        );
    }

    /**
     * Test setPartUnitSymbols() throws for non-string items.
     */
    public function testSetPartUnitSymbolsThrowsForNonStringItems(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must contain only strings');

        QuantityPartsService::setPartUnitSymbols(
            Time::getQuantityType(),
            ['h', 42] // @phpstan-ignore argument.type
        );
    }

    /**
     * Test setPartUnitSymbols() throws for empty string symbol.
     */
    public function testSetPartUnitSymbolsThrowsForEmptyStringSymbol(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid part unit symbol');

        QuantityPartsService::setPartUnitSymbols(
            Time::getQuantityType(),
            ['h', '']
        );
    }

    /**
     * Test setPartUnitSymbols() throws for invalid unit symbol.
     */
    public function testSetPartUnitSymbolsThrowsForInvalidSymbol(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid part unit symbol');

        QuantityPartsService::setPartUnitSymbols(
            Time::getQuantityType(),
            ['h', '123']
        );
    }

    /**
     * Test getResultUnitSymbol() returns configured symbol.
     */
    public function testGetResultUnitSymbolReturnsConfigured(): void
    {
        $symbol = QuantityPartsService::getResultUnitSymbol(
            Time::getQuantityType()
        );

        $this->assertSame('s', $symbol);
    }

    /**
     * Test setResultUnitSymbol() changes the symbol.
     */
    public function testSetResultUnitSymbolChangesSymbol(): void
    {
        $qtyType = Time::getQuantityType();

        $original = QuantityPartsService::getResultUnitSymbol($qtyType);

        try {
            QuantityPartsService::setResultUnitSymbol($qtyType, 'min');
            $this->assertSame('min', QuantityPartsService::getResultUnitSymbol($qtyType));
        } finally {
            QuantityPartsService::setResultUnitSymbol($qtyType, $original);
        }
    }

    /**
     * Test setResultUnitSymbol(null) clears the symbol.
     */
    public function testSetResultUnitSymbolNullClearsSymbol(): void
    {
        $qtyType = Time::getQuantityType();

        $original = QuantityPartsService::getResultUnitSymbol($qtyType);

        try {
            QuantityPartsService::setResultUnitSymbol($qtyType, null);
            $this->assertNull(QuantityPartsService::getResultUnitSymbol($qtyType));
        } finally {
            QuantityPartsService::setResultUnitSymbol($qtyType, $original);
        }
    }

    /**
     * Test setResultUnitSymbol() throws for empty string.
     */
    public function testSetResultUnitSymbolThrowsForEmptyString(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot set an empty string as result unit symbol');

        QuantityPartsService::setResultUnitSymbol(
            Time::getQuantityType(),
            ''
        );
    }

    /**
     * Test reset() restores default configuration.
     */
    public function testResetRestoresDefaults(): void
    {
        $qtyType = Time::getQuantityType();


        // Change something.
        QuantityPartsService::setPartUnitSymbols($qtyType, ['h', 'min', 's']);
        $this->assertSame(['h', 'min', 's'], QuantityPartsService::getPartUnitSymbols($qtyType));

        // Reset and verify defaults are restored.
        QuantityPartsService::reset();
        $this->assertSame(
            ['y', 'mo', 'w', 'd', 'h', 'min', 's'],
            QuantityPartsService::getPartUnitSymbols($qtyType)
        );
    }

    // endregion

    // region fromParts() error tests

    /**
     * Test fromParts() throws when no result unit is configured.
     */
    public function testFromPartsThrowsForNoResultUnit(): void
    {
        $qtyType = Time::getQuantityType();

        $original = QuantityPartsService::getResultUnitSymbol($qtyType);

        try {
            QuantityPartsService::setResultUnitSymbol($qtyType, null);

            $this->expectException(DomainException::class);
            $this->expectExceptionMessage('No result unit symbol configured');

            QuantityPartsService::fromParts($qtyType, [
                'h' => 1,
            ]);
        } finally {
            QuantityPartsService::setResultUnitSymbol($qtyType, $original);
        }
    }

    /**
     * Test fromParts() throws for unknown result unit symbol.
     */
    public function testFromPartsThrowsForUnknownResultUnit(): void
    {
        $qtyType = Time::getQuantityType();

        $original = QuantityPartsService::getResultUnitSymbol($qtyType);

        try {
            QuantityPartsService::setResultUnitSymbol($qtyType, 'x');

            $this->expectException(DomainException::class);
            $this->expectExceptionMessage("Unknown result unit 'x'");

            QuantityPartsService::fromParts($qtyType, [
                'h' => 1,
            ]);
        } finally {
            QuantityPartsService::setResultUnitSymbol($qtyType, $original);
        }
    }

    /**
     * Test fromParts() throws for incompatible result unit.
     */
    public function testFromPartsThrowsForIncompatibleResultUnit(): void
    {
        $qtyType = Time::getQuantityType();

        $original = QuantityPartsService::getResultUnitSymbol($qtyType);

        try {
            // Set a length unit as the result for a time quantity type.
            QuantityPartsService::setResultUnitSymbol($qtyType, 'm');

            $this->expectException(DomainException::class);
            $this->expectExceptionMessage('incompatible');

            QuantityPartsService::fromParts($qtyType, [
                'h' => 1,
            ]);
        } finally {
            QuantityPartsService::setResultUnitSymbol($qtyType, $original);
        }
    }

    // endregion

    // region parseParts() error tests

    /**
     * Test parseParts() throws when no result unit is configured.
     */
    public function testParsePartsThrowsForNoResultUnit(): void
    {
        $qtyType = Time::getQuantityType();

        $original = QuantityPartsService::getResultUnitSymbol($qtyType);

        try {
            QuantityPartsService::setResultUnitSymbol($qtyType, null);

            $this->expectException(DomainException::class);
            $this->expectExceptionMessage('No result unit symbol configured');

            QuantityPartsService::parseParts($qtyType, '1h 30min');
        } finally {
            QuantityPartsService::setResultUnitSymbol($qtyType, $original);
        }
    }

    // endregion

    // region formatParts() additional tests

    /**
     * Test formatParts() with ascii flag on Time quantity.
     */
    public function testFormatPartsAsciiTime(): void
    {
        $qty = new Time(3661, 's');
        $result = QuantityPartsService::formatParts($qty, ascii: true);

        $this->assertSame('1h 1min 1s', $result);
    }

    /**
     * Test formatParts() with precision where smallest part has no fractional component.
     */
    public function testFormatPartsWithPrecisionNoFraction(): void
    {
        $qty = new Time(3660, 's');
        $result = QuantityPartsService::formatParts($qty, precision: 2, showZeros: true);

        $this->assertSame('0y 0mo 0w 0d 1h 1min 0.00s', $result);
    }

    // endregion

    // region validatePartUnitSymbols() coverage tests

    /**
     * Test toParts() throws when part unit symbols are not configured (empty).
     */
    public function testToPartsThrowsForEmptyPartUnitSymbols(): void
    {
        // Mass has no partUnitSymbols configured.
        $qty = new Mass(100, 'kg');

        $this->expectException(LengthException::class);
        $this->expectExceptionMessage('Cannot use an empty array');

        QuantityPartsService::toParts($qty);
    }

    /**
     * Test toParts() throws when part unit symbols contain an unknown unit.
     */
    public function testToPartsThrowsForUnknownPartUnitSymbol(): void
    {
        $qtyType = Time::getQuantityType();

        $original = QuantityPartsService::getPartUnitSymbols($qtyType);

        try {
            QuantityPartsService::setPartUnitSymbols($qtyType, ['h', 'min', 'xyz']);

            $this->expectException(UnknownUnitException::class);
            $this->expectExceptionMessage("Unknown unit: 'xyz'");

            $qty = new Time(3661, 's');
            QuantityPartsService::toParts($qty);
        } finally {
            QuantityPartsService::setPartUnitSymbols($qtyType, $original);
        }
    }

    // endregion

    // region Explicit symbol parameter tests

    /**
     * Test toParts() with an explicit $partUnitSymbols argument bypasses the configured default.
     */
    public function testToPartsWithExplicitPartUnitSymbols(): void
    {
        $qty = new Time(3661, 's');
        $parts = QuantityPartsService::toParts($qty, partUnitSymbols: ['h', 'min', 's']);
        $this->assertSame(1, $parts['sign']);
        $this->assertSame(1, $parts['h']);
        $this->assertSame(1, $parts['min']);
        $this->assertSame(1.0, $parts['s']);
        // Default Time parts include y, mo, w, d — none should appear here.
        $this->assertArrayNotHasKey('d', $parts);
        $this->assertArrayNotHasKey('y', $parts);
    }

    /**
     * Test toParts() with explicit symbols does not mutate the configured default.
     */
    public function testToPartsExplicitSymbolsLeavesDefaultsIntact(): void
    {
        $original = QuantityPartsService::getPartUnitSymbols(Time::getQuantityType());
        $qty = new Time(3661, 's');
        QuantityPartsService::toParts($qty, partUnitSymbols: ['h', 'min', 's']);
        $this->assertSame($original, QuantityPartsService::getPartUnitSymbols(Time::getQuantityType()));
    }

    /**
     * Test toParts() with explicit Length symbols.
     */
    public function testToPartsExplicitLengthSymbols(): void
    {
        $height = new Length(68, 'in');
        $parts = QuantityPartsService::toParts($height, partUnitSymbols: ['ft', 'in']);
        $this->assertSame(1, $parts['sign']);
        $this->assertSame(5, $parts['ft']);
        $this->assertSame(8.0, $parts['in']);
    }

    /**
     * Test toParts() with explicit symbols and a precision argument.
     */
    public function testToPartsExplicitSymbolsWithPrecision(): void
    {
        $qty = new Time(3661.789, 's');
        $parts = QuantityPartsService::toParts($qty, precision: 1, partUnitSymbols: ['h', 'min', 's']);
        $this->assertSame(1, $parts['h']);
        $this->assertSame(1, $parts['min']);
        $this->assertSame(1.8, $parts['s']);
    }

    /**
     * Test toParts() throws when an explicit empty array is passed.
     */
    public function testToPartsExplicitEmptyArrayThrows(): void
    {
        $this->expectException(LengthException::class);

        $qty = new Time(60, 's');
        QuantityPartsService::toParts($qty, partUnitSymbols: []);
    }

    /**
     * Test toParts() throws when an explicit symbol is unknown.
     */
    public function testToPartsExplicitUnknownSymbolThrows(): void
    {
        $this->expectException(UnknownUnitException::class);
        $this->expectExceptionMessage("Unknown unit: 'xyz'");

        $qty = new Time(60, 's');
        QuantityPartsService::toParts($qty, partUnitSymbols: ['h', 'xyz']);
    }

    /**
     * Test formatParts() with an explicit $partUnitSymbols argument.
     */
    public function testFormatPartsWithExplicitPartUnitSymbols(): void
    {
        $qty = new Time(3661, 's');
        $result = QuantityPartsService::formatParts($qty, partUnitSymbols: ['h', 'min', 's']);
        $this->assertSame('1h 1min 1s', $result);
    }

    /**
     * Test formatParts() explicit symbols does not mutate the configured default.
     */
    public function testFormatPartsExplicitSymbolsLeavesDefaultsIntact(): void
    {
        $original = QuantityPartsService::getPartUnitSymbols(Time::getQuantityType());
        $qty = new Time(3661, 's');
        QuantityPartsService::formatParts($qty, partUnitSymbols: ['h', 'min', 's']);
        $this->assertSame($original, QuantityPartsService::getPartUnitSymbols(Time::getQuantityType()));
    }

    /**
     * Test formatParts() with explicit symbols, precision, and showZeros.
     */
    public function testFormatPartsExplicitSymbolsWithOptions(): void
    {
        $qty = new Time(3600, 's');
        $result = QuantityPartsService::formatParts(
            $qty,
            precision: 0,
            showZeros: true,
            partUnitSymbols: ['h', 'min', 's']
        );
        $this->assertSame('1h 0min 0s', $result);
    }

    /**
     * Test formatParts() with explicit Length symbols.
     */
    public function testFormatPartsExplicitLengthSymbols(): void
    {
        $height = new Length(68, 'in');
        $result = QuantityPartsService::formatParts($height, partUnitSymbols: ['ft', 'in']);
        $this->assertSame('5ft 8in', $result);
    }

    /**
     * Test fromParts() with an explicit $resultUnitSymbol argument.
     */
    public function testFromPartsWithExplicitResultUnitSymbol(): void
    {
        $qty = QuantityPartsService::fromParts(
            Time::getQuantityType(),
            ['h' => 1, 'min' => 30],
            resultUnitSymbol: 'min'
        );
        $this->assertSame(90.0, $qty->value);
        $this->assertSame('min', $qty->compoundUnit->asciiSymbol);
    }

    /**
     * Test fromParts() explicit result unit does not mutate the configured default.
     */
    public function testFromPartsExplicitResultUnitLeavesDefaultsIntact(): void
    {
        $original = QuantityPartsService::getResultUnitSymbol(Time::getQuantityType());
        QuantityPartsService::fromParts(
            Time::getQuantityType(),
            ['h' => 1],
            resultUnitSymbol: 'min'
        );
        $this->assertSame($original, QuantityPartsService::getResultUnitSymbol(Time::getQuantityType()));
    }

    /**
     * Test fromParts() throws when the explicit result unit is unknown.
     */
    public function testFromPartsExplicitUnknownResultUnitThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Unknown result unit 'xyz'");

        QuantityPartsService::fromParts(
            Time::getQuantityType(),
            ['h' => 1],
            resultUnitSymbol: 'xyz'
        );
    }

    /**
     * Test fromParts() throws when the explicit result unit has a different dimension.
     */
    public function testFromPartsExplicitIncompatibleResultUnitThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Result unit 'kg' is incompatible with time quantities.");

        QuantityPartsService::fromParts(
            Time::getQuantityType(),
            ['h' => 1],
            resultUnitSymbol: 'kg'
        );
    }

    /**
     * Test parseParts() with an explicit $resultUnitSymbol argument.
     */
    public function testParsePartsWithExplicitResultUnitSymbol(): void
    {
        $qty = QuantityPartsService::parseParts(
            Time::getQuantityType(),
            '1h 30min',
            resultUnitSymbol: 'min'
        );
        $this->assertSame(90.0, $qty->value);
        $this->assertSame('min', $qty->compoundUnit->asciiSymbol);
    }

    /**
     * Test parseParts() explicit result unit does not mutate the configured default.
     */
    public function testParsePartsExplicitResultUnitLeavesDefaultsIntact(): void
    {
        $original = QuantityPartsService::getResultUnitSymbol(Time::getQuantityType());
        QuantityPartsService::parseParts(
            Time::getQuantityType(),
            '1h',
            resultUnitSymbol: 'min'
        );
        $this->assertSame($original, QuantityPartsService::getResultUnitSymbol(Time::getQuantityType()));
    }

    // endregion
}
