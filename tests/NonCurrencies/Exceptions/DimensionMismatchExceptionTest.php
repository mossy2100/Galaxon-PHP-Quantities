<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\NonCurrencies\Exceptions;

use DomainException;
use Galaxon\Quantities\Exceptions\DimensionMismatchException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests for DimensionMismatchException.
 */
#[CoversClass(DimensionMismatchException::class)]
final class DimensionMismatchExceptionTest extends TestCase
{
    /**
     * Test exception extends DomainException.
     */
    public function testExtendsDomainException(): void
    {
        $exception = new DimensionMismatchException('L', 'M');
        $this->assertInstanceOf(DomainException::class, $exception);
    }

    /**
     * Test default message with known dimension names.
     */
    public function testDefaultMessageWithKnownDimensions(): void
    {
        $exception = new DimensionMismatchException('L', 'M');
        $message = $exception->getMessage();

        // Both 'L' (length) and 'M' (mass) are registered quantity types.
        $this->assertStringContainsString('length', $message);
        $this->assertStringContainsString('mass', $message);
        $this->assertStringContainsString('Dimension mismatch', $message);
    }

    /**
     * Test default message with unknown dimensions omits type names.
     */
    public function testDefaultMessageWithUnknownDimensions(): void
    {
        // Use a valid but unregistered compound dimension.
        $exception = new DimensionMismatchException('L3T', 'M2L');
        $message = $exception->getMessage();

        // Unknown dimensions should not include type names in parentheses.
        $this->assertSame("Dimension mismatch: 'L3T' and 'M2L'.", $message);
    }

    /**
     * Test default message with one known and one unknown dimension.
     */
    public function testDefaultMessageMixedDimensions(): void
    {
        $exception = new DimensionMismatchException('L', 'M2L');
        $message = $exception->getMessage();

        $this->assertStringContainsString("'L' (length)", $message);
        $this->assertStringContainsString("'M2L'", $message);
        // The unknown dimension should not have a type name in parentheses.
        $this->assertStringNotContainsString('(M2L)', $message);
    }

    /**
     * Test custom message overrides default.
     */
    public function testCustomMessage(): void
    {
        $exception = new DimensionMismatchException('L', 'M', 'Custom error.');
        $this->assertSame('Custom error.', $exception->getMessage());
    }

    /**
     * Test dimension properties are accessible.
     */
    public function testDimensionProperties(): void
    {
        $exception = new DimensionMismatchException('MLT-2', 'L');
        $this->assertSame('MLT-2', $exception->dimension1);
        $this->assertSame('L', $exception->dimension2);
    }

    /**
     * Test exception code.
     */
    public function testExceptionCode(): void
    {
        $exception = new DimensionMismatchException('L', 'M', '', 99);
        $this->assertSame(99, $exception->getCode());
    }

    /**
     * Test previous exception chaining.
     */
    public function testPreviousException(): void
    {
        $previous = new RuntimeException('root cause');
        $exception = new DimensionMismatchException('L', 'M', '', 0, $previous);
        $this->assertSame($previous, $exception->getPrevious());
    }

    /**
     * Test default message with null first dimension.
     */
    public function testDefaultMessageWithNullFirstDimension(): void
    {
        $exception = new DimensionMismatchException(null, 'L');

        $this->assertNull($exception->dimension1);
        $this->assertSame('L', $exception->dimension2);
        $this->assertSame("Dimension mismatch: null and 'L' (length).", $exception->getMessage());
    }

    /**
     * Test default message with null second dimension.
     */
    public function testDefaultMessageWithNullSecondDimension(): void
    {
        $exception = new DimensionMismatchException('L', null);

        $this->assertSame('L', $exception->dimension1);
        $this->assertNull($exception->dimension2);
        $this->assertSame("Dimension mismatch: 'L' (length) and null.", $exception->getMessage());
    }

    /**
     * Test default message with both dimensions null.
     */
    public function testDefaultMessageWithBothDimensionsNull(): void
    {
        $exception = new DimensionMismatchException(null, null);

        $this->assertNull($exception->dimension1);
        $this->assertNull($exception->dimension2);
        $this->assertSame('Dimension mismatch: null and null.', $exception->getMessage());
    }
}
