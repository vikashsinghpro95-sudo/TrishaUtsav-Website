<?php
/**
 * Validator Helper Class
 */

class Validator {
    private array $data = [];
    private array $errors = [];
    private PDO $db;

    /**
     * Constructor
     *
     * @param array $data Input data to validate
     */
    public function __construct(array $data) {
        $this->data = $data;
        $this->db = Database::getInstance();
    }

    /**
     * Run validation rules against input data
     *
     * Rules syntax example:
     * [
     *     'email' => ['required', 'email', 'unique:users,email'],
     *     'password' => ['required', 'minLength:8']
     * ]
     *
     * @param array $rules List of validation rules
     * @return array Array of error messages, grouped by field
     */
    public function validate(array $rules): array {
        foreach ($rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;

            foreach ($fieldRules as $rule) {
                // Split rules with parameters, e.g. "minLength:8"
                $parts = explode(':', $rule, 2);
                $ruleName = $parts[0];
                $param = $parts[1] ?? null;

                switch ($ruleName) {
                    case 'required':
                        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                            $this->addError($field, "The " . str_replace('_', ' ', $field) . " field is required.");
                        }
                        break;

                    case 'email':
                        if ($value !== null && $value !== '') {
                            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                $this->addError($field, "The " . str_replace('_', ' ', $field) . " field must be a valid email address.");
                            }
                        }
                        break;

                    case 'minLength':
                        if ($value !== null && $value !== '') {
                            if (strlen($value) < (int)$param) {
                                $this->addError($field, "The " . str_replace('_', ' ', $field) . " field must be at least $param characters.");
                            }
                        }
                        break;

                    case 'maxLength':
                        if ($value !== null && $value !== '') {
                            if (strlen($value) > (int)$param) {
                                $this->addError($field, "The " . str_replace('_', ' ', $field) . " field must not exceed $param characters.");
                            }
                        }
                        break;

                    case 'numeric':
                        if ($value !== null && $value !== '') {
                            if (!is_numeric($value)) {
                                $this->addError($field, "The " . str_replace('_', ' ', $field) . " field must be a numeric value.");
                            }
                        }
                        break;

                    case 'alpha':
                        if ($value !== null && $value !== '') {
                            if (!ctype_alpha(str_replace(' ', '', $value))) {
                                $this->addError($field, "The " . str_replace('_', ' ', $field) . " field must contain only letters.");
                            }
                        }
                        break;

                    case 'unique':
                        if ($value !== null && $value !== '') {
                            // unique:table,column,excludeId
                            $uniqueParams = explode(',', $param);
                            $table = $uniqueParams[0] ?? '';
                            $column = $uniqueParams[1] ?? $field;
                            $excludeId = $uniqueParams[2] ?? null;

                            $query = "SELECT COUNT(*) FROM `$table` WHERE `$column` = ?";
                            $queryParams = [$value];
                            if ($excludeId !== null && $excludeId !== '') {
                                $query .= " AND id != ?";
                                $queryParams[] = $excludeId;
                            }

                            $stmt = $this->db->prepare($query);
                            $stmt->execute($queryParams);
                            if ($stmt->fetchColumn() > 0) {
                                $this->addError($field, "The " . str_replace('_', ' ', $field) . " has already been taken.");
                            }
                        }
                        break;
                }
            }
        }

        return $this->errors;
    }

    /**
     * Add validation error message
     *
     * @param string $field
     * @param string $message
     */
    private function addError(string $field, string $message): void {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * Get list of errors
     *
     * @return array
     */
    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * Check if validation passes
     *
     * @return bool
     */
    public function passes(): bool {
        return empty($this->errors);
    }
}
