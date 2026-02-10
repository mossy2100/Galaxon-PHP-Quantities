<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests;

use DomainException;
use Galaxon\Quantities\Conversion;
use Galaxon\Quantities\Converter;
use Galaxon\Quantities\DerivedUnit;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Converter class.
 */
#[CoversClass(Converter::class)]
class ConverterTest extends TestCase
{
    // region Setup

    public static function setUpBeforeClass(): void
    {
        // Load Imperial units for cross-system tests.
        UnitRegistry::loadSystem(System::Imperial);
    }

    // endregion

    // region reset() tests

    /**
     * Test reset clears cached instances.
     */
    public function testResetClearsCachedInstances(): void
    {
        // Get an instance to populate the cache.
        $converter1 = Converter::getByDimension('L');

        // Reset the cache.
        Converter::clear();

        // Get a new instance - should be a different object.
        $converter2 = Converter::getByDimension('L');

        // They should not be the same instance.
        $this->assertNotSame($converter1, $converter2);
    }

    // endregion

    // region getByDimension() tests

    /**
     * Test getByDimension returns instance for valid dimension.
     */
    public function testGetByDimensionReturnsInstance(): void
    {
        $converter = Converter::getByDimension('L');

        $this->assertInstanceOf(Converter::class, $converter);
        $this->assertSame('L', $converter->dimension);
    }

    /**
     * Test getByDimension throws for invalid dimension.
     */
    public function testGetByDimensionThrowsForInvalidDimension(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid dimension code 'X'.");

        Converter::getByDimension('X');
    }

    /**
     * Test getByDimension returns same instance for same dimension (singleton).
     */
    public function testGetByDimensionReturnsSameInstance(): void
    {
        $converter1 = Converter::getByDimension('L');
        $converter2 = Converter::getByDimension('L');

        $this->assertSame($converter1, $converter2);
    }

    /**
     * Test getByDimension returns different instances for different dimensions.
     */
    public function testGetByDimensionReturnsDifferentInstancesForDifferentDimensions(): void
    {
        $lengthConverter = Converter::getByDimension('L');
        $timeConverter = Converter::getByDimension('T');

        $this->assertNotSame($lengthConverter, $timeConverter);
        $this->assertSame('L', $lengthConverter->dimension);
        $this->assertSame('T', $timeConverter->dimension);
    }

    // endregion

    // region getConversion() tests

    /**
     * Test getConversion returns conversion for known units.
     */
    public function testGetConversionReturnsConversion(): void
    {
        $converter = Converter::getByDimension('L');

        $conversion = $converter->getConversion('m', 'ft');

        $this->assertInstanceOf(Conversion::class, $conversion);
        $this->assertSame('m', (string)$conversion->srcUnit);
        $this->assertSame('ft', (string)$conversion->destUnit);
        $this->assertGreaterThan(0.0, $conversion->factor->value);
    }

    /**
     * Test getConversion returns unity conversion for identical units.
     */
    public function testGetConversionReturnsUnityForIdenticalUnits(): void
    {
        $converter = Converter::getByDimension('L');

        $conversion = $converter->getConversion('m', 'm');

        $this->assertInstanceOf(Conversion::class, $conversion);
        $this->assertSame(1.0, $conversion->factor->value);
    }

    /**
     * Test getConversion handles prefix-only differences.
     */
    public function testGetConversionHandlesPrefixOnlyDifference(): void
    {
        $converter = Converter::getByDimension('L');

        $conversion = $converter->getConversion('km', 'm');

        $this->assertInstanceOf(Conversion::class, $conversion);
        $this->assertSame('km', (string)$conversion->srcUnit);
        $this->assertSame('m', (string)$conversion->destUnit);
        $this->assertEqualsWithDelta(1000.0, $conversion->factor->value, 1e-10);
    }

    /**
     * Test getConversion handles centimeters to meters.
     */
    public function testGetConversionCentimetersToMeters(): void
    {
        $converter = Converter::getByDimension('L');

        $conversion = $converter->getConversion('cm', 'm');

        $this->assertInstanceOf(Conversion::class, $conversion);
        $this->assertEqualsWithDelta(0.01, $conversion->factor->value, 1e-10);
    }

    /**
     * Test getConversion with UnitTerm objects.
     */
    public function testGetConversionWithUnitTermObjects(): void
    {
        $converter = Converter::getByDimension('L');
        $srcUnitTerm = DerivedUnit::parse('m');
        $destUnitTerm = DerivedUnit::parse('ft');

        $conversion = $converter->getConversion($srcUnitTerm, $destUnitTerm);

        $this->assertInstanceOf(Conversion::class, $conversion);
    }

    /**
     * Test getConversion throws for invalid unit term.
     */
    public function testGetConversionThrowsForInvalidUnitTerm(): void
    {
        $converter = Converter::getByDimension('L');

        $this->expectException(DomainException::class);

        $converter->getConversion('s', 'm'); // seconds is not a length unit
    }

    /**
     * Test getConversion caches results.
     */
    public function testGetConversionCachesResults(): void
    {
        $converter = Converter::getByDimension('L');

        // First call generates the conversion
        $conversion1 = $converter->getConversion('m', 'ft');

        // Second call should return cached result
        $conversion2 = $converter->getConversion('m', 'ft');

        $this->assertInstanceOf(Conversion::class, $conversion1);
        $this->assertInstanceOf(Conversion::class, $conversion2);

        $this->assertSame($conversion1->factor->value, $conversion2->factor->value);
    }

    // endregion

    // region getConversionFactor() tests

    /**
     * Test getConversionFactor returns factor for known units.
     */
    public function testGetConversionFactorReturnsFactor(): void
    {
        $converter = Converter::getByDimension('L');

        $factor = $converter->getConversionFactor('m', 'ft');

        $this->assertIsFloat($factor);
        $this->assertGreaterThan(0.0, $factor);
    }

    /**
     * Test getConversionFactor returns 1.0 for identical units.
     */
    public function testGetConversionFactorReturnsOneForIdenticalUnits(): void
    {
        $converter = Converter::getByDimension('L');

        $factor = $converter->getConversionFactor('m', 'm');

        $this->assertSame(1.0, $factor);
    }

    /**
     * Test getConversionFactor for prefix conversion.
     */
    public function testGetConversionFactorForPrefixConversion(): void
    {
        $converter = Converter::getByDimension('L');

        $factor = $converter->getConversionFactor('km', 'mm');

        // km → mm = 1,000,000
        $this->assertEqualsWithDelta(1e6, $factor, 1e-10);
    }

    // endregion

    // region validateUnitTerm() tests

    /**
     * Test validateUnitTerm returns validated unit term.
     */
    public function testValidateUnitTermReturnsUnitTerm(): void
    {
        $converter = Converter::getByDimension('L');

        $unit = $converter->validateUnit('m');

        $this->assertInstanceOf(DerivedUnit::class, $unit);
        $this->assertSame('m', (string)$unit);
    }

    /**
     * Test validateUnitTerm accepts prefixed units.
     */
    public function testValidateUnitTermAcceptsPrefixedUnits(): void
    {
        $converter = Converter::getByDimension('L');

        $unit = $converter->validateUnit('km');

        $this->assertSame('km', (string)$unit);
    }

    /**
     * Test validateUnitTerm throws for wrong dimension.
     */
    public function testValidateUnitTermThrowsForWrongDimension(): void
    {
        $converter = Converter::getByDimension('L');

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("The unit 's' is invalid for length quantities.");

        $converter->validateUnit('s'); // seconds is time, not length
    }

    /**
     * Test validateUnitTerm throws for unknown unit.
     */
    public function testValidateUnitTermThrowsForUnknownUnit(): void
    {
        $converter = Converter::getByDimension('L');

        $this->expectException(DomainException::class);

        $converter->validateUnit('xyz');
    }

    // endregion

    // region convert() tests

    /**
     * Test convert converts value between units.
     */
    public function testConvertConvertsValue(): void
    {
        $converter = Converter::getByDimension('L');

        $result = $converter->convert(1.0, 'm', 'ft');

        // 1 meter ≈ 3.28084 feet
        $this->assertEqualsWithDelta(3.28084, $result, 1e-4);
    }

    /**
     * Test convert returns same value for identical units.
     */
    public function testConvertReturnsSameValueForIdenticalUnits(): void
    {
        $converter = Converter::getByDimension('L');

        $result = $converter->convert(42.5, 'm', 'm');

        $this->assertSame(42.5, $result);
    }

    /**
     * Test convert handles prefix conversions.
     */
    public function testConvertHandlesPrefixConversions(): void
    {
        $converter = Converter::getByDimension('L');

        $result = $converter->convert(5.0, 'km', 'm');

        $this->assertEqualsWithDelta(5000.0, $result, 1e-10);
    }

    /**
     * Test convert handles zero value.
     */
    public function testConvertHandlesZeroValue(): void
    {
        $converter = Converter::getByDimension('L');

        $result = $converter->convert(0.0, 'm', 'ft');

        $this->assertSame(0.0, $result);
    }

    /**
     * Test convert handles negative value.
     */
    public function testConvertHandlesNegativeValue(): void
    {
        $converter = Converter::getByDimension('L');

        $result = $converter->convert(-10.0, 'm', 'ft');

        $this->assertLessThan(0.0, $result);
        $this->assertEqualsWithDelta(-32.8084, $result, 1e-3);
    }

    /**
     * Test convert throws for invalid source unit.
     */
    public function testConvertThrowsForInvalidSourceUnit(): void
    {
        $converter = Converter::getByDimension('L');

        $this->expectException(DomainException::class);

        $converter->convert(1.0, 's', 'm');
    }

    /**
     * Test convert throws for invalid destination unit.
     */
    public function testConvertThrowsForInvalidDestinationUnit(): void
    {
        $converter = Converter::getByDimension('L');

        $this->expectException(DomainException::class);

        $converter->convert(1.0, 'm', 's');
    }

    // endregion

    // region Graph traversal tests

    /**
     * Test converter finds indirect conversions via graph traversal.
     */
    public function testFindsIndirectConversions(): void
    {
        $converter = Converter::getByDimension('L');

        // Even if m → yd isn't directly defined, it should be found via m → ft → yd or similar
        $conversion = $converter->getConversion('m', 'yd');

        $this->assertInstanceOf(Conversion::class, $conversion);
        // 1 meter ≈ 1.09361 yards
        $this->assertEqualsWithDelta(1.09361, $conversion->factor->value, 1e-4);
    }

    /**
     * Test converter handles inverse conversions.
     */
    public function testHandlesInverseConversions(): void
    {
        $converter = Converter::getByDimension('L');

        // If m → ft exists, ft → m should be derivable
        $mToFt = $converter->getConversion('m', 'ft');
        $ftToM = $converter->getConversion('ft', 'm');

        $this->assertInstanceOf(Conversion::class, $mToFt);
        $this->assertInstanceOf(Conversion::class, $ftToM);

        $this->assertEqualsWithDelta(1.0, $mToFt->factor->value * $ftToM->factor->value, 1e-6);
    }

    // endregion

    // region Exponentiated dimension tests

    /**
     * Test converter handles L2 (area) dimension.
     */
    public function testHandlesAreaDimension(): void
    {
        $converter = Converter::getByDimension('L2');

        $conversion = $converter->getConversion('m2', 'ft2');

        $this->assertInstanceOf(Conversion::class, $conversion);
        // 1 m² ≈ 10.7639 ft²
        $this->assertEqualsWithDelta(10.7639, $conversion->factor->value, 1e-3);
    }

    /**
     * Test converter handles prefixed area units.
     */
    public function testHandlesPrefixedAreaUnits(): void
    {
        $converter = Converter::getByDimension('L2');

        $conversion = $converter->getConversion('km2', 'm2');

        $this->assertInstanceOf(Conversion::class, $conversion);

        // 1 km² = 1,000,000 m²
        $this->assertEqualsWithDelta(1e6, $conversion->factor->value, 1e-10);
    }

    /**
     * Test converter handles L3 (volume) dimension.
     */
    public function testHandlesVolumeDimension(): void
    {
        $converter = Converter::getByDimension('L3');

        $conversion = $converter->getConversion('m3', 'ft3');

        $this->assertInstanceOf(Conversion::class, $conversion);
        // 1 m³ ≈ 35.3147 ft³
        $this->assertEqualsWithDelta(35.3147, $conversion->factor->value, 1e-3);
    }

    /**
     * Test exponentiated conversion derives from base dimension.
     */
    public function testExponentiatedConversionDerivesFromBaseDimension(): void
    {
        $lengthConverter = Converter::getByDimension('L');
        $areaConverter = Converter::getByDimension('L2');

        $mToFt = $lengthConverter->getConversionFactor('m', 'ft');
        $m2ToFt2 = $areaConverter->getConversionFactor('m2', 'ft2');

        // m² → ft² factor should be (m → ft factor)²
        $this->assertEqualsWithDelta($mToFt ** 2, $m2ToFt2, 1e-6);
    }

    // endregion

    // region Time dimension tests

    /**
     * Test converter handles time dimension.
     */
    public function testHandlesTimeDimension(): void
    {
        $converter = Converter::getByDimension('T');

        $conversion = $converter->getConversion('h', 's');

        $this->assertInstanceOf(Conversion::class, $conversion);

        // 1 hour = 3600 seconds
        $this->assertEqualsWithDelta(3600.0, $conversion->factor->value, 1e-10);
    }

    /**
     * Test converter handles time with prefixes.
     */
    public function testHandlesTimeWithPrefixes(): void
    {
        $converter = Converter::getByDimension('T');

        $conversion = $converter->getConversion('ms', 's');

        $this->assertInstanceOf(Conversion::class, $conversion);

        // 1 ms = 0.001 s
        $this->assertEqualsWithDelta(0.001, $conversion->factor->value, 1e-10);
    }

    // endregion

    // region Mass dimension tests

    /**
     * Test converter handles mass dimension.
     */
    public function testHandlesMassDimension(): void
    {
        $converter = Converter::getByDimension('M');

        $conversion = $converter->getConversion('kg', 'g');

        $this->assertInstanceOf(Conversion::class, $conversion);

        // 1 kg = 1000 g
        $this->assertEqualsWithDelta(1000.0, $conversion->factor->value, 1e-10);
    }

    /**
     * Test converter handles mass unit conversions.
     */
    public function testHandlesMassUnitConversions(): void
    {
        $converter = Converter::getByDimension('M');

        $conversion = $converter->getConversion('kg', 'lb');

        $this->assertInstanceOf(Conversion::class, $conversion);

        // 1 kg ≈ 2.20462 lb
        $this->assertEqualsWithDelta(2.20462, $conversion->factor->value, 1e-4);
    }

    // endregion

    // region Edge cases

    /**
     * Test unitTerms property contains unprefixed terms.
     */
    public function testUnitTermsContainsUnprefixedTerms(): void
    {
        $converter = Converter::getByDimension('L');

        // Trigger some conversions to populate unitTerms
        $converter->getConversion('m', 'ft');

        $this->assertNotEmpty($converter->units);

        // All unit terms should be unprefixed
        foreach ($converter->units as $unit) {
            $this->assertFalse($unit->hasPrefixes());
        }
    }

    /**
     * Test converter handles very small conversion factors.
     */
    public function testHandlesVerySmallConversionFactors(): void
    {
        $converter = Converter::getByDimension('L');

        // mm to km is a very small factor
        $factor = $converter->getConversionFactor('mm', 'km');

        $this->assertEqualsWithDelta(1e-6, $factor, 1e-12);
    }

    /**
     * Test converter handles very large conversion factors.
     */
    public function testHandlesVeryLargeConversionFactors(): void
    {
        $converter = Converter::getByDimension('L');

        // km to mm is a very large factor
        $factor = $converter->getConversionFactor('km', 'mm');

        $this->assertEqualsWithDelta(1e6, $factor, 1e-10);
    }

    // endregion

    // region expand() tests

    /**
     * Test expand returns unchanged value and unit when no expansion needed.
     */
    public function testExpandReturnsUnchangedWhenNoExpansion(): void
    {
        // Metre has no expansion - it's a base SI unit.
        $unit = DerivedUnit::parse('m');

        [$value, $resultUnit] = Converter::expand(5.0, $unit);

        $this->assertSame(5.0, $value);
        $this->assertSame('m', $resultUnit->asciiSymbol);
    }

    /**
     * Test expand returns unchanged for unit without expansion.
     */
    public function testExpandReturnsUnchangedForSecond(): void
    {
        // Second has no expansion.
        $unit = DerivedUnit::parse('s');

        [$value, $resultUnit] = Converter::expand(10.0, $unit);

        $this->assertSame(10.0, $value);
        $this->assertSame('s', $resultUnit->asciiSymbol);
    }

    // endregion

    // region merge() tests

    /**
     * Test merge returns unchanged value and unit when no merging needed.
     */
    public function testMergeReturnsUnchangedWhenNoMerging(): void
    {
        // Simple unit with no mergeable components.
        $unit = DerivedUnit::parse('m');

        [$value, $resultUnit] = Converter::merge(5.0, $unit);

        $this->assertSame(5.0, $value);
        $this->assertSame('m', $resultUnit->asciiSymbol);
    }

    /**
     * Test merge returns unchanged for compound unit with different dimensions.
     */
    public function testMergeReturnsUnchangedForDifferentDimensions(): void
    {
        // m/s has different dimensions so nothing to merge.
        $unit = DerivedUnit::parse('m/s');

        [$value, $resultUnit] = Converter::merge(1.0, $unit);

        $this->assertSame(1.0, $value);
        $this->assertSame('m/s', $resultUnit->asciiSymbol);
    }

    // endregion
}
