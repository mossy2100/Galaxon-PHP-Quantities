<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\NonCurrencies\Services;

use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Exceptions\DimensionMismatchException;
use Galaxon\Quantities\Exceptions\UnknownUnitException;
use Galaxon\Quantities\Internal\CompoundUnit;
use Galaxon\Quantities\Internal\Conversion;
use Galaxon\Quantities\Internal\Converter;
use Galaxon\Quantities\Internal\Unit;
use Galaxon\Quantities\Internal\UnitSystem;
use Galaxon\Quantities\Services\ConversionService;
use Galaxon\Quantities\Services\UnitService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ConversionService class.
 */
#[CoversClass(ConversionService::class)]
final class ConversionServiceTest extends TestCase
{
    // region has() tests

    /**
     * Test has() returns true for existing conversion.
     */
    public function testHasReturnsTrueForExistingConversion(): void
    {
        // Add a known conversion
        $conversion = new Conversion('m', 'ft', 3.28084);
        ConversionService::add($conversion);

        $result = ConversionService::has('m', 'ft');

        $this->assertTrue($result);
    }

    /**
     * Test has() throws UnknownUnitException for unknown units.
     */
    public function testHasThrowsForUnknownUnits(): void
    {
        $this->expectException(UnknownUnitException::class);

        ConversionService::has('noexist', 'doesnt');
    }

    /**
     * Test has() checks the specific direction in the matrix.
     */
    public function testHasChecksDirection(): void
    {
        // Use an isolated dimension to avoid interference from loaded conversions.
        $srcUnit = new Unit('test conv src a', 'Xcsa', 'L7');
        $destUnit = new Unit('test conv dest a', 'Xcda', 'L7');
        UnitService::add($srcUnit);
        UnitService::add($destUnit);

        $conversion = new Conversion('Xcsa', 'Xcda', 2.0);
        ConversionService::add($conversion);

        // Forward direction should exist.
        $this->assertTrue(ConversionService::has('Xcsa', 'Xcda'));

        // Reverse direction was not added, so has() should return false.
        $this->assertFalse(ConversionService::has('Xcda', 'Xcsa'));
    }

    // endregion

    // region get() tests

    /**
     * Test get() returns Conversion for existing conversion.
     */
    public function testGetReturnsConversionForExisting(): void
    {
        // Add a known conversion
        $conversion = new Conversion('m', 'in', 39.3701);
        ConversionService::add($conversion);

        $result = ConversionService::get('m', 'in');

        $this->assertInstanceOf(Conversion::class, $result);
        $this->assertSame('m', $result->srcUnit->asciiSymbol);
        $this->assertSame('in', $result->destUnit->asciiSymbol);
    }

    /**
     * Test get() throws UnknownUnitException for unknown units.
     */
    public function testGetThrowsForUnknownUnits(): void
    {
        $this->expectException(UnknownUnitException::class);

        ConversionService::get('noexist', 'doesnt');
    }

    /**
     * Test get() returns correct conversion factor.
     */
    public function testGetReturnsCorrectFactor(): void
    {
        $factor = 2.54;
        $conversion = new Conversion('in', 'cm', $factor);
        ConversionService::add($conversion);

        $result = ConversionService::get('in', 'cm');

        $this->assertInstanceOf(Conversion::class, $result);
        $this->assertEqualsWithDelta($factor, $result->factor->value, 1e-10);
    }

    /**
     * Test get() checks the specific direction in the matrix.
     */
    public function testGetChecksDirection(): void
    {
        // Use an isolated dimension to avoid interference from loaded conversions.
        $srcUnit = new Unit('test conv src b', 'Xcsb', 'L7');
        $destUnit = new Unit('test conv dest b', 'Xcdb', 'L7');
        UnitService::add($srcUnit);
        UnitService::add($destUnit);

        $conversion = new Conversion('Xcsb', 'Xcdb', 3.0);
        ConversionService::add($conversion);

        // Forward should return the conversion.
        $forward = ConversionService::get('Xcsb', 'Xcdb');
        $this->assertInstanceOf(Conversion::class, $forward);

        // Reverse was not added, so get() should return null.
        $reverse = ConversionService::get('Xcdb', 'Xcsb');
        $this->assertNull($reverse);
    }

    // endregion

    // region add() tests

    /**
     * Test add() stores a conversion.
     */
    public function testAddStoresConversion(): void
    {
        $conversion = new Conversion('m', 'nmi', 0.000539957);
        ConversionService::add($conversion, true);

        $result = ConversionService::get('m', 'nmi');

        $this->assertInstanceOf(Conversion::class, $result);
        $this->assertSame($conversion->factor->value, $result->factor->value);
    }

    /**
     * Test add() overwrites existing conversion.
     */
    public function testAddOverwritesExisting(): void
    {
        // Add initial conversion
        $conversion1 = new Conversion('m', 'yd', 1.09361);
        ConversionService::add($conversion1);

        // Add different conversion for same units
        $newFactor = 1.09362;
        $conversion2 = new Conversion('m', 'yd', $newFactor);
        ConversionService::add($conversion2, true);

        $result = ConversionService::get('m', 'yd');

        $this->assertInstanceOf(Conversion::class, $result);
        $this->assertEqualsWithDelta($newFactor, $result->factor->value, 1e-10);
    }

    /**
     * Test add() handles prefixed unit terms.
     */
    public function testAddHandlesPrefixedUnitTerms(): void
    {
        // Add conversion with prefixed units
        $conversion = new Conversion('km', 'mi', 0.621371);
        ConversionService::add($conversion);

        $result = ConversionService::get('km', 'mi');

        $this->assertInstanceOf(Conversion::class, $result);
        $this->assertSame('km', $result->srcUnit->asciiSymbol);
        $this->assertSame('mi', $result->destUnit->asciiSymbol);
    }

    /**
     * Test add() handles unit terms with exponents.
     */
    public function testAddHandlesUnitTermsWithExponents(): void
    {
        // Add area conversion
        $conversion = new Conversion('m2', 'ft2', 10.7639);
        ConversionService::add($conversion);

        $result = ConversionService::get('m2', 'ft2');

        $this->assertInstanceOf(Conversion::class, $result);
        $this->assertSame('m2', $result->srcUnit->asciiSymbol);
        $this->assertSame('ft2', $result->destUnit->asciiSymbol);
    }

    // endregion

    // region remove() tests

    /**
     * Test remove() removes an existing conversion.
     */
    public function testRemoveRemovesExisting(): void
    {
        // Add a conversion.
        $conversion = new Conversion('m', 'dm', 10.0);
        ConversionService::add($conversion);

        // Verify it exists.
        $this->assertTrue(ConversionService::has('m', 'dm'));

        // Remove it.
        ConversionService::remove($conversion);

        // Verify it's gone.
        $this->assertFalse(ConversionService::has('m', 'dm'));
    }

    // endregion

    // region removeByUnit() tests

    /**
     * Test removeByUnit() removes all conversions involving a given unit.
     */
    public function testRemoveByUnitRemovesAllConversions(): void
    {
        // Create isolated units.
        $unitA = new Unit('test rbu a', 'Xrua', 'L7');
        $unitB = new Unit('test rbu b', 'Xrub', 'L7');
        $unitC = new Unit('test rbu c', 'Xruc', 'L7');
        UnitService::add($unitA);
        UnitService::add($unitB);
        UnitService::add($unitC);

        // Add conversions involving unitB.
        ConversionService::add(new Conversion('Xrua', 'Xrub', 2.0));
        ConversionService::add(new Conversion('Xrub', 'Xruc', 3.0));
        $this->assertTrue(ConversionService::has('Xrua', 'Xrub'));
        $this->assertTrue(ConversionService::has('Xrub', 'Xruc'));

        // Remove all conversions involving unitB.
        ConversionService::removeByUnit($unitB);

        // Both conversions should be gone.
        $this->assertFalse(ConversionService::has('Xrua', 'Xrub'));
        $this->assertFalse(ConversionService::has('Xrub', 'Xruc'));
    }

    // endregion

    // region unloadBySystem() tests

    /**
     * Test unloadBySystem() removes conversions for units in the given system.
     */
    public function testUnloadBySystemRemovesConversions(): void
    {
        // Verify a nautical conversion exists (nmi to m, or similar).
        // nmi should have conversions after loading.
        $converter = Converter::getInstance('L');
        $nmiUnit = CompoundUnit::toCompoundUnit('nmi');
        $hasConversions = !empty($converter->conversionMatrix[$nmiUnit->asciiSymbol] ?? []);
        $this->assertTrue($hasConversions);

        // Unload nautical system conversions.
        ConversionService::removeBySystem(UnitSystem::Nautical);

        // Conversions involving nmi should be gone.
        $hasConversionsAfter = !empty($converter->conversionMatrix[$nmiUnit->asciiSymbol] ?? []);
        $this->assertFalse($hasConversionsAfter);
    }

    // endregion

    // region convert() tests

    /**
     * Test convert() converts a value between units.
     */
    public function testConvertReturnsConvertedValue(): void
    {
        // Convert 1 km to m.
        $result = ConversionService::convert(1.0, 'km', 'm');

        $this->assertEqualsWithDelta(1000.0, $result, 1e-10);
    }

    /**
     * Test convert() returns same value for identical units.
     */
    public function testConvertIdenticalUnitsReturnsSameValue(): void
    {
        $result = ConversionService::convert(42.0, 'm', 'm');

        $this->assertEqualsWithDelta(42.0, $result, 1e-10);
    }

    /**
     * Test convert() throws DimensionMismatchException for dimension mismatch.
     */
    public function testConvertThrowsForDimensionMismatch(): void
    {
        $this->expectException(DimensionMismatchException::class);
        $this->expectExceptionMessage('Dimension mismatch');

        ConversionService::convert(1.0, 'm', 's');
    }

    /**
     * Test convert() throws for unknown unit.
     */
    public function testConvertThrowsForUnknownUnit(): void
    {
        $this->expectException(UnknownUnitException::class);

        ConversionService::convert(1.0, 'noexist', 'm');
    }

    /**
     * Test convert() handles compound units.
     */
    public function testConvertHandlesCompoundUnits(): void
    {
        // Convert 1 N to kg*m/s2.
        $result = ConversionService::convert(1.0, 'N', 'kg*m/s2');

        $this->assertEqualsWithDelta(1.0, $result, 1e-10);
    }

    // endregion

    // region find() tests

    /**
     * Test find() discovers a conversion via path-finding.
     */
    public function testFindDiscoversConversion(): void
    {
        // find() should discover conversions even if not directly stored.
        $conversion = ConversionService::find('ft', 'cm');

        $this->assertInstanceOf(Conversion::class, $conversion);
        // 1 ft = 30.48 cm.
        $this->assertEqualsWithDelta(30.48, $conversion->factor->value, 0.01);
    }

    /**
     * Test find() returns unity conversion for identical units.
     */
    public function testFindReturnsUnityForIdenticalUnits(): void
    {
        $conversion = ConversionService::find('m', 'm');

        $this->assertInstanceOf(Conversion::class, $conversion);
        $this->assertEqualsWithDelta(1.0, $conversion->factor->value, 1e-10);
    }

    /**
     * Test find() returns null when no path exists.
     */
    public function testFindReturnsNullForNoPath(): void
    {
        // Create isolated units with no conversions between them.
        $unitD = new Unit('test find d', 'Xfnd', 'L7');
        $unitE = new Unit('test find e', 'Xfne', 'L7');
        UnitService::add($unitD);
        UnitService::add($unitE);

        $result = ConversionService::find('Xfnd', 'Xfne');

        $this->assertNull($result);
    }

    /**
     * Test find() throws DimensionMismatchException for dimension mismatch.
     */
    public function testFindThrowsForDimensionMismatch(): void
    {
        $this->expectException(DimensionMismatchException::class);
        $this->expectExceptionMessage('Dimension mismatch');

        ConversionService::find('m', 'kg');
    }

    /**
     * Test find() accepts UnitInterface objects.
     */
    public function testFindAcceptsUnitInterfaceObjects(): void
    {
        $srcUnit = CompoundUnit::toCompoundUnit('m');
        $destUnit = CompoundUnit::toCompoundUnit('ft');

        $conversion = ConversionService::find($srcUnit, $destUnit);

        $this->assertInstanceOf(Conversion::class, $conversion);
    }

    // endregion

    // region validateUnits() tests (via public methods)

    /**
     * Test that dimension mismatch throws DimensionMismatchException with descriptive message.
     */
    public function testValidateUnitsThrowsForDimensionMismatch(): void
    {
        $this->expectException(DimensionMismatchException::class);
        $this->expectExceptionMessage('Dimension mismatch');

        ConversionService::has('m', 'kg');
    }

    /**
     * Test that FormatException is thrown for unparseable unit strings.
     */
    public function testValidateUnitsThrowsFormatExceptionForInvalidFormat(): void
    {
        $this->expectException(FormatException::class);

        ConversionService::has('123invalid', 'm');
    }

    // endregion
}
