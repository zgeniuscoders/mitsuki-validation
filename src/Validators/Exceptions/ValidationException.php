<?php

namespace Mitsuki\Hermite\Validators\Exceptions;

use Mitsuki\Contracts\Validation\Exceptions\ValidationException as MitsukiValidationException;

/**
 * Custom ValidationException for the Hermite validator.
 *
 * This exception is thrown when validation fails, carrying detailed error messages
 * for each failed validation rule. It extends PHP's base Exception class and
 * implements the MitsukiValidationException contract, providing a structured way
 * to handle validation errors with HTTP 422 status code.
 *
 * @implements Mitsuki\Contracts\Validation\Exceptions\ValidationException
 */
class ValidationException extends \Exception implements MitsukiValidationException
{
    /**
     * @var array<string, array<string>> $errors
     *     Validation errors structured as field_name => [error_message1, error_message2, ...]
     */
    private array $errors;

    /**
     * Creates a new ValidationException instance.
     *
     * @param array<string, array<string>> $errors
     *     Array of validation errors where keys are field names and values are arrays
     *     of error messages for that field.
     * @param string $message
     *     Custom error message (defaults to "Unprocessable Request")
     */
    public function __construct(array $errors, string $message = "Unprocessable Request")
    {
        parent::__construct($message, 422);
        $this->errors = $errors;
    }

    /**
     * Retrieves the validation errors array.
     *
     * @return array<string, array<string>>
     *     Returns the structured validation errors.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
