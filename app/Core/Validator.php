<?php

namespace App\Core;

/**
 * Validates request data using simple reusable rules.
 */
class Validator
{
    private array $errors = [];

    public function __construct(private array $data)
    {
    }

    /**
     * Validate fields with pipe-separated rules.
     */
    public function validate(array $rules): bool
    {
        foreach ($rules as $field => $fieldRules) {
            foreach (explode('|', $fieldRules) as $rule) {
                $this->applyRule($field, $rule);
            }
        }

        return $this->errors === [];
    }

    /**
     * Return all validation errors.
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Return validation errors as a flat list of messages.
     */
    public function messages(): array
    {
        return array_merge(...array_values($this->errors ?: [[]]));
    }

    /**
     * Add a custom validation error from controller-level checks.
     */
    public function add(string $field, string $message): void
    {
        $this->addError($field, $message);
    }

    /**
     * Apply one validation rule to one field.
     */
    private function applyRule(string $field, string $rule): void
    {
        [$ruleName, $parameter] = array_pad(explode(':', $rule, 2), 2, null);
        $value = trim((string) ($this->data[$field] ?? ''));

        if ($ruleName === 'required' && $value === '') {
            $this->addError($field, 'This field is required.');
        }

        if ($ruleName === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'Enter a valid email address.');
        }

        if ($ruleName === 'username' && $value !== '' && !preg_match('/^[A-Za-z0-9_]+$/', $value)) {
            $this->addError($field, 'Use letters, numbers, and underscores only.');
        }

        if ($ruleName === 'min' && $value !== '' && strlen($value) < (int) $parameter) {
            $this->addError($field, 'Enter at least ' . (int) $parameter . ' characters.');
        }

        if ($ruleName === 'max' && $value !== '' && strlen($value) > (int) $parameter) {
            $this->addError($field, 'Enter no more than ' . (int) $parameter . ' characters.');
        }

        if ($ruleName === 'same' && $value !== (string) ($this->data[$parameter] ?? '')) {
            $this->addError($field, 'This field must match ' . $parameter . '.');
        }
    }

    /**
     * Register one field validation error.
     */
    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
}
