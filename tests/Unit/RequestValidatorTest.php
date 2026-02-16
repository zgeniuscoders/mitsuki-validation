<?php

use Mitsuki\Hermite\Validators\Exceptions\ValidationException;
use Mitsuki\Hermite\Validators\RequestValidator;
use Symfony\Component\Validator\Constraints as Assert;

class MockUserRequest extends RequestValidator
{
    public function rules(): array
    {
        return [
            'email' => [new Assert\Email(), new Assert\NotBlank()],
            'age' => [new Assert\Type('numeric'), new Assert\GreaterThan(17)],
        ];
    }
}


/**
 * Test successful validation.
 */
test('it passes validation with valid data', function () {
    $request = new MockUserRequest();

    // Inject valid data: Email is correct and Age is over 17
    $request->initialize(
        [], // GET
        ['email' => 'zgenius@example.com', 'age' => 25, 'username' => 'mitsuki'] // POST
    );

    // Assert that no exception is thrown
    expect(/**
     * @throws ValidationException
     */ fn() => $request->validateOrFail())->not->toThrow(ValidationException::class);
});

/**
 * Test validation failure and exception throwing.
 */
test('it throws ValidationException when data is invalid', function () {
    $request = new MockUserRequest();

    // Inject invalid data: Missing email and age too low
    $request->initialize([], ['email' => 'not-an-email', 'age' => 10]);

    try {
        $request->validateOrFail();
    } catch (ValidationException $e) {
        $errors = $e->getErrors();

        // Check if the correct fields failed
        expect($errors)->toBeArray()
            ->toHaveKey('email')
            ->toHaveKey('age');

        // Ensure property path cleaning works (e.g., 'email' instead of '[email]')
        expect(key($errors))->toBe('email');

        return;
    }

    $this->fail('ValidationException was not thrown despite invalid data.');
});

/**
 * Test the data merging logic (GET + POST + FILES).
 */
test('the all() method merges query and request parameters', function () {
    $request = new MockUserRequest();

    $request->initialize(
        ['sort' => 'desc'],      // GET params
        ['title' => 'My Post']   // POST params
    );

    $allData = $request->all();

    expect($allData)
        ->toBeArray()
        ->toHaveCount(2)
        ->toHaveKey('sort', 'desc')
        ->toHaveKey('title', 'My Post');
});

/**
 * Test that getErrors() returns the captured violations.
 */
test('it provides access to errors via getErrors()', function () {
    $request = new MockUserRequest();
    $request->initialize([], ['email' => 'zgenius']);

    try {
        $request->validateOrFail();
    } catch (ValidationException $e) {
        expect($request->getErrors())->toBe($e->getErrors())
            ->toHaveKey('email');
    }
});