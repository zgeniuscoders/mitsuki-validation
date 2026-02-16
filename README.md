# Mitsuki Validators

A lightweight request validation layer built on top of Symfony Validator, designed to work seamlessly with custom HTTP
requests in PHP applications.

## Features

- Abstract `RequestValidator` class that merges query, request body, and files into a single dataset.
- Easy rule definition via the `rules()` method using Symfony `Constraint` objects.
- Throws a structured `ValidationException` (HTTP 422) when validation fails.
- Clean, reusable validators for your API or web controllers.

## Installation

Install the package via Composer:

```bash
composer require mitsuki/hermite-validators
```

1. Create a validator class extending RequestValidator:

```php
use Mitsuki\Hermite\Validators\RequestValidator;
use Symfony\Component\Validator\Constraints as Assert;

class CreateUserRequest extends RequestValidator
{
    public function rules(): array
    {
        return [
            'email' => [new Assert\Email(), new Assert\NotBlank()],
            'age'   => [new Assert\Type('numeric'), new Assert\GreaterThan(17)],
        ];
    }
}

```
2. Inject request data (e.g., in tests or controllers) and validate:

```php
$request = new CreateUserRequest();
$request->initialize(
    [], // GET
    ['email' => 'user@example.com', 'age' => 25]
);

$request->validateOrFail(); // Throws ValidationException on failure
```

3. Catch and inspect validation errors:

```php
use Mitsuki\Hermite\Validators\Exceptions\ValidationException;

try {
    $request->validateOrFail();
} catch (ValidationException $e) {
    $errors = $e->getErrors();
    // $errors['email'] = 'This value is not a valid email address.'
}
```

