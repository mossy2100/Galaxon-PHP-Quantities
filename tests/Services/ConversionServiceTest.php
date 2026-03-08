<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Services;

use DomainException;
use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Internal\Conversion;
use Galaxon\Quantities\Internal\Converter;
use Galaxon\Quantities\Internal\DerivedUnit;
use Galaxon\Quantities\Internal\Unit;
use Galaxon\Quantities\Services\ConversionService;
use Galaxon\Quantities\Services\UnitService;
use Galaxon\Quantities\UnitSystem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ConversionService class.
 */
#[CoversClass(ConversionService::class)]
final class ConversionServiceTest extends TestCase
{
    // region Setup

    public static function setUpBeforeClass(): void
    {
        // Load Imperial/US units for cross-system tests.
        UnitService::loadSystem(UnitSystem::Imperial);
        UnitService::loadSystem(UnitSystem::UsCustomary);
        UnitService::loadSystem(UnitSystem::Nautical);
    }

    // endregion

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
     * Test has() throws DomainException for unknown units.
     */
    public function testHasThrowsForUnknownUnits(): void
    {
        $this->expectException(DomainException::class);

        ConversionService::has('nonexistent1', 'nonexistent2');
    }

    /**
     * Test has() checks the specific direction in the matrix.
     */
    public function testHasChecksDirection(): void
    {
        // Use an isolated dimension to avoid interference from loaded conversions.
        $srcUnit = new Unit('test conv src a', 'Xcsa', 'L7', UnitSystem::Custom);
        $destUnit = new Unit('test conv dest a', 'Xcda', 'L7', UnitSystem::Custom);
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
     * Test get() throws DomainException for unknown units.
     */
    public function testGetThrowsForUnknownUnits(): void
    {
        $this->expectException(DomainException::class);

        ConversionService::get('nonexistent1', 'nonexistent2');
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
        $srcUnit = new Unit('test conv src b', 'Xcsb', 'L7', UnitSystem::Custom);
        $destUnit = new Unit('test conv dest b', 'Xcdb', 'L7', UnitSystem::Custom);
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
        $unitA = new Unit('test rbu a', 'Xrua', 'L7', UnitSystem::Custom);
        $unitB = new Unit('test rbu b', 'Xrub', 'L7', UnitSystem::Custom);
        $unitC = new Unit('test rbu c', 'Xruc', 'L7', UnitSystem::Custom);
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
        // Ensure Nautical units are loaded.
        UnitService::loadSystem(UnitSystem::Nautical);

        // Verify a nautical conversion exists (nmi to m, or similar).
        // nmi should have conversions after loading.
        $converter = Converter::getInstance('L');
        $nmiUnit = DerivedUnit::toDerivedUnit('nmi');
        $hasConversions = !empty($converter->conversionMatrix[$nmiUnit->asciiSymbol] ?? []);
        $this->assertTrue($hasConversions);

        // Unload nautical system conversions.
        ConversionService::removeBySystem(UnitSystem::Nautical);

        // Conversions involving nmi should be gone.
        $hasConversionsAfter = !empty($converter->conversionMatrix[$nmiUnit->asciiSymbol] ?? []);
        $this->assertFalse($hasConversionsAfter);

        // Reload nautical units so other tests aren't affected.
        UnitService::loadSystem(UnitSystem::Nautical);
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
     * Test convert() throws DomainException for dimension mismatch.
     */
    public function testConvertThrowsForDimensionMismatch(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('different dimensions');

        ConversionService::convert(1.0, 'm', 's');
    }

    /**
     * Test convert() throws for unknown unit.
     */
    public function testConvertThrowsForUnknownUnit(): void
    {
        $this->expectException(DomainException::class);

        ConversionService::convert(1.0, 'nonexistent', 'm');
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
        $unitD = new Unit('test find d', 'Xfnd', 'L7', UnitSystem::Custom);
        $unitE = new Unit('test find e', 'Xfne', 'L7', UnitSystem::Custom);
        UnitService::add($unitD);
        UnitService::add($unitE);

        $result = ConversionService::find('Xfnd', 'Xfne');

        $this->assertNull($result);
    }

    /**
     * Test find() throws DomainException for dimension mismatch.
     */
    public function testFindThrowsForDimensionMismatch(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('different dimensions');

        ConversionService::find('m', 'kg');
    }

    /**
     * Test find() accepts UnitInterface objects.
     */
    public function testFindAcceptsUnitInterfaceObjects(): void
    {
        $srcUnit = DerivedUnit::toDerivedUnit('m');
        $destUnit = DerivedUnit::toDerivedUnit('ft');

        $conversion = ConversionService::find($srcUnit, $destUnit);

        $this->assertInstanceOf(Conversion::class, $conversion);
    }

    // endregion

    // region findFactor() tests

    /**
     * Test findFactor() returns the conversion factor.
     */
    public function testFindFactorReturnsCorrectValue(): void
    {
        $factor = ConversionService::findFactor('km', 'm');

        $this->assertNotNull($factor);
        $this->assertEqualsWithDelta(1000.0, $factor, 1e-10);
    }

    /**
     * Test findFactor() returns null when no conversion exists.
     */
    public function testFindFactorReturnsNullForNoConversion(): void
    {
        // Create isolated units with no conversions.
        $unitF = new Unit('test ff f', 'Xfff', 'L7', UnitSystem::Custom);
        $unitG = new Unit('test ff g', 'Xffg', 'L7', UnitSystem::Custom);
        UnitService::add($unitF);
        UnitService::add($unitG);

        $result = ConversionService::findFactor('Xfff', 'Xffg');

        $this->assertNull($result);
    }

    /**
     * Test findFactor() throws DomainException for dimension mismatch.
     */
    public function testFindFactorThrowsForDimensionMismatch(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('different dimensions');

        ConversionService::findFactor('m', 's');
    }

    // endregion

    // region getAllDefinitions() tests

    /**
     * Test getAllDefinitions() returns an array of definition tuples.
     */
    public function testGetAllDefinitionsReturnsArray(): void
    {
        $definitions = ConversionService::getAllDefinitions();

        $this->assertIsArray($definitions); // @phpstan-ignore method.alreadyNarrowedType
        $this->assertNotEmpty($definitions);

        // Each definition should be [srcSymbol, destSymbol, factor].
        $first = $definitions[0];
        $this->assertCount(3, $first);
        $this->assertIsString($first[0]);
        $this->assertIsString($first[1]);
        $this->assertIsNumeric($first[2]);
    }

    // endregion

    // region loadDefinitions() tests

    /**
     * Test loadDefinitions() loads conversions without replacing existing ones.
     */
    public function testLoadDefinitionsDoesNotReplaceExisting(): void
    {
        // Add a custom conversion with a known factor.
        $customFactor = 999.999;
        $conversion = new Conversion('m', 'ft', $customFactor);
        ConversionService::add($conversion, true);

        // Reload definitions without replacing.
        ConversionService::loadDefinitions(false);

        // The custom conversion should still be there.
        $result = ConversionService::get('m', 'ft');
        $this->assertInstanceOf(Conversion::class, $result);
        $this->assertEqualsWithDelta($customFactor, $result->factor->value, 1e-10);

        // Restore the correct conversion.
        ConversionService::loadDefinitions(true);
    }

    /**
     * Test loadDefinitions() replaces existing when flag is true.
     */
    public function testLoadDefinitionsReplacesExistingWhenFlagged(): void
    {
        // Add a wrong conversion for ft→m (which IS directly in Length's definitions as 0.3048).
        $wrongConversion = new Conversion('ft', 'm', 999.0);
        ConversionService::add($wrongConversion, true);

        // Verify the wrong factor is stored.
        $result = ConversionService::get('ft', 'm');
        $this->assertInstanceOf(Conversion::class, $result);
        $this->assertEqualsWithDelta(999.0, $result->factor->value, 1e-10);

        // Reload with replacement.
        ConversionService::loadDefinitions(true);

        // The conversion should now have the correct factor (0.3048).
        $result = ConversionService::get('ft', 'm');
        $this->assertInstanceOf(Conversion::class, $result);
        $this->assertEqualsWithDelta(0.3048, $result->factor->value, 1e-10);
    }

    // endregion

    // region validateUnits() tests (via public methods)

    /**
     * Test that dimension mismatch throws DomainException with descriptive message.
     */
    public function testValidateUnitsThrowsForDimensionMismatch(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('different dimensions');

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
