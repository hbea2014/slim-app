<?php

namespace SlimApp;

use SlimApp\Db\Mapper;

abstract class Validator
{

    /**
     * @var SlimApp\Db\Mapper
     */
    protected $mapper;

    /**
     * Validation rules for each fieldName (set in subclass) in form
     * [fieldName => [ruleName1 => ruleValue1, ruleName2 => ruleValue2]]
     *
     * @var array 
     */
    protected $rules;

    /**
     * Custom error messages for given fieldName and ruleName in form
     * [fieldName => [ruleName1 => customErrorMessage1, ruleName2 => customErrorMessage2]]
     *
     * @var array
     */
    protected $customErrorMessages;

    /**
     * Error messages for each fieldName in form
     * [fieldName => [customErrorMessage1, customErrorMessage2]]
     *
     * @var array
     */
    protected $errors;

    /**
     * Constructor
     *
     * @param null|SlimApp\Db\Mapper $mapper
     */
    public function __construct($mapper = null)
    {
        if (null !== $mapper) {
            $this->setMapper($mapper);
        }
    }

    /**
     * Sets the mapper
     *
     * @param SlimApp\Db\Mapper $mapper
     */
    protected function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * Retrieves the mapper
     *
     * @return null|SlimApp\Db\Mapper
     */
    protected function getMapper()
    {
        return $this->mapper;
    }

    /**
     * Gets all rules
     *
     * @throws \DomainException
     * @return array The validation rules
     */
    protected function getRules()
    {
        if (empty($this->rules)) {
            throw new \DomainException('Rules not set');
        }

        return $this->rules;
    }

    /**
     * Get custom error messages
     *
     * @return null|array
     */
    protected function getCustomErrorMessages()
    {
        return $this->customErrorMessages;
    }

    /**
     * Gets the custom error message for given field and rule names
     *
     * @param string $fieldName
     * @param string $ruleName
     * @return false|string Returns the custom error message if set, false instead
     */
    protected function getCustomErrorMessage($fieldName, $ruleName)
    {
        $messages = $this->getCustomErrorMessages();

        if ( ! empty($messages) && isset($messages[$fieldName], $messages[$fieldName][$ruleName]) ) {
            return $messages[$fieldName][$ruleName];
        }

        return false;
    }

    /**
     * Returns custom error message if set or default error message otherwise
     *
     * @param string $fieldName
     * @param string $ruleName
     * @param string $defaultErrorMessage
     * @return false|string Returns the custom error message if set, false instead
     */
    public function getErrorMessage($fieldName, $ruleName, $defaultErrorMessage)
    {
        // Check if custom error message
        $customErrorMessage = $this->getCustomErrorMessage($fieldName, $ruleName);

        // Use custom error message if any, default error message otherwise
        $errorMessage = (false !== $customErrorMessage) ? $customErrorMessage : $defaultErrorMessage;

        return $errorMessage;
    }

    /**
     * Adds an error to errors
     *
     * @param string $fieldName
     * @param string $errorMessage
     */
    protected function addError($fieldName, $errorMessage)
    {
        $this->errors[$fieldName][] = $errorMessage;
    }

    /**
     * Gets the errors (or null) for the last validation
     *
     * @return null|array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Removes previous errors
     */
    protected function resetErrors()
    {
        $this->errors = null;
    }

    /**
     * Checks if last validation was successful or not
     *
     * @return boolean
     */
    public function hasErrors()
    {
        return ! empty($this->getErrors());
    }

    /**
     * Checks if last validation was successful or not
     *
     * @return boolean
     */
    public function failed()
    {
        return ! empty($this->getErrors());
    }

    /**
     * Checks if last validation was successful or not
     *
     * @return boolean
     */
    public function passed()
    {
        return empty($this->getErrors());
    }

    /**
     * Validate the data
     *
     * @throwns \DomainException
     * @return boolean True if data valid, false otherwise
     */
    public function validate(array $data)
    {
        // Check that field names in submitted data and in rules match
        $fieldNamesNotInRules = array_diff(array_keys($data), array_keys($this->rules));

        if ( ! empty($fieldNamesNotInRules) ) {
            throw new \DomainException('Following field name(s) absent from rules: "' 
                . implode('", "', $fieldNamesNotInRules) . '"');
        }

        // Check that each field has at least one validation rule
        $fieldNamesWithoutRules = [];

        foreach ($this->rules as $fieldName => $rules) {
            if ( empty($rules) ) {
                $fieldNamesWithoutRules[] = $fieldName;
            }
        }

        if ( ! empty($fieldNamesWithoutRules) ) {
            throw new \DomainException('No rules set for the following field name(s): "' 
                . implode('", "', $fieldNamesWithoutRules) . '"');
        }

        // Remove errors from previous validation
        $this->resetErrors();

        foreach ($this->rules as $fieldName => $rules) {
            foreach ($rules as $ruleName => $ruleValue) {
                $value = $data[$fieldName];

                // Run specific validation function and get result
                if ('matches' !== $ruleName) {
                    $result = $this->$ruleName($ruleValue, $fieldName, $value);
                } else {
                    // Get the value of the field to match against
                    $matchingValue = $data[$ruleValue];

                    $result = $this->$ruleName($ruleValue, $fieldName, $value, $matchingValue);
                }

                // Add error if any
                if (true !== $result) {
                    $this->addError($fieldName, $result);
                }
            }
        }

        return $this;
    }

    /**
     * Checks if value is submitted
     *
     * @param mixed $ruleValue The value of the rule
     * @param string $fieldName The name of the field
     * @param mixed $value The value to validate
     * @return true|string Returns true if value submitted, default error message instead
     * @throws \InvalidArgumentException
     */
    public function required($ruleValue, $fieldName, $value)
    {
        if ( ! is_bool($ruleValue) ) {
            throw new \InvalidArgumentException('"required" rule must have a boolean as value');
        }

        if ( is_string($value) ) {
            $value = trim($value);
        }

        if ($ruleValue) {
            if (empty($value)) {
                $defaultErrorMessage = $fieldName . ' is required.';

                return $this->getErrorMessage($fieldName, 'required', $defaultErrorMessage);
            }
        }

        return true;
    }

    /**
     * Checks if value has at least a certain length
     *
     * @param mixed $ruleValue The value of the rule
     * @param string $fieldName The name of the field
     * @param mixed $value The value to validate
     * @return true|string Returns true if value submitted, default error message instead
     * @throws \InvalidArgumentException
     */
    public function min($ruleValue, $fieldName, $value)
    {
        if ( ! is_integer($ruleValue) || $ruleValue < 0 ) {
            throw new \InvalidArgumentException('"min" rule must have a positive integer as value');
        }

        if ( ! is_string($value) ) {
            return 'Value is not a string. Cannot check string length.';
        }

        if (strlen(trim($value)) < $ruleValue) {
            $defaultErrorMessage = $fieldName . ' must be at least ' . (string) $ruleValue . ' characters.';

            return $this->getErrorMessage($fieldName, 'min', $defaultErrorMessage);
        }

        return true;
    }

    /**
     * Checks if value does not exceed a certain length
     *
     * @param mixed $ruleValue The value of the rule
     * @param string $fieldName The name of the field
     * @param mixed $value The value to validate
     * @return true|string Returns true if value submitted, default error message instead
     * @throws \InvalidArgumentException
     */
    public function max($ruleValue, $fieldName, $value)
    {
        if ( ! is_integer($ruleValue) || $ruleValue < 0 ) {
            throw new \InvalidArgumentException('"max" rule must have a positive integer as value');
        }

        if ( ! is_string($value) ) {
            return 'Value is not a string. Cannot check string length.';
        }

        if (strlen(trim($value)) > $ruleValue) {
            $defaultErrorMessage = $fieldName . ' must be under ' . (string) $ruleValue . ' characters.';

            return $this->getErrorMessage($fieldName, 'max', $defaultErrorMessage);
        }

        return true;
    }

    /**
     * Checks if value matches another value
     *
     * @param mixed $ruleValue The value of the rule
     * @param string $fieldName The name of the field
     * @param mixed $value The value to validate
     * @return true|string Returns true if value submitted, default error message instead
     * @param string $matchingValue The value of the matching field
     * @throws \InvalidArgumentException
     */
    public function matches($ruleValue, $fieldName, $value, $matchingValue)
    {
        if ( is_string($value) ) {
            $value = trim($value);
            $matchingValue = trim($matchingValue);
        }

        if ($value !== $matchingValue) {
            $defaultErrorMessage = $fieldName . ' must match ' . $ruleValue . '.';

            return $this->getErrorMessage($fieldName, 'matches', $defaultErrorMessage);
        }

        return true;
    }

    /**
     * Checks if value is a valid email address
     *
     * @param mixed $ruleValue The value of the rule
     * @param string $fieldName The name of the field
     * @param mixed $value The value to validate
     * @return true|string Returns true if value submitted, default error message instead
     * @throws \InvalidArgumentException
     */
    public function email($ruleValue, $fieldName, $value)
    {
        if ( ! is_bool($ruleValue) ) {
            throw new \InvalidArgumentException('"email" rule must have a boolean as value');
        }

        if ( ! is_string($value) ) {
            return 'Value is not a string. Cannot check if it is a valid email address.';
        }

        if ( ! filter_var(trim($value), FILTER_VALIDATE_EMAIL) ) {
            $defaultErrorMessage = $fieldName . ' is not a valid email address.';

            return $this->getErrorMessage($fieldName, 'email', $defaultErrorMessage);
        }

        return true;
    }

    /**
     * Checks if value is one of a set of values
     *
     * @param array $ruleValue The value of the rule
     * @param string $fieldName The name of the field
     * @param mixed $value The value to validate
     * @return true|string Returns true if value submitted, default error message instead
     * @throws \DomainException
     */
    public function in(array $ruleValue, $fieldName, $value)
    {
        if (empty($ruleValue)) {
            throw new \DomainException('Empty set of values to check against.');
        }

        if ( is_string($value) ) {
            $value = trim($value);
        }

        if ( ! in_array($value, $ruleValue) ) {
            $defaultErrorMessage = $fieldName . ' does not match any of the accepted values.';

            return $this->getErrorMessage($fieldName, 'in', $defaultErrorMessage);
        }

        return true;
    }

    /**
     * Checks if value contains only alphabetic characters
     *
     * @param mixed $ruleValue The value of the rule
     * @param string $fieldName The name of the field
     * @param mixed $value The value to validate
     * @return true|string Returns true if value submitted, default error message instead
     * @throws \InvalidArgumentException
     */
    public function alpha($ruleValue, $fieldName, $value)
    {
        if ( ! is_bool($ruleValue) ) {
            throw new \InvalidArgumentException('"alpha" rule must have a boolean as value');
        }

        if ( ! is_string($value) ) {
            return 'Value is not a string. Cannot check if it contains only alphabetic characters.';
        }

        if ( ! ctype_alpha(trim($value)) ) {
            $defaultErrorMessage = $fieldName . ' does not contain only alphabetic characters.';

            return $this->getErrorMessage($fieldName, 'alpha', $defaultErrorMessage);
        }

        return true;
    }

    /**
     * Checks if value contains only alphanumeric characters
     *
     * @param mixed $ruleValue The value of the rule
     * @param string $fieldName The name of the field
     * @param mixed $value The value to validate
     * @return true|string Returns true if value submitted, default error message instead
     * @throws \InvalidArgumentException
     */
    public function alphanum($ruleValue, $fieldName, $value)
    {
        if ( ! is_bool($ruleValue) ) {
            throw new \InvalidArgumentException('"alphanum" rule must have a boolean as value');
        }

        if ( ! is_string($value) ) {
            return 'Value is not a string. Cannot check if it contains only alphanumeric characters.';
        }

        if ( ! ctype_alnum(trim($value)) ) {
            $defaultErrorMessage = $fieldName . ' does not contain only alphanumeric characters.';

            return $this->getErrorMessage($fieldName, 'alphanum', $defaultErrorMessage);
        }

        return true;
    }

    /**
     * Checks if value is a valid url
     *
     * @param array $ruleValue The value of the rule
     * @param string $fieldName The name of the field
     * @param mixed $value The value to validate
     * @return true|string Returns true if value submitted, default error message instead
     * @throws \InvalidArgumentException
     */
    public function url(array $ruleValue, $fieldName, $value)
    {
        if ( ! is_string($value) ) {
            return 'Value is not a string. Cannot check if it is a valid url.';
        }

        $ruleValueFlags = [
            // URL must...
            // ... be RFC compliant, eg. http://example.com
            'schemeRequired' => FILTER_FLAG_SCHEME_REQUIRED, 
            // ... contain a host, eg. http://example.com
            'hostRequired' => FILTER_FLAG_HOST_REQUIRED,
            // ... contain a path after the domain host, eg. http://example.com/, http://example.com/whatever
            'pathRequired' => FILTER_FLAG_PATH_REQUIRED, 
            // ... contain a query string, eg. http://example.com/?field=value
            'queryRequired' => FILTER_FLAG_QUERY_REQUIRED 
        ];

        $flags = null;

        if ( ! empty($ruleValue) ) {
            //foreach ($ruleValue as $rule) {
            for ($i = 0; $i < count($ruleValue); $i++) {
                if ( in_array($ruleValue[$i], array_keys($ruleValueFlags)) ) {
                    if (0 === $i) {
                        $flags = $ruleValueFlags[$ruleValue[$i]];
                    } else {
                        $flags = $flags | $ruleValueFlags[$ruleValue[$i]];
                    }
                }
            }
        }

        if ( ! filter_var(trim($value), FILTER_VALIDATE_URL, ['flags' => $flags]) ) {
            $defaultErrorMessage = $fieldName . ' is not a valid url.';

            return $this->getErrorMessage($fieldName, 'url', $defaultErrorMessage);
        }

        return true;
    }

    /**
     * Checks if value contains only uniqueeric characters
     *
     * @param mixed $ruleValue The value of the rule
     * @param string $fieldName The name of the field
     * @param mixed $value The value to validate
     * @return true|string Returns true if value submitted, default error message instead
     * @throws \InvalidArgumentException
     * @throws \DomainException
     */
    public function unique($ruleValue, $fieldName, $value)
    {
        if ( ! is_string($ruleValue) ) {
            throw new \InvalidArgumentException('"unique" rule must have a string as value');
        }

        if ( null === $this->getMapper() ) {
            throw new \DomainException('No data mapper set.');
        }

        if ( null === $this->getMapper()->getDbTable() ) {
            throw new \DomainException('No DbTable set in data mapper.');
        }

        if ( null === $this->getMapper()->getModel() ) {
            throw new \DomainException('No model set in data mapper.');
        }

        // Get the name of the model set in the mapper
        $mapperModelName = get_class($this->getMapper()->getModel());

        // Check that the model name set in the rule matches the name of the model set in the mapper
        if ( $mapperModelName !== $ruleValue) {
            throw new \DomainException('Model set in Validator\'s mapper not matching model set in rule (validator: "'
                . $mapperModelName . '", rule: "' . $ruleValue . '".');
        }

        // Get the name of the model associated with the DbTable
        // Each DbTable name is basically
        $dbTableName = $this->getMapper()->getDbTable()->getTableName();
        $dbTableNameFromRuleValue = $ruleValue . 's';

        // table name is basically the model name in plural with 'Table'
        // eg. model: User, tableName: Users
        if ($dbTableName !== $dbTableNameFromRuleValue) {
            throw new \DomainException('Table name set in the Validator\'s mapper\'s not corresponding to model set in rule (validator: "'
                . $dbTableName . '", table name from rule: "' . $dbTableNameFromRuleValue 
                . '" (original ruleValue: "' . $ruleValue . '")).');
        }

        $value = trim($value);
        $where = '`' . $fieldName . '` = ' . $value;

        if (false !== $this->getMapper()->findRow($where)) {
            // Found a row for the given fieldname / value in the table
            return $fieldName . ' already exists.';
        }

        return true;
    }

}

