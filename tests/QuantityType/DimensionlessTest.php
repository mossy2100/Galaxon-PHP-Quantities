<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\QuantityType;

use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\Helpers\UnitRegistry;
use Galaxon\Quantities\QuantityType\Dimensionless;
use Galaxon\Quantities\System;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Dimensionless quantity type.
 */
#[CoversClass(Dimensionless::class)]
final class DimensionlessTest extends TestCase
{
    use FloatAssertions;

    // region Setup

    public static function setUpBeforeClass(): void
    {
        // Load Common units for dimensionless quantities.
        UnitRegistry::loadSystem(System::Common);
    }

    // endregion

    // region Basic conversion tests

    /**
     * Test converting percentage to parts per thousand.
     */
    public function testConvertPercentageToPartsPerThousand(): void
    {
        $ratio = new Dimensionless(1, '%');
        $ppt = $ratio->to('ppt');

        $this->assertInstanceOf(Dimensionless::class, $ppt);
        $this->assertSame(10.0, $ppt->value);
        $this->assertSame('ppt', $ppt->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting parts per thousand to percentage.
     */
    public function testConvertPartsPerThousandToPercentage(): void
    {
        $ratio = new Dimensionless(10, 'ppt');
        $pct = $ratio->to('%');

        $this->assertSame(1.0, $pct->value);
        $this->assertSame('%', $pct->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting parts per thousand to parts per million.
     */
    public function testConvertPartsPerThousandToPartsPerMillion(): void
    {
        $ratio = new Dimensionless(1, 'ppt');
        $ppm = $ratio->to('ppm');

        $this->assertSame(1000.0, $ppm->value);
        $this->assertSame('ppm', $ppm->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting parts per million to parts per thousand.
     */
    public function testConvertPartsPerMillionToPartsPerThousand(): void
    {
        $ratio = new Dimensionless(1000, 'ppm');
        $ppt = $ratio->to('ppt');

        $this->assertSame(1.0, $ppt->value);
    }

    /**
     * Test converting parts per million to parts per billion.
     */
    public function testConvertPartsPerMillionToPartsPerBillion(): void
    {
        $ratio = new Dimensionless(1, 'ppm');
        $ppb = $ratio->to('ppb');

        $this->assertSame(1000.0, $ppb->value);
        $this->assertSame('ppb', $ppb->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting parts per billion to parts per million.
     */
    public function testConvertPartsPerBillionToPartsPerMillion(): void
    {
        $ratio = new Dimensionless(1000, 'ppb');
        $ppm = $ratio->to('ppm');

        $this->assertSame(1.0, $ppm->value);
    }

    // endregion

    // region Multi-step conversion tests

    /**
     * Test converting percentage to parts per million.
     */
    public function testConvertPercentageToPartsPerMillion(): void
    {
        $ratio = new Dimensionless(1, '%');
        $ppm = $ratio->to('ppm');

        // 1% = 10 ppt = 10,000 ppm
        $this->assertSame(10000.0, $ppm->value);
    }

    /**
     * Test converting parts per million to percentage.
     */
    public function testConvertPartsPerMillionToPercentage(): void
    {
        $ratio = new Dimensionless(10000, 'ppm');
        $pct = $ratio->to('%');

        $this->assertSame(1.0, $pct->value);
    }

    /**
     * Test converting percentage to parts per billion.
     */
    public function testConvertPercentageToPartsPerBillion(): void
    {
        $ratio = new Dimensionless(1, '%');
        $ppb = $ratio->to('ppb');

        // 1% = 10 ppt = 10,000 ppm = 10,000,000 ppb
        $this->assertSame(10000000.0, $ppb->value);
    }

    /**
     * Test converting parts per billion to percentage.
     */
    public function testConvertPartsPerBillionToPercentage(): void
    {
        $ratio = new Dimensionless(10000000, 'ppb');
        $pct = $ratio->to('%');

        $this->assertSame(1.0, $pct->value);
    }

    /**
     * Test converting parts per thousand to parts per billion.
     */
    public function testConvertPartsPerThousandToPartsPerBillion(): void
    {
        $ratio = new Dimensionless(1, 'ppt');
        $ppb = $ratio->to('ppb');

        // 1 ppt = 1000 ppm = 1,000,000 ppb
        $this->assertSame(1000000.0, $ppb->value);
    }

    // endregion

    // region Addition tests

    /**
     * Test adding percentages.
     */
    public function testAddPercentages(): void
    {
        $a = new Dimensionless(25, '%');
        $b = new Dimensionless(15, '%');
        $result = $a->add($b);

        $this->assertInstanceOf(Dimensionless::class, $result);
        $this->assertSame(40.0, $result->value);
        $this->assertSame('%', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding parts per thousand to percentage.
     */
    public function testAddPartsPerThousandToPercentage(): void
    {
        $a = new Dimensionless(5, '%');
        $b = new Dimensionless(50, 'ppt');
        $result = $a->add($b);

        // 5% + 50 ppt = 5% + 5% = 10%
        $this->assertSame(10.0, $result->value);
        $this->assertSame('%', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding parts per million to parts per thousand.
     */
    public function testAddPartsPerMillionToPartsPerThousand(): void
    {
        $a = new Dimensionless(1, 'ppt');
        $b = new Dimensionless(500, 'ppm');
        $result = $a->add($b);

        // 1 ppt + 500 ppm = 1 ppt + 0.5 ppt = 1.5 ppt
        $this->assertSame(1.5, $result->value);
        $this->assertSame('ppt', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding parts per billion to parts per million.
     */
    public function testAddPartsPerBillionToPartsPerMillion(): void
    {
        $a = new Dimensionless(1, 'ppm');
        $b = new Dimensionless(500, 'ppb');
        $result = $a->add($b);

        // 1 ppm + 500 ppb = 1 ppm + 0.5 ppm = 1.5 ppm
        $this->assertSame(1.5, $result->value);
        $this->assertSame('ppm', $result->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Parse tests

    /**
     * Test parsing percentage.
     */
    public function testParsePercentage(): void
    {
        $ratio = Dimensionless::parse('50 %');

        $this->assertInstanceOf(Dimensionless::class, $ratio);
        $this->assertSame(50.0, $ratio->value);
        $this->assertSame('%', $ratio->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing parts per thousand with ASCII symbol.
     */
    public function testParsePartsPerThousandAscii(): void
    {
        $ratio = Dimensionless::parse('5 ppt');

        $this->assertSame(5.0, $ratio->value);
        $this->assertSame('ppt', $ratio->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing parts per thousand with Unicode symbol.
     */
    public function testParsePartsPerThousandUnicode(): void
    {
        $ratio = Dimensionless::parse('5 ‰');

        $this->assertSame(5.0, $ratio->value);
        $this->assertSame('‰', $ratio->derivedUnit->unicodeSymbol);
    }

    /**
     * Test parsing parts per million.
     */
    public function testParsePartsPerMillion(): void
    {
        $ratio = Dimensionless::parse('100 ppm');

        $this->assertSame(100.0, $ratio->value);
        $this->assertSame('ppm', $ratio->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing parts per billion.
     */
    public function testParsePartsPerBillion(): void
    {
        $ratio = Dimensionless::parse('500 ppb');

        $this->assertSame(500.0, $ratio->value);
        $this->assertSame('ppb', $ratio->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Format tests

    /**
     * Test formatting percentage.
     */
    public function testFormatPercentage(): void
    {
        $ratio = new Dimensionless(75.5, '%');

        // No space before % symbol
        $this->assertSame('75.5%', $ratio->format());
    }

    /**
     * Test formatting parts per thousand with Unicode.
     */
    public function testFormatPartsPerThousandUnicode(): void
    {
        $ratio = new Dimensionless(5, 'ppt');

        // Unicode format uses ‰
        $this->assertSame('5‰', $ratio->format());
    }

    /**
     * Test formatting parts per thousand with ASCII.
     */
    public function testFormatPartsPerThousandAscii(): void
    {
        $ratio = new Dimensionless(5, 'ppt');

        // ASCII format uses ppt with space
        $this->assertSame('5 ppt', $ratio->format(ascii: true));
    }

    /**
     * Test formatting parts per million.
     */
    public function testFormatPartsPerMillion(): void
    {
        $ratio = new Dimensionless(100, 'ppm');

        $this->assertSame('100 ppm', $ratio->format());
    }

    /**
     * Test formatting parts per billion.
     */
    public function testFormatPartsPerBillion(): void
    {
        $ratio = new Dimensionless(500, 'ppb');

        $this->assertSame('500 ppb', $ratio->format());
    }

    // endregion

    // region Static convert method tests

    /**
     * Test static convert method for percentage to ppm.
     */
    public function testStaticConvertPercentageToPpm(): void
    {
        $value = Dimensionless::convert(1, '%', 'ppm');

        $this->assertSame(10000.0, $value);
    }

    /**
     * Test static convert method for ppm to ppb.
     */
    public function testStaticConvertPpmToPpb(): void
    {
        $value = Dimensionless::convert(1, 'ppm', 'ppb');

        $this->assertSame(1000.0, $value);
    }

    /**
     * Test static convert method for ppb to percentage.
     */
    public function testStaticConvertPpbToPercentage(): void
    {
        $value = Dimensionless::convert(10000000, 'ppb', '%');

        $this->assertSame(1.0, $value);
    }

    // endregion

    // region Practical examples

    /**
     * Test alcohol by volume conversion.
     */
    public function testAlcoholByVolume(): void
    {
        // 5% ABV beer
        $abv = new Dimensionless(5, '%');
        $ppt = $abv->to('ppt');

        // 5% = 50 parts per thousand
        $this->assertSame(50.0, $ppt->value);
    }

    /**
     * Test water contaminant level.
     */
    public function testWaterContaminantLevel(): void
    {
        // EPA limit for lead in drinking water is 15 ppb
        $lead = new Dimensionless(15, 'ppb');
        $ppm = $lead->to('ppm');

        // 15 ppb = 0.015 ppm
        $this->assertSame(0.015, $ppm->value);
    }

    /**
     * Test atmospheric CO2 concentration.
     */
    public function testAtmosphericCO2(): void
    {
        // Atmospheric CO2 is about 420 ppm
        $co2 = new Dimensionless(420, 'ppm');
        $pct = $co2->to('%');

        // 420 ppm = 0.042%
        $this->assertSame(0.042, $pct->value);
    }

    /**
     * Test blood alcohol content.
     */
    public function testBloodAlcoholContent(): void
    {
        // Legal limit in many places is 0.08% BAC
        $bac = new Dimensionless(0.08, '%');
        $ppt = $bac->to('ppt');

        // 0.08% = 0.8 parts per thousand
        $this->assertSame(0.8, $ppt->value);
    }

    /**
     * Test converting zero value.
     */
    public function testConvertZeroValue(): void
    {
        $ratio = new Dimensionless(0, '%');
        $ppm = $ratio->to('ppm');

        $this->assertSame(0.0, $ppm->value);
    }

    /**
     * Test very small percentage.
     */
    public function testVerySmallPercentage(): void
    {
        // 0.0001% = 1 ppm
        $ratio = new Dimensionless(0.0001, '%');
        $ppm = $ratio->to('ppm');

        $this->assertSame(1.0, $ppm->value);
    }

    // endregion
}
