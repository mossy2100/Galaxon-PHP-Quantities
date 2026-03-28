<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Internal;

use DomainException;
use Galaxon\Quantities\Currencies\CurrencyService;
use Galaxon\Quantities\Currencies\ExchangeRateServices\FrankfurterService;
use Galaxon\Quantities\Exceptions\DimensionMismatchException;
use Galaxon\Quantities\Internal\Conversion;
use Galaxon\Quantities\Internal\Converter;
use Galaxon\Quantities\Internal\DerivedUnit;
use Galaxon\Quantities\Internal\FloatWithError;
use Galaxon\Quantities\Internal\Unit;
use Galaxon\Quantities\Services\ConversionService;
use Galaxon\Quantities\Services\QuantityTypeService;
use Galaxon\Quantities\Services\UnitService;
use Galaxon\Quantities\Tests\Fixtures\UnregisteredQuantity;
use Galaxon\Quantities\UnitSystem;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Converter class.
 */
#[CoversClass(Converter::class)]
class ConverterTest extends TestCase
{
    // region removeAllInstances() tests

    /**
     * Test removeAllInstances clears cached instances.
     */
    public function testResetClearsCachedInstances(): void
    {
        // Get an instance to populate the cache.
        $converter1 = Converter::getInstance('L');

        // Reset the cache.
        Converter::removeAllInstances();

        // Get a new instance - should be a different object.
        $converter2 = Converter::getInstance('L');

        // They should not be the same instance.
        $this->assertNotSame($converter1, $converter2);
    }

    // endregion

    // region getInstances() tests

    /**
     * Test getInstances returns all cached Converter instances.
     */
    public function testGetInstancesReturnsAllCachedConverters(): void
    {
        Converter::removeAllInstances();

        $this->assertEmpty(Converter::getInstances());

        $length = Converter::getInstance('L');
        $time = Converter::getInstance('T');

        $instances = Converter::getInstances();

        $this->assertCount(2, $instances);
        $this->assertSame($length, $instances['L']);
        $this->assertSame($time, $instances['T']);
    }

    // endregion

    // region removeInstance() tests

    /**
     * Test removeInstance removes a single cached Converter.
     */
    public function testRemoveInstanceRemovesCachedConverter(): void
    {
        Converter::removeAllInstances();

        Converter::getInstance('L');
        Converter::getInstance('T');

        $this->assertCount(2, Converter::getInstances());

        Converter::removeInstance('L');

        $instances = Converter::getInstances();
        $this->assertCount(1, $instances);
        $this->assertArrayHasKey('T', $instances);
        $this->assertArrayNotHasKey('L', $instances);
    }

    // endregion

    // region getInstance() tests

    /**
     * Test getInstance returns instance for valid dimension.
     */
    public function testGetInstanceReturnsInstance(): void
    {
        $converter = Converter::getInstance('L');

        $this->assertInstanceOf(Converter::class, $converter);
        $this->assertSame('L', $converter->dimension);
    }

    /**
     * Test getInstance throws for invalid dimension.
     */
    public function testGetInstanceThrowsForInvalidDimension(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid dimension code 'X'.");

        Converter::getInstance('X');
    }

    /**
     * Test getInstance returns same instance for same dimension (singleton).
     */
    public function testGetInstanceReturnsSameInstance(): void
    {
        $converter1 = Converter::getInstance('L');
        $converter2 = Converter::getInstance('L');

        $this->assertSame($converter1, $converter2);
    }

    /**
     * Test getInstance returns different instances for different dimensions.
     */
    public function testGetInstanceReturnsDifferentInstancesForDifferentDimensions(): void
    {
        $lengthConverter = Converter::getInstance('L');
        $timeConverter = Converter::getInstance('T');

        $this->assertNotSame($lengthConverter, $timeConverter);
        $this->assertSame('L', $lengthConverter->dimension);
        $this->assertSame('T', $timeConverter->dimension);
    }

    // endregion

    // region findConversion() tests

    /**
     * Test findConversion returns conversion for known units.
     */
    public function testGetConversionReturnsConversion(): void
    {
        $converter = Converter::getInstance('L');

        $conversion = $converter->findConversion('m', 'ft');

        $this->assertInstanceOf(Conversion::class, $conversion);
        $this->assertSame('m', (string)$conversion->srcUnit);
        $this->assertSame('ft', (string)$conversion->destUnit);
        $this->assertGreaterThan(0.0, $conversion->factor->value);
    }

    /**
     * Test findConversion returns unity conversion for identical units.
     */
    public function testGetConversionReturnsUnityForIdenticalUnits(): void
    {
        $converter = Converter::getInstance('L');

        $conversion = $converter->findConversion('m', 'm');

        $this->assertInstanceOf(Conversion::class, $conversion);
        $this->assertSame(1.0, $conversion->factor->value);
    }

    /**
     * Test findConversion handles prefix-only differences.
     */
    public function testGetConversionHandlesPrefixOnlyDifference(): void
    {
        $converter = Converter::getInstance('L');

        $conversion = $converter->findConversion('km', 'm');

        $this->assertInstanceOf(Conversion::class, $conversion);
        $this->assertSame('km', (string)$conversion->srcUnit);
        $this->assertSame('m', (string)$conversion->destUnit);
        $this->assertEqualsWithDelta(1000.0, $conversion->factor->value, 1e-10);
    }

    /**
     * Test findConversion handles centimeters to meters.
     */
    public function testGetConversionCentimetersToMeters(): void
    {
        $converter = Converter::getInstance('L');

        $conversion = $converter->findConversion('cm', 'm');

        $this->assertInstanceOf(Conversion::class, $conversion);
        $this->assertEqualsWithDelta(0.01, $conversion->factor->value, 1e-10);
    }

    /**
     * Test findConversion with DerivedUnit objects.
     */
    public function testGetConversionWithUnitTermObjects(): void
    {
        $converter = Converter::getInstance('L');
        $srcUnitTerm = DerivedUnit::parse('m');
        $destUnitTerm = DerivedUnit::parse('ft');

        $conversion = $converter->findConversion($srcUnitTerm, $destUnitTerm);

        $this->assertInstanceOf(Conversion::class, $conversion);
    }

    /**
     * Test findConversion throws for invalid unit.
     */
    public function testGetConversionThrowsForInvalidUnitTerm(): void
    {
        $converter = Converter::getInstance('L');

        $this->expectException(DimensionMismatchException::class);

        $converter->findConversion('s', 'm'); // seconds is not a length unit
    }

    /**
     * Test findConversion caches results.
     */
    public function testGetConversionCachesResults(): void
    {
        $converter = Converter::getInstance('L');

        // First call generates the conversion
        $conversion1 = $converter->findConversion('m', 'ft');

        // Second call should return cached result
        $conversion2 = $converter->findConversion('m', 'ft');

        $this->assertInstanceOf(Conversion::class, $conversion1);
        $this->assertInstanceOf(Conversion::class, $conversion2);

        $this->assertSame($conversion1->factor->value, $conversion2->factor->value);
    }

    /**
     * Test findConversion returns null when no conversion path exists.
     */
    public function testGetConversionReturnsNullWhenNoPathExists(): void
    {
        // Register a custom unit with no conversions defined.
        $unit = new Unit(
            name: 'isolated length',
            asciiSymbol: 'Xu',
            dimension: 'L',
            systems: [UnitSystem::Si]
        );
        UnitService::add($unit);

        try {
            // Clear cached converters to pick up the new unit.
            Converter::removeAllInstances();

            $converter = Converter::getInstance('L');

            // Try to convert between the isolated unit and meter - no path exists.
            $conversion = $converter->findConversion('Xu', 'm');

            $this->assertNull($conversion);
        } finally {
            // Clean up.
            UnitService::remove($unit);
            Converter::removeAllInstances();
        }
    }

    /**
     * Test findConversion throws generic error when dimension has no registered QuantityType.
     */
    public function testFindConversionThrowsGenericErrorForUnregisteredDimension(): void
    {
        // L5 is a valid dimension but has no registered QuantityType.
        $converter = Converter::getInstance('L5');

        $this->expectException(DimensionMismatchException::class);
        $this->expectExceptionMessage('Dimension mismatch');

        // 's' is a time unit (dimension T), not L5.
        $converter->findConversion('s', 'm5');
    }

    // endregion

    // region findConversionFactor() tests

    /**
     * Test findConversionFactor returns factor for known units.
     */
    public function testGetConversionFactorReturnsFactor(): void
    {
        $converter = Converter::getInstance('L');

        $factor = $converter->findConversionFactor('m', 'ft');

        $this->assertIsFloat($factor);
        $this->assertGreaterThan(0.0, $factor);
    }

    /**
     * Test findConversionFactor returns 1.0 for identical units.
     */
    public function testGetConversionFactorReturnsOneForIdenticalUnits(): void
    {
        $converter = Converter::getInstance('L');

        $factor = $converter->findConversionFactor('m', 'm');

        $this->assertSame(1.0, $factor);
    }

    /**
     * Test findConversionFactor for prefix conversion.
     */
    public function testGetConversionFactorForPrefixConversion(): void
    {
        $converter = Converter::getInstance('L');

        $factor = $converter->findConversionFactor('km', 'mm');

        // km → mm = 1,000,000
        $this->assertEqualsWithDelta(1e6, $factor, 1e-10);
    }

    /**
     * Test findConversionFactor returns null when no path exists.
     */
    public function testGetConversionFactorReturnsNullWhenNoPathExists(): void
    {
        // Register a custom unit with no conversions defined.
        $unit = new Unit(
            name: 'isolated length two',
            asciiSymbol: 'Yu',
            dimension: 'L',
            systems: [UnitSystem::Si]
        );
        UnitService::add($unit);

        try {
            // Clear cached converters to pick up the new unit.
            Converter::removeAllInstances();

            $converter = Converter::getInstance('L');

            // Try to get factor between the isolated unit and meter - no path exists.
            $factor = $converter->findConversionFactor('Yu', 'm');

            $this->assertNull($factor);
        } finally {
            // Clean up.
            UnitService::remove($unit);
            Converter::removeAllInstances();
        }
    }

    // endregion

    // region convert() tests

    /**
     * Test convert converts value between units.
     */
    public function testConvertConvertsValue(): void
    {
        $converter = Converter::getInstance('L');

        $result = $converter->convert(1.0, 'm', 'ft');

        // 1 meter ≈ 3.28084 feet
        $this->assertEqualsWithDelta(3.28084, $result, 1e-4);
    }

    /**
     * Test convert returns same value for identical units.
     */
    public function testConvertReturnsSameValueForIdenticalUnits(): void
    {
        $converter = Converter::getInstance('L');

        $result = $converter->convert(42.5, 'm', 'm');

        $this->assertSame(42.5, $result);
    }

    /**
     * Test convert handles prefix conversions.
     */
    public function testConvertHandlesPrefixConversions(): void
    {
        $converter = Converter::getInstance('L');

        $result = $converter->convert(5.0, 'km', 'm');

        $this->assertEqualsWithDelta(5000.0, $result, 1e-10);
    }

    /**
     * Test convert handles zero value.
     */
    public function testConvertHandlesZeroValue(): void
    {
        $converter = Converter::getInstance('L');

        $result = $converter->convert(0.0, 'm', 'ft');

        $this->assertSame(0.0, $result);
    }

    /**
     * Test convert handles negative value.
     */
    public function testConvertHandlesNegativeValue(): void
    {
        $converter = Converter::getInstance('L');

        $result = $converter->convert(-10.0, 'm', 'ft');

        $this->assertLessThan(0.0, $result);
        $this->assertEqualsWithDelta(-32.8084, $result, 1e-3);
    }

    /**
     * Test convert throws for invalid source unit.
     */
    public function testConvertThrowsForInvalidSourceUnit(): void
    {
        $converter = Converter::getInstance('L');

        $this->expectException(DimensionMismatchException::class);

        $converter->convert(1.0, 's', 'm');
    }

    /**
     * Test convert throws for invalid destination unit.
     */
    public function testConvertThrowsForInvalidDestinationUnit(): void
    {
        $converter = Converter::getInstance('L');

        $this->expectException(DimensionMismatchException::class);

        $converter->convert(1.0, 'm', 's');
    }

    /**
     * Test convert throws LogicException when no conversion path exists.
     */
    public function testConvertThrowsLogicExceptionWhenNoPathExists(): void
    {
        // Register a custom unit with no conversions defined.
        $unit = new Unit(
            name: 'isolated length three',
            asciiSymbol: 'Wu',
            dimension: 'L',
            systems: [UnitSystem::Si]
        );
        UnitService::add($unit);

        try {
            // Clear cached converters to pick up the new unit.
            Converter::removeAllInstances();

            $converter = Converter::getInstance('L');

            $this->expectException(LogicException::class);
            $this->expectExceptionMessage("No conversion path found between 'Wu' and 'm'");

            $converter->convert(1.0, 'Wu', 'm');
        } finally {
            // Clean up.
            UnitService::remove($unit);
            Converter::removeAllInstances();
        }
    }

    // endregion

    // region Graph traversal tests

    /**
     * Test converter finds indirect conversions via graph traversal.
     */
    public function testFindsIndirectConversions(): void
    {
        $converter = Converter::getInstance('L');

        // Even if m → yd isn't directly defined, it should be found via m → ft → yd or similar
        $conversion = $converter->findConversion('m', 'yd');

        $this->assertInstanceOf(Conversion::class, $conversion);
        // 1 meter ≈ 1.09361 yards
        $this->assertEqualsWithDelta(1.09361, $conversion->factor->value, 1e-4);
    }

    /**
     * Test converter handles inverse conversions.
     */
    public function testHandlesInverseConversions(): void
    {
        $converter = Converter::getInstance('L');

        // If m → ft exists, ft → m should be derivable
        $mToFt = $converter->findConversion('m', 'ft');
        $ftToM = $converter->findConversion('ft', 'm');

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
        $converter = Converter::getInstance('L2');

        $conversion = $converter->findConversion('m2', 'ft2');

        $this->assertInstanceOf(Conversion::class, $conversion);
        // 1 m² ≈ 10.7639 ft²
        $this->assertEqualsWithDelta(10.7639, $conversion->factor->value, 1e-3);
    }

    /**
     * Test converter handles prefixed area units.
     */
    public function testHandlesPrefixedAreaUnits(): void
    {
        $converter = Converter::getInstance('L2');

        $conversion = $converter->findConversion('km2', 'm2');

        $this->assertInstanceOf(Conversion::class, $conversion);

        // 1 km² = 1,000,000 m²
        $this->assertEqualsWithDelta(1e6, $conversion->factor->value, 1e-10);
    }

    /**
     * Test converter handles L3 (volume) dimension.
     */
    public function testHandlesVolumeDimension(): void
    {
        $converter = Converter::getInstance('L3');

        $conversion = $converter->findConversion('m3', 'ft3');

        $this->assertInstanceOf(Conversion::class, $conversion);
        // 1 m³ ≈ 35.3147 ft³
        $this->assertEqualsWithDelta(35.3147, $conversion->factor->value, 1e-3);
    }

    /**
     * Test exponentiated conversion derives from base dimension.
     */
    public function testExponentiatedConversionDerivesFromBaseDimension(): void
    {
        $lengthConverter = Converter::getInstance('L');
        $areaConverter = Converter::getInstance('L2');

        $mToFt = $lengthConverter->findConversionFactor('m', 'ft');
        $m2ToFt2 = $areaConverter->findConversionFactor('m2', 'ft2');

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
        $converter = Converter::getInstance('T');

        $conversion = $converter->findConversion('h', 's');

        $this->assertInstanceOf(Conversion::class, $conversion);

        // 1 hour = 3600 seconds
        $this->assertEqualsWithDelta(3600.0, $conversion->factor->value, 1e-10);
    }

    /**
     * Test converter handles time with prefixes.
     */
    public function testHandlesTimeWithPrefixes(): void
    {
        $converter = Converter::getInstance('T');

        $conversion = $converter->findConversion('ms', 's');

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
        $converter = Converter::getInstance('M');

        $conversion = $converter->findConversion('kg', 'g');

        $this->assertInstanceOf(Conversion::class, $conversion);

        // 1 kg = 1000 g
        $this->assertEqualsWithDelta(1000.0, $conversion->factor->value, 1e-10);
    }

    /**
     * Test converter handles mass unit conversions.
     */
    public function testHandlesMassUnitConversions(): void
    {
        $converter = Converter::getInstance('M');

        $conversion = $converter->findConversion('kg', 'lb');

        $this->assertInstanceOf(Conversion::class, $conversion);

        // 1 kg ≈ 2.20462 lb
        $this->assertEqualsWithDelta(2.20462, $conversion->factor->value, 1e-4);
    }

    // endregion

    // region Edge cases

    /**
     * Test converter handles very small conversion factors.
     */
    public function testHandlesVerySmallConversionFactors(): void
    {
        $converter = Converter::getInstance('L');

        // mm to km is a very small factor
        $factor = $converter->findConversionFactor('mm', 'km');

        $this->assertEqualsWithDelta(1e-6, $factor, 1e-12);
    }

    /**
     * Test converter handles very large conversion factors.
     */
    public function testHandlesVeryLargeConversionFactors(): void
    {
        $converter = Converter::getInstance('L');

        // km to mm is a very large factor
        $factor = $converter->findConversionFactor('km', 'mm');

        $this->assertEqualsWithDelta(1e6, $factor, 1e-10);
    }

    // endregion

    // region Compound unit conversion tests

    /**
     * Test conversion of force units (N to lbf).
     */
    public function testConversionOfForceUnits(): void
    {
        $converter = Converter::getInstance('MLT-2');

        $conversion = $converter->findConversion('N', 'lbf');

        $this->assertInstanceOf(Conversion::class, $conversion);
        // 1 N ≈ 0.2248 lbf
        $this->assertEqualsWithDelta(0.2248, $conversion->factor->value, 1e-3);
    }

    // endregion

    // region Currency conversion tests

    /**
     * Test that findConversion refreshes currency data when dimension contains 'C'.
     */
    public function testFindConversionRefreshesCurrencyData(): void
    {
        CurrencyService::init(new FrankfurterService());

        try {
            Converter::removeAllInstances();

            $conversion = ConversionService::find('AUD', 'USD');

            $this->assertInstanceOf(Conversion::class, $conversion);
            $this->assertGreaterThan(0.0, $conversion->factor->value);
        } finally {
            CurrencyService::setExchangeRateService(null);
            Converter::removeAllInstances();
        }
    }

    // endregion

    // region Multi-hop conversion tests

    /**
     * Test that findConversion uses generateConversions to find multi-hop paths.
     *
     * Some conversions (like in → mi) require multiple hops that cannot be found by
     * findNewConversion directly, but are discovered after generateConversions fills in
     * intermediate pairs.
     */
    public function testFindConversionViaGenerateConversions(): void
    {
        Converter::removeAllInstances();

        $converter = Converter::getInstance('L');
        $conversion = $converter->findConversion('mi', 'in');

        $this->assertInstanceOf(Conversion::class, $conversion);
        // 1 in = 1/63360 mi.
        $this->assertEqualsWithDelta(63360, $conversion->factor->value, 1e-10);
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
        // Reset converters.
        Converter::removeAllInstances();

        // Create custom units.
        $tx = new Unit('test x', 'Tx', 'L', systems: [UnitSystem::Si]);
        $ty = new Unit('test y', 'Ty', 'L', systems: [UnitSystem::Si]);
        $tz = new Unit('test z', 'Tz', 'L', systems: [UnitSystem::Si]);

        // Register custom units.
        UnitService::add($tx);
        UnitService::add($ty);
        UnitService::add($tz);

        // Add conversions that enable divergent path: Y→X and Y→Z.
        ConversionService::add(new Conversion('Ty', 'Tx', 2.0));
        ConversionService::add(new Conversion('Ty', 'Tz', 4.0));

        try {
            $converter = Converter::getInstance('L');

            // This should find X→Z via divergent combination.
            $conversion = $converter->findConversion('Tx', 'Tz');

            $this->assertInstanceOf(Conversion::class, $conversion);
            $this->assertEqualsWithDelta(2.0, $conversion->factor->value, 1e-10);
        } finally {
            UnitService::remove($tx);
            UnitService::remove($ty);
            UnitService::remove($tz);
            Converter::removeAllInstances();
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
        // Reset converters.
        Converter::removeAllInstances();

        // Use a custom isolated dimension so only our units exist.
        $dimension = 'L7';
        QuantityTypeService::add('hypertest', $dimension, UnregisteredQuantity::class);

        // Create custom units.
        $oppA = new Unit('opp a', 'Oa', $dimension, systems: [UnitSystem::Si]);
        $oppB = new Unit('opp b', 'Ob', $dimension, systems: [UnitSystem::Si]);
        $oppC = new Unit('opp c', 'Oc', $dimension, systems: [UnitSystem::Si]);

        // Register exactly three units in this dimension.
        UnitService::add($oppA);
        UnitService::add($oppB);
        UnitService::add($oppC);

        // Add conversions that enable opposite path: B→A and C→B.
        // Use explicit zero error for 0.5 so that 1/(2*0.5) = 1.0 has zero error.
        ConversionService::add(new Conversion('Ob', 'Oa', 2.0));
        ConversionService::add(new Conversion('Oc', 'Ob', new FloatWithError(0.5, 0.0)));

        try {
            $converter = Converter::getInstance($dimension);

            // This should find A→C via opposite combination with zero error.
            $conversion = $converter->findConversion('Oa', 'Oc');

            $this->assertInstanceOf(Conversion::class, $conversion);
            $this->assertEqualsWithDelta(1.0, $conversion->factor->value, 1e-10);
            $this->assertSame(0.0, $conversion->factor->relativeError);
        } finally {
            UnitService::remove($oppA);
            UnitService::remove($oppB);
            UnitService::remove($oppC);
            Converter::removeAllInstances();
            QuantityTypeService::reset();
        }
    }

    /**
     * Test that convergent combination path is used in graph traversal with zero error.
     *
     * Convergent: src→mid and dest→mid both exist, compute src→dest as m1/m2.
     * We set up: A→B = 6 and C→B = 3, so A→C = 6/3 = 2.
     * This triggers the convergent early return when zero error is achieved.
     */
    public function testConvergentCombinationPathWithZeroError(): void
    {
        // Reset converters.
        Converter::removeAllInstances();

        // Use a custom isolated dimension.
        $dimension = 'L8';
        QuantityTypeService::add('convtest', $dimension, UnregisteredQuantity::class);

        // Create custom units.
        $convA = new Unit('conv a', 'Ca', $dimension, systems: [UnitSystem::Si]);
        $convB = new Unit('conv b', 'Cb', $dimension, systems: [UnitSystem::Si]);
        $convC = new Unit('conv c', 'Cc', $dimension, systems: [UnitSystem::Si]);

        UnitService::add($convA);
        UnitService::add($convB);
        UnitService::add($convC);

        // Add conversions that enable convergent path: A→B and C→B.
        ConversionService::add(new Conversion('Ca', 'Cb', 6.0));
        ConversionService::add(new Conversion('Cc', 'Cb', 3.0));

        try {
            $converter = Converter::getInstance($dimension);

            // This should find A→C via convergent combination: 6/3 = 2.
            $conversion = $converter->findConversion('Ca', 'Cc');

            $this->assertInstanceOf(Conversion::class, $conversion);
            $this->assertEqualsWithDelta(2.0, $conversion->factor->value, 1e-10);
        } finally {
            UnitService::remove($convA);
            UnitService::remove($convB);
            UnitService::remove($convC);
            Converter::removeAllInstances();
            QuantityTypeService::reset();
        }
    }

    // endregion

    // region addMerged() tests

    /**
     * Test that addMerged is triggered when finding conversion with mergeable units.
     *
     * This covers addMerged() by going through findConversion() with a mergeable unit.
     */
    public function testGetConversionTriggersMergeForMergeableUnits(): void
    {
        $converter = Converter::getInstance('L2');

        // m*ft has two length units that should be merged.
        // Getting a conversion should trigger addMerged internally.
        $conversion = $converter->findConversion('m*ft', 'm2');

        $this->assertInstanceOf(Conversion::class, $conversion);
        // 1 m*ft = 0.3048 m² (since 1 ft = 0.3048 m)
        $this->assertEqualsWithDelta(0.3048, $conversion->factor->value, 1e-4);
    }

    /**
     * Test that addExpanded is triggered for compound units during findConversion.
     *
     * When a non-base compound unit like N*m is first encountered, its expansion
     * (N → kg*m/s2, so N*m → kg*m2/s2) is discovered and added as a new conversion.
     */
    public function testFindConversionTriggersAddExpanded(): void
    {
        Converter::removeAllInstances();

        // N*m is not in the energy (ML2T-2) conversion definitions, but N can be expanded
        // to kg*m/s2, so N*m expands to kg*m2/s2 = J. This triggers addExpanded().
        $converter = Converter::getInstance('ML2T-2');
        $conversion = $converter->findConversion('N*m', 'J');

        $this->assertInstanceOf(Conversion::class, $conversion);
        $this->assertEqualsWithDelta(1.0, $conversion->factor->value, 1e-10);
    }

    // endregion

    // region findConversionByPrefix() tests

    /**
     * Test findConversionByPrefix returns null when exponents differ.
     */
    public function testFindConversionByPrefixReturnsNullForDifferentExponents(): void
    {
        $converter = Converter::getInstance('L2');

        $m2 = DerivedUnit::parse('m2');
        $m3 = DerivedUnit::parse('m3');

        $result = $converter->findConversionByPrefix($m2, $m3);

        $this->assertNull($result);
    }

    // endregion

    // region addConversion() tests

    /**
     * Test addConversion throws LogicException when conversion dimension doesn't match Converter.
     */
    public function testAddConversionThrowsForDimensionMismatch(): void
    {
        $converter = Converter::getInstance('L');

        // Create a mass conversion (dimension M).
        $conversion = new Conversion('kg', 'g', 1000);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("Cannot add conversion with dimension 'M'");

        $converter->addConversion($conversion);
    }

    // endregion

    // region removeConversion() tests

    /**
     * Test removeConversion removes a specific conversion from the matrix.
     */
    public function testRemoveConversionRemovesFromMatrix(): void
    {
        Converter::removeAllInstances();
        $converter = Converter::getInstance('L');

        // Verify a conversion exists after loading definitions.
        $conversion = $converter->getConversion('m', 'ft');

        if ($conversion === null) {
            // Add one if not loaded by default.
            $conversion = new Conversion('m', 'ft', 3.28084);
            $converter->addConversion($conversion);
        }

        $this->assertNotNull($converter->getConversion('m', 'ft'));

        $converter->removeConversion($conversion);

        $this->assertNull($converter->getConversion('m', 'ft'));
    }

    // endregion

    // region removeAllConversions() tests

    /**
     * Test removeAllConversions clears the conversion matrix.
     */
    public function testRemoveAllConversionsClearsMatrix(): void
    {
        Converter::removeAllInstances();
        $converter = Converter::getInstance('L');

        $this->assertNotEmpty($converter->conversionMatrix);

        $converter->removeAllConversions();

        $this->assertEmpty($converter->conversionMatrix);
    }

    // endregion

    // region removeConversionsByUnit() tests

    /**
     * Test removeConversionsByUnit removes all conversions involving a given unit.
     */
    public function testRemoveConversionsByUnitRemovesAllInvolving(): void
    {
        Converter::removeAllInstances();

        // Use an isolated dimension to avoid side effects.
        $dimension = 'L9';
        QuantityTypeService::add('remtest', $dimension, UnregisteredQuantity::class);

        $unitA = new Unit('rem a', 'Xra', $dimension, systems: [UnitSystem::Si]);
        $unitB = new Unit('rem b', 'Xrb', $dimension, systems: [UnitSystem::Si]);
        $unitC = new Unit('rem c', 'Xrc', $dimension, systems: [UnitSystem::Si]);

        UnitService::add($unitA);
        UnitService::add($unitB);
        UnitService::add($unitC);

        // Add conversions: A→B and B→C.
        ConversionService::add(new Conversion('Xra', 'Xrb', 2.0));
        ConversionService::add(new Conversion('Xrb', 'Xrc', 3.0));

        try {
            $converter = Converter::getInstance($dimension);

            $this->assertTrue($converter->hasConversion('Xra', 'Xrb'));
            $this->assertTrue($converter->hasConversion('Xrb', 'Xrc'));

            // Remove all conversions involving unitB.
            $converter->removeConversionsByUnit($unitB);

            // Both conversions should be gone since they both involve Xrb.
            $this->assertFalse($converter->hasConversion('Xra', 'Xrb'));
            $this->assertFalse($converter->hasConversion('Xrb', 'Xrc'));
        } finally {
            UnitService::remove($unitA);
            UnitService::remove($unitB);
            UnitService::remove($unitC);
            Converter::removeAllInstances();
            QuantityTypeService::reset();
        }
    }

    // endregion

    // region hasUnit() tests

    /**
     * Test hasUnit() returns true for a unit that has been added.
     */
    public function testHasUnitReturnsTrueForAddedUnit(): void
    {
        Converter::removeAllInstances();
        $converter = Converter::getInstance('L');

        $unit = DerivedUnit::parse('m');
        $converter->addUnit($unit);

        $this->assertTrue($converter->hasUnit($unit));
    }

    /**
     * Test hasUnit() returns false for a unit that has not been added.
     */
    public function testHasUnitReturnsFalseForUnknownUnit(): void
    {
        Converter::removeAllInstances();
        $converter = Converter::getInstance('L');
        $converter->removeAllUnits();

        $unit = DerivedUnit::parse('m');

        $this->assertFalse($converter->hasUnit($unit));
    }

    // endregion

    // region removeUnit() tests

    /**
     * Test removeUnit removes a unit from the unit list.
     */
    public function testRemoveUnitRemovesFromList(): void
    {
        Converter::removeAllInstances();
        $converter = Converter::getInstance('L');

        $unit = DerivedUnit::parse('m');
        $converter->addUnit($unit);
        $this->assertArrayHasKey('m', $converter->units);

        $converter->removeUnit($unit);

        $this->assertArrayNotHasKey('m', $converter->units);
    }

    // endregion

    // region quantityType property tests

    /**
     * Test quantityType property returns QuantityType for a registered dimension.
     */
    public function testQuantityTypePropertyReturnsQuantityType(): void
    {
        $converter = Converter::getInstance('L');

        $this->assertNotNull($converter->quantityType);
        $this->assertSame('length', $converter->quantityType->name);
    }

    /**
     * Test quantityType property returns null for an unregistered dimension.
     */
    public function testQuantityTypePropertyReturnsNullForUnregisteredDimension(): void
    {
        $converter = Converter::getInstance('L5T3');

        $this->assertNull($converter->quantityType);
    }

    // endregion

    // region removeAllUnits() tests

    /**
     * Test removeAllUnits clears the unit list.
     */
    public function testRemoveAllUnitsClearsList(): void
    {
        Converter::removeAllInstances();
        $converter = Converter::getInstance('L');

        $this->assertNotEmpty($converter->units);

        $converter->removeAllUnits();

        $this->assertEmpty($converter->units);
    }

    // endregion
}
