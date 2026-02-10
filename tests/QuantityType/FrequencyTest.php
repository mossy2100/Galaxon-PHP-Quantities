<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\QuantityType;

use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\QuantityType\Dimensionless;
use Galaxon\Quantities\QuantityType\Frequency;
use Galaxon\Quantities\QuantityType\Time;
use Galaxon\Quantities\Tests\Traits\ArrayShapeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Frequency quantity type.
 */
#[CoversClass(Frequency::class)]
final class FrequencyTest extends TestCase
{
    use ArrayShapeTrait;
    use FloatAssertions;

    // region Overridden methods

    /**
     * Test getUnitDefinitions() returns valid unit definitions.
     */
    public function testGetUnitDefinitionsReturnsValidArray(): void
    {
        $units = Frequency::getUnitDefinitions();

        $this->assertValidUnitDefinitionsShape($units);
    }

    /**
     * Test getConversionDefinitions() returns empty array.
     */
    public function testGetConversionDefinitionsReturnsEmptyArray(): void
    {
        $conversions = Frequency::getConversionDefinitions();

        $this->assertEmpty($conversions);
    }

    // endregion

    // region Metric prefix conversion tests

    /**
     * Test converting hertz to kilohertz.
     */
    public function testConvertHertzToKilohertz(): void
    {
        $freq = new Frequency(1000, 'Hz');
        $khz = $freq->to('kHz');

        $this->assertInstanceOf(Frequency::class, $khz);
        $this->assertSame(1.0, $khz->value);
        $this->assertSame('kHz', $khz->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting kilohertz to hertz.
     */
    public function testConvertKilohertzToHertz(): void
    {
        $freq = new Frequency(1, 'kHz');
        $hz = $freq->to('Hz');

        $this->assertSame(1000.0, $hz->value);
        $this->assertSame('Hz', $hz->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting hertz to megahertz.
     */
    public function testConvertHertzToMegahertz(): void
    {
        $freq = new Frequency(1000000, 'Hz');
        $mhz = $freq->to('MHz');

        $this->assertSame(1.0, $mhz->value);
        $this->assertSame('MHz', $mhz->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting megahertz to hertz.
     */
    public function testConvertMegahertzToHertz(): void
    {
        $freq = new Frequency(1, 'MHz');
        $hz = $freq->to('Hz');

        $this->assertSame(1000000.0, $hz->value);
    }

    /**
     * Test converting hertz to gigahertz.
     */
    public function testConvertHertzToGigahertz(): void
    {
        $freq = new Frequency(1e9, 'Hz');
        $ghz = $freq->to('GHz');

        $this->assertSame(1.0, $ghz->value);
        $this->assertSame('GHz', $ghz->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting gigahertz to hertz.
     */
    public function testConvertGigahertzToHertz(): void
    {
        $freq = new Frequency(2.4, 'GHz');
        $hz = $freq->to('Hz');

        $this->assertSame(2.4e9, $hz->value);
    }

    /**
     * Test converting hertz to terahertz.
     */
    public function testConvertHertzToTerahertz(): void
    {
        $freq = new Frequency(1e12, 'Hz');
        $thz = $freq->to('THz');

        $this->assertSame(1.0, $thz->value);
        $this->assertSame('THz', $thz->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting hertz to millihertz.
     */
    public function testConvertHertzToMillihertz(): void
    {
        $freq = new Frequency(1, 'Hz');
        $mhz = $freq->to('mHz');

        $this->assertSame(1000.0, $mhz->value);
        $this->assertSame('mHz', $mhz->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting millihertz to hertz.
     */
    public function testConvertMillihertzToHertz(): void
    {
        $freq = new Frequency(500, 'mHz');
        $hz = $freq->to('Hz');

        $this->assertSame(0.5, $hz->value);
    }

    // endregion

    // region Cross-prefix conversion tests

    /**
     * Test converting kilohertz to megahertz.
     */
    public function testConvertKilohertzToMegahertz(): void
    {
        $freq = new Frequency(1000, 'kHz');
        $mhz = $freq->to('MHz');

        $this->assertSame(1.0, $mhz->value);
    }

    /**
     * Test converting megahertz to gigahertz.
     */
    public function testConvertMegahertzToGigahertz(): void
    {
        $freq = new Frequency(1000, 'MHz');
        $ghz = $freq->to('GHz');

        $this->assertSame(1.0, $ghz->value);
    }

    /**
     * Test converting gigahertz to megahertz.
     */
    public function testConvertGigahertzToMegahertz(): void
    {
        $freq = new Frequency(2.4, 'GHz');
        $mhz = $freq->to('MHz');

        $this->assertSame(2400.0, $mhz->value);
    }

    /**
     * Test converting gigahertz to kilohertz.
     */
    public function testConvertGigahertzToKilohertz(): void
    {
        $freq = new Frequency(1, 'GHz');
        $khz = $freq->to('kHz');

        $this->assertSame(1000000.0, $khz->value);
    }

    // endregion

    // region Becquerel tests

    /**
     * Test converting becquerel to kilobecquerel.
     */
    public function testConvertBecquerelToKilobecquerel(): void
    {
        $activity = new Frequency(1000, 'Bq');
        $kbq = $activity->to('kBq');

        $this->assertSame(1.0, $kbq->value);
        $this->assertSame('kBq', $kbq->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting megabecquerel to becquerel.
     */
    public function testConvertMegabecquerelToBecquerel(): void
    {
        $activity = new Frequency(1, 'MBq');
        $bq = $activity->to('Bq');

        $this->assertSame(1000000.0, $bq->value);
    }

    /**
     * Test converting gigabecquerel to megabecquerel.
     */
    public function testConvertGigabecquerelToMegabecquerel(): void
    {
        $activity = new Frequency(1, 'GBq');
        $mbq = $activity->to('MBq');

        $this->assertSame(1000.0, $mbq->value);
    }

    // endregion

    // region toSi() tests

    /**
     * Test converting hertz to SI base units.
     */
    public function testConvertHertzToSi(): void
    {
        $freq = new Frequency(100, 'Hz');
        $si = $freq->toSiBase();

        // Hz expands to s⁻¹
        $this->assertSame(100.0, $si->value);
        $this->assertSame('s-1', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting kilohertz to SI base units.
     */
    public function testConvertKilohertzToSi(): void
    {
        $freq = new Frequency(1, 'kHz');
        $si = $freq->toSiBase();

        $this->assertSame(1000.0, $si->value);
        $this->assertSame('s-1', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting megahertz to SI base units.
     */
    public function testConvertMegahertzToSi(): void
    {
        $freq = new Frequency(1, 'MHz');
        $si = $freq->toSiBase();

        $this->assertSame(1000000.0, $si->value);
        $this->assertSame('s-1', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting becquerel to SI base units.
     */
    public function testConvertBecquerelToSi(): void
    {
        $activity = new Frequency(1000, 'Bq');
        $si = $activity->toSiBase();

        // Bq also expands to s⁻¹
        $this->assertSame(1000.0, $si->value);
        $this->assertSame('s-1', $si->derivedUnit->asciiSymbol);
    }

    // endregion

    // region expand() tests

    /**
     * Test expanding hertz to per-second.
     */
    public function testExpandHertz(): void
    {
        $freq = new Frequency(50, 'Hz');
        $expanded = $freq->expand();

        $this->assertSame(50.0, $expanded->value);
        $this->assertSame('s-1', $expanded->derivedUnit->asciiSymbol);
    }

    /**
     * Test expanding kilohertz to per-second.
     */
    public function testExpandKilohertz(): void
    {
        $freq = new Frequency(1, 'kHz');
        $expanded = $freq->expand();

        $this->assertSame(1000.0, $expanded->value);
        $this->assertSame('s-1', $expanded->derivedUnit->asciiSymbol);
    }

    // endregion

    // region autoPrefix() tests

    /**
     * Test auto-prefixing large hertz value.
     */
    public function testAutoPrefixLargeHertz(): void
    {
        $freq = new Frequency(2400000000, 'Hz');
        $prefixed = $freq->autoPrefix();

        $this->assertSame(2.4, $prefixed->value);
        $this->assertSame('GHz', $prefixed->derivedUnit->asciiSymbol);
    }

    /**
     * Test auto-prefixing small hertz value.
     */
    public function testAutoPrefixSmallHertz(): void
    {
        $freq = new Frequency(0.001, 'Hz');
        $prefixed = $freq->autoPrefix();

        $this->assertSame(1.0, $prefixed->value);
        $this->assertSame('mHz', $prefixed->derivedUnit->asciiSymbol);
    }

    /**
     * Test auto-prefixing kilohertz to megahertz.
     */
    public function testAutoPrefixKilohertzToMegahertz(): void
    {
        $freq = new Frequency(5000, 'kHz');
        $prefixed = $freq->autoPrefix();

        $this->assertSame(5.0, $prefixed->value);
        $this->assertSame('MHz', $prefixed->derivedUnit->asciiSymbol);
    }

    /**
     * Test auto-prefixing on s⁻¹ form of frequency.
     *
     * 1 kHz = 1000 s⁻¹, which autoprefixes to 1 ms⁻¹ (1 per millisecond = 1000 per second).
     * This is mathematically correct but the notation can be counterintuitive.
     */
    public function testAutoPrefixOnInverseSeconds(): void
    {
        $freq = new Frequency(1, 'kHz');
        $si = $freq->toSiBase();
        $prefixed = $si->autoPrefix();

        // 1000 s⁻¹ = 1 ms⁻¹ (per millisecond)
        $this->assertSame(1.0, $prefixed->value);
        $this->assertSame('ms-1', $prefixed->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Addition tests

    /**
     * Test adding hertz to hertz.
     */
    public function testAddHertzToHertz(): void
    {
        $a = new Frequency(100, 'Hz');
        $b = new Frequency(50, 'Hz');
        $result = $a->add($b);

        $this->assertInstanceOf(Frequency::class, $result);
        $this->assertSame(150.0, $result->value);
        $this->assertSame('Hz', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding kilohertz to hertz.
     */
    public function testAddKilohertzToHertz(): void
    {
        $a = new Frequency(500, 'Hz');
        $b = new Frequency(1, 'kHz');
        $result = $a->add($b);

        // 500 Hz + 1 kHz = 500 Hz + 1000 Hz = 1500 Hz
        $this->assertSame(1500.0, $result->value);
        $this->assertSame('Hz', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding megahertz to kilohertz.
     */
    public function testAddMegahertzToKilohertz(): void
    {
        $a = new Frequency(500, 'kHz');
        $b = new Frequency(1, 'MHz');
        $result = $a->add($b);

        // 500 kHz + 1 MHz = 500 kHz + 1000 kHz = 1500 kHz
        $this->assertSame(1500.0, $result->value);
        $this->assertSame('kHz', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding gigahertz to megahertz.
     */
    public function testAddGigahertzToMegahertz(): void
    {
        $a = new Frequency(400, 'MHz');
        $b = new Frequency(2, 'GHz');
        $result = $a->add($b);

        // 400 MHz + 2 GHz = 400 MHz + 2000 MHz = 2400 MHz
        $this->assertSame(2400.0, $result->value);
        $this->assertSame('MHz', $result->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Parse tests

    /**
     * Test parsing hertz.
     */
    public function testParseHertz(): void
    {
        $freq = Frequency::parse('440 Hz');

        $this->assertInstanceOf(Frequency::class, $freq);
        $this->assertSame(440.0, $freq->value);
        $this->assertSame('Hz', $freq->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing kilohertz.
     */
    public function testParseKilohertz(): void
    {
        $freq = Frequency::parse('88.5 kHz');

        $this->assertSame(88.5, $freq->value);
        $this->assertSame('kHz', $freq->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing megahertz.
     */
    public function testParseMegahertz(): void
    {
        $freq = Frequency::parse('101.5 MHz');

        $this->assertSame(101.5, $freq->value);
        $this->assertSame('MHz', $freq->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing gigahertz.
     */
    public function testParseGigahertz(): void
    {
        $freq = Frequency::parse('2.4 GHz');

        $this->assertSame(2.4, $freq->value);
        $this->assertSame('GHz', $freq->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing becquerel.
     */
    public function testParseBecquerel(): void
    {
        $activity = Frequency::parse('370 MBq');

        $this->assertSame(370.0, $activity->value);
        $this->assertSame('MBq', $activity->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Static convert method tests

    /**
     * Test static convert from Hz to kHz.
     */
    public function testStaticConvertHertzToKilohertz(): void
    {
        $value = Frequency::convert(5000, 'Hz', 'kHz');

        $this->assertSame(5.0, $value);
    }

    /**
     * Test static convert from MHz to GHz.
     */
    public function testStaticConvertMegahertzToGigahertz(): void
    {
        $value = Frequency::convert(2400, 'MHz', 'GHz');

        $this->assertSame(2.4, $value);
    }

    /**
     * Test static convert from GHz to Hz.
     */
    public function testStaticConvertGigahertzToHertz(): void
    {
        $value = Frequency::convert(1, 'GHz', 'Hz');

        $this->assertSame(1e9, $value);
    }

    // endregion

    // region Frequency-time relationship tests

    /**
     * Test that frequency times period equals 1.
     */
    public function testFrequencyTimesPeriodEqualsOne(): void
    {
        // f × T = 1
        // 100 Hz × 0.01 s = 1
        $freq = new Frequency(100, 'Hz');
        $period = new Time(0.01, 's');
        $result = $freq->mul($period);

        $this->assertApproxEqual(1.0, $result->value);
    }

    /**
     * Test calculating period from frequency.
     */
    public function testCalculatePeriodFromFrequency(): void
    {
        // T = 1/f
        // Period of 50 Hz = 1/50 = 0.02 s = 20 ms
        $one = new Dimensionless(1);
        $freq = new Frequency(50, 'Hz');
        $period = $one->div($freq);

        $this->assertInstanceOf(Time::class, $period);
        $this->assertSame(0.02, $period->value);

        $ms = $period->to('ms');
        $this->assertSame(20.0, $ms->value);
    }

    /**
     * Test calculating frequency from period.
     */
    public function testCalculateFrequencyFromPeriod(): void
    {
        // f = 1/T
        // Frequency for 0.001 s period = 1/0.001 = 1000 Hz = 1 kHz
        $one = new Dimensionless(1);
        $period = new Time(0.001, 's');
        $freq = $one->div($period);

        $this->assertInstanceOf(Frequency::class, $freq);
        $this->assertSame(1000.0, $freq->value);

        $khz = $freq->to('kHz');
        $this->assertSame(1.0, $khz->value);
    }

    // endregion

    // region Practical examples

    /**
     * Test concert pitch A4 frequency.
     */
    public function testConcertPitchA4(): void
    {
        // Concert pitch A4 = 440 Hz
        $a4 = new Frequency(440, 'Hz');
        $khz = $a4->to('kHz');

        $this->assertSame(0.44, $khz->value);
    }

    /**
     * Test FM radio frequency.
     */
    public function testFmRadioFrequency(): void
    {
        // Typical FM radio station at 101.5 MHz
        $fm = new Frequency(101.5, 'MHz');
        $hz = $fm->to('Hz');

        $this->assertSame(101500000.0, $hz->value);
    }

    /**
     * Test WiFi 2.4 GHz frequency.
     */
    public function testWifi24GhzFrequency(): void
    {
        $wifi = new Frequency(2.4, 'GHz');
        $mhz = $wifi->to('MHz');

        $this->assertSame(2400.0, $mhz->value);
    }

    /**
     * Test WiFi 5 GHz frequency.
     */
    public function testWifi5GhzFrequency(): void
    {
        $wifi = new Frequency(5, 'GHz');
        $hz = $wifi->to('Hz');

        $this->assertSame(5e9, $hz->value);
    }

    /**
     * Test CPU clock speed.
     */
    public function testCpuClockSpeed(): void
    {
        // 3.5 GHz processor
        $clock = new Frequency(3.5, 'GHz');
        $mhz = $clock->to('MHz');

        $this->assertSame(3500.0, $mhz->value);
    }

    /**
     * Test heartbeat frequency.
     */
    public function testHeartbeatFrequency(): void
    {
        // 72 beats per minute = 72/60 Hz = 1.2 Hz
        $heartRate = new Frequency(1.2, 'Hz');
        $mhz = $heartRate->to('mHz');

        $this->assertSame(1200.0, $mhz->value);
    }

    /**
     * Test medical isotope activity.
     */
    public function testMedicalIsotopeActivity(): void
    {
        // Tc-99m injection typically 370-740 MBq
        $activity = new Frequency(555, 'MBq');
        $gbq = $activity->to('GBq');

        $this->assertSame(0.555, $gbq->value);
    }

    /**
     * Test zero frequency conversion.
     */
    public function testZeroFrequencyConversion(): void
    {
        $freq = new Frequency(0, 'Hz');
        $ghz = $freq->to('GHz');

        $this->assertSame(0.0, $ghz->value);
    }

    // endregion

    // region Format tests

    /**
     * Test formatting hertz.
     */
    public function testFormatHertz(): void
    {
        $freq = new Frequency(440, 'Hz');

        $this->assertSame('440 Hz', $freq->format());
    }

    /**
     * Test formatting gigahertz.
     */
    public function testFormatGigahertz(): void
    {
        $freq = new Frequency(2.4, 'GHz');

        $this->assertSame('2.4 GHz', $freq->format());
    }

    /**
     * Test formatting megabecquerel.
     */
    public function testFormatMegabecquerel(): void
    {
        $activity = new Frequency(370, 'MBq');

        $this->assertSame('370 MBq', $activity->format());
    }

    // endregion
}
