<?php

declare(strict_types=1);

namespace Kkxdev\PhonePe\Exceptions;

/**
 * Validation Exception
 *
 * Thrown when DTO validation fails before API request.
 */
class ValidationException extends PhonePeException
{
    protected array $errors;

    public function __construct(
        string $message,
        array $errors = [],
        ?\Throwable $previous = null
    ) {
        $this->errors = $errors;
        parent::__construct($message, 0, $previous, ['errors' => $errors]);
    }

    /**
     * Get validation errors
     *
     * @return array<string, string[]>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Create from field errors
     *
     * @param array<string, string[]> $errors Field => error messages
     * @return static
     */
    public static function fromErrors(array $errors): static
    {
        $message = 'Validation failed: ' . implode(', ', array_map(
            fn($field, $msgs) => "{$field}: " . implode(', ', $msgs),
            array_keys($errors),
            array_values($errors)
        ));

        return new static($message, $errors);
    }

    /**
     * Create for missing required field
     *
     * @param string $field
     * @return static
     */
    public static function missingField(string $field): static
    {
        return new static(
            "Required field '{$field}' is missing",
            [$field => ['This field is required']]
        );
    }

    /**
     * Create for invalid field value
     *
     * @param string $field
     * @param string $reason
     * @return static
     */
    public static function invalidField(string $field, string $reason): static
    {
        return new static(
            "Invalid value for field '{$field}': {$reason}",
            [$field => [$reason]]
        );
    }
}
