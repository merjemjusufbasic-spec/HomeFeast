<?php

/**
 * An error that should be shown to the API client.
 * The exception code IS the HTTP status (400, 404, 503...), so services can
 * simply `throw new ApiException('Category not found.', 404)`.
 */
class ApiException extends Exception
{
    private array $errors;

    public function __construct(string $message, int $status = 400, array $errors = [])
    {
        parent::__construct($message, $status);
        $this->errors = $errors;
    }

    public function getStatus(): int
    {
        return $this->getCode();
    }

    /** Field-level validation messages, e.g. ['email' => 'Email is not valid.'] */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
