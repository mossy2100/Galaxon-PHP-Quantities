<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\QuantityType;

use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\QuantityType\Length;
use Galaxon\Quantities\Registry\UnitRegistry;
use Galaxon\Quantities\System;
use Galaxon\Quantities\Tests\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Length quantity type.
 */
#[CoversClass(Length::class)]
final class LengthTest extends TestCase
{
    use ArrayShapeTrait;
    use FloatAssertions;

    // region Setup

    public static function setUpBeforeClass(): void
    {
        // Load additional unit systems.
        UnitRegistry::loadSystem(System::Imperial);
        UnitRegistry::loadSystem(System::UsCustomary);
        UnitRegistry::loadSystem(System::Astronomical);
        UnitRegistry::loadSystem(System::Typographical);
        UnitRegistry::loadSystem(System::Nautical);
    }

    // endregion

    // region Overridden methods

    /**
     * Test getUnitDefinitions() returns valid unit definitions.
     */
    public function testGetUnitDefinitionsReturnsValidArray(): void
    {
        $units = Length::getUnitDefinitions();

        $this->assertValidUnitDefinitionsShape($units);
    }

    /**
     * Test getConversionDefinitions() returns valid conversion definitions.
     */
    public function testGetConversionDefinitionsReturnsValidArray(): void
    {
        $conversions = Length::getConversionDefinitions();

        $this->assertValidConversionDefinitionsShape($conversions);
    }

    // endregion

    // region Metric conversion tests

    /**
     * Test converting metres to kilometres.
     */
    public function testConvertMetresToKilometres(): void
    {
        $length = new Length(1000, 'm');
        $km = $length->to('km');

        $this->assertInstanceOf(Length::class, $km);
        $this->assertSame(1.0, $km->value);
        $this->assertSame('km', $km->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting kilometres to metres.
     */
    public function testConvertKilometresToMetres(): void
    {
        $length = new Length(1, 'km');
        $m = $length->to('m');

        $this->assertSame(1000.0, $m->value);
    }

    /**
     * Test converting metres to centimetres.
     */
    public function testConvertMetresToCentimetres(): void
    {
        $length = new Length(1, 'm');
        $cm = $length->to('cm');

        $this->assertSame(100.0, $cm->value);
    }

    /**
     * Test converting centimetres to millimetres.
     */
    public function testConvertCentimetresToMillimetres(): void
    {
        $length = new Length(1, 'cm');
        $mm = $length->to('mm');

        $this->assertSame(10.0, $mm->value);
    }

    /**
     * Test converting metres to micrometres.
     */
    public function testConvertMetresToMicrometres(): void
    {
        $length = new Length(1, 'm');
        $um = $length->to('μm');

        $this->assertSame(1000000.0, $um->value);
    }

    /**
     * Test converting metres to nanometres.
     */
    public function testConvertMetresToNanometres(): void
    {
        $length = new Length(1, 'm');
        $nm = $length->to('nm');

        $this->assertApproxEqual(1000000000.0, $nm->value);
    }

    // endregion

    // region Imperial/US conversion tests

    /**
     * Test converting feet to inches.
     */
    public function testConvertFeetToInches(): void
    {
        $length = new Length(1, 'ft');
        $in = $length->to('in');

        $this->assertSame(12.0, $in->value);
    }

    /**
     * Test converting yards to feet.
     */
    public function testConvertYardsToFeet(): void
    {
        $length = new Length(1, 'yd');
        $ft = $length->to('ft');

        $this->assertSame(3.0, $ft->value);
    }

    /**
     * Test converting miles to yards.
     */
    public function testConvertMilesToYards(): void
    {
        $length = new Length(1, 'mi');
        $yd = $length->to('yd');

        $this->assertSame(1760.0, $yd->value);
    }

    // endregion

    // region Cross-system conversion tests (metric to imperial)

    /**
     * Test converting metres to feet.
     */
    public function testConvertMetresToFeet(): void
    {
        $length = new Length(1, 'm');
        $ft = $length->to('ft');

        // 1 metre = 1/0.3048 feet
        $this->assertApproxEqual(1 / 0.3048, $ft->value);
    }

    /**
     * Test converting feet to metres.
     */
    public function testConvertFeetToMetres(): void
    {
        $length = new Length(1, 'ft');
        $m = $length->to('m');

        // 1 foot = 0.3048 metres (exactly)
        $this->assertSame(0.3048, $m->value);
    }

    /**
     * Test converting inches to centimetres.
     */
    public function testConvertInchesToCentimetres(): void
    {
        $length = new Length(1, 'in');
        $cm = $length->to('cm');

        // 1 inch = 2.54 cm (exactly)
        $this->assertSame(2.54, $cm->value);
    }

    /**
     * Test converting centimetres to inches.
     */
    public function testConvertCentimetresToInches(): void
    {
        $length = new Length(2.54, 'cm');
        $in = $length->to('in');

        $this->assertApproxEqual(1.0, $in->value);
    }

    /**
     * Test converting miles to kilometres.
     */
    public function testConvertMilesToKilometres(): void
    {
        $length = new Length(1, 'mi');
        $km = $length->to('km');

        // 1 mile = 1.609344 km (exactly)
        $this->assertSame(1.609344, $km->value);
    }

    /**
     * Test converting kilometres to miles.
     */
    public function testConvertKilometresToMiles(): void
    {
        $length = new Length(1.609344, 'km');
        $mi = $length->to('mi');

        $this->assertApproxEqual(1.0, $mi->value);
    }

    /**
     * Test converting yards to metres.
     */
    public function testConvertYardsToMetres(): void
    {
        $length = new Length(1, 'yd');
        $m = $length->to('m');

        // 1 yard = 0.9144 metres (exactly)
        $this->assertSame(0.9144, $m->value);
    }

    // endregion

    // region Astronomical unit tests

    /**
     * Test converting astronomical units to metres.
     */
    public function testConvertAuToMetres(): void
    {
        $length = new Length(1, 'au');
        $m = $length->to('m');

        $this->assertSame(149597870700.0, $m->value);
    }

    /**
     * Test converting light years to metres.
     */
    public function testConvertLightYearsToMetres(): void
    {
        $length = new Length(1, 'ly');
        $m = $length->to('m');

        $this->assertSame(9460730472580800.0, $m->value);
    }

    /**
     * Test converting parsecs to astronomical units.
     */
    public function testConvertParsecsToAu(): void
    {
        $length = new Length(1, 'pc');
        $au = $length->to('au');

        // 1 parsec = 648000/π AU
        $this->assertApproxEqual(648000 / M_PI, $au->value);
    }

    /**
     * Test converting kiloparsecs to parsecs.
     */
    public function testConvertKiloparsecsToParsecs(): void
    {
        $length = new Length(1, 'kpc');
        $pc = $length->to('pc');

        $this->assertSame(1000.0, $pc->value);
    }

    /**
     * Test converting megaparsecs to parsecs.
     */
    public function testConvertMegaparsecsToParsecs(): void
    {
        $length = new Length(1, 'Mpc');
        $pc = $length->to('pc');

        $this->assertSame(1000000.0, $pc->value);
    }

    /**
     * Test converting parsecs to kiloparsecs.
     */
    public function testConvertParsecsToKiloparsecs(): void
    {
        $length = new Length(1000, 'pc');
        $kpc = $length->to('kpc');

        $this->assertSame(1.0, $kpc->value);
    }

    /**
     * Test converting AU to kiloparsecs.
     */
    public function testConvertAstronomicalUnitsToKiloparsecs(): void
    {
        $length = new Length(1e9, 'au');
        $kpc = $length->to('kpc');

        $this->assertApproxEqual(4.84813678202, $kpc->value, 1e-8);
    }

    /**
     * Test converting light years to megaparsecs.
     */
    public function testConvertLightYearsToMegaparsecs(): void
    {
        $length = new Length(1e8, 'ly');
        $mpc = $length->to('Mpc');

        $this->assertApproxEqual(30.660139393, $mpc->value, 1e-8);
    }

    /**
     * Test converting kiloparsecs to light years.
     */
    public function testConvertKiloparsecsToLightYears(): void
    {
        $length = new Length(42, 'kpc');
        $ly = $length->to('ly');

        $this->assertApproxEqual(136986, $ly->value, 1e-5);
    }

    // endregion

    // region Typography unit tests

    /**
     * Test converting inches to pixels.
     */
    public function testConvertInchesToPixels(): void
    {
        $length = new Length(1, 'in');
        $px = $length->to('px');

        $this->assertSame(96.0, $px->value);
    }

    /**
     * Test converting inches to points.
     */
    public function testConvertInchesToPoints(): void
    {
        $length = new Length(1, 'in');
        $pt = $length->to('p');

        $this->assertSame(72.0, $pt->value);
    }

    /**
     * Test converting inches to picas.
     */
    public function testConvertInchesToPicas(): void
    {
        $length = new Length(1, 'in');
        $pica = $length->to('P');

        $this->assertSame(6.0, $pica->value);
    }

    /**
     * Test converting points to picas.
     */
    public function testConvertPointsToPicas(): void
    {
        $length = new Length(12, 'p');
        $pica = $length->to('P');

        $this->assertSame(1.0, $pica->value);
    }

    // endregion

    // region Nautical unit tests

    /**
     * Test converting nautical miles to metres.
     */
    public function testConvertNauticalMilesToMetres(): void
    {
        $length = new Length(1, 'nmi');
        $m = $length->to('m');

        $this->assertSame(1852.0, $m->value);
    }

    /**
     * Test converting fathoms to yards.
     */
    public function testConvertFathomsToYards(): void
    {
        $length = new Length(1, 'ftm');
        $yd = $length->to('yd');

        $this->assertSame(2.0, $yd->value);
    }

    /**
     * Test converting fathoms to feet.
     */
    public function testConvertFathomsToFeet(): void
    {
        $length = new Length(1, 'ftm');
        $ft = $length->to('ft');

        $this->assertSame(6.0, $ft->value);
    }

    // endregion

    // region Static convert method tests

    /**
     * Test static convert method.
     */
    public function testStaticConvertMethod(): void
    {
        $value = Length::convert(1000, 'm', 'km');

        $this->assertSame(1.0, $value);
    }

    /**
     * Test static convert cross-system.
     */
    public function testStaticConvertCrossSystem(): void
    {
        $value = Length::convert(1, 'in', 'cm');

        $this->assertSame(2.54, $value);
    }

    // endregion

    // region Parse tests

    /**
     * Test parsing metres.
     */
    public function testParseMetres(): void
    {
        $length = Length::parse('100 m');

        $this->assertInstanceOf(Length::class, $length);
        $this->assertSame(100.0, $length->value);
        $this->assertSame('m', $length->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing kilometres.
     */
    public function testParseKilometres(): void
    {
        $length = Length::parse('5.5 km');

        $this->assertSame(5.5, $length->value);
        $this->assertSame('km', $length->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing feet.
     */
    public function testParseFeet(): void
    {
        $length = Length::parse('6 ft');

        $this->assertSame(6.0, $length->value);
        $this->assertSame('ft', $length->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing without space.
     */
    public function testParseWithoutSpace(): void
    {
        $length = Length::parse('100m');

        $this->assertSame(100.0, $length->value);
    }

    /**
     * Test parsing scientific notation.
     */
    public function testParseScientificNotation(): void
    {
        $length = Length::parse('1.5e3 m');

        $this->assertSame(1500.0, $length->value);
    }

    // endregion

    // region Chained conversion tests

    /**
     * Test chained conversions.
     */
    public function testChainedConversions(): void
    {
        $length = new Length(1, 'km');

        // km -> m -> cm -> mm
        $mm = $length->to('m')->to('cm')->to('mm');

        $this->assertSame(1000000.0, $mm->value);
    }

    // endregion

    // region Same unit conversion tests

    /**
     * Test converting to same unit.
     */
    public function testConvertToSameUnit(): void
    {
        $length = new Length(5, 'm');
        $same = $length->to('m');

        $this->assertSame(5.0, $same->value);
        $this->assertSame('m', $same->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Parts methods tests

    /**
     * Test getPartsConfig returns correct structure.
     */
    public function testGetPartsConfig(): void
    {
        $config = Length::getPartsConfig();

        $this->assertArrayHasKey('from', $config);
        $this->assertArrayHasKey('to', $config);
        $this->assertSame('ft', $config['from']);
        $this->assertSame(['mi', 'yd', 'ft', 'in'], $config['to']);
    }

    /**
     * Test fromParts with feet and inches.
     */
    public function testFromPartsFeetInches(): void
    {
        $length = Length::fromParts([
            'ft' => 5,
            'in' => 6,
        ]);

        // 5 feet + 6 inches = 5.5 feet
        $this->assertSame(5.5, $length->value);
        $this->assertSame('ft', $length->derivedUnit->asciiSymbol);
    }

    /**
     * Test fromParts with miles and yards.
     */
    public function testFromPartsMilesYards(): void
    {
        $length = Length::fromParts([
            'mi' => 1,
            'yd' => 880,
        ]);

        // 1 mile + 880 yards = 1.5 miles
        $this->assertSame(7920.0, $length->value);  // in feet (default)
        $this->assertSame('ft', $length->derivedUnit->asciiSymbol);
    }

    /**
     * Test fromParts with negative sign.
     */
    public function testFromPartsNegative(): void
    {
        $length = Length::fromParts([
            'ft'   => 6,
            'sign' => -1,
        ]);

        $this->assertSame(-6.0, $length->value);
    }

    /**
     * Test fromParts with negative value uses sign key instead.
     */
    public function testFromPartsNegativeValueUsesSign(): void
    {
        // Negative values in parts are handled via the 'sign' key
        $length = Length::fromParts([
            'ft'   => 5,
            'sign' => -1,
        ]);

        $this->assertSame(-5.0, $length->value);
    }

    /**
     * Test fromParts uses default result unit from config.
     */
    public function testFromPartsDefaultResultUnit(): void
    {
        $length = Length::fromParts([
            'yd' => 1,
        ]);

        // Default result unit for Length is 'ft'
        $this->assertSame('ft', $length->derivedUnit->asciiSymbol);
    }

    /**
     * Test fromParts with custom result unit.
     */
    public function testFromPartsCustomResultUnit(): void
    {
        $length = Length::fromParts([
            'ft' => 3,
        ], 'yd');

        // 3 feet = 1 yard
        $this->assertSame(1.0, $length->value);
        $this->assertSame('yd', $length->derivedUnit->asciiSymbol);
    }

    /**
     * Test toParts decomposes length correctly.
     */
    public function testToParts(): void
    {
        // 5.5 feet = 1 yard + 2 feet + 6 inches (since 3 ft = 1 yd)
        $length = new Length(5.5, 'ft');
        $parts = $length->toParts(null, 'in', 0);

        $this->assertSame(1, $parts['sign']);
        $this->assertSame(0, $parts['mi']);
        $this->assertSame(1, $parts['yd']);
        $this->assertSame(2, $parts['ft']);
        $this->assertSame(6.0, $parts['in']);
    }

    /**
     * Test toParts with negative value.
     */
    public function testToPartsNegative(): void
    {
        // -5.5 feet = -(1 yard + 2 feet + 6 inches)
        $length = new Length(-5.5, 'ft');
        $parts = $length->toParts(null, 'in', 0);

        $this->assertSame(-1, $parts['sign']);
        $this->assertSame(1, $parts['yd']);
        $this->assertSame(2, $parts['ft']);
        $this->assertSame(6.0, $parts['in']);
    }

    /**
     * Test toParts with precision causes carry.
     */
    public function testToPartsCarry(): void
    {
        $length = new Length(2.99999999, 'ft');  // Just under 3 feet = 1 yard
        $parts = $length->toParts(null, 'ft', 0);

        // Should round to 3 feet and carry to 1 yard
        $this->assertSame(1, $parts['yd']);
        $this->assertSame(0.0, $parts['ft']);
    }

    /**
     * Test formatParts with feet and inches.
     */
    public function testFormatPartsFeetInches(): void
    {
        // 5.5 feet = 1 yard 2 feet 6 inches
        $length = new Length(5.5, 'ft');
        $result = $length->formatParts(null, 'in', 0);

        $this->assertSame('1yd 2ft 6in', $result);
    }

    /**
     * Test formatParts with yards, feet, and inches.
     */
    public function testFormatPartsYardsFeetInches(): void
    {
        // 4.5 feet = 1 yard 1 foot 6 inches
        $length = new Length(4.5, 'ft');
        $result = $length->formatParts(null, 'in', 0);

        $this->assertSame('1yd 1ft 6in', $result);
    }

    /**
     * Test formatParts with precision.
     */
    public function testFormatPartsWithPrecision(): void
    {
        // 5.541666... feet = 1 yard 2 feet 6.5 inches
        $length = new Length(5.541666666666667, 'ft');
        $result = $length->formatParts(null, 'in', 1);

        $this->assertSame('1yd 2ft 6.5in', $result);
    }

    /**
     * Test formatParts for negative length.
     */
    public function testFormatPartsNegative(): void
    {
        // -5.5 feet = -(1 yard 2 feet 6 inches)
        $length = new Length(-5.5, 'ft');
        $result = $length->formatParts(null, 'in', 0);

        $this->assertSame('-1yd 2ft 6in', $result);
    }

    /**
     * Test formatParts showZeros option.
     */
    public function testFormatPartsShowZeros(): void
    {
        $length = new Length(5280, 'ft');  // 1 mile
        $result = $length->formatParts(null, 'ft', 0, true);

        // Shows all parts including zeros
        $this->assertSame('1mi 0yd 0ft', $result);
    }

    /**
     * Test parts round-trip conversion.
     */
    public function testPartsRoundTrip(): void
    {
        $length = Length::fromParts([
            'yd' => 1,
            'ft' => 2,
            'in' => 6,
        ]);
        $formatted = $length->formatParts(null, 'in', 0);

        $this->assertSame('1yd 2ft 6in', $formatted);
    }

    // endregion

    // region Zero and negative value tests

    /**
     * Test converting zero value.
     */
    public function testConvertZeroValue(): void
    {
        $length = new Length(0, 'm');
        $km = $length->to('km');

        $this->assertSame(0.0, $km->value);
    }

    /**
     * Test converting negative value.
     */
    public function testConvertNegativeValue(): void
    {
        $length = new Length(-100, 'm');
        $km = $length->to('km');

        $this->assertSame(-0.1, $km->value);
    }

    // endregion

    // region Arithmetic (add) tests

    /**
     * Test adding SI units (metres + kilometres).
     */
    public function testAddMetresAndKilometres(): void
    {
        $a = new Length(500, 'm');
        $b = new Length(1, 'km');
        $result = $a->add($b);

        $this->assertInstanceOf(Length::class, $result);
        $this->assertSame(1500.0, $result->value);
        $this->assertSame('m', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding SI units (kilometres + metres).
     */
    public function testAddKilometresAndMetres(): void
    {
        $a = new Length(1, 'km');
        $b = new Length(500, 'm');
        $result = $a->add($b);

        $this->assertSame(1.5, $result->value);
        $this->assertSame('km', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding SI units (centimetres + metres).
     */
    public function testAddCentimetresAndMetres(): void
    {
        $a = new Length(50, 'cm');
        $b = new Length(1, 'm');
        $result = $a->add($b);

        $this->assertSame(150.0, $result->value);
        $this->assertSame('cm', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding Imperial units (feet + inches).
     */
    public function testAddFeetAndInches(): void
    {
        $a = new Length(2, 'ft');
        $b = new Length(6, 'in');
        $result = $a->add($b);

        $this->assertSame(2.5, $result->value);
        $this->assertSame('ft', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding Imperial units (yards + feet).
     */
    public function testAddYardsAndFeet(): void
    {
        $a = new Length(1, 'yd');
        $b = new Length(3, 'ft');
        $result = $a->add($b);

        $this->assertSame(2.0, $result->value);
        $this->assertSame('yd', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding Imperial units (miles + yards).
     */
    public function testAddMilesAndYards(): void
    {
        $a = new Length(1, 'mi');
        $b = new Length(1760, 'yd');
        $result = $a->add($b);

        $this->assertSame(2.0, $result->value);
        $this->assertSame('mi', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding mixed units (metres + feet).
     */
    public function testAddMetresAndFeet(): void
    {
        $a = new Length(1, 'm');
        $b = new Length(1, 'ft');
        $result = $a->add($b);

        // 1 m + 0.3048 m = 1.3048 m
        $this->assertSame(1.3048, $result->value);
        $this->assertSame('m', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding mixed units (feet + metres).
     */
    public function testAddFeetAndMetres(): void
    {
        $a = new Length(1, 'ft');
        $b = new Length(1, 'm');
        $result = $a->add($b);

        // 1 ft + (1/0.3048) ft ≈ 4.28 ft
        $this->assertApproxEqual(1 + 1 / 0.3048, $result->value);
        $this->assertSame('ft', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding mixed units (kilometres + miles).
     */
    public function testAddKilometresAndMiles(): void
    {
        $a = new Length(1, 'km');
        $b = new Length(1, 'mi');
        $result = $a->add($b);

        // 1 km + 1.609344 km = 2.609344 km
        $this->assertSame(2.609344, $result->value);
        $this->assertSame('km', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding mixed units (inches + centimetres).
     */
    public function testAddInchesAndCentimetres(): void
    {
        $a = new Length(1, 'in');
        $b = new Length(2.54, 'cm');
        $result = $a->add($b);

        // 1 in + 1 in = 2 in
        $this->assertApproxEqual(2.0, $result->value);
        $this->assertSame('in', $result->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Arithmetic (mul) tests

    /**
     * Test multiplying metres by a scalar.
     */
    public function testMulMetresByScalar(): void
    {
        $length = new Length(5, 'm');
        $result = $length->mul(3);

        $this->assertInstanceOf(Length::class, $result);
        $this->assertSame(15.0, $result->value);
        $this->assertSame('m', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test multiplying kilometres by a scalar.
     */
    public function testMulKilometresByScalar(): void
    {
        $length = new Length(2.5, 'km');
        $result = $length->mul(4);

        $this->assertSame(10.0, $result->value);
        $this->assertSame('km', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test multiplying feet by a scalar.
     */
    public function testMulFeetByScalar(): void
    {
        $length = new Length(6, 'ft');
        $result = $length->mul(2);

        $this->assertSame(12.0, $result->value);
        $this->assertSame('ft', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test multiplying by zero.
     */
    public function testMulByZero(): void
    {
        $length = new Length(100, 'm');
        $result = $length->mul(0);

        $this->assertSame(0.0, $result->value);
    }

    /**
     * Test multiplying by a fractional scalar.
     */
    public function testMulByFraction(): void
    {
        $length = new Length(10, 'm');
        $result = $length->mul(0.5);

        $this->assertSame(5.0, $result->value);
    }

    /**
     * Test multiplying by a negative scalar.
     */
    public function testMulByNegative(): void
    {
        $length = new Length(5, 'm');
        $result = $length->mul(-2);

        $this->assertSame(-10.0, $result->value);
    }

    // endregion

    // region Arithmetic (div) tests

    /**
     * Test dividing metres by a scalar.
     */
    public function testDivMetresByScalar(): void
    {
        $length = new Length(15, 'm');
        $result = $length->div(3);

        $this->assertInstanceOf(Length::class, $result);
        $this->assertSame(5.0, $result->value);
        $this->assertSame('m', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test dividing kilometres by a scalar.
     */
    public function testDivKilometresByScalar(): void
    {
        $length = new Length(10, 'km');
        $result = $length->div(4);

        $this->assertSame(2.5, $result->value);
        $this->assertSame('km', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test dividing feet by a scalar.
     */
    public function testDivFeetByScalar(): void
    {
        $length = new Length(12, 'ft');
        $result = $length->div(3);

        $this->assertSame(4.0, $result->value);
        $this->assertSame('ft', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test dividing by a fractional scalar.
     */
    public function testDivByFraction(): void
    {
        $length = new Length(10, 'm');
        $result = $length->div(0.5);

        $this->assertSame(20.0, $result->value);
    }

    /**
     * Test dividing by a negative scalar.
     */
    public function testDivByNegative(): void
    {
        $length = new Length(10, 'm');
        $result = $length->div(-2);

        $this->assertSame(-5.0, $result->value);
    }

    /**
     * Test dividing results in non-integer value.
     */
    public function testDivNonInteger(): void
    {
        $length = new Length(10, 'm');
        $result = $length->div(3);

        $this->assertApproxEqual(10.0 / 3.0, $result->value);
    }

    // endregion
}
