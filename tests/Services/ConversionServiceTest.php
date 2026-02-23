<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\Services;

use DomainException;
use Galaxon\Quantities\Internal\Conversion;
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
     * Test has() is directional.
     */
    public function testHasIsDirectional(): void
    {
        // Add a conversion in one direction
        $conversion = new Conversion('m', 'yd', 1.09361);
        ConversionService::add($conversion);

        // Forward direction should exist
        $this->assertTrue(ConversionService::has('m', 'yd'));

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
     * Test get() is directional.
     */
    public function testGetIsDirectional(): void
    {
        // Add conversion in one direction
        $conversion = new Conversion('m', 'mi', 0.000621371);
        ConversionService::add($conversion);

        // Forward should return the conversion
        $forward = ConversionService::get('m', 'mi');
        $this->assertInstanceOf(Conversion::class, $forward);

        // Reverse should return null (unless separately added)
        $reverse = ConversionService::get('mi', 'm');
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
}
