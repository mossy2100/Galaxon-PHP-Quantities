<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\QuantityType;

use DomainException;
use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\QuantityType\Angle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Angle quantity type.
 */
#[CoversClass(Angle::class)]
final class AngleTest extends TestCase
{
    use FloatAssertions;

    // region Conversion tests

    /**
     * Test converting degrees to radians.
     */
    public function testConvertDegreesToRadians(): void
    {
        $angle = new Angle(180, 'deg');
        $rad = $angle->to('rad');

        $this->assertInstanceOf(Angle::class, $rad);
        $this->assertApproxEqual(M_PI, $rad->value);
    }

    /**
     * Test converting radians to degrees.
     */
    public function testConvertRadiansToDegrees(): void
    {
        $angle = new Angle(M_PI / 2, 'rad');
        $deg = $angle->to('deg');

        $this->assertApproxEqual(90.0, $deg->value);
    }

    /**
     * Test converting degrees to gradians.
     */
    public function testConvertDegreesToGradians(): void
    {
        $angle = new Angle(90, 'deg');
        $grad = $angle->to('grad');

        $this->assertApproxEqual(100.0, $grad->value);
    }

    /**
     * Test converting turns to degrees.
     */
    public function testConvertTurnsToDegrees(): void
    {
        $angle = new Angle(0.5, 'turn');
        $deg = $angle->to('deg');

        $this->assertSame(180.0, $deg->value);
    }

    /**
     * Test converting degrees to arcminutes.
     */
    public function testConvertDegreesToArcminutes(): void
    {
        $angle = new Angle(1, 'deg');
        $arcmin = $angle->to('arcmin');

        $this->assertSame(60.0, $arcmin->value);
    }

    /**
     * Test converting arcminutes to arcseconds.
     */
    public function testConvertArcminutesToArcseconds(): void
    {
        $angle = new Angle(1, 'arcmin');
        $arcsec = $angle->to('arcsec');

        $this->assertSame(60.0, $arcsec->value);
    }

    /**
     * Test creating angle with single quote for arcminutes.
     */
    public function testConstructorSingleQuoteArcminutes(): void
    {
        $angle = new Angle(60, "'");

        $this->assertInstanceOf(Angle::class, $angle);
        $this->assertSame(60.0, $angle->value);
        $this->assertSame('arcmin', $angle->derivedUnit->asciiSymbol);
    }

    /**
     * Test creating angle with double quote for arcseconds.
     */
    public function testConstructorDoubleQuoteArcseconds(): void
    {
        $angle = new Angle(60, '"');

        $this->assertInstanceOf(Angle::class, $angle);
        $this->assertSame(60.0, $angle->value);
        $this->assertSame('arcsec', $angle->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting arcminutes created with single quote.
     */
    public function testConvertSingleQuoteArcminutesToDegrees(): void
    {
        $angle = new Angle(60, "'");
        $deg = $angle->to('deg');

        $this->assertSame(1.0, $deg->value);
    }

    /**
     * Test converting arcseconds created with double quote.
     */
    public function testConvertDoubleQuoteArcsecondsToDegrees(): void
    {
        $angle = new Angle(3600, '"');
        $deg = $angle->to('deg');

        $this->assertSame(1.0, $deg->value);
    }

    // endregion

    // region Inspection methods tests

    /**
     * Test isRadians returns true for radians.
     */
    public function testIsRadiansTrue(): void
    {
        $angle = new Angle(M_PI, 'rad');

        $this->assertTrue($angle->isRadians());
    }

    /**
     * Test isRadians returns false for degrees.
     */
    public function testIsRadiansFalse(): void
    {
        $angle = new Angle(180, 'deg');

        $this->assertFalse($angle->isRadians());
    }

    /**
     * Test toRadians for angle already in radians.
     */
    public function testToRadiansFromRadians(): void
    {
        $angle = new Angle(M_PI, 'rad');

        $this->assertSame(M_PI, $angle->toRadians());
    }

    /**
     * Test toRadians for angle in degrees.
     */
    public function testToRadiansFromDegrees(): void
    {
        $angle = new Angle(180, 'deg');

        $this->assertApproxEqual(M_PI, $angle->toRadians());
    }

    // endregion

    // region Parse tests - DMS notation

    /**
     * Test parsing DMS notation with all components.
     */
    public function testParseDmsNotation(): void
    {
        $angle = Angle::parse('45° 30′ 15″');

        $this->assertInstanceOf(Angle::class, $angle);
        // 45 + 30/60 + 15/3600 = 45.504166...
        $this->assertApproxEqual(45.504166666666666, $angle->value);
        $this->assertSame('deg', $angle->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing degrees-only with degree symbol.
     */
    public function testParseDmsDegreesOnly(): void
    {
        $angle = Angle::parse('90°');

        $this->assertSame(90.0, $angle->value);
        $this->assertSame('deg', $angle->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing DMS with degrees and minutes.
     */
    public function testParseDmsDegreesAndMinutes(): void
    {
        $angle = Angle::parse('45° 30′');

        // 45 + 30/60 = 45.5
        $this->assertSame(45.5, $angle->value);
    }

    /**
     * Test parsing DMS with ASCII quote characters.
     */
    public function testParseDmsAsciiQuotes(): void
    {
        $angle = Angle::parse("45° 30' 15\"");

        // 45 + 30/60 + 15/3600 = 45.504166...
        $this->assertApproxEqual(45.504166666666666, $angle->value);
    }

    /**
     * Test parsing negative DMS notation.
     */
    public function testParseDmsNegative(): void
    {
        $angle = Angle::parse('-45° 30′');

        // -(45 + 30/60) = -45.5
        $this->assertSame(-45.5, $angle->value);
    }

    /**
     * Test parsing DMS with decimal seconds.
     */
    public function testParseDmsDecimalSeconds(): void
    {
        $angle = Angle::parse('45° 30′ 15.5″');

        // 45 + 30/60 + 15.5/3600 = 45.50430555...
        $this->assertApproxEqual(45.50430555555556, $angle->value);
    }

    /**
     * Test parsing standard unit format still works.
     */
    public function testParseStandardFormat(): void
    {
        $angle = Angle::parse('180 deg');

        $this->assertSame(180.0, $angle->value);
        $this->assertSame('deg', $angle->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing radians.
     */
    public function testParseRadians(): void
    {
        $angle = Angle::parse('3.14159 rad');

        $this->assertApproxEqual(M_PI, $angle->value, 1e-5);
    }

    // endregion

    // region Comparison tests

    /**
     * Test approxEqual for equal angles in same units.
     */
    public function testApproxEqualSameUnits(): void
    {
        $a = new Angle(90, 'deg');
        $b = new Angle(90, 'deg');

        $this->assertTrue($a->approxEqual($b));
    }

    /**
     * Test approxEqual for equal angles in different units.
     */
    public function testApproxEqualDifferentUnits(): void
    {
        $a = new Angle(180, 'deg');
        $b = new Angle(M_PI, 'rad');

        $this->assertTrue($a->approxEqual($b));
    }

    /**
     * Test approxEqual for slightly different angles within tolerance.
     */
    public function testApproxEqualWithinTolerance(): void
    {
        $a = new Angle(M_PI, 'rad');
        $b = new Angle(M_PI + 1e-10, 'rad');

        $this->assertTrue($a->approxEqual($b));
    }

    /**
     * Test approxEqual for angles outside tolerance.
     */
    public function testApproxEqualOutsideTolerance(): void
    {
        $a = new Angle(90, 'deg');
        $b = new Angle(91, 'deg');

        $this->assertFalse($a->approxEqual($b));
    }

    /**
     * Test approxEqual returns false for non-Angle.
     */
    public function testApproxEqualNonAngle(): void
    {
        $angle = new Angle(90, 'deg');

        $this->assertFalse($angle->approxEqual(90));
        $this->assertFalse($angle->approxEqual('90 deg'));
    }

    // endregion

    // region Wrap tests

    /**
     * Test wrap signed range for angle within range.
     */
    public function testWrapSignedWithinRange(): void
    {
        $angle = new Angle(90, 'deg');
        $wrapped = $angle->wrap();

        $this->assertSame(90.0, $wrapped->value);
    }

    /**
     * Test wrap signed range for angle at boundary.
     */
    public function testWrapSignedAtBoundary(): void
    {
        $angle = new Angle(180, 'deg');
        $wrapped = $angle->wrap();

        // 180 is included in (-180, 180]
        $this->assertSame(180.0, $wrapped->value);
    }

    /**
     * Test wrap signed range for angle beyond range.
     */
    public function testWrapSignedBeyondRange(): void
    {
        $angle = new Angle(270, 'deg');
        $wrapped = $angle->wrap();

        $this->assertSame(-90.0, $wrapped->value);
    }

    /**
     * Test wrap signed range for negative angle.
     */
    public function testWrapSignedNegative(): void
    {
        $angle = new Angle(-270, 'deg');
        $wrapped = $angle->wrap();

        $this->assertSame(90.0, $wrapped->value);
    }

    /**
     * Test wrap unsigned range.
     */
    public function testWrapUnsigned(): void
    {
        $angle = new Angle(-90, 'deg');
        $wrapped = $angle->wrap(false);

        $this->assertSame(270.0, $wrapped->value);
    }

    /**
     * Test wrap unsigned at boundary.
     */
    public function testWrapUnsignedAtBoundary(): void
    {
        $angle = new Angle(360, 'deg');
        $wrapped = $angle->wrap(false);

        // 360 is excluded from [0, 360)
        $this->assertSame(0.0, $wrapped->value);
    }

    /**
     * Test wrap preserves unit.
     */
    public function testWrapPreservesUnit(): void
    {
        $angle = new Angle(3 * M_PI, 'rad');
        $wrapped = $angle->wrap();

        $this->assertApproxEqual(M_PI, $wrapped->value);
        $this->assertSame('rad', $wrapped->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Trigonometric method tests

    /**
     * Test sin of 0 degrees.
     */
    public function testSinZero(): void
    {
        $angle = new Angle(0, 'deg');

        $this->assertSame(0.0, $angle->sin());
    }

    /**
     * Test sin of 90 degrees.
     */
    public function testSin90(): void
    {
        $angle = new Angle(90, 'deg');

        $this->assertApproxEqual(1.0, $angle->sin());
    }

    /**
     * Test cos of 0 degrees.
     */
    public function testCosZero(): void
    {
        $angle = new Angle(0, 'deg');

        $this->assertSame(1.0, $angle->cos());
    }

    /**
     * Test cos of 90 degrees.
     */
    public function testCos90(): void
    {
        $angle = new Angle(90, 'deg');

        $this->assertApproxEqual(0.0, $angle->cos(), absTol: 1e-15);
    }

    /**
     * Test tan of 45 degrees.
     */
    public function testTan45(): void
    {
        $angle = new Angle(45, 'deg');

        $this->assertApproxEqual(1.0, $angle->tan());
    }

    /**
     * Test tan of 90 degrees returns infinity.
     */
    public function testTan90(): void
    {
        $angle = new Angle(90, 'deg');

        $this->assertSame(INF, $angle->tan());
    }

    /**
     * Test tan of -90 degrees returns negative infinity.
     */
    public function testTanNegative90(): void
    {
        $angle = new Angle(-90, 'deg');

        $this->assertSame(-INF, $angle->tan());
    }

    /**
     * Test sec of 0 degrees.
     */
    public function testSecZero(): void
    {
        $angle = new Angle(0, 'deg');

        $this->assertSame(1.0, $angle->sec());
    }

    /**
     * Test sec of 90 degrees returns infinity.
     */
    public function testSec90(): void
    {
        $angle = new Angle(90, 'deg');

        $this->assertSame(INF, $angle->sec());
    }

    /**
     * Test csc of 90 degrees.
     */
    public function testCsc90(): void
    {
        $angle = new Angle(90, 'deg');

        $this->assertApproxEqual(1.0, $angle->csc());
    }

    /**
     * Test csc of 0 degrees returns infinity.
     */
    public function testCscZero(): void
    {
        $angle = new Angle(0, 'deg');

        $this->assertSame(INF, $angle->csc());
    }

    /**
     * Test cot of 45 degrees.
     */
    public function testCot45(): void
    {
        $angle = new Angle(45, 'deg');

        $this->assertApproxEqual(1.0, $angle->cot());
    }

    /**
     * Test cot of 0 degrees returns infinity.
     */
    public function testCotZero(): void
    {
        $angle = new Angle(0, 'deg');

        $this->assertSame(INF, $angle->cot());
    }

    // endregion

    // region Parts methods tests

    /**
     * Test getPartsConfig returns correct structure.
     */
    public function testGetPartsConfig(): void
    {
        $config = Angle::getPartsConfig();

        $this->assertArrayHasKey('from', $config);
        $this->assertArrayHasKey('to', $config);
        $this->assertSame('deg', $config['from']);
        $this->assertSame(['deg', 'arcmin', 'arcsec'], $config['to']);
    }

    /**
     * Test fromParts with degrees, minutes, and seconds.
     */
    public function testFromParts(): void
    {
        $angle = Angle::fromParts(['deg' => 45, 'arcmin' => 30, 'arcsec' => 15]);

        // 45 + 30/60 + 15/3600 = 45.504166...
        $this->assertApproxEqual(45.504166666666666, $angle->value);
        $this->assertSame('deg', $angle->derivedUnit->asciiSymbol);
    }

    /**
     * Test fromParts with only degrees.
     */
    public function testFromPartsDegreesOnly(): void
    {
        $angle = Angle::fromParts(['deg' => 90]);

        $this->assertSame(90.0, $angle->value);
    }

    /**
     * Test fromParts with negative sign.
     */
    public function testFromPartsNegative(): void
    {
        $angle = Angle::fromParts(['deg' => 45, 'arcmin' => 30, 'sign' => -1]);

        // -(45 + 30/60) = -45.5
        $this->assertSame(-45.5, $angle->value);
    }

    /**
     * Test fromParts with negative value uses sign key instead.
     */
    public function testFromPartsNegativeValueUsesSign(): void
    {
        // Negative values in parts are handled via the 'sign' key
        $angle = Angle::fromParts(['deg' => 45, 'sign' => -1]);

        $this->assertSame(-45.0, $angle->value);
    }

    /**
     * Test fromParts uses default result unit from config.
     */
    public function testFromPartsDefaultResultUnit(): void
    {
        $angle = Angle::fromParts(['deg' => 45]);

        // Default result unit for Angle is 'deg'
        $this->assertSame('deg', $angle->derivedUnit->asciiSymbol);
    }

    /**
     * Test fromParts with custom result unit.
     */
    public function testFromPartsCustomResultUnit(): void
    {
        $angle = Angle::fromParts(['deg' => 180], 'rad');

        // 180 degrees = π radians
        $this->assertApproxEqual(M_PI, $angle->value);
        $this->assertSame('rad', $angle->derivedUnit->asciiSymbol);
    }

    /**
     * Test toParts decomposes angle correctly.
     */
    public function testToParts(): void
    {
        $angle = new Angle(45.504166666666666, 'deg');
        $parts = $angle->toParts('arcsec', 0);

        $this->assertSame(1, $parts['sign']);
        $this->assertSame(45.0, $parts['deg']);
        $this->assertSame(30.0, $parts['arcmin']);
        $this->assertSame(15.0, $parts['arcsec']);
    }

    /**
     * Test toParts with negative value.
     */
    public function testToPartsNegative(): void
    {
        $angle = new Angle(-45.5, 'deg');
        $parts = $angle->toParts('arcmin', 0);

        $this->assertSame(-1, $parts['sign']);
        $this->assertSame(45.0, $parts['deg']);
        $this->assertSame(30.0, $parts['arcmin']);
    }

    /**
     * Test toParts with precision causes carry.
     */
    public function testToPartsCarry(): void
    {
        $angle = new Angle(44.99999999, 'deg');  // Just under 45 degrees
        $parts = $angle->toParts('arcsec', 0);

        // Should round and carry to 45 degrees
        // Use assertEquals because carry logic may produce floats
        $this->assertSame(45.0, $parts['deg']);
        $this->assertSame(0.0, $parts['arcmin']);
        $this->assertSame(0.0, $parts['arcsec']);
    }

    /**
     * Test formatParts default (arcseconds).
     */
    public function testFormatPartsDefault(): void
    {
        $angle = new Angle(45.504166666666666, 'deg');
        $result = $angle->formatParts('arcsec', 0);

        $this->assertSame('45° 30′ 15″', $result);
    }

    /**
     * Test formatParts to arcminutes.
     */
    public function testFormatPartsToArcminutes(): void
    {
        $angle = new Angle(45.5, 'deg');
        $result = $angle->formatParts('arcmin', 0);

        $this->assertSame('45° 30′', $result);
    }

    /**
     * Test formatParts with precision.
     */
    public function testFormatPartsWithPrecision(): void
    {
        $angle = new Angle(45.504305555555556, 'deg');
        $result = $angle->formatParts('arcsec', 1);

        $this->assertSame('45° 30′ 15.5″', $result);
    }

    /**
     * Test formatParts for negative angle.
     */
    public function testFormatPartsNegative(): void
    {
        $angle = new Angle(-45.5, 'deg');
        $result = $angle->formatParts('arcmin', 0);

        $this->assertSame('-45° 30′', $result);
    }

    /**
     * Test parts round-trip conversion.
     */
    public function testPartsRoundTrip(): void
    {
        $angle = Angle::fromParts(['deg' => 45, 'arcmin' => 30, 'arcsec' => 15]);
        $formatted = $angle->formatParts('arcsec', 0);

        $this->assertSame('45° 30′ 15″', $formatted);
    }

    // endregion
}
