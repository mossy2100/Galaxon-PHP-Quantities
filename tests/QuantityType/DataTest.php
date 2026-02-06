<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\QuantityType;

use Galaxon\Core\Traits\FloatAssertions;
use Galaxon\Quantities\Quantity;
use Galaxon\Quantities\QuantityType\Data;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Data quantity type.
 */
#[CoversClass(Data::class)]
final class DataTest extends TestCase
{
    use FloatAssertions;

    // region Basic conversion tests

    /**
     * Test converting bytes to bits.
     */
    public function testConvertBytesToBits(): void
    {
        $data = new Data(1, 'B');
        $bits = $data->to('b');

        $this->assertInstanceOf(Data::class, $bits);
        $this->assertSame(8.0, $bits->value);
        $this->assertSame('b', $bits->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting bits to bytes.
     */
    public function testConvertBitsToByte(): void
    {
        $data = new Data(8, 'b');
        $bytes = $data->to('B');

        $this->assertSame(1.0, $bytes->value);
        $this->assertSame('B', $bytes->derivedUnit->asciiSymbol);
    }

    /**
     * Test converting bits to bytes with non-integer result.
     */
    public function testConvertBitsToBytesNonInteger(): void
    {
        $data = new Data(12, 'b');
        $bytes = $data->to('B');

        $this->assertSame(1.5, $bytes->value);
    }

    // endregion

    // region Metric prefix conversion tests

    /**
     * Test converting kilobytes to bytes.
     */
    public function testConvertKilobytesToBytes(): void
    {
        $data = new Data(1, 'kB');
        $bytes = $data->to('B');

        $this->assertSame(1000.0, $bytes->value);
    }

    /**
     * Test converting bytes to kilobytes.
     */
    public function testConvertBytesToKilobytes(): void
    {
        $data = new Data(2000, 'B');
        $kb = $data->to('kB');

        $this->assertSame(2.0, $kb->value);
    }

    /**
     * Test converting megabytes to bytes.
     */
    public function testConvertMegabytesToBytes(): void
    {
        $data = new Data(1, 'MB');
        $bytes = $data->to('B');

        $this->assertSame(1000000.0, $bytes->value);
    }

    /**
     * Test converting bytes to megabytes.
     */
    public function testConvertBytesToMegabytes(): void
    {
        $data = new Data(5000000, 'B');
        $mb = $data->to('MB');

        $this->assertSame(5.0, $mb->value);
    }

    /**
     * Test converting gigabytes to megabytes.
     */
    public function testConvertGigabytesToMegabytes(): void
    {
        $data = new Data(1, 'GB');
        $mb = $data->to('MB');

        $this->assertSame(1000.0, $mb->value);
    }

    /**
     * Test converting megabytes to gigabytes.
     */
    public function testConvertMegabytesToGigabytes(): void
    {
        $data = new Data(2500, 'MB');
        $gb = $data->to('GB');

        $this->assertSame(2.5, $gb->value);
    }

    /**
     * Test converting terabytes to gigabytes.
     */
    public function testConvertTerabytesToGigabytes(): void
    {
        $data = new Data(1, 'TB');
        $gb = $data->to('GB');

        $this->assertSame(1000.0, $gb->value);
    }

    /**
     * Test converting kilobits to bits.
     */
    public function testConvertKilobitsToBits(): void
    {
        $data = new Data(1, 'kb');
        $bits = $data->to('b');

        $this->assertSame(1000.0, $bits->value);
    }

    /**
     * Test converting megabits to kilobits.
     */
    public function testConvertMegabitsToKilobits(): void
    {
        $data = new Data(1, 'Mb');
        $kb = $data->to('kb');

        $this->assertSame(1000.0, $kb->value);
    }

    // endregion

    // region Binary prefix conversion tests

    /**
     * Test converting kibibytes to bytes.
     */
    public function testConvertKibibytesToBytes(): void
    {
        $data = new Data(1, 'KiB');
        $bytes = $data->to('B');

        $this->assertSame(1024.0, $bytes->value);
    }

    /**
     * Test converting bytes to kibibytes.
     */
    public function testConvertBytesToKibibytes(): void
    {
        $data = new Data(2048, 'B');
        $kib = $data->to('KiB');

        $this->assertSame(2.0, $kib->value);
    }

    /**
     * Test converting mebibytes to bytes.
     */
    public function testConvertMebibytesToBytes(): void
    {
        $data = new Data(1, 'MiB');
        $bytes = $data->to('B');

        // 1 MiB = 1024 * 1024 = 1048576 bytes
        $this->assertSame(1048576.0, $bytes->value);
    }

    /**
     * Test converting bytes to mebibytes.
     */
    public function testConvertBytesToMebibytes(): void
    {
        $data = new Data(2097152, 'B');
        $mib = $data->to('MiB');

        // 2097152 = 2 * 1024 * 1024
        $this->assertSame(2.0, $mib->value);
    }

    /**
     * Test converting gibibytes to mebibytes.
     */
    public function testConvertGibibytesToMebibytes(): void
    {
        $data = new Data(1, 'GiB');
        $mib = $data->to('MiB');

        $this->assertSame(1024.0, $mib->value);
    }

    /**
     * Test converting tebibytes to gibibytes.
     */
    public function testConvertTebibytesToGibibytes(): void
    {
        $data = new Data(1, 'TiB');
        $gib = $data->to('GiB');

        $this->assertSame(1024.0, $gib->value);
    }

    /**
     * Test converting kibibits to bits.
     */
    public function testConvertKibibitsToBits(): void
    {
        $data = new Data(1, 'Kib');
        $bits = $data->to('b');

        $this->assertSame(1024.0, $bits->value);
    }

    // endregion

    // region Cross-prefix conversion tests

    /**
     * Test converting kilobytes to kibibytes.
     */
    public function testConvertKilobytesToKibibytes(): void
    {
        $data = new Data(1, 'kB');
        $kib = $data->to('KiB');

        // 1 kB = 1000 B, 1 KiB = 1024 B
        // 1 kB = 1000/1024 KiB ≈ 0.9765625 KiB
        $this->assertApproxEqual(1000.0 / 1024.0, $kib->value);
    }

    /**
     * Test converting kibibytes to kilobytes.
     */
    public function testConvertKibibytesToKilobytes(): void
    {
        $data = new Data(1, 'KiB');
        $kb = $data->to('kB');

        // 1 KiB = 1024 B = 1.024 kB
        $this->assertSame(1.024, $kb->value);
    }

    /**
     * Test converting megabytes to mebibytes.
     */
    public function testConvertMegabytesToMebibytes(): void
    {
        $data = new Data(1, 'MB');
        $mib = $data->to('MiB');

        // 1 MB = 1000000 B, 1 MiB = 1048576 B
        // 1 MB = 1000000/1048576 MiB ≈ 0.953674 MiB
        $this->assertApproxEqual(1000000.0 / 1048576.0, $mib->value);
    }

    /**
     * Test converting mebibytes to megabytes.
     */
    public function testConvertMebibytesToMegabytes(): void
    {
        $data = new Data(1, 'MiB');
        $mb = $data->to('MB');

        // 1 MiB = 1048576 B = 1.048576 MB
        $this->assertSame(1.048576, $mb->value);
    }

    /**
     * Test converting gigabytes to gibibytes.
     */
    public function testConvertGigabytesToGibibytes(): void
    {
        $data = new Data(1, 'GB');
        $gib = $data->to('GiB');

        // 1 GB = 1e9 B, 1 GiB = 1024^3 = 1073741824 B
        // 1 GB = 1e9/1073741824 GiB ≈ 0.931323 GiB
        $this->assertApproxEqual(1e9 / 1073741824.0, $gib->value);
    }

    /**
     * Test converting gibibytes to gigabytes.
     */
    public function testConvertGibibytesToGigabytes(): void
    {
        $data = new Data(1, 'GiB');
        $gb = $data->to('GB');

        // 1 GiB = 1073741824 B = 1.073741824 GB
        $this->assertSame(1.073741824, $gb->value);
    }

    // endregion

    // region Bits to bytes with prefixes

    /**
     * Test converting megabits to megabytes.
     */
    public function testConvertMegabitsToMegabytes(): void
    {
        $data = new Data(8, 'Mb');
        $mb = $data->to('MB');

        // 8 Mb = 8 * 1e6 bits = 8e6 / 8 bytes = 1e6 bytes = 1 MB
        $this->assertSame(1.0, $mb->value);
    }

    /**
     * Test converting megabytes to megabits.
     */
    public function testConvertMegabytesToMegabits(): void
    {
        $data = new Data(1, 'MB');
        $mb = $data->to('Mb');

        // 1 MB = 1e6 bytes = 8e6 bits = 8 Mb
        $this->assertSame(8.0, $mb->value);
    }

    /**
     * Test converting gigabits to gigabytes.
     */
    public function testConvertGigabitsToGigabytes(): void
    {
        $data = new Data(8, 'Gb');
        $gb = $data->to('GB');

        $this->assertSame(1.0, $gb->value);
    }

    // endregion

    // region Addition tests

    /**
     * Test adding bytes to bytes.
     */
    public function testAddBytesToBytes(): void
    {
        $a = new Data(500, 'B');
        $b = new Data(300, 'B');
        $result = $a->add($b);

        $this->assertInstanceOf(Data::class, $result);
        $this->assertSame(800.0, $result->value);
        $this->assertSame('B', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding bytes to kilobytes.
     */
    public function testAddBytesToKilobytes(): void
    {
        $a = new Data(1, 'kB');
        $b = new Data(500, 'B');
        $result = $a->add($b);

        $this->assertInstanceOf(Data::class, $result);
        $this->assertSame(1.5, $result->value);
        $this->assertSame('kB', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding megabytes to gigabytes.
     */
    public function testAddMegabytesToGigabytes(): void
    {
        $a = new Data(1, 'GB');
        $b = new Data(500, 'MB');
        $result = $a->add($b);

        $this->assertSame(1.5, $result->value);
        $this->assertSame('GB', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding kibibytes to kilobytes (cross-prefix).
     */
    public function testAddKibibytesToKilobytes(): void
    {
        $a = new Data(1, 'kB');
        $b = new Data(1, 'KiB');
        $result = $a->add($b);

        // 1 kB + 1 KiB = 1 kB + 1.024 kB = 2.024 kB
        $this->assertSame(2.024, $result->value);
        $this->assertSame('kB', $result->derivedUnit->asciiSymbol);
    }

    /**
     * Test adding bits to bytes.
     */
    public function testAddBitsToBytes(): void
    {
        $a = new Data(1, 'B');
        $b = new Data(8, 'b');
        $result = $a->add($b);

        // 1 B + 8 b = 1 B + 1 B = 2 B
        $this->assertSame(2.0, $result->value);
        $this->assertSame('B', $result->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Parse tests

    /**
     * Test parsing bytes.
     */
    public function testParseBytes(): void
    {
        $data = Data::parse('1024 B');

        $this->assertInstanceOf(Data::class, $data);
        $this->assertSame(1024.0, $data->value);
        $this->assertSame('B', $data->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing kilobytes.
     */
    public function testParseKilobytes(): void
    {
        $data = Data::parse('500 kB');

        $this->assertSame(500.0, $data->value);
        $this->assertSame('kB', $data->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing megabytes.
     */
    public function testParseMegabytes(): void
    {
        $data = Data::parse('256 MB');

        $this->assertSame(256.0, $data->value);
        $this->assertSame('MB', $data->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing gigabytes.
     */
    public function testParseGigabytes(): void
    {
        $data = Data::parse('2 GB');

        $this->assertSame(2.0, $data->value);
        $this->assertSame('GB', $data->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing kibibytes.
     */
    public function testParseKibibytes(): void
    {
        $data = Data::parse('512 KiB');

        $this->assertSame(512.0, $data->value);
        $this->assertSame('KiB', $data->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing mebibytes.
     */
    public function testParseMebibytes(): void
    {
        $data = Data::parse('128 MiB');

        $this->assertSame(128.0, $data->value);
        $this->assertSame('MiB', $data->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing bits.
     */
    public function testParseBits(): void
    {
        $data = Data::parse('64 b');

        $this->assertSame(64.0, $data->value);
        $this->assertSame('b', $data->derivedUnit->asciiSymbol);
    }

    /**
     * Test parsing megabits.
     */
    public function testParseMegabits(): void
    {
        $data = Data::parse('100 Mb');

        $this->assertSame(100.0, $data->value);
        $this->assertSame('Mb', $data->derivedUnit->asciiSymbol);
    }

    // endregion

    // region Static convert method tests

    /**
     * Test static convert method for bytes to kilobytes.
     */
    public function testStaticConvertBytesToKilobytes(): void
    {
        $value = Data::convert(1000, 'B', 'kB');

        $this->assertSame(1.0, $value);
    }

    /**
     * Test static convert method for kilobytes to kibibytes.
     */
    public function testStaticConvertKilobytesToKibibytes(): void
    {
        $value = Data::convert(1, 'kB', 'KiB');

        $this->assertApproxEqual(1000.0 / 1024.0, $value);
    }

    /**
     * Test static convert method for bytes to bits.
     */
    public function testStaticConvertBytesToBits(): void
    {
        $value = Data::convert(1, 'B', 'b');

        $this->assertSame(8.0, $value);
    }

    // endregion

    // region Practical examples

    /**
     * Test file size conversion (practical example).
     */
    public function testFileSizeConversion(): void
    {
        // A 4.7 GB DVD
        $dvd = new Data(4.7, 'GB');

        $mb = $dvd->to('MB');
        $gib = $dvd->to('GiB');

        $this->assertSame(4700.0, $mb->value);
        $this->assertApproxEqual(4.7e9 / 1073741824.0, $gib->value);
    }

    /**
     * Test network speed conversion (practical example).
     *
     * Uses Quantity::create() since there's no DataRate quantity type for dimension DT-1.
     */
    public function testNetworkSpeedConversion(): void
    {
        // 100 Mbps network speed - how many MB/s?
        $speed = Quantity::create(100, 'Mb/s');
        $mbytesPerSec = $speed->to('MB/s');

        // 100 Mb/s = 100 * 1e6 bits/s = 1e8 bits/s = 1e8/8 bytes/s = 12.5e6 bytes/s = 12.5 MB/s
        $this->assertInstanceOf(Quantity::class, $mbytesPerSec);
        $this->assertSame(12.5, $mbytesPerSec->value);
        $this->assertSame('MB/s', $mbytesPerSec->derivedUnit->asciiSymbol);
    }

    /**
     * Test RAM size conversion (practical example).
     */
    public function testRamSizeConversion(): void
    {
        // 16 GiB of RAM
        $ram = new Data(16, 'GiB');

        $gb = $ram->to('GB');
        $mb = $ram->to('MB');

        // 16 GiB = 16 * 1024^3 bytes = 17179869184 bytes
        $this->assertApproxEqual(16 * 1.073741824, $gb->value);
        $this->assertApproxEqual(16 * 1073.741824, $mb->value);
    }

    /**
     * Test converting zero data.
     */
    public function testConvertZeroData(): void
    {
        $data = new Data(0, 'GB');
        $mb = $data->to('MB');

        $this->assertSame(0.0, $mb->value);
    }

    // endregion

    // region toSi() and autoPrefix() tests

    /**
     * Test toSi() converts kilobytes to bytes.
     */
    public function testToSiFromKilobytes(): void
    {
        $data = new Data(5, 'kB');
        $si = $data->toSi(true, false);

        $this->assertSame(5000.0, $si->value);
        $this->assertSame('B', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test toSi() converts megabytes to bytes.
     */
    public function testToSiFromMegabytes(): void
    {
        $data = new Data(2, 'MB');
        $si = $data->toSi(true, false);

        $this->assertSame(2000000.0, $si->value);
        $this->assertSame('B', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test toSi() converts kibibytes to bytes.
     */
    public function testToSiFromKibibytes(): void
    {
        $data = new Data(1, 'KiB');
        $si = $data->toSi(true, false);

        $this->assertSame(1024.0, $si->value);
        $this->assertSame('B', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test toSi() converts megabits to bytes (SI base for data).
     */
    public function testToSiFromMegabits(): void
    {
        $data = new Data(1, 'Mb');
        $si = $data->toSi(true, false);

        // 1 Mb = 1,000,000 bits = 125,000 bytes
        $this->assertSame(125000.0, $si->value);
        $this->assertSame('B', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test toSi() with autoPrefix on large byte value.
     */
    public function testToSiWithAutoPrefixLargeBytes(): void
    {
        $data = new Data(5000000, 'B');
        $si = $data->toSi(autoPrefix: true);

        $this->assertSame(5.0, $si->value);
        $this->assertSame('MB', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test toSi() with autoPrefix on gigabytes.
     */
    public function testToSiWithAutoPrefixFromGigabytes(): void
    {
        $data = new Data(2.5, 'GB');
        $si = $data->toSi(autoPrefix: true);

        $this->assertSame(2.5, $si->value);
        $this->assertSame('GB', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test toSi() with autoPrefix converts gibibytes appropriately.
     */
    public function testToSiWithAutoPrefixFromGibibytes(): void
    {
        $data = new Data(1, 'GiB');
        $si = $data->toSi(autoPrefix: true);

        // 1 GiB = 1073741824 bytes ≈ 1.074 GB
        $this->assertApproxEqual(1.073741824, $si->value);
        $this->assertSame('GB', $si->derivedUnit->asciiSymbol);
    }

    /**
     * Test autoPrefix() on bytes converts to kilobytes.
     */
    public function testAutoPrefixBytesToKilobytes(): void
    {
        $data = new Data(5000, 'B');
        $prefixed = $data->autoPrefix();

        $this->assertSame(5.0, $prefixed->value);
        $this->assertSame('kB', $prefixed->derivedUnit->asciiSymbol);
    }

    /**
     * Test autoPrefix() on bytes converts to megabytes.
     */
    public function testAutoPrefixBytesToMegabytes(): void
    {
        $data = new Data(5000000, 'B');
        $prefixed = $data->autoPrefix();

        $this->assertSame(5.0, $prefixed->value);
        $this->assertSame('MB', $prefixed->derivedUnit->asciiSymbol);
    }

    /**
     * Test autoPrefix() on bytes converts to gigabytes.
     */
    public function testAutoPrefixBytesToGigabytes(): void
    {
        $data = new Data(5000000000, 'B');
        $prefixed = $data->autoPrefix();

        $this->assertSame(5.0, $prefixed->value);
        $this->assertSame('GB', $prefixed->derivedUnit->asciiSymbol);
    }

    /**
     * Test autoPrefix() on kilobytes stays as kilobytes when optimal.
     */
    public function testAutoPrefixKilobytesStaysKilobytes(): void
    {
        $data = new Data(500, 'kB');
        $prefixed = $data->autoPrefix();

        $this->assertSame(500.0, $prefixed->value);
        $this->assertSame('kB', $prefixed->derivedUnit->asciiSymbol);
    }

    /**
     * Test autoPrefix() on kilobytes converts to megabytes.
     */
    public function testAutoPrefixKilobytesToMegabytes(): void
    {
        $data = new Data(5000, 'kB');
        $prefixed = $data->autoPrefix();

        $this->assertSame(5.0, $prefixed->value);
        $this->assertSame('MB', $prefixed->derivedUnit->asciiSymbol);
    }

    /**
     * Test autoPrefix() on small byte value stays as bytes.
     */
    public function testAutoPrefixSmallBytesStaysBytes(): void
    {
        $data = new Data(500, 'B');
        $prefixed = $data->autoPrefix();

        $this->assertSame(500.0, $prefixed->value);
        $this->assertSame('B', $prefixed->derivedUnit->asciiSymbol);
    }

    /**
     * Test autoPrefix() on bits.
     */
    public function testAutoPrefixBitsToMegabits(): void
    {
        $data = new Data(5000000, 'b');
        $prefixed = $data->autoPrefix();

        $this->assertSame(5.0, $prefixed->value);
        $this->assertSame('Mb', $prefixed->derivedUnit->asciiSymbol);
    }

    /**
     * Test autoPrefix() on data rate (compound unit).
     */
    public function testAutoPrefixDataRate(): void
    {
        // 1000000 bits per second = 1 Mb/s
        $rate = Quantity::create(1000000, 'b/s');
        $prefixed = $rate->autoPrefix();

        $this->assertSame(1.0, $prefixed->value);
        $this->assertSame('Mb/s', $prefixed->derivedUnit->asciiSymbol);
    }

    /**
     * Test autoPrefix() on data rate with larger value.
     */
    public function testAutoPrefixDataRateLarge(): void
    {
        // 10 Gbps in b/s
        $rate = Quantity::create(10000000000, 'b/s');
        $prefixed = $rate->autoPrefix();

        $this->assertSame(10.0, $prefixed->value);
        $this->assertSame('Gb/s', $prefixed->derivedUnit->asciiSymbol);
    }

    // endregion
}
