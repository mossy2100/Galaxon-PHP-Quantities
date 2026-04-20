<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\NonCurrencies\QuantityType;

use Galaxon\Core\Traits\Asserts\FloatAssertions;
use Galaxon\Quantities\QuantityType\Mass;
use Galaxon\Quantities\Tests\NonCurrencies\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Mass quantity type.
 */
#[CoversClass(Mass::class)]
final class MassTest extends TestCase
{
    use ArrayShapeTrait;
    use FloatAssertions;

    // region Overridden methods

    /**
     * Test getUnitDefinitions() returns valid unit definitions.
     */
    public function testGetUnitDefinitionsReturnsValidArray(): void
    {
        $units = Mass::getUnitDefinitions();

        $this->assertValidUnitDefinitionsShape($units);
    }

    /**
     * Test getConversionDefinitions() returns valid conversion definitions.
     */
    public function testGetConversionDefinitionsReturnsValidArray(): void
    {
        $conversions = Mass::getConversionDefinitions();

        $this->assertValidConversionDefinitionsShape($conversions);
    }

    // endregion

    // region Metric conversion tests

    /**
     * Test converting kilograms to grams.
     */
    public function testConvertKilogramsToGrams(): void
    {
        $mass = new Mass(1, 'kg');
        $g = $mass->to('g');

        $this->assertInstanceOf(Mass::class, $g);
        $this->assertSame(1000.0, $g->value);
        $this->assertSame('g', $g->compoundUnit->asciiSymbol);
    }

    /**
     * Test converting grams to kilograms.
     */
    public function testConvertGramsToKilograms(): void
    {
        $mass = new Mass(1000, 'g');
        $kg = $mass->to('kg');

        $this->assertSame(1.0, $kg->value);
    }

    /**
     * Test converting grams to milligrams.
     */
    public function testConvertGramsToMilligrams(): void
    {
        $mass = new Mass(1, 'g');
        $mg = $mass->to('mg');

        $this->assertSame(1000.0, $mg->value);
    }

    /**
     * Test converting milligrams to grams.
     */
    public function testConvertMilligramsToGrams(): void
    {
        $mass = new Mass(500, 'mg');
        $g = $mass->to('g');

        $this->assertSame(0.5, $g->value);
    }

    /**
     * Test converting kilograms to milligrams.
     */
    public function testConvertKilogramsToMilligrams(): void
    {
        $mass = new Mass(1, 'kg');
        $mg = $mass->to('mg');

        $this->assertSame(1000000.0, $mg->value);
    }

    /**
     * Test converting tonnes to kilograms.
     */
    public function testConvertTonnesToKilograms(): void
    {
        $mass = new Mass(1, 't');
        $kg = $mass->to('kg');

        $this->assertSame(1000.0, $kg->value);
    }

    /**
     * Test converting kilograms to tonnes.
     */
    public function testConvertKilogramsToTonnes(): void
    {
        $mass = new Mass(2500, 'kg');
        $t = $mass->to('t');

        $this->assertSame(2.5, $t->value);
    }

    /**
     * Test converting micrograms to grams.
     */
    public function testConvertMicrogramsToGrams(): void
    {
        $mass = new Mass(1000000, 'μg');
        $g = $mass->to('g');

        $this->assertSame(1.0, $g->value);
    }

    // endregion

    // region Imperial/US conversion tests

    /**
     * Test converting pounds to ounces.
     */
    public function testConvertPoundsToOunces(): void
    {
        $mass = new Mass(1, 'lb');
        $oz = $mass->to('oz');

        $this->assertSame(16.0, $oz->value);
        $this->assertSame('oz', $oz->compoundUnit->asciiSymbol);
    }

    /**
     * Test converting ounces to pounds.
     */
    public function testConvertOuncesToPounds(): void
    {
        $mass = new Mass(32, 'oz');
        $lb = $mass->to('lb');

        $this->assertSame(2.0, $lb->value);
    }

    /**
     * Test converting stone to pounds.
     */
    public function testConvertStoneToPounds(): void
    {
        $mass = new Mass(1, 'st');
        $lb = $mass->to('lb');

        $this->assertSame(14.0, $lb->value);
    }

    /**
     * Test converting pounds to stone.
     */
    public function testConvertPoundsToStone(): void
    {
        $mass = new Mass(140, 'lb');
        $st = $mass->to('st');

        $this->assertSame(10.0, $st->value);
    }

    /**
     * Test converting short tons to pounds.
     */
    public function testConvertShortTonsToPounds(): void
    {
        $mass = new Mass(1, 'tn');
        $lb = $mass->to('lb');

        $this->assertSame(2000.0, $lb->value);
    }

    /**
     * Test converting long tons to pounds.
     */
    public function testConvertLongTonsToPounds(): void
    {
        $mass = new Mass(1, 'LT');
        $lb = $mass->to('lb');

        $this->assertSame(2240.0, $lb->value);
    }

    /**
     * Test converting short tons to long tons.
     */
    public function testConvertShortTonsToLongTons(): void
    {
        $mass = new Mass(1, 'tn');
        $lt = $mass->to('LT');

        // 1 short ton = 2000 lb, 1 long ton = 2240 lb
        // 1 short ton = 2000/2240 long tons ≈ 0.892857
        $this->assertApproxEqual(2000.0 / 2240.0, $lt->value);
    }

    /**
     * Test converting grains to milligrams.
     */
    public function testConvertGrainsToMilligrams(): void
    {
        $mass = new Mass(1, 'gr');
        $mg = $mass->to('mg');

        $this->assertSame(64.79891, $mg->value);
    }

    /**
     * Test converting grains to grams.
     */
    public function testConvertGrainsToGrams(): void
    {
        $mass = new Mass(1, 'gr');
        $g = $mass->to('g');

        $this->assertSame(0.06479891, $g->value);
    }

    // endregion

    // region Cross-system conversion tests

    /**
     * Test converting pounds to kilograms.
     */
    public function testConvertPoundsToKilograms(): void
    {
        $mass = new Mass(1, 'lb');
        $kg = $mass->to('kg');

        $this->assertSame(0.45359237, $kg->value);
    }

    /**
     * Test converting kilograms to pounds.
     */
    public function testConvertKilogramsToPounds(): void
    {
        $mass = new Mass(1, 'kg');
        $lb = $mass->to('lb');

        // 1 kg = 1/0.45359237 lb ≈ 2.20462 lb
        $this->assertApproxEqual(1 / 0.45359237, $lb->value);
    }

    /**
     * Test converting ounces to grams.
     */
    public function testConvertOuncesToGrams(): void
    {
        $mass = new Mass(1, 'oz');
        $g = $mass->to('g');

        // 1 oz = 1/16 lb = 0.45359237/16 kg = 28.349523125 g
        $this->assertApproxEqual(0.45359237 * 1000 / 16, $g->value);
    }

    /**
     * Test converting tonnes to short tons.
     */
    public function testConvertTonnesToShortTons(): void
    {
        $mass = new Mass(1, 't');
        $tn = $mass->to('tn');

        // 1 t = 1000 kg = 1000/0.45359237 lb = 2204.62... lb
        // 1 short ton = 2000 lb
        // 1 t = 2204.62.../2000 short tons ≈ 1.10231 short tons
        $this->assertApproxEqual(1000 / 0.45359237 / 2000, $tn->value);
    }

    /**
     * Test converting tonnes to long tons.
     */
    public function testConvertTonnesToLongTons(): void
    {
        $mass = new Mass(1, 't');
        $lt = $mass->to('LT');

        // 1 t = 1000 kg = 1000/0.45359237 lb = 2204.62... lb
        // 1 long ton = 2240 lb
        // 1 t = 2204.62.../2240 long tons ≈ 0.984207 long tons
        $this->assertApproxEqual(1000 / 0.45359237 / 2240, $lt->value);
    }

    /**
     * Test converting stone to kilograms.
     */
    public function testConvertStoneToKilograms(): void
    {
        $mass = new Mass(1, 'st');
        $kg = $mass->to('kg');

        // 1 st = 14 lb = 14 × 0.45359237 kg = 6.35029318 kg
        $this->assertApproxEqual(14 * 0.45359237, $kg->value);
    }

    // endregion

    // region Addition tests

    /**
     * Test adding grams to grams.
     */
    public function testAddGramsToGrams(): void
    {
        $a = new Mass(500, 'g');
        $b = new Mass(750, 'g');
        $result = $a->add($b);

        $this->assertInstanceOf(Mass::class, $result);
        $this->assertSame(1250.0, $result->value);
        $this->assertSame('g', $result->compoundUnit->asciiSymbol);
    }

    /**
     * Test adding grams to kilograms.
     */
    public function testAddGramsToKilograms(): void
    {
        $a = new Mass(1, 'kg');
        $b = new Mass(500, 'g');
        $result = $a->add($b);

        $this->assertInstanceOf(Mass::class, $result);
        $this->assertSame(1.5, $result->value);
        $this->assertSame('kg', $result->compoundUnit->asciiSymbol);
    }

    /**
     * Test adding ounces to pounds.
     */
    public function testAddOuncesToPounds(): void
    {
        $a = new Mass(1, 'lb');
        $b = new Mass(8, 'oz');
        $result = $a->add($b);

        // 1 lb + 8 oz = 1 lb + 0.5 lb = 1.5 lb
        $this->assertInstanceOf(Mass::class, $result);
        $this->assertSame(1.5, $result->value);
        $this->assertSame('lb', $result->compoundUnit->asciiSymbol);
    }

    /**
     * Test adding pounds to stone.
     */
    public function testAddPoundsToStone(): void
    {
        $a = new Mass(10, 'st');
        $b = new Mass(7, 'lb');
        $result = $a->add($b);

        // 10 st + 7 lb = 10 st + 0.5 st = 10.5 st
        $this->assertInstanceOf(Mass::class, $result);
        $this->assertSame(10.5, $result->value);
        $this->assertSame('st', $result->compoundUnit->asciiSymbol);
    }

    /**
     * Test adding kilograms to pounds (cross-system).
     */
    public function testAddKilogramsToPounds(): void
    {
        $a = new Mass(1, 'lb');
        $b = new Mass(1, 'kg');
        $result = $a->add($b);

        // 1 lb + 1 kg = 1 lb + 2.20462... lb ≈ 3.20462 lb
        $this->assertInstanceOf(Mass::class, $result);
        $this->assertApproxEqual(1 + 1 / 0.45359237, $result->value);
        $this->assertSame('lb', $result->compoundUnit->asciiSymbol);
    }

    /**
     * Test adding tonnes to short tons (cross-system).
     */
    public function testAddTonnesToShortTons(): void
    {
        $a = new Mass(1, 'tn');
        $b = new Mass(1, 't');
        $result = $a->add($b);

        // 1 short ton + 1 tonne = 1 tn + 1.10231... tn ≈ 2.10231 tn
        $this->assertInstanceOf(Mass::class, $result);
        $this->assertApproxEqual(1 + 1000 / 0.45359237 / 2000, $result->value);
        $this->assertSame('tn', $result->compoundUnit->asciiSymbol);
    }

    // endregion

    // region Parse tests

    /**
     * Test parsing kilograms.
     */
    public function testParseKilograms(): void
    {
        $mass = Mass::parse('75 kg');

        $this->assertInstanceOf(Mass::class, $mass);
        $this->assertSame(75.0, $mass->value);
        $this->assertSame('kg', $mass->compoundUnit->asciiSymbol);
    }

    /**
     * Test parsing grams.
     */
    public function testParseGrams(): void
    {
        $mass = Mass::parse('250 g');

        $this->assertSame(250.0, $mass->value);
        $this->assertSame('g', $mass->compoundUnit->asciiSymbol);
    }

    /**
     * Test parsing pounds.
     */
    public function testParsePounds(): void
    {
        $mass = Mass::parse('150 lb');

        $this->assertSame(150.0, $mass->value);
        $this->assertSame('lb', $mass->compoundUnit->asciiSymbol);
    }

    /**
     * Test parsing ounces.
     */
    public function testParseOunces(): void
    {
        $mass = Mass::parse('8 oz');

        $this->assertSame(8.0, $mass->value);
        $this->assertSame('oz', $mass->compoundUnit->asciiSymbol);
    }

    /**
     * Test parsing tonnes.
     */
    public function testParseTonnes(): void
    {
        $mass = Mass::parse('2.5 t');

        $this->assertSame(2.5, $mass->value);
        $this->assertSame('t', $mass->compoundUnit->asciiSymbol);
    }

    // endregion

    // region Static convert method tests

    /**
     * Test static convert method.
     */
    public function testStaticConvertMethod(): void
    {
        $value = Mass::convert(1, 'kg', 'g');

        $this->assertSame(1000.0, $value);
    }

    /**
     * Test static convert cross-system.
     */
    public function testStaticConvertCrossSystem(): void
    {
        $value = Mass::convert(1, 'lb', 'kg');

        $this->assertSame(0.45359237, $value);
    }

    // endregion

    // region fromParts tests

    /**
     * Test fromParts creates a mass quantity using the default result unit.
     */
    public function testFromPartsCreatesMassQuantity(): void
    {
        $mass = Mass::fromParts([
            'lb' => 2,
            'oz' => 8,
        ]);

        $this->assertInstanceOf(Mass::class, $mass);
        // 2 lb + 8 oz = 2 lb + 0.5 lb = 2.5 lb.
        $this->assertSame('lb', $mass->compoundUnit->asciiSymbol);
        $this->assertEqualsWithDelta(2.5, $mass->value, 1e-10);
    }

    /**
     * Test fromParts with cross-system units.
     */
    public function testFromPartsCrossSystem(): void
    {
        // Mix metric and imperial units.
        $mass = Mass::fromParts([
            'lb' => 1,
            'kg' => 1,
        ]);

        $this->assertInstanceOf(Mass::class, $mass);
        // 1 lb + 1 kg (= 2.20462262... lb) ≈ 3.20462262... lb.
        $this->assertSame('lb', $mass->compoundUnit->asciiSymbol);
        $this->assertEqualsWithDelta(3.20462262, $mass->value, 1e-5);
    }

    /**
     * Test fromParts with negative sign.
     */
    public function testFromPartsWithSign(): void
    {
        $mass = Mass::fromParts([
            'lb'   => 3,
            'oz'   => 4,
            'sign' => -1,
        ]);

        $this->assertInstanceOf(Mass::class, $mass);
        // -(3 lb + 4 oz) = -(3 + 0.25) = -3.25 lb.
        $this->assertEqualsWithDelta(-3.25, $mass->value, 1e-10);
    }

    // endregion

    // region Constant tests

    /**
     * Test the IMP_PART_UNITS constant has the expected symbols.
     */
    public function testImpPartUnitsConstant(): void
    {
        $this->assertSame(['LT', 'st', 'lb', 'oz'], Mass::IMP_PART_UNITS);
    }

    /**
     * Test the US_PART_UNITS constant has the expected symbols.
     */
    public function testUsPartUnitsConstant(): void
    {
        $this->assertSame(['tn', 'lb', 'oz', 'gr'], Mass::US_PART_UNITS);
    }

    // endregion

    // region Imperial parts tests

    /**
     * Test toParts() with imperial units.
     */
    public function testToPartsImperial(): void
    {
        // 11 stone 3 lb = 11 * 14 + 3 = 157 lb
        $mass = new Mass(157, 'lb');
        $parts = $mass->toParts(partUnitSymbols: Mass::IMP_PART_UNITS);

        $this->assertSame(1, $parts['sign']);
        $this->assertSame(0, $parts['LT']);
        $this->assertSame(11, $parts['st']);
        $this->assertSame(3, $parts['lb']);
        $this->assertSame(0.0, $parts['oz']);
    }

    /**
     * Test toParts() with imperial units and fractional ounces.
     */
    public function testToPartsImperialWithOunces(): void
    {
        // 2 lb 8 oz = 2.5 lb = 40 oz
        $mass = new Mass(40, 'oz');
        $parts = $mass->toParts(partUnitSymbols: Mass::IMP_PART_UNITS);

        $this->assertSame(0, $parts['LT']);
        $this->assertSame(0, $parts['st']);
        $this->assertSame(2, $parts['lb']);
        $this->assertSame(8.0, $parts['oz']);
    }

    /**
     * Test fromParts() with imperial units.
     */
    public function testFromPartsImperial(): void
    {
        $mass = Mass::fromParts([
            'st' => 11,
            'lb' => 3,
        ]);

        $this->assertInstanceOf(Mass::class, $mass);
        $this->assertSame('lb', $mass->compoundUnit->asciiSymbol);
        // 11 st 3 lb = 11 * 14 + 3 = 157 lb
        $this->assertSame(157.0, $mass->value);
    }

    /**
     * Test formatParts() with imperial units.
     */
    public function testFormatPartsImperial(): void
    {
        // 157 lb = 11 st 3 lb
        $mass = new Mass(157, 'lb');
        $result = $mass->formatParts(partUnitSymbols: Mass::IMP_PART_UNITS);

        $this->assertSame('11st 3lb', $result);
    }

    /**
     * Test parseParts() with imperial units.
     */
    public function testParsePartsImperial(): void
    {
        $mass = Mass::parseParts('11 st 3 lb');

        $this->assertInstanceOf(Mass::class, $mass);
        $this->assertSame('st', $mass->compoundUnit->asciiSymbol);
        $this->assertEqualsWithDelta(11.214285714, $mass->value, 1e-6);
    }

    // endregion

    // region US customary parts tests

    /**
     * Test toParts() with US customary units.
     */
    public function testToPartsUsCustomary(): void
    {
        // 3 lb 4 oz = 52 oz
        $mass = new Mass(52, 'oz');
        $parts = $mass->toParts(partUnitSymbols: Mass::US_PART_UNITS);

        $this->assertSame(1, $parts['sign']);
        $this->assertSame(0, $parts['tn']);
        $this->assertSame(3, $parts['lb']);
        $this->assertSame(4, $parts['oz']);
        $this->assertSame(0.0, $parts['gr']);
    }

    /**
     * Test toParts() with US customary units including grains.
     */
    public function testToPartsUsCustomaryWithGrains(): void
    {
        // 1 lb = 7000 gr, so 1 lb 1 oz = 7000 + 437.5 = 7437.5 gr
        $mass = new Mass(7437.5, 'gr');
        $parts = $mass->toParts(partUnitSymbols: Mass::US_PART_UNITS);

        $this->assertSame(0, $parts['tn']);
        $this->assertSame(1, $parts['lb']);
        $this->assertSame(1, $parts['oz']);
        $this->assertSame(0.0, $parts['gr']);
    }

    /**
     * Test fromParts() with US customary units.
     */
    public function testFromPartsUsCustomary(): void
    {
        $mass = Mass::fromParts([
            'lb' => 3,
            'oz' => 4,
        ]);

        $this->assertInstanceOf(Mass::class, $mass);
        $this->assertSame('lb', $mass->compoundUnit->asciiSymbol);
        // 3 lb 4 oz = 3.25 lb
        $this->assertSame(3.25, $mass->value);
    }

    /**
     * Test formatParts() with US customary units.
     */
    public function testFormatPartsUsCustomary(): void
    {
        // 3.25 lb = 3 lb 4 oz
        $mass = new Mass(3.25, 'lb');
        $result = $mass->formatParts(partUnitSymbols: Mass::US_PART_UNITS);

        $this->assertSame('3lb 4oz', $result);
    }

    /**
     * Test parseParts() with US customary units.
     */
    public function testParsePartsUsCustomary(): void
    {
        $mass = Mass::parseParts('3 lb 4 oz');

        $this->assertInstanceOf(Mass::class, $mass);
        $this->assertSame('lb', $mass->compoundUnit->asciiSymbol);
        $this->assertSame(3.25, $mass->value);
    }

    /**
     * Test parseParts() with negative US customary parts.
     */
    public function testParsePartsUsCustomaryNegative(): void
    {
        $mass = Mass::parseParts('-3lb 4oz');

        $this->assertInstanceOf(Mass::class, $mass);
        $this->assertSame(-3.25, $mass->value);
    }

    // endregion

    // region Special cases

    /**
     * Test converting zero mass.
     */
    public function testConvertZeroMass(): void
    {
        $mass = new Mass(0, 'kg');
        $lb = $mass->to('lb');

        $this->assertSame(0.0, $lb->value);
    }

    /**
     * Test body weight conversion (practical example).
     */
    public function testBodyWeightConversion(): void
    {
        // 70 kg person
        $mass = new Mass(70, 'kg');

        // Convert to stone and pounds
        $st = $mass->to('st');
        $lb = $mass->to('lb');

        // 70 kg ≈ 154.32 lb ≈ 11.02 st
        $this->assertApproxEqual(70 / 0.45359237, $lb->value);
        $this->assertApproxEqual(70 / 0.45359237 / 14, $st->value);
    }

    // endregion
}
