<?php

declare(strict_types=1);

namespace Galaxon\Quantities\Exceptions;

use DomainException;
use Throwable;

/**
 * Exception thrown when a unit symbol cannot be resolved to a known unit.
 *
 * This exception is used when a string is provided as a unit symbol, but no matching unit is found
 * in the unit registry. This may indicate a typo, an unsupported unit, or that the required system
 * of units has not been loaded.
 */
class UnknownUnitException extends DomainException
{
    /**
     * Create a new UnknownUnitException.
     *
     * @param string $unit The unrecognised unit symbol.
     * @param string $message Optional custom message. If empty, a default message is generated.
     * @param int $code The exception code.
     * @param Throwable|null $previous The previous throwable used for exception chaining.
     */
    public function __construct(
        public readonly string $unit,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        if ($message === '') {
            $message = "Unknown unit: '$unit'.";
        }

        parent::__construct($message, $code, $previous);
    }
}
