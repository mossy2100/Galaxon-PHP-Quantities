<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Exceptions;

use DomainException;
use Galaxon\Quantities\Services\QuantityTypeService;
use Throwable;

/**
 * Exception thrown when an operation requires matching dimensions but receives different ones.
 *
 * This exception is used when attempting to convert, compare, or combine quantities whose
 * dimensions are incompatible (e.g. comparing a Length with a Mass).
 */
class DimensionMismatchException extends DomainException
{
    /**
     * Create a new DimensionMismatchException.
     *
     * @param ?string $dimension1 The first dimension code (e.g. 'L', 'M', 'LT-2').
     * @param ?string $dimension2 The second dimension code.
     * @param string $message Optional custom message. If empty, a default message is generated.
     * @param int $code The exception code.
     * @param Throwable|null $previous The previous throwable used for exception chaining.
     */
    public function __construct(
        public readonly ?string $dimension1,
        public readonly ?string $dimension2,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        if ($message === '') {
            $message = self::buildMessage($dimension1, $dimension2);
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * Build a descriptive message including quantity type names where possible.
     *
     * @param ?string $dimension1 The first dimension code.
     * @param ?string $dimension2 The second dimension code.
     * @return string The formatted error message.
     */
    private static function buildMessage(?string $dimension1, ?string $dimension2): string
    {
        if ($dimension1 === null) {
            $desc1 = 'null';
        } else {
            $name1 = QuantityTypeService::getByDimension($dimension1)?->name;
            $desc1 = $name1 !== null ? "'$dimension1' ($name1)" : "'$dimension1'";
        }

        if ($dimension2 === null) {
            $desc2 = 'null';
        } else {
            $name2 = QuantityTypeService::getByDimension($dimension2)?->name;
            $desc2 = $name2 !== null ? "'$dimension2' ($name2)" : "'$dimension2'";
        }

        return "Dimension mismatch: $desc1 and $desc2.";
    }
}
