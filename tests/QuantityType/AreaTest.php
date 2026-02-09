<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\QuantityType;

use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\QuantityType\Area;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Area quantity type.
 */
#[CoversClass(Area::class)]
final class AreaTest extends TestCase
{
    use FloatAssertions;

    // region Setup

    public static function setUpBeforeClass(): void
    {
        // Load Imperial/US units for acres and square feet/yards.
        UnitRegistry::loadSystem(System::Imperial);
        UnitRegistry::loadSystem(System::UsCustomary);
    }

    // endregion

    // region Metric conversion tests

    /**
     * Test converting square metres to square kilometres.
     */
    public function testConvertSquareMetresToSquareKilometres(): void
    {
        $area = new Area(1000000, 'm2');
        $km2 = $area->to('km2');

        $this->assertInstanceOf(Area::class, $km2);
        $this->assertSame(1.0, $km2->value);
        $this->assertSame('km²', $km2->derivedUnit->unicodeSymbol);
    }

    /**
     * Test converting square kilometres to square metres.
     */
    public function testConvertSquareKilometresToSquareMetres(): void
    {
        $area = new Area(1, 'km2');
        $m2 = $area->to('m2');

        $this->assertSame(1000000.0, $m2->value);
    }

    /**
     * Test converting square metres to square centimetres.
     */
    public function testConvertSquareMetresToSquareCentimetres(): void
    {
        $area = new Area(1, 'm2');
        $cm2 = $area->to('cm2');

        $this->assertSame(10000.0, $cm2->value);
    }

    /**
     * Test converting square centimetres to square millimetres.
     */
    public function testConvertSquareCentimetresToSquareMillimetres(): void
    {
        $area = new Area(1, 'cm2');
        $mm2 = $area->to('mm2');

        $this->assertApproxEqual(100.0, $mm2->value);
    }

    /**
     * Test converting hectares to square metres.
     */
    public function testConvertHectaresToSquareMetres(): void
    {
        $area = new Area(1, 'ha');
        $m2 = $area->to('m2');

        $this->assertSame(10000.0, $m2->value);
    }

    /**
     * Test converting square metres to hectares.
     */
    public function testConvertSquareMetresToHectares(): void
    {
        $area = new Area(10000, 'm2');
        $ha = $area->to('ha');

        $this->assertSame(1.0, $ha->value);
    }

    /**
     * Test converting square kilometres to hectares.
     */
    public function testConvertSquareKilometresToHectares(): void
    {
        $area = new Area(1, 'km2');
        $ha = $area->to('ha');

        $this->assertSame(100.0, $ha->value);
    }

    // endregion

    // region Imperial/US conversion tests

    /**
     * Test converting square feet to square inches.
     */
    public function testConvertSquareFeetToSquareInches(): void
    {
        $area = new Area(1, 'ft2');
        $in2 = $area->to('in2');

        $this->assertSame(144.0, $in2->value);
    }

    /**
     * Test converting square yards to square feet.
     */
    public function testConvertSquareYardsToSquareFeet(): void
    {
        $area = new Area(1, 'yd2');
        $ft2 = $area->to('ft2');

        $this->assertSame(9.0, $ft2->value);
    }

    /**
     * Test converting acres to square yards.
     */
    public function testConvertAcresToSquareYards(): void
    {
        $area = new Area(1, 'ac');
        $yd2 = $area->to('yd2');

        $this->assertSame(4840.0, $yd2->value);
    }

    /**
     * Test converting square yards to acres.
     */
    public function testConvertSquareYardsToAcres(): void
    {
        $area = new Area(4840, 'yd2');
        $ac = $area->to('ac');

        $this->assertSame(1.0, $ac->value);
    }

    /**
     * Test converting square miles to acres.
     */
    public function testConvertSquareMilesToAcres(): void
    {
        $area = new Area(1, 'mi2');
        $ac = $area->to('ac');

        $this->assertSame(640.0, $ac->value);
    }

    // endregion

    // region Cross-system conversion tests

    /**
     * Test converting square metres to square feet.
     */
    public function testConvertSquareMetresToSquareFeet(): void
    {
        $area = new Area(1, 'm2');
        $ft2 = $area->to('ft2');

        // 1 m² = 1/(0.3048²) ft² ≈ 10.7639 ft²
        $this->assertApproxEqual(1 / (0.3048 * 0.3048), $ft2->value);
    }

    /**
     * Test converting square feet to square metres.
     */
    public function testConvertSquareFeetToSquareMetres(): void
    {
        $area = new Area(1, 'ft2');
        $m2 = $area->to('m2');

        // 1 ft² = 0.3048² m² = 0.09290304 m²
        $this->assertApproxEqual(0.3048 * 0.3048, $m2->value);
    }

    /**
     * Test converting acres to square metres.
     */
    public function testConvertAcresToSquareMetres(): void
    {
        $area = new Area(1, 'ac');
        $m2 = $area->to('m2');

        $this->assertSame(4046.8564224, $m2->value);
    }

    /**
     * Test converting hectares to acres.
     */
    public function testConvertHectaresToAcres(): void
    {
        $area = new Area(1, 'ha');
        $ac = $area->to('ac');

        // 1 ha = 10000 m² / 4046.8564224 m²/ac ≈ 2.4711 ac
        $this->assertApproxEqual(10000 / 4046.8564224, $ac->value);
    }

    /**
     * Test converting acres to hectares.
     */
    public function testConvertAcresToHectares(): void
    {
        $area = new Area(1, 'ac');
        $ha = $area->to('ha');

        // 1 ac = 4046.8564224 m² / 10000 m²/ha ≈ 0.4047 ha
        $this->assertApproxEqual(4046.8564224 / 10000, $ha->value);
    }

    // endregion

    // region Unicode symbol tests

    /**
     * Test creating area with Unicode superscript.
     */
    public function testCreateWithUnicodeSuperscript(): void
    {
        $area = new Area(100, 'm²');

        $this->assertSame(100.0, $area->value);
        $this->assertSame('m²', $area->derivedUnit->unicodeSymbol);
    }

    /**
     * Test converting with Unicode symbols.
     */
    public function testConvertWithUnicodeSymbols(): void
    {
        $area = new Area(1, 'km²');
        $m2 = $area->to('m²');

        $this->assertSame(1000000.0, $m2->value);
    }

    // endregion

    // region Static convert method tests

    /**
     * Test static convert method.
     */
    public function testStaticConvertMethod(): void
    {
        $value = Area::convert(1, 'ha', 'm2');

        $this->assertSame(10000.0, $value);
    }

    /**
     * Test static convert cross-system.
     */
    public function testStaticConvertCrossSystem(): void
    {
        $value = Area::convert(1, 'ac', 'm2');

        $this->assertSame(4046.8564224, $value);
    }

    // endregion

    // region Parse tests

    /**
     * Test parsing square metres.
     */
    public function testParseSquareMetres(): void
    {
        $area = Area::parse('100 m2');

        $this->assertInstanceOf(Area::class, $area);
        $this->assertSame(100.0, $area->value);
    }

    /**
     * Test parsing with Unicode superscript.
     */
    public function testParseUnicodeSuperscript(): void
    {
        $area = Area::parse('50 m²');

        $this->assertSame(50.0, $area->value);
    }

    /**
     * Test parsing hectares.
     */
    public function testParseHectares(): void
    {
        $area = Area::parse('2.5 ha');

        $this->assertSame(2.5, $area->value);
        $this->assertSame('ha', $area->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing acres.
     */
    public function testParseAcres(): void
    {
        $area = Area::parse('10 ac');

        $this->assertSame(10.0, $area->value);
        $this->assertSame('ac', $area->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Zero and negative value tests

    /**
     * Test converting zero value.
     */
    public function testConvertZeroValue(): void
    {
        $area = new Area(0, 'm2');
        $km2 = $area->to('km2');

        $this->assertSame(0.0, $km2->value);
    }

    /**
     * Test converting negative value.
     */
    public function testConvertNegativeValue(): void
    {
        $area = new Area(-100, 'm2');
        $ha = $area->to('ha');

        $this->assertSame(-0.01, $ha->value);
    }

    // endregion

    // region Multiplication tests (Length × Length = Area)

    /**
     * Test multiplying metres by metres.
     */
    public function testMulMetresByMetres(): void
    {
        $a = new Length(5, 'm');
        $b = new Length(4, 'm');
        $result = $a->mul($b);

        // 5 m × 4 m = 20 m²
        $this->assertInstanceOf(Area::class, $result);
        $this->assertSame(20.0, $result->value);
        $this->assertSame('m²', $result->derivedUnit->unicodeSymbol);
    }

    /**
     * Test multiplying kilometres by kilometres.
     */
    public function testMulKilometresByKilometres(): void
    {
        $a = new Length(3, 'km');
        $b = new Length(2, 'km');
        $result = $a->mul($b);

        // 3 km × 2 km = 6 km²
        $this->assertInstanceOf(Area::class, $result);
        $this->assertSame(6.0, $result->value);
        $this->assertSame('km²', $result->derivedUnit->unicodeSymbol);
    }

    /**
     * Test multiplying feet by feet.
     */
    public function testMulFeetByFeet(): void
    {
        $a = new Length(12, 'ft');
        $b = new Length(10, 'ft');
        $result = $a->mul($b);

        // 12 ft × 10 ft = 120 ft²
        $this->assertInstanceOf(Area::class, $result);
        $this->assertSame(120.0, $result->value);
        $this->assertSame('ft²', $result->derivedUnit->unicodeSymbol);
    }

    /**
     * Test multiplying metres by kilometres (mixed metric).
     */
    public function testMulMetresByKilometres(): void
    {
        $a = new Length(1000, 'm');
        $b = new Length(2, 'km');
        $result = $a->mul($b);

        // 1000 m × 2 km = 1000 m × 2000 m = 2,000,000 m²
        // (km converted to m to match first operand's unit)
        $this->assertInstanceOf(Area::class, $result);
        $this->assertSame(2000000.0, $result->value);
        $this->assertSame('m²', $result->derivedUnit->unicodeSymbol);
    }

    /**
     * Test multiplying feet by yards (mixed imperial).
     */
    public function testMulFeetByYards(): void
    {
        $a = new Length(9, 'ft');
        $b = new Length(2, 'yd');
        $result = $a->mul($b);

        // 9 ft × 2 yd = 9 ft × 6 ft = 54 ft²
        // (yd converted to ft to match first operand's unit)
        $this->assertInstanceOf(Area::class, $result);
        $this->assertSame(54.0, $result->value);
        $this->assertSame('ft²', $result->derivedUnit->unicodeSymbol);
    }

    /**
     * Test multiplying metres by feet (cross-system).
     */
    public function testMulMetresByFeet(): void
    {
        $a = new Length(10, 'm');
        $b = new Length(1, 'ft');
        $result = $a->mul($b);

        // 10 m × 1 ft = 10 m × 0.3048 m = 3.048 m²
        // (ft converted to m to match first operand's unit)
        $this->assertInstanceOf(Area::class, $result);
        $this->assertSame(3.048, $result->value);
        $this->assertSame('m²', $result->derivedUnit->unicodeSymbol);
    }

    /**
     * Test multiplying centimetres by centimetres.
     */
    public function testMulCentimetresByCentimetres(): void
    {
        $a = new Length(100, 'cm');
        $b = new Length(100, 'cm');
        $result = $a->mul($b);

        // 100 cm × 100 cm = 10000 cm²
        $this->assertInstanceOf(Area::class, $result);
        $this->assertSame(10000.0, $result->value);
        $this->assertSame('cm²', $result->derivedUnit->unicodeSymbol);

        // Convert to m²: should be 1 m²
        $m2 = $result->to('m2');
        $this->assertInstanceOf(Area::class, $m2);
        $this->assertSame(1.0, $m2->value);
    }

    // endregion

    // region Addition tests

    /**
     * Test adding hectares to hectares.
     */
    public function testAddHectaresToHectares(): void
    {
        $a = new Area(100, 'ha');
        $b = new Area(50, 'ha');
        $result = $a->add($b);

        $this->assertInstanceOf(Area::class, $result);
        $this->assertSame(150.0, $result->value);
        $this->assertSame('ha', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding hectares to square kilometres.
     */
    public function testAddHectaresToSquareKilometres(): void
    {
        $a = new Area(1, 'km2');
        $b = new Area(50, 'ha');
        $result = $a->add($b);

        // 1 km² + 50 ha = 1 km² + 0.5 km² = 1.5 km²
        $this->assertInstanceOf(Area::class, $result);
        $this->assertSame(1.5, $result->value);
        $this->assertSame('km²', $result->derivedUnit->unicodeSymbol);
    }

    /**
     * Test adding acres to acres.
     */
    public function testAddAcresToAcres(): void
    {
        $a = new Area(100, 'ac');
        $b = new Area(60, 'ac');
        $result = $a->add($b);

        $this->assertInstanceOf(Area::class, $result);
        $this->assertSame(160.0, $result->value);
        $this->assertSame('ac', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding acres to square miles.
     */
    public function testAddAcresToSquareMiles(): void
    {
        $a = new Area(1, 'mi2');
        $b = new Area(320, 'ac');
        $result = $a->add($b);

        // 1 mi² + 320 ac = 1 mi² + 0.5 mi² = 1.5 mi²
        $this->assertInstanceOf(Area::class, $result);
        $this->assertSame(1.5, $result->value);
        $this->assertSame('mi²', $result->derivedUnit->unicodeSymbol);
    }

    /**
     * Test adding acres to hectares (cross-system).
     */
    public function testAddAcresToHectares(): void
    {
        $a = new Area(1, 'ha');
        $b = new Area(1, 'ac');
        $result = $a->add($b);

        // 1 ha + 1 ac = 1 ha + 0.40468564224 ha = 1.40468564224 ha
        // (1 ac = 4046.8564224 m² = 0.40468564224 ha)
        $this->assertInstanceOf(Area::class, $result);
        $this->assertApproxEqual(1.40468564224, $result->value);
        $this->assertSame('ha', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding square miles to square kilometres (cross-system).
     */
    public function testAddSquareMilesToSquareKilometres(): void
    {
        $a = new Area(1, 'km2');
        $b = new Area(1, 'mi2');
        $result = $a->add($b);

        // 1 km² + 1 mi² = 1 km² + 2.589988110336 km² = 3.589988110336 km²
        // (1 mi = 1609.344 m, so 1 mi² = 1609.344² m² = 2589988.110336 m² = 2.589988110336 km²)
        $this->assertInstanceOf(Area::class, $result);
        $this->assertApproxEqual(3.589988110336, $result->value);
        $this->assertSame('km²', $result->derivedUnit->unicodeSymbol);
    }

    /**
     * Test adding hectares to square miles.
     */
    public function testAddHectaresToSquareMiles(): void
    {
        $a = new Area(1, 'mi2');
        $b = new Area(100, 'ha');
        $result = $a->add($b);

        // 1 mi² + 100 ha = 1 mi² + (100 * 10000 / 2589988.110336) mi²
        // = 1 mi² + 0.386102158542... mi² ≈ 1.386102158542 mi²
        $this->assertInstanceOf(Area::class, $result);
        $this->assertApproxEqual(1 + 1000000 / 2589988.110336, $result->value);
        $this->assertSame('mi²', $result->derivedUnit->unicodeSymbol);
    }

    // endregion
}
