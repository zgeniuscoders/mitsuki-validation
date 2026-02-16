<?php

namespace Mitsuki\Hermite\Validators;

use Mitsuki\Contracts\Validation\ValidatableRequestInterface;
use Mitsuki\Hermite\Validators\Exceptions\ValidationException;
use Mitsuki\Http\Requests\Request;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Zgenius Matondo <zgeniuscoders@gmail.com>
 *
 * Abstract base class for request validation using Symfony Validator.
 *
 * Extends the base Request class and implements ValidatableRequestInterface.
 * Concrete validators must implement the `rules()` method to define validation rules.
 * Automatically merges query, request body, and file data into a single dataset for validation.
 */
abstract class RequestValidator extends Request implements ValidatableRequestInterface
{
    /**
     * @var array<string, string|array<string>> $errors
     *     Validation errors structured as field_name => error_message (or array of messages)
     */
    private array $errors = [];

    /**
     * Returns validation rules for this request.
     *
     * Concrete validator classes must implement this method to define their
     * specific validation constraints for each field.
     *
     * @return array<string, Assert\Constraint|array<Assert\Constraint>>
     *     Field names mapped to Symfony constraint objects or arrays of constraints.
     *     Example: `['email' => new Assert\Email(), 'name' => new Assert\NotBlank()]`
     */
    abstract public function rules(): array;

    /**
     * Retrieves all request data merged from multiple sources.
     *
     * Combines query parameters, POST data, and uploaded files into a single array.
     *
     * @return array<string, mixed>
     *     Complete request data including query, body, and files.
     */
    public function all(): array
    {
        return array_merge($this->query->all(), $this->request->all(), $this->files->all());
    }

    /**
     * Validates the request using defined rules or throws ValidationException.
     *
     * Creates Symfony validator instance and applies Collection constraint based
     * on rules defined in `rules()`. Throws ValidationException with structured
     * errors if validation fails.
     *
     * @throws ValidationException When validation fails
     */
    public function validateOrFail(): void
    {
        $validator = Validation::createValidator();

        $fields = [];
        foreach ($this->rules() as $name => $constraints) {
            $fields[$name] = new Assert\Required(is_array($constraints) ? $constraints : [$constraints]);
        }

        $collectionConstraint = new Assert\Collection(
            fields: $fields,
            allowExtraFields: true,
            allowMissingFields: true
        );

        $violations = $validator->validate($this->all(), $collectionConstraint);

        if (count($violations) > 0) {
            $this->errors = [];
            foreach ($violations as $violation) {
                $field = str_replace(['[', ']'], '', $violation->getPropertyPath());
                $this->errors[$field] = $violation->getMessage();
            }
            throw new ValidationException($this->getErrors());
        }
    }

    /**
     * Retrieves validation errors from last validation attempt.
     *
     * @return array<string, string|array<string>>
     *     Structured validation errors where keys are field names and values
     *     are error messages (or arrays of messages).
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
