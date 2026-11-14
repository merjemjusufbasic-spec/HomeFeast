<?php
require_once __DIR__ . '/ApiException.php';
require_once __DIR__ . '/../dao/BaseDao.php';

/**
 * Shared business logic for every entity service.
 *
 * - Services are the ONLY layer that talks to the DAOs.
 * - Each child declares `$rules` (what a valid payload looks like) and, if needed,
 *   overrides validate() to add entity-specific constraints.
 * - Problems are reported with ApiException (its code is the HTTP status).
 */
abstract class BaseService
{
    protected BaseDao $dao;
    /** Human readable name used in error messages, e.g. "Recipe" */
    protected string $entityName = 'Record';
    /**
     * Validation rules per field. Supported keys:
     *   type: string | email | int | number | enum
     *   required (checked on create only), nullable, min, max, values (enum)
     * For string/email min/max are LENGTHS, for int/number they are VALUES.
     */
    protected array $rules = [];

    public function __construct(BaseDao $dao)
    {
        $this->dao = $dao;
    }

    public function getAll(): array
    {
        return $this->dao->getAll();
    }

    public function getById(int $id): array
    {
        $row = $this->dao->getById($id);
        if ($row === null) {
            throw new ApiException($this->entityName . ' not found.', 404);
        }
        return $row;
    }

    public function create(array $data): array
    {
        $clean = $this->validate($data, false);
        $id = $this->dao->insert($clean);
        return $this->getById($id);
    }

    /** Partial update: only the fields that are sent are validated and changed. */
    public function update(int $id, array $data): array
    {
        $this->getById($id);                       // 404 if it does not exist
        $clean = $this->validate($data, true, $id);
        if (empty($clean)) {
            throw new ApiException('No valid fields to update.', 400);
        }
        $this->dao->update($id, $clean);
        return $this->getById($id);
    }

    public function delete(int $id): void
    {
        $this->getById($id);                       // 404 if it does not exist
        $this->dao->delete($id);
    }

    /**
     * Checks $data against $this->rules and returns only the clean, typed fields.
     * Throws ApiException(400) listing every invalid field.
     */
    protected function validate(array $data, bool $isUpdate, ?int $id = null): array
    {
        $clean  = [];
        $errors = [];

        foreach ($this->rules as $field => $rule) {
            if (!array_key_exists($field, $data)) {
                if (!$isUpdate && !empty($rule['required'])) {
                    $errors[$field] = "$field is required.";
                }
                continue;
            }

            $value = $data[$field];
            if ($value === null || (is_string($value) && trim($value) === '')) {
                if (!empty($rule['nullable'])) {
                    $clean[$field] = null;
                } else {
                    $errors[$field] = "$field must not be empty.";
                }
                continue;
            }

            [$error, $converted] = $this->checkRule($field, $value, $rule);
            if ($error !== null) {
                $errors[$field] = $error;
            } else {
                $clean[$field] = $converted;
            }
        }

        $this->fail($errors);
        return $clean;
    }

    /** Throws a 400 ApiException if there are any errors. */
    protected function fail(array $errors): void
    {
        if (!empty($errors)) {
            throw new ApiException('Validation failed.', 400, $errors);
        }
    }

    /** @return array [error message or null, converted value] */
    private function checkRule(string $field, $value, array $rule): array
    {
        $type = $rule['type'] ?? 'string';
        $min  = $rule['min'] ?? null;
        $max  = $rule['max'] ?? null;

        switch ($type) {
            case 'string':
            case 'email':
                if (!is_string($value)) {
                    return ["$field must be text.", null];
                }
                $value = trim($value);
                $len = mb_strlen($value);
                if ($type === 'email') {
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        return ["$field must be a valid email address.", null];
                    }
                    $value = strtolower($value);
                }
                if ($min !== null && $len < $min) {
                    return ["$field must be at least $min characters.", null];
                }
                if ($max !== null && $len > $max) {
                    return ["$field must be at most $max characters.", null];
                }
                return [null, $value];

            case 'int':
                if (is_string($value) && preg_match('/^-?\d+$/', $value)) {
                    $value = (int) $value;
                }
                if (!is_int($value)) {
                    return ["$field must be a whole number.", null];
                }
                break;

            case 'number':
                if (!is_int($value) && !is_float($value) && !(is_string($value) && is_numeric($value))) {
                    return ["$field must be a number.", null];
                }
                $value = (float) $value;
                break;

            case 'enum':
                if (!in_array($value, $rule['values'], true)) {
                    return ["$field must be one of: " . implode(', ', $rule['values']) . '.', null];
                }
                return [null, $value];

            default:
                return ["$field has an unknown rule type.", null];
        }

        // int / number range check
        if ($min !== null && $value < $min) {
            return ["$field must be at least $min.", null];
        }
        if ($max !== null && $value > $max) {
            return ["$field must be at most $max.", null];
        }
        return [null, $value];
    }
}
