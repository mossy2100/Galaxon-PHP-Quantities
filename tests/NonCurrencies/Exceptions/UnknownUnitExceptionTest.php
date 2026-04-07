<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Tests\NonCurrencies\Exceptions;

use DomainException;
use Galaxon\Quantities\Exceptions\UnknownUnitException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests for UnknownUnitException.
 */
#[CoversClass(UnknownUnitException::class)]
final class UnknownUnitExceptionTest extends TestCase
{
    /**
     * Test exception extends DomainException.
     */
    public function testExtendsDomainException(): void
    {
        $exception = new UnknownUnitException('xyz');
        $this->assertInstanceOf(DomainException::class, $exception);
    }

    /**
     * Test default message includes the unit symbol.
     */
    public function testDefaultMessage(): void
    {
        $exception = new UnknownUnitException('xyz');
        $this->assertSame("Unknown unit: 'xyz'.", $exception->getMessage());
    }

    /**
     * Test default message with empty unit symbol.
     */
    public function testDefaultMessageWithEmptySymbol(): void
    {
        $exception = new UnknownUnitException('');
        $this->assertSame("Unknown unit: ''.", $exception->getMessage());
    }

    /**
     * Test custom message overrides default.
     */
    public function testCustomMessage(): void
    {
        $exception = new UnknownUnitException('xyz', 'Custom error message.');
        $this->assertSame('Custom error message.', $exception->getMessage());
    }

    /**
     * Test unit property is accessible.
     */
    public function testUnitProperty(): void
    {
        $exception = new UnknownUnitException('ft3');
        $this->assertSame('ft3', $exception->unit);
    }

    /**
     * Test exception code.
     */
    public function testExceptionCode(): void
    {
        $exception = new UnknownUnitException('xyz', '', 42);
        $this->assertSame(42, $exception->getCode());
    }

    /**
     * Test previous exception chaining.
     */
    public function testPreviousException(): void
    {
        $previous = new RuntimeException('root cause');
        $exception = new UnknownUnitException('xyz', '', 0, $previous);
        $this->assertSame($previous, $exception->getPrevious());
    }
}
