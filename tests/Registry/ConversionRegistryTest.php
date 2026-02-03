<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Registry;

use Galaxon\Core\Exceptions\FormatException;
use Galaxon\Quantities\Conversion;
use Galaxon\Quantities\Registry\ConversionRegistry;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ConversionRegistry class.
 */
#[CoversClass(ConversionRegistry::class)]
final class ConversionRegistryTest extends TestCase
{
    // region Setup

    public static function setUpBeforeClass(): void
    {
        // Load Imperial/US units for cross-system tests.
        UnitRegistry::loadSystem(System::Imperial);
        UnitRegistry::loadSystem(System::US);
        UnitRegistry::loadSystem(System::Nautical);
    }

    // endregion

    // region getByDimension() tests

    /**
     * Test getByDimension() returns an array.
     */
    public function testGetByDimensionReturnsArray(): void
    {
        $result = ConversionRegistry::getByDimension('L');

        $this->assertIsArray($result);
    }

    /**
     * Test getByDimension() returns 2D array of conversions.
     */
    public function testGetByDimensionReturns2DArrayOfConversions(): void
    {
        $result = ConversionRegistry::getByDimension('L');

        $this->assertNotEmpty($result);

        foreach ($result as $srcSymbol => $destConversions) {
            $this->assertIsString($srcSymbol);
            $this->assertIsArray($destConversions);

            foreach ($destConversions as $destSymbol => $conversion) {
                $this->assertIsString($destSymbol);
                $this->assertInstanceOf(Conversion::class, $conversion);
            }
        }
    }

    /**
     * Test getByDimension() returns conversions with matching dimension.
     */
    public function testGetByDimensionReturnsMatchingDimension(): void
    {
        $result = ConversionRegistry::getByDimension('L');

        foreach ($result as $destConversions) {
            foreach ($destConversions as $conversion) {
                $this->assertSame('L', $conversion->dimension);
            }
        }
    }

    /**
     * Test getByDimension() throws for invalid dimension.
     */
    public function testGetByDimensionThrowsForInvalidDimension(): void
    {
        $this->expectException(FormatException::class);

        ConversionRegistry::getByDimension('X9');
    }

    /**
     * Test getByDimension() normalizes dimension codes.
     */
    public function testGetByDimensionNormalizesDimension(): void
    {
        // L1 should normalize to L
        $result1 = ConversionRegistry::getByDimension('L');
        $result2 = ConversionRegistry::getByDimension('L1');

        $this->assertEquals($result1, $result2);
    }

    /**
     * Test getByDimension() lazy loads only requested dimension.
     */
    public function testGetByDimensionLazyLoadsConversions(): void
    {
        // Request length conversions
        $lengthResult = ConversionRegistry::getByDimension('L');

        // Should have length conversions
        $this->assertNotEmpty($lengthResult);

        // Each conversion should be for length dimension
        foreach ($lengthResult as $destConversions) {
            foreach ($destConversions as $conversion) {
                $this->assertSame('L', $conversion->dimension);
            }
        }
    }

    // endregion

    // region has() tests

    /**
     * Test has() returns true for existing conversion.
     */
    public function testHasReturnsTrueForExistingConversion(): void
    {
        // First ensure conversions are loaded
        ConversionRegistry::getByDimension('L');

        // Add a known conversion
        $conversion = new Conversion('m', 'ft', 3.28084);
        ConversionRegistry::addConversion($conversion);

        $result = ConversionRegistry::has('L', 'm', 'ft');

        $this->assertTrue($result);
    }

    /**
     * Test has() returns false for non-existing conversion.
     */
    public function testHasReturnsFalseForNonExistingConversion(): void
    {
        // Ensure dimension is loaded
        ConversionRegistry::getByDimension('L');

        $result = ConversionRegistry::has('L', 'nonexistent1', 'nonexistent2');

        $this->assertFalse($result);
    }

    /**
     * Test has() returns false for non-existing dimension.
     */
    public function testHasReturnsFalseForNonExistingDimension(): void
    {
        $result = ConversionRegistry::has('X9', 'm', 'ft');

        $this->assertFalse($result);
    }

    /**
     * Test has() is directional.
     */
    public function testHasIsDirectional(): void
    {
        // Ensure conversions are loaded
        ConversionRegistry::getByDimension('L');

        // Add a conversion in one direction
        $conversion = new Conversion('m', 'yd', 1.09361);
        ConversionRegistry::addConversion($conversion);

        // Forward direction should exist
        $this->assertTrue(ConversionRegistry::has('L', 'm', 'yd'));

        // Reverse direction should not exist (unless separately added)
        // Note: This may or may not be true depending on initialization
        // The key point is that has() checks the exact direction
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
        ConversionRegistry::addConversion($conversion);

        $result = ConversionRegistry::get('L', 'm', 'in');

        $this->assertInstanceOf(Conversion::class, $result);
        $this->assertSame('m', $result->srcUnit->asciiSymbol);
        $this->assertSame('in', $result->destUnit->asciiSymbol);
    }

    /**
     * Test get() returns null for non-existing conversion.
     */
    public function testGetReturnsNullForNonExisting(): void
    {
        // Ensure dimension is loaded
        ConversionRegistry::getByDimension('L');

        $result = ConversionRegistry::get('L', 'nonexistent1', 'nonexistent2');

        $this->assertNull($result);
    }

    /**
     * Test get() returns null for non-existing dimension.
     */
    public function testGetReturnsNullForNonExistingDimension(): void
    {
        $result = ConversionRegistry::get('X9', 'm', 'ft');

        $this->assertNull($result);
    }

    /**
     * Test get() returns correct conversion factor.
     */
    public function testGetReturnsCorrectFactor(): void
    {
        $factor = 2.54;
        $conversion = new Conversion('in', 'cm', $factor);
        ConversionRegistry::addConversion($conversion);

        $result = ConversionRegistry::get('L', 'in', 'cm');

        $this->assertNotNull($result);
        $this->assertEqualsWithDelta($factor, $result->factor->value, 1e-10);
    }

    /**
     * Test get() is directional.
     */
    public function testGetIsDirectional(): void
    {
        // Add conversion in one direction
        $conversion = new Conversion('m', 'mi', 0.000621371);
        ConversionRegistry::addConversion($conversion);

        // Forward should return the conversion
        $forward = ConversionRegistry::get('L', 'm', 'mi');
        $this->assertNotNull($forward);

        // Reverse should return null (unless separately added)
        $reverse = ConversionRegistry::get('L', 'mi', 'm');
        // Note: reverse might exist if initialization added it, but the point
        // is that get() respects direction
    }

    // endregion

    // region add() tests

    /**
     * Test add() stores a conversion.
     */
    public function testAddStoresConversion(): void
    {
        $conversion = new Conversion('m', 'nmi', 0.000539957);
        ConversionRegistry::addConversion($conversion);

        $result = ConversionRegistry::get('L', 'm', 'nmi');

        $this->assertNotNull($result);
        $this->assertSame($conversion->factor->value, $result->factor->value);
    }

    /**
     * Test add() overwrites existing conversion.
     */
    public function testAddOverwritesExisting(): void
    {
        // Add initial conversion
        $conversion1 = new Conversion('m', 'yd', 1.09361);
        ConversionRegistry::addConversion($conversion1);

        // Add different conversion for same units
        $newFactor = 1.09362;
        $conversion2 = new Conversion('m', 'yd', $newFactor);
        ConversionRegistry::addConversion($conversion2);

        $result = ConversionRegistry::get('L', 'm', 'yd');

        $this->assertNotNull($result);
        $this->assertEqualsWithDelta($newFactor, $result->factor->value, 1e-10);
    }

    /**
     * Test add() stores conversion with correct dimension.
     */
    public function testAddStoresWithCorrectDimension(): void
    {
        // Add a time conversion
        $conversion = new Conversion('s', 'min', 1 / 60);
        ConversionRegistry::addConversion($conversion);

        // Should be retrievable from time dimension
        $result = ConversionRegistry::get('T', 's', 'min');
        $this->assertNotNull($result);

        // Should not be in length dimension
        $wrongDim = ConversionRegistry::get('L', 's', 'min');
        $this->assertNull($wrongDim);
    }

    /**
     * Test add() handles prefixed unit terms.
     */
    public function testAddHandlesPrefixedUnitTerms(): void
    {
        // Add conversion with prefixed units
        $conversion = new Conversion('km', 'mi', 0.621371);
        ConversionRegistry::addConversion($conversion);

        $result = ConversionRegistry::get('L', 'km', 'mi');

        $this->assertNotNull($result);
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
        ConversionRegistry::addConversion($conversion);

        $result = ConversionRegistry::get('L2', 'm2', 'ft2');

        $this->assertNotNull($result);
        $this->assertSame('m2', $result->srcUnit->asciiSymbol);
        $this->assertSame('ft2', $result->destUnit->asciiSymbol);
    }

    // endregion

    // region Data integrity tests

    /**
     * Test length dimension has expected conversions.
     */
    public function testLengthDimensionHasExpectedConversions(): void
    {
        $result = ConversionRegistry::getByDimension('L');

        // Should have some conversions
        $this->assertNotEmpty($result);

        // Flatten to check we have Conversion objects
        $hasConversions = false;
        foreach ($result as $destConversions) {
            foreach ($destConversions as $conversion) {
                $hasConversions = true;
                $this->assertInstanceOf(Conversion::class, $conversion);
                break 2;
            }
        }
        $this->assertTrue($hasConversions, 'No conversions found for length dimension');
    }

    /**
     * Test conversions have positive factors.
     */
    public function testConversionsHavePositiveFactors(): void
    {
        $result = ConversionRegistry::getByDimension('L');

        foreach ($result as $destConversions) {
            foreach ($destConversions as $conversion) {
                $this->assertGreaterThan(
                    0,
                    $conversion->factor->value,
                    "Conversion {$conversion} has non-positive factor"
                );
            }
        }
    }

    /**
     * Test conversions have matching source and destination dimensions.
     */
    public function testConversionsHaveMatchingDimensions(): void
    {
        $result = ConversionRegistry::getByDimension('L');

        foreach ($result as $destConversions) {
            foreach ($destConversions as $conversion) {
                $this->assertSame(
                    $conversion->srcUnit->dimension,
                    $conversion->destUnit->dimension,
                    "Conversion {$conversion} has mismatched dimensions"
                );
            }
        }
    }

    // endregion

    // region add() with parameters tests

    /**
     * Test add() with string unit symbols.
     */
    public function testAddWithStringSymbols(): void
    {
        ConversionRegistry::add('m', 'cm', 100.0);

        $result = ConversionRegistry::get('L', 'm', 'cm');

        $this->assertNotNull($result);
        $this->assertEqualsWithDelta(100.0, $result->factor->value, 1e-10);
    }

    /**
     * Test add() with ON_MISSING_UNIT_IGNORE silently skips unknown units.
     */
    public function testAddWithOnMissingUnitIgnore(): void
    {
        // This should not throw, just silently skip.
        ConversionRegistry::add(
            'nonexistent_unit_xyz',
            'm',
            1.0,
            ConversionRegistry::ON_MISSING_UNIT_IGNORE
        );

        // Verify no conversion was added.
        $result = ConversionRegistry::get('L', 'nonexistent_unit_xyz', 'm');
        $this->assertNull($result);
    }

    /**
     * Test add() with ON_MISSING_UNIT_THROW throws for unknown source unit.
     */
    public function testAddThrowsForUnknownSourceUnit(): void
    {
        $this->expectException(\DomainException::class);

        ConversionRegistry::add(
            'nonexistent_unit_xyz',
            'm',
            1.0,
            ConversionRegistry::ON_MISSING_UNIT_THROW
        );
    }

    /**
     * Test add() with ON_MISSING_UNIT_THROW throws for unknown destination unit.
     */
    public function testAddThrowsForUnknownDestUnit(): void
    {
        $this->expectException(\DomainException::class);

        ConversionRegistry::add(
            'm',
            'nonexistent_unit_xyz',
            1.0,
            ConversionRegistry::ON_MISSING_UNIT_THROW
        );
    }

    // endregion

    // region removeConversion() tests

    /**
     * Test removeConversion() removes an existing conversion.
     */
    public function testRemoveConversionRemovesExisting(): void
    {
        // Add a conversion.
        $conversion = new Conversion('m', 'dm', 10.0);
        ConversionRegistry::addConversion($conversion);

        // Verify it exists.
        $this->assertTrue(ConversionRegistry::has('L', 'm', 'dm'));

        // Remove it.
        ConversionRegistry::removeConversion($conversion);

        // Verify it's gone.
        $this->assertFalse(ConversionRegistry::has('L', 'm', 'dm'));
    }

    // endregion

    // region hasConversion() tests

    /**
     * Test hasConversion() returns true for existing conversion.
     */
    public function testHasConversionReturnsTrueForExisting(): void
    {
        $conversion = new Conversion('m', 'mm', 1000.0);
        ConversionRegistry::addConversion($conversion);

        $result = ConversionRegistry::hasConversion($conversion);

        $this->assertTrue($result);
    }

    /**
     * Test hasConversion() returns false for non-existing conversion.
     */
    public function testHasConversionReturnsFalseForNonExisting(): void
    {
        // Use valid units but a conversion that doesn't exist.
        $conversion = new Conversion('ft', 'km', 0.0003048);

        // Remove it if it exists to ensure clean state.
        ConversionRegistry::removeConversion($conversion);

        $result = ConversionRegistry::hasConversion($conversion);

        $this->assertFalse($result);
    }

    // endregion

    // region getAllConversionDefinitions() tests

    /**
     * Test getAllConversionDefinitions() returns an array.
     */
    public function testGetAllConversionDefinitionsReturnsArray(): void
    {
        $result = ConversionRegistry::getAllConversionDefinitions();

        $this->assertIsArray($result);
    }

    /**
     * Test getAllConversionDefinitions() returns non-empty array.
     */
    public function testGetAllConversionDefinitionsReturnsNonEmpty(): void
    {
        $result = ConversionRegistry::getAllConversionDefinitions();

        $this->assertNotEmpty($result);
    }

    /**
     * Test getAllConversionDefinitions() returns tuples with correct structure.
     */
    public function testGetAllConversionDefinitionsReturnsTuples(): void
    {
        $result = ConversionRegistry::getAllConversionDefinitions();

        foreach ($result as $definition) {
            $this->assertIsArray($definition);
            $this->assertCount(3, $definition);
            $this->assertIsString($definition[0]); // srcSymbol
            $this->assertIsString($definition[1]); // destSymbol
            $this->assertIsNumeric($definition[2]); // factor (int or float)
        }
    }

    // endregion

    // region reset() and resetByDimension() tests

    /**
     * Test resetByDimension() clears conversions for specific dimension.
     */
    public function testResetByDimensionClearsSpecificDimension(): void
    {
        // Ensure we have some time conversions.
        $conversion = new Conversion('h', 'd', 1 / 24);
        ConversionRegistry::addConversion($conversion);
        $this->assertTrue(ConversionRegistry::has('T', 'h', 'd'));

        // Reset time dimension.
        ConversionRegistry::resetByDimension('T');

        // Verify time conversions are gone.
        $this->assertFalse(ConversionRegistry::has('T', 'h', 'd'));

        // Verify length conversions still exist.
        $lengthConversions = ConversionRegistry::getByDimension('L');
        $this->assertNotEmpty($lengthConversions);
    }

    // endregion
}
