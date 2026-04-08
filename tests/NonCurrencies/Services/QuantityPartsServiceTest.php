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
use Galaxon\Quantities\QuantityType\Force;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\QuantityType\Volume;
use Galaxon\Quantities\Services\QuantityPartsService;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for QuantityPartsService class.
 */
#[CoversClass(QuantityPartsService::class)]
final class QuantityPartsServiceTest extends TestCase
{
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

        // "10imp" parses as value=10 unit="imp", then "gal" fails as a quantity.
        QuantityPartsService::parseParts(Volume::getQuantityType(), '10imp gal', resultUnitSymbol: 'L');
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

    // region fromParts() error tests

    /**
     * Test fromParts() throws for an unknown inline result unit symbol.
     */
    public function testFromPartsThrowsForUnknownResultUnit(): void
    {
        $this->expectException(UnknownUnitException::class);
        $this->expectExceptionMessage("Unknown result unit 'x'");

        QuantityPartsService::fromParts(
            Time::getQuantityType(),
            [
                'h' => 1,
            ],
            resultUnitSymbol: 'x'
        );
    }

    /**
     * Test fromParts() throws for an inline result unit incompatible with the quantity type.
     */
    public function testFromPartsThrowsForIncompatibleResultUnit(): void
    {
        $this->expectException(DimensionMismatchException::class);
        $this->expectExceptionMessage('incompatible');

        QuantityPartsService::fromParts(
            Time::getQuantityType(),
            [
                'h' => 1,
            ],
            resultUnitSymbol: 'm'
        );
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

    // region toParts() error tests

    /**
     * Test toParts() throws when no part unit symbols are configured for the quantity type.
     */
    public function testToPartsThrowsForEmptyPartUnitSymbols(): void
    {
        // Mass has no partUnitSymbols configured.
        $qty = new Mass(100, 'kg');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('No default part unit symbols configured for mass quantities');

        QuantityPartsService::toParts($qty);
    }

    /**
     * Test toParts() throws when an inline part unit symbol is unknown.
     */
    public function testToPartsThrowsForUnknownPartUnitSymbol(): void
    {
        $this->expectException(UnknownUnitException::class);
        $this->expectExceptionMessage("Unknown unit: 'xyz'");

        $qty = new Time(3661, 's');
        QuantityPartsService::toParts($qty, partUnitSymbols: ['h', 'min', 'xyz']);
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
     * Test toParts() with explicit symbols does not affect a subsequent default-decomposition call.
     */
    public function testToPartsExplicitSymbolsLeavesDefaultsIntact(): void
    {
        $qty = new Time(3661, 's');
        QuantityPartsService::toParts($qty, partUnitSymbols: ['h', 'min', 's']);
        $defaultParts = QuantityPartsService::toParts($qty);
        // Default Time parts include the larger units (y, mo, w, d).
        $this->assertArrayHasKey('d', $defaultParts);
        $this->assertArrayHasKey('y', $defaultParts);
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
        $this->expectException(LogicException::class);

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
     * Test formatParts() with explicit symbols does not affect a subsequent default-format call.
     */
    public function testFormatPartsExplicitSymbolsLeavesDefaultsIntact(): void
    {
        $qty = new Time(3661, 's');
        QuantityPartsService::formatParts($qty, partUnitSymbols: ['h', 'min', 's']);
        // The default Time formatting still uses the full part list.
        $this->assertSame('1h 1min 1s', QuantityPartsService::formatParts($qty));
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
            [
                'h'   => 1,
                'min' => 30,
            ],
            resultUnitSymbol: 'min'
        );
        $this->assertSame(90.0, $qty->value);
        $this->assertSame('min', $qty->compoundUnit->asciiSymbol);
    }

    /**
     * Test fromParts() with an explicit result unit does not affect a subsequent default call.
     */
    public function testFromPartsExplicitResultUnitLeavesDefaultsIntact(): void
    {
        QuantityPartsService::fromParts(
            Time::getQuantityType(),
            [
                'h' => 1,
            ],
            resultUnitSymbol: 'min'
        );
        $qty = QuantityPartsService::fromParts(Time::getQuantityType(), [
            'h' => 1,
        ]);
        $this->assertSame('s', $qty->compoundUnit->asciiSymbol);
    }

    /**
     * Test fromParts() throws when the explicit result unit is unknown.
     */
    public function testFromPartsExplicitUnknownResultUnitThrows(): void
    {
        $this->expectException(UnknownUnitException::class);
        $this->expectExceptionMessage("Unknown result unit 'xyz'");

        QuantityPartsService::fromParts(
            Time::getQuantityType(),
            [
                'h' => 1,
            ],
            resultUnitSymbol: 'xyz'
        );
    }

    /**
     * Test fromParts() throws when the explicit result unit has a different dimension.
     */
    public function testFromPartsExplicitIncompatibleResultUnitThrows(): void
    {
        $this->expectException(DimensionMismatchException::class);
        $this->expectExceptionMessage(
            "Result unit 'kg' (dimension 'M') is incompatible with time quantities (dimension 'T')."
        );

        QuantityPartsService::fromParts(
            Time::getQuantityType(),
            [
                'h' => 1,
            ],
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
     * Test parseParts() with an explicit result unit does not affect a subsequent default call.
     */
    public function testParsePartsExplicitResultUnitLeavesDefaultsIntact(): void
    {
        QuantityPartsService::parseParts(
            Time::getQuantityType(),
            '1h',
            resultUnitSymbol: 'min'
        );
        $qty = QuantityPartsService::parseParts(Time::getQuantityType(), '1h');
        $this->assertSame('s', $qty->compoundUnit->asciiSymbol);
    }

    /**
     * Test fromParts() throws when no result unit is provided and the quantity type has no default.
     *
     * Force has no entry in DEFAULT_PARTS_CONFIGS, so passing null for $resultUnitSymbol
     * (or omitting it) leaves nothing for validateResultUnit() to fall back to.
     */
    public function testFromPartsThrowsWhenNoDefaultResultUnit(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('No default result unit symbol configured for force quantities');

        QuantityPartsService::fromParts(Force::getQuantityType(), [
            'N' => 1,
        ]);
    }

    /**
     * Test toParts() throws when the part unit symbols array contains a non-string item.
     *
     * The parameter is typed `?list<string>`, so this is a defensive runtime check that
     * cannot be reached without bypassing the type system.
     */
    public function testToPartsThrowsForNonStringPartUnitSymbol(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must contain only strings');

        $qty = new Time(60, 's');
        QuantityPartsService::toParts($qty, partUnitSymbols: ['h', 42, 's']);
    }

    // endregion
}
