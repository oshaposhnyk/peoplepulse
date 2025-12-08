<?php

declare(strict_types=1);

namespace Application\Exceptions;

/**
 * Validation exception
 * 
 * Thrown when input data validation fails.
 */
class ValidationException extends ApplicationException
{
    /**
     * @param array<string, array<string>> $errors
     */
    public function __construct(
        string $message,
        private array $errors = [],
        int $code = 422
    ) {
        parent::__construct($message, $code);
    }

    /**
     * Get validation errors
     * 
     * @return array<string, array<string>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Create validation exception with errors
     * 
     * @param array<string, array<string>> $errors
     */
    public static function withErrors(array $errors, string $message = 'Validation failed'): self
    {
        return new self($message, $errors);
    }
}

