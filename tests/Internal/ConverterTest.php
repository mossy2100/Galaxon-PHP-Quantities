<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Internal;

use DomainException;
use Galaxon\Quantities\Internal\Conversion;
use Galaxon\Quantities\Internal\Converter;
use Galaxon\Quantities\Internal\DerivedUnit;
use Galaxon\Quantities\Internal\FloatWithError;
use Galaxon\Quantities\Internal\Unit;
use Galaxon\Quantities\Registry\ConversionRegistry;
use Galaxon\Quantities\Registry\QuantityTypeRegistry;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
use LogicException;
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

    /**
     * Test getConversion returns null when no conversion path exists.
     */
    public function testGetConversionReturnsNullWhenNoPathExists(): void
    {
        // Register a custom unit with no conversions defined.
        UnitRegistry::add(new Unit(
            name: 'isolated length',
            asciiSymbol: 'Xu',
            dimension: 'L',
            systems: [System::Si]
        ));

        try {
            // Clear cached converters to pick up the new unit.
            Converter::clear();

            $converter = Converter::getByDimension('L');

            // Try to convert between the isolated unit and meter - no path exists.
            $conversion = $converter->getConversion('Xu', 'm');

            $this->assertNull($conversion);
        } finally {
            // Clean up.
            UnitRegistry::remove('isolated-length');
            Converter::clear();
        }
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

    /**
     * Test getConversionFactor returns null when no path exists.
     */
    public function testGetConversionFactorReturnsNullWhenNoPathExists(): void
    {
        // Register a custom unit with no conversions defined.
        UnitRegistry::add(new Unit(
            name: 'isolated length two',
            asciiSymbol: 'Yu',
            dimension: 'L',
            systems: [System::Si]
        ));

        try {
            // Clear cached converters to pick up the new unit.
            Converter::clear();

            $converter = Converter::getByDimension('L');

            // Try to get factor between the isolated unit and meter - no path exists.
            $factor = $converter->getConversionFactor('Yu', 'm');

            $this->assertNull($factor);
        } finally {
            // Clean up.
            UnitRegistry::remove('isolated-length2');
            Converter::clear();
        }
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

    /**
     * Test validateUnit throws with generic message for unknown dimension.
     *
     * When the converter's dimension is not in QuantityTypeRegistry, the error message
     * should use the generic format rather than the quantity-name format.
     */
    public function testValidateUnitThrowsGenericMessageForUnknownDimension(): void
    {
        // Register a custom unit with a dimension not in QuantityTypeRegistry.
        UnitRegistry::add(new Unit(
            name: 'custom unit',
            asciiSymbol: 'Zu',
            dimension: 'L4',
            systems: [System::Si]
        ));

        // Also register a conversion so the converter has something to load.
        $conversion = new Conversion('Zu', 'Zu', 1.0);
        ConversionRegistry::add($conversion);

        try {
            Converter::clear();

            $converter = Converter::getByDimension('L4');

            // Try to validate a unit with wrong dimension.
            $this->expectException(DomainException::class);
            $this->expectExceptionMessage('does not match the converter dimension');

            $converter->validateUnit('m'); // L dimension, not L4
        } finally {
            UnitRegistry::remove('custom-unit');
            ConversionRegistry::remove($conversion);
            Converter::clear();
        }
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

    /**
     * Test convert throws LogicException when no conversion path exists.
     */
    public function testConvertThrowsLogicExceptionWhenNoPathExists(): void
    {
        // Register a custom unit with no conversions defined.
        UnitRegistry::add(new Unit(
            name: 'isolated length three',
            asciiSymbol: 'Wu',
            dimension: 'L',
            systems: [System::Si]
        ));

        try {
            // Clear cached converters to pick up the new unit.
            Converter::clear();

            $converter = Converter::getByDimension('L');

            $this->expectException(LogicException::class);
            $this->expectExceptionMessage("No conversion path found between 'Wu' and 'm'");

            $converter->convert(1.0, 'Wu', 'm');
        } finally {
            // Clean up.
            UnitRegistry::remove('isolated-length3');
            Converter::clear();
        }
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
        // Meter has no expansion - it's a base SI unit.
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

    /**
     * Test expand expands Newton to base SI units.
     */
    public function testExpandExpandsNewtonToBaseSi(): void
    {
        // Newton (N) expands to kg·m/s².
        $unit = DerivedUnit::parse('N');

        [$value, $resultUnit] = Converter::expand(1.0, $unit);

        $this->assertSame(1.0, $value);
        $this->assertSame('MLT-2', $resultUnit->dimension);
        $this->assertSame('kg*m/s2', $resultUnit->asciiSymbol);
    }

    /**
     * Test expand expands Joule to base SI units.
     */
    public function testExpandExpandsJouleToBaseSi(): void
    {
        // Joule (J) expands to kg·m²/s².
        $unit = DerivedUnit::parse('J');

        [$value, $resultUnit] = Converter::expand(1.0, $unit);

        $this->assertSame(1.0, $value);
        $this->assertSame('ML2T-2', $resultUnit->dimension);
        $this->assertSame('kg*m2/s2', $resultUnit->asciiSymbol);
    }

    /**
     * Test expand handles prefixed expandable units.
     */
    public function testExpandHandlesPrefixedExpandableUnits(): void
    {
        // kN (kilonewton) should expand with the prefix applied.
        $unit = DerivedUnit::parse('kN');

        [$value, $resultUnit] = Converter::expand(1.0, $unit);

        // 1 kN = 1000 N = 1000 kg·m/s²
        $this->assertEqualsWithDelta(1000.0, $value, 1e-10);
        $this->assertSame('MLT-2', $resultUnit->dimension);
        $this->assertSame('kg*m/s2', $resultUnit->asciiSymbol);
    }

    /**
     * Test expand handles exponentiated expandable units.
     */
    public function testExpandHandlesExponentiatedExpandableUnits(): void
    {
        // N² should expand to (kg·m/s²)².
        $unit = DerivedUnit::parse('N2');

        [$value, $resultUnit] = Converter::expand(1.0, $unit);

        $this->assertSame(1.0, $value);
        $this->assertSame('M2L2T-4', $resultUnit->dimension);
        $this->assertSame('kg2*m2/s4', $resultUnit->asciiSymbol);
    }

    /**
     * Test expand handles compound unit where one part expands and another doesn't.
     *
     * This covers line 326 in expand() where a unit term has no expansion.
     */
    public function testExpandHandlesPartiallyExpandableUnit(): void
    {
        // N*s where N expands to kg*m/s² but s doesn't expand.
        $unit = DerivedUnit::parse('N*s');

        [$value, $resultUnit] = Converter::expand(1.0, $unit);

        // N*s = kg*m/s² * s = kg*m/s (impulse).
        $this->assertSame(1.0, $value);
        $this->assertSame('MLT-1', $resultUnit->dimension);
        $this->assertSame('kg*m/s', $resultUnit->asciiSymbol);
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

    /**
     * Test merge combines compatible length units.
     */
    public function testMergeCombinesCompatibleLengthUnits(): void
    {
        // m*ft has two length units that should merge.
        $unit = DerivedUnit::parse('m*ft');

        [$value, $resultUnit] = Converter::merge(1.0, $unit);

        // Result should be in m² (first unit encountered).
        $this->assertSame('L2', $resultUnit->dimension);
        // 1 ft ≈ 0.3048 m, so 1 m*ft ≈ 0.3048 m²
        $this->assertEqualsWithDelta(0.3048, $value, 1e-4);
    }

    /**
     * Test merge handles units with same base (combines exponents).
     */
    public function testMergeHandlesSameBaseUnits(): void
    {
        // m*m should combine to m².
        $unit = DerivedUnit::parse('m*m');

        [$value, $resultUnit] = Converter::merge(1.0, $unit);

        $this->assertSame(1.0, $value);
        $this->assertSame('m2', $resultUnit->asciiSymbol);
    }

    /**
     * Test merge handles division with compatible units.
     */
    public function testMergeHandlesDivisionWithCompatibleUnits(): void
    {
        // ft/m should merge to dimensionless.
        $unit = DerivedUnit::parse('ft/m');

        [$value, $resultUnit] = Converter::merge(1.0, $unit);

        // ft/m → should become dimensionless with value ≈ 0.3048
        $this->assertSame('1', $resultUnit->dimension);
        $this->assertEqualsWithDelta(0.3048, $value, 1e-4);
    }

    // endregion

    // region Compound unit conversion tests

    /**
     * Test conversion of force units (N to lbf).
     */
    public function testConversionOfForceUnits(): void
    {
        $converter = Converter::getByDimension('MLT-2');

        $conversion = $converter->getConversion('N', 'lbf');

        $this->assertInstanceOf(Conversion::class, $conversion);
        // 1 N ≈ 0.2248 lbf
        $this->assertEqualsWithDelta(0.2248, $conversion->factor->value, 1e-3);
    }

    // endregion

    // region Combination path coverage tests

    /**
     * Test that divergent combination path is used in graph traversal.
     *
     * Divergent: mid→src and mid→dest both exist, compute src→dest as dest/src.
     * We set up: Y→X = 2 and Y→Z = 4, so X→Z = 4/2 = 2.
     */
    public function testDivergentCombinationPath(): void
    {
        // Register custom units.
        UnitRegistry::add(new Unit('test x', 'Tx', 'L', systems: [System::Si]));
        UnitRegistry::add(new Unit('test y', 'Ty', 'L', systems: [System::Si]));
        UnitRegistry::add(new Unit('test z', 'Tz', 'L', systems: [System::Si]));

        // Add conversions that enable divergent path: Y→X and Y→Z.
        ConversionRegistry::add(new Conversion('Ty', 'Tx', 2.0));
        ConversionRegistry::add(new Conversion('Ty', 'Tz', 4.0));

        try {
            Converter::clear();
            $converter = Converter::getByDimension('L');

            // This should find X→Z via divergent combination.
            $conversion = $converter->getConversion('Tx', 'Tz');

            $this->assertInstanceOf(Conversion::class, $conversion);
            $this->assertEqualsWithDelta(2.0, $conversion->factor->value, 1e-10);
        } finally {
            UnitRegistry::remove('test-x');
            UnitRegistry::remove('test-y');
            UnitRegistry::remove('test-z');
            ConversionRegistry::reset();
            Converter::clear();
        }
    }

    /**
     * Test that opposite combination path is used in graph traversal with zero error.
     *
     * Uses an isolated custom dimension to ensure only our three test units exist.
     * Opposite: mid→src and dest→mid both exist, compute src→dest as 1/(m1*m2).
     * We set up: B→A = 2 and C→B = 0.5 (with explicit zero error), so A→C = 1/(2*0.5) = 1.0.
     * This triggers the early return when zero error is achieved.
     */
    public function testOppositeCombinationPathWithZeroError(): void
    {
        // Use a custom isolated dimension so only our units exist.
        $dimension = 'L7';
        QuantityTypeRegistry::add('hypertest', $dimension);

        // Register exactly three units in this dimension.
        UnitRegistry::add(new Unit('opp a', 'Oa', $dimension, systems: [System::Si]));
        UnitRegistry::add(new Unit('opp b', 'Ob', $dimension, systems: [System::Si]));
        UnitRegistry::add(new Unit('opp c', 'Oc', $dimension, systems: [System::Si]));

        // Add conversions that enable opposite path: B→A and C→B.
        // Use explicit zero error for 0.5 so that 1/(2*0.5) = 1.0 has zero error.
        ConversionRegistry::add(new Conversion('Ob', 'Oa', 2.0));
        ConversionRegistry::add(new Conversion('Oc', 'Ob', new FloatWithError(0.5, 0.0)));

        try {
            Converter::clear();
            $converter = Converter::getByDimension($dimension);

            // This should find A→C via opposite combination with zero error.
            $conversion = $converter->getConversion('Oa', 'Oc');

            $this->assertInstanceOf(Conversion::class, $conversion);
            $this->assertEqualsWithDelta(1.0, $conversion->factor->value, 1e-10);
            $this->assertSame(0.0, $conversion->factor->relativeError);
        } finally {
            UnitRegistry::remove('opp-a');
            UnitRegistry::remove('opp-b');
            UnitRegistry::remove('opp-c');
            ConversionRegistry::reset();
            Converter::clear();
            QuantityTypeRegistry::reset();
        }
    }

    // endregion

    // region addMergedUnit() tests

    /**
     * Test that addMergedUnit is triggered when getting conversion with mergeable units.
     *
     * This covers addMergedUnit() by going through getConversion() with a mergeable unit.
     */
    public function testGetConversionTriggersMergeForMergeableUnits(): void
    {
        $converter = Converter::getByDimension('L2');

        // m*ft has two length units that should be merged.
        // Getting a conversion should trigger addMergedUnit internally.
        $conversion = $converter->getConversion('m*ft', 'm2');

        $this->assertInstanceOf(Conversion::class, $conversion);
        // 1 m*ft = 0.3048 m² (since 1 ft = 0.3048 m)
        $this->assertEqualsWithDelta(0.3048, $conversion->factor->value, 1e-4);
    }

    // endregion
}
