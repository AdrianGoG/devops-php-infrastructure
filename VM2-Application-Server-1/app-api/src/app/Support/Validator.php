<?php

namespace App\Support;

/**
 * Validation of the payload sent to POST /api/deployments.
 *
 * ------------------------------------------------------------------------
 * LEGACY WARNING (see MIGRATION.md, incompatibility #6)
 *
 * isBlank() passes its argument straight to trim() and strlen(), including when
 * it is null. Passing null to a non-nullable internal parameter is deprecated
 * from PHP 8.1 onwards, so after the upgrade every request with a missing field
 * fills the log with deprecation notices. The endpoint keeps working, which is
 * why this one is easy to miss.
 * ------------------------------------------------------------------------
 *
 * No declare(strict_types=1) here on purpose - with strict types the same call
 * would be a TypeError instead of a deprecation.
 */
class Validator
{
    /** @var array */
    private $errors = array();

    /**
     * @param array $data
     * @param array $rules field => list of rules ('required', 'max:120', 'in:a,b')
     * @return bool
     */
    public function validate(array $data, array $rules)
    {
        $this->errors = array();

        foreach ($rules as $field => $fieldRules) {
            $value = isset($data[$field]) ? $data[$field] : null;

            foreach ($fieldRules as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        return count($this->errors) === 0;
    }

    /**
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $rule
     * @return void
     */
    private function applyRule($field, $value, $rule)
    {
        if ($rule === 'required') {
            if (self::isBlank($value)) {
                $this->errors[$field][] = 'The ' . $field . ' field is required.';
            }

            return;
        }

        if (strpos($rule, 'max:') === 0) {
            $max = (int) substr($rule, 4);

            if ($value !== null && strlen((string) $value) > $max) {
                $this->errors[$field][] = 'The ' . $field . ' field may not exceed ' . $max . ' characters.';
            }

            return;
        }

        if (strpos($rule, 'in:') === 0) {
            $allowed = explode(',', substr($rule, 3));

            if ($value !== null && !in_array((string) $value, $allowed, true)) {
                $this->errors[$field][] = 'The ' . $field . ' field must be one of: ' . implode(', ', $allowed) . '.';
            }

            return;
        }

        if ($rule === 'integer') {
            if ($value !== null && !is_numeric($value)) {
                $this->errors[$field][] = 'The ' . $field . ' field must be a number.';
            }
        }
    }

    /**
     * LEGACY: null reaches trim() and strlen() directly - deprecated on PHP 8.1+.
     *
     * @param mixed $value
     * @return bool
     */
    private static function isBlank($value)
    {
        if (is_array($value)) {
            return count($value) === 0;
        }

        return strlen(trim($value)) === 0;
    }
}
