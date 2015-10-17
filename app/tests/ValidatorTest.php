<?php

namespace SlimApp\Test;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Gets a mock of the Validator abstract class
     *
     * @return \SebastianBergmann\PeekAndPoke\Proxy
     */
    public function getMockForValidatorClass()
    {
        $mock = $this->getMockBuilder('SlimApp\Validator')
                     ->getMockForAbstractClass();
        $validator = new \SebastianBergmann\PeekAndPoke\Proxy($mock);

        return $validator;
    }

    /**
     * @test
     * @covers SlimApp\Validator::setMapper
     * @covers SlimApp\Validator::getMapper
     */
    public function setMapper_getMapper_set_and_get_mapper()
    {
        $mapper = $this->getMockBuilder('SlimApp\Db\Mapper')
                       ->getMock();
        $validator = $this->getMockForValidatorClass();

        $resultMapperNotSet = $validator->getMapper();
        $validator->setMapper($mapper);
        $resultMapperSet = $validator->getMapper();

        $this->assertNull($resultMapperNotSet);
        $this->assertSame($resultMapperSet, $mapper);
    }

    /**
     * @test
     * @covers SlimApp\Validator::__construct
     * @uses SlimApp\Validator::setMapper
     * @uses SlimApp\Validator::getMapper
     */
    public function constructor_sets_mapper_if_given()
    {
        $mapper = $this->getMockBuilder('SlimApp\Db\Mapper')
                       ->getMock();
        $mockNoMapper = $this->getMockBuilder('SlimApp\Validator')
                             ->getMockForAbstractClass();
        $mockMapper = $this->getMockBuilder('SlimApp\Validator')
                           ->setConstructorArgs([$mapper])
                           ->getMockForAbstractClass();
        $validatorNoMapper = new \SebastianBergmann\PeekAndPoke\Proxy($mockNoMapper);
        $validatorMapper = new \SebastianBergmann\PeekAndPoke\Proxy($mockMapper);

        $resultMapperSet = $validatorMapper->getMapper();
        $resultMapperNotSet = $validatorNoMapper->getMapper();

        $this->assertNull($resultMapperNotSet);
        $this->assertSame($resultMapperSet, $mapper);
    }

    /**
     * @test
     * @covers SlimApp\Validator::getRules
     * @expectedException \DomainException
     * @expectedExceptionMessage Rules not set
     */
    public function getRules_throws_DomainException_if_no_rules_set()
    {
        $validator = $this->getMockForValidatorClass();

        $validator->getRules();
    }

    /**
     * @test
     * @covers SlimApp\Validator::getRules
     */
    public function getRules_returns_rules_when_rules_set()
    {
        $validator = $this->getMockForValidatorClass();
        $rules = ['username' => ['required' => true], 'password' => ['required' => true]];
        $validator->rules = $rules;

        $result = $validator->getRules();

        $this->assertEquals($rules, $result);
    }

    /**
     * @test
     * @covers SlimApp\Validator::getCustomErrorMessages
     */
    public function getCustomErrorMessages_returns_customErrorMessages()
    {
        $validator = $this->getMockForValidatorClass();
        $customErrorMessages = ['username' => ['required' => 'Please provide a username'], 'password' => ['required' => 'You must provide a password as well']];

        $resultNoMessages = $validator->getCustomErrorMessages();

        $validator->customErrorMessages = $customErrorMessages;

        $resultMessages = $validator->getCustomErrorMessages();

        $this->assertNull($resultNoMessages);
        $this->assertEquals($customErrorMessages, $resultMessages);
    }

    /**
     * @test
     * @covers SlimApp\Validator::getCustomErrorMessage
     */
    public function getCustomErrorMessage_returns_custom_error_message_for_given_fieldName_and_ruleName_if_custom_error_message_set()
    {
        $validator = $this->getMockForValidatorClass();
        $customErrorMessage = 'Please provide a username';
        $customErrorMessages = ['username' => ['required' => $customErrorMessage]];
        $validator->customErrorMessages = $customErrorMessages;

        $resultErrorMessage = $validator->getCustomErrorMessage('username', 'required');

        $resultNoErrorMessage = $validator->getCustomErrorMessage('password', 'required');

        $this->assertEquals($customErrorMessage, $resultErrorMessage);
        $this->assertFalse($resultNoErrorMessage);
    }

    /**
     * @test
     * @covers SlimApp\Validator::getErrorMessage
     */
    public function getErrorMessage_returns_custom_error_message_if_set_default_error_message_otherwise()
    {
        $validator = $this->getMockForValidatorClass();
        $usernameCustomErrorMessage = 'Please provide a username';
        $customErrorMessages = ['username' => ['required' => $usernameCustomErrorMessage]];
        $validator->customErrorMessages = $customErrorMessages;

        $usernameDefaultErrorMessage = 'username is required';
        $resultUsernameCustomErrorMessage = $validator->getErrorMessage('username', 'required', $usernameDefaultErrorMessage);

        $passwordDefaultErrorMessage = 'password is required';
        $resultPasswordDefaultErrorMessage = $validator->getErrorMessage('password', 'required', $passwordDefaultErrorMessage);

        $this->assertEquals($usernameCustomErrorMessage, $resultUsernameCustomErrorMessage);
        $this->assertEquals($passwordDefaultErrorMessage, $resultPasswordDefaultErrorMessage);
    }

    /**
     * @test
     * @covers SlimApp\Validator::addError
     * @covers SlimApp\Validator::getErrors
     * @covers SlimApp\Validator::resetErrors
     */
    public function addError_getErrors_resetErrors_respectively_add_retrieve_reset_errors()
    {
        $validator = $this->getMockForValidatorClass();
        $usernameErrorFieldName = 'username';
        $usernameErrorMessage1 = 'username cannot be empty';
        $usernameErrorMessage2 = 'username must have at least 4 characters';
        $passwordErrorFieldName = 'password';
        $passwordErrorMessage1 = 'password cannot be empty';
        $passwordErrorMessage2 = 'password must have at least 4 characters';

        $resultNoError = $validator->getErrors();

        $validator->addError($usernameErrorFieldName, $usernameErrorMessage1);
        $validator->addError($usernameErrorFieldName, $usernameErrorMessage2);
        $validator->addError($passwordErrorFieldName, $passwordErrorMessage1);
        $validator->addError($passwordErrorFieldName, $passwordErrorMessage2);
        $resultErrors = $validator->getErrors();
        $expectedErrors = [
            $usernameErrorFieldName => [$usernameErrorMessage1, $usernameErrorMessage2],
            $passwordErrorFieldName => [$passwordErrorMessage1, $passwordErrorMessage2]
        ];

        $validator->resetErrors();
        $resultNoErrorsAnymore = $validator->resetErrors();

        $this->assertNull($resultNoError);
        $this->assertEquals($expectedErrors, $resultErrors);
        $this->assertNull($resultNoErrorsAnymore);
    }

    /**
     * @test
     * @covers SlimApp\Validator::hasErrors
     * @covers SlimApp\Validator::passed
     * @covers SlimApp\Validator::failed
     */
    public function hasErrors_failed_passed_work_as_expected()
    {
        $validator = $this->getMockForValidatorClass();

        $noErrorsHasErrors = $validator->hasErrors();
        $noErrorsPassed = $validator->passed();
        $noErrorsFailed = $validator->failed();

        $validator->errors = ['username' => ['required' => 'Please provide a username'], 'password' => ['required' => 'Please provide a password']];

        $errorsHasErrors = $validator->hasErrors();
        $errorsPassed = $validator->passed();
        $errorsFailed = $validator->failed();

        $this->assertFalse($noErrorsHasErrors);
        $this->assertTrue($errorsHasErrors);
        $this->assertTrue($noErrorsPassed);
        $this->assertFalse($errorsPassed);
        $this->assertFalse($noErrorsFailed);
        $this->assertTrue($errorsFailed);
    }

    /**
     * @test
     * @covers SlimApp\Validator::validate
     * @expectedException \DomainException
     * @expectedExceptionMessage Following field name(s) absent from rules: " 
     */
    public function validate_throws_DomainException_if_fieldNames_absent_from_rules()
    {
        $validator = $this->getMockForValidatorClass();

        $rules = ['username' => ['required' => true], 'password' => ['required' => true]];
        $validator->rules = $rules;

        $data = ['username' => 'bob', 'password' => 'bobpw', 'passwordConfirm' => 'bobpw2', 'email' => 'bob@asdf.df'];

        $validator->validate($data);
    }

    /**
     * @test
     * @covers SlimApp\Validator::validate
     * @expectedException \DomainException
     * @expectedExceptionMessage No rules set for the following field name(s): " 
     */
    public function validate_throws_DomainException_if_no_rules_set_for_fieldName()
    {
        $validator = $this->getMockForValidatorClass();

        $rules = ['username' => ['required' => true], 'password' => [], 'passwordConfirm' => ['required' => true, 'matches' => 'password']];
        $validator->rules = $rules;

        $data = ['username' => 'bob', 'password' => 'bobpw', 'passwordConfirm' => 'bobpw2'];

        $validator->validate($data);
    }

    /**
     * @test
     * @covers SlimApp\Validator::validate
     * @covers SlimApp\Validator::hasErrors
     * @covers SlimApp\Validator::passed
     * @covers SlimApp\Validator::failed
     */
    public function validate_resets_errors_and_returns_validator_object_if_no_exception_raised()
    {
        $validatorMethodsReturningTrue = ['required', 'min', 'max', 'matches', 'email', 'in', 'alpha', 'alphanum', 'url', 'unique'];
        $mock = $this->getMockBuilder('SlimApp\Validator')
                     ->setMethods($validatorMethodsReturningTrue)
                     ->getMockForAbstractClass();

        foreach ($validatorMethodsReturningTrue as $methodName) {
            $mock->method($methodName)
                 ->will($this->returnValue(true));
        }

        $validator = new \SebastianBergmann\PeekAndPoke\Proxy($mock);
        // There were errors for the previous validation
        $previousErrors = [
            'username' => 'Username already exists.',
            'password' => 'Password is too short'
        ];

        $validator->errors = $previousErrors;
        $errorsFromPreviousValidation = $validator->getErrors();

        $fromPreviousHasErrors = $validator->hasErrors();
        $fromPreviousFailed = $validator->failed();
        $fromPreviousPassed = $validator->passed();

        $rules = [
            'username' => [
                'required' => true,
                'min' => 4,
                'max' => 24,
                'alpha' => true,
                'unique' => 'User',
            ],
            'password' => [
                'required' => true,
                'min' => 4,
                'max' => 24,
                'alphanum' => true,
            ],
            'passwordConfirm' => [
                'required' => true,
                'matches' => 'password',
            ],
            'email' => [
                'required' => true,
                'email' => true,
            ],
            'website' => [
                'url' => ['schemeRequired', 'hostRequired'],
            ],
            'gender' => [
                'in' => ['male', 'female'],
            ],
        ];
        $validator->rules = $rules;

        $data = [
            'username' => null, // not checked: method mocked
            'password' => null, // not checked: method mocked
            'passwordConfirm' => null, // not checked: method mocked
            'email' => null, // not checked: method mocked
            'website' => null, // not checked: method mocked
            'gender' => null, // not checked: method mocked
        ];

        $result = $validator->validate($data);

        $errorsAfterSuccessfulValidation = $validator->getErrors();
        $afterSuccessfulValidationHasErrors = $validator->hasErrors();
        $afterSuccessfulValidationFailed = $validator->failed();
        $afterSuccessfulValidationPassed = $validator->passed();

        $this->assertEquals($previousErrors, $errorsFromPreviousValidation);
        $this->assertTrue($fromPreviousHasErrors);
        $this->assertTrue($fromPreviousFailed);
        $this->assertFalse($fromPreviousPassed);

        $this->assertInstanceOf('\SlimApp\Validator', $result);

        $this->assertNull($errorsAfterSuccessfulValidation);
        $this->assertFalse($afterSuccessfulValidationHasErrors);
        $this->assertFalse($afterSuccessfulValidationFailed);
        $this->assertTrue($afterSuccessfulValidationPassed);
    }

    /**
     * @test
     * @covers SlimApp\Validator::validate
     * @covers SlimApp\Validator::hasErrors
     * @covers SlimApp\Validator::passed
     * @covers SlimApp\Validator::failed
     */
    public function validate_adds_errors_and_returns_validator_object_if_data_not_valid_and_no_exception_raised()
    {
        $validatorMethodsReturningTrue = ['required', 'min', 'max', 'matches', 'email', 'in', 'alpha', 'alphanum', 'url', 'unique'];
        $mock = $this->getMockBuilder('SlimApp\Validator')
                     ->setMethods($validatorMethodsReturningTrue)
                     ->getMockForAbstractClass();

        foreach ($validatorMethodsReturningTrue as $methodName) {
            $mock->method($methodName)
                 ->will($this->returnValue('Error message from ' . $methodName));
        }

        $validator = new \SebastianBergmann\PeekAndPoke\Proxy($mock);
        // There were errors for the previous validation
        $previousErrors = [
            'username' => 'Username already exists.',
            'password' => 'Password is too short'
        ];

        $validator->errors = $previousErrors;
        $errorsFromPreviousValidation = $validator->getErrors();

        $fromPreviousHasErrors = $validator->hasErrors();
        $fromPreviousFailed = $validator->failed();
        $fromPreviousPassed = $validator->passed();

        $rules = [
            'username' => [
                'required' => true,
                'min' => 4,
                'max' => 24,
                'alpha' => true,
                'unique' => 'User',
            ],
            'password' => [
                'required' => true,
                'min' => 4,
                'max' => 24,
                'alphanum' => true,
            ],
            'passwordConfirm' => [
                'required' => true,
                'matches' => 'password',
            ],
            'email' => [
                'required' => true,
                'email' => true,
            ],
            'website' => [
                'url' => ['schemeRequired', 'hostRequired'],
            ],
            'gender' => [
                'in' => ['male', 'female'],
            ],
        ];
        $validator->rules = $rules;

        $data = [
            'username' => null, // not checked: method mocked
            'password' => null, // not checked: method mocked
            'passwordConfirm' => null, // not checked: method mocked
            'email' => null, // not checked: method mocked
            'website' => null, // not checked: method mocked
            'gender' => null, // not checked: method mocked
        ];

        $expectedErrors = [
            'username' => [
                'Error message from required',
                'Error message from min',
                'Error message from max',
                'Error message from alpha',
                'Error message from unique',
            ],
            'password' => [
                'Error message from required',
                'Error message from min',
                'Error message from max',
                'Error message from alphanum',
            ],
            'passwordConfirm' => [
                'Error message from required',
                'Error message from matches',
            ],
            'email' => [
                'Error message from required',
                'Error message from email',
            ],
            'website' => [
                'Error message from url'
            ],
            'gender' => [
                'Error message from in'
            ],
        ];
        $result = $validator->validate($data);

        $errorsAfterFailingValidation = $validator->getErrors();
        $afterFailingValidationHasErrors = $validator->hasErrors();
        $afterFailingValidationFailed = $validator->failed();
        $afterFailingValidationPassed = $validator->passed();

        $this->assertEquals($previousErrors, $errorsFromPreviousValidation);
        $this->assertTrue($fromPreviousHasErrors);
        $this->assertTrue($fromPreviousFailed);
        $this->assertFalse($fromPreviousPassed);

        $this->assertInstanceOf('\SlimApp\Validator', $result);

        $this->assertEquals($expectedErrors, $errorsAfterFailingValidation);
        $this->assertTrue($afterFailingValidationHasErrors);
        $this->assertTrue($afterFailingValidationFailed);
        $this->assertFalse($afterFailingValidationPassed);
    }

    /**
     * @test
     * @covers SlimApp\Validator::required
     * @param mixed $ruleValue The value for the rule, stores in the rules property
     * @dataProvider provider_required_throws_InvalidArgumentException_if_ruleValue_is_not_a_boolean
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "required" rule must have a boolean as value
     */
    public function required_throws_InvalidArgumentException_if_ruleValue_is_not_a_boolean($ruleValue)
    {
        $validator = $this->getMockForValidatorClass();
        $rules = ['fieldName' => ['required' => $ruleValue]];
        $validator->rules = $rules;

        $result = $validator->required($ruleValue, 'fieldName', 'whateverValue');
    }

    public function provider_required_throws_InvalidArgumentException_if_ruleValue_is_not_a_boolean()
    {
        return [
            ['true'],
            ['false'],
            [1],
            [0],
        ];
    }

    /**
     * @test
     * @covers SlimApp\Validator::required
     * @param boolean|string $expectedResult The result returned by the function
     * @param boolean $ruleValue The value for the rule, stores in the rules property
     * @param mixed $value The value to validate
     * @dataProvider provider_required_returns_true_if_value_provided_error_message_otherwise
     */
    public function required_returns_true_if_value_provided_error_message_otherwise($expectedResult, $ruleValue, $value)
    {
        $validator = $this->getMockForValidatorClass();
        $rules = ['fieldName' => ['required' => $ruleValue]];
        $validator->rules = $rules;

        $result = $validator->required($ruleValue, 'fieldName', $value);

        $this->assertEquals($expectedResult, $result);
    }

    public function provider_required_returns_true_if_value_provided_error_message_otherwise()
    {
        return [
            // expectedResult, ruleValue, value
            [true, true, ' sdf   '],
            [true, true, 123],
            [true, true, 123.2],
            [true, true, true],
            [true, true, ['asdf']],
            ['fieldName is required.', true, ''],
            ['fieldName is required.', true, '   '],
            ['fieldName is required.', true, 0],
            ['fieldName is required.', true, false],
            ['fieldName is required.', true, []],
        ];
    }

    /**
     * @test
     * @covers SlimApp\Validator::min
     * @param mixed $ruleValue The value for the rule, stores in the rules property
     * @dataProvider provider_min_throws_InvalidArgumentException_if_ruleValue_is_not_a_positive_integer
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "min" rule must have a positive integer as value
     */
    public function min_throws_InvalidArgumentException_if_ruleValue_is_not_a_positive_integer($ruleValue)
    {
        $validator = $this->getMockForValidatorClass();
        $rules = ['fieldName' => ['min' => $ruleValue]];
        $validator->rules = $rules;

        $result = $validator->min($ruleValue, 'fieldName', 'whateverValue');
    }

    public function provider_min_throws_InvalidArgumentException_if_ruleValue_is_not_a_positive_integer()
    {
        return [
            ['4'],
            [-1],
        ];
    }

    /**
     * @test
     * @covers SlimApp\Validator::min
     * @param boolean|string $expectedResult The result returned by the function
     * @param boolean $ruleValue The value for the rule, stores in the rules property
     * @param mixed $value The value to validate
     * @dataProvider provider_min_returns_true_if_value_superior_to_ruleValue_error_message_otherwise_or_if_value_is_not_a_string
     */
    public function min_returns_true_if_value_superior_to_ruleValue_error_message_otherwise_or_if_value_is_not_a_string($expectedResult, $ruleValue, $value)
    {
        $validator = $this->getMockForValidatorClass();
        $rules = ['fieldName' => ['min' => $ruleValue]];
        $validator->rules = $rules;

        $result = $validator->min($ruleValue, 'fieldName', $value);

        $this->assertEquals($expectedResult, $result);
    }

    public function provider_min_returns_true_if_value_superior_to_ruleValue_error_message_otherwise_or_if_value_is_not_a_string()
    {
        return [
            // expectedResult, ruleValue, value
            ['Value is not a string. Cannot check string length.', 4, 234],
            [true, 3, ' sdfa  '],
            ['fieldName must be at least 5 characters.', 5, 'sdf'],
        ];
    }

    /**
     * @test
     * @covers SlimApp\Validator::max
     * @param mixed $ruleValue The value for the rule, stores in the rules property
     * @dataProvider provider_max_throws_InvalidArgumentException_if_ruleValue_is_not_a_positive_integer
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "max" rule must have a positive integer as value
     */
    public function max_throws_InvalidArgumentException_if_ruleValue_is_not_a_positive_integer($ruleValue)
    {
        $validator = $this->getMockForValidatorClass();
        $rules = ['fieldName' => ['max' => $ruleValue]];
        $validator->rules = $rules;

        $result = $validator->max($ruleValue, 'fieldName', 'whateverValue');
    }

    public function provider_max_throws_InvalidArgumentException_if_ruleValue_is_not_a_positive_integer()
    {
        return [
            ['4'],
            [-1],
        ];
    }

    /**
     * @test
     * @covers SlimApp\Validator::max
     * @param boolean|string $expectedResult The result returned by the function
     * @param boolean $ruleValue The value for the rule, stores in the rules property
     * @param mixed $value The value to validate
     * @dataProvider provider_max_returns_true_if_value_inferior_to_ruleValue_error_message_otherwise_or_if_value_is_not_a_string
     */
    public function max_returns_true_if_value_inferior_to_ruleValue_error_message_otherwise_or_if_value_is_not_a_string($expectedResult, $ruleValue, $value)
    {
        $validator = $this->getMockForValidatorClass();
        $rules = ['fieldName' => ['max' => $ruleValue]];
        $validator->rules = $rules;

        $result = $validator->max($ruleValue, 'fieldName', $value);

        $this->assertEquals($expectedResult, $result);
    }

    public function provider_max_returns_true_if_value_inferior_to_ruleValue_error_message_otherwise_or_if_value_is_not_a_string()
    {
        return [
            // expectedResult, ruleValue, value
            ['Value is not a string. Cannot check string length.', 4, 234],
            [true, 5, ' sdfa  '],
            ['fieldName must be under 5 characters.', 5, 'sdfasdfasdf'],
        ];
    }

    /**
     * @test
     * @covers SlimApp\Validator::matches
     * @param boolean|string $expectedResult The result returned by the function
     * @param boolean $ruleValue The value for the rule, stores in the rules property
     * @param mixed $value The value to validate
     * @param string $matchingValue The value of the matching field
     * @dataProvider provider_matches_returns_true_if_value_matches_matchingValue_error_message_otherwise
     */
    public function matches_returns_true_if_value_matches_matchingValue_error_message_otherwise($expectedResult, $ruleValue, $value, $matchingValue)
    {
        $validator = $this->getMockForValidatorClass();
        $rules = ['fieldName' => ['matches' => $ruleValue]];
        $validator->rules = $rules;

        $result = $validator->matches($ruleValue, 'fieldName', $value, $matchingValue);

        $this->assertEquals($expectedResult, $result);
    }

    public function provider_matches_returns_true_if_value_matches_matchingValue_error_message_otherwise()
    {
        return [
            // expectedResult, ruleValue, value, matchingValue
            [true, 'fieldName', 'asdf', 'asdf'],
            [true, 'fieldName', 'asdf   ', ' asdf'],
            [true, 'fieldName', 2, 2],
            [true, 'fieldName', 2, 2],
            ['fieldName must match fieldNameConfirm.', 'fieldNameConfirm', 'qwerqwer', 'sdfasdfasdf'],
        ];
    }

    /**
     * @test
     * @covers SlimApp\Validator::email
     * @param mixed $ruleValue The value for the rule, stores in the rules property
     * @dataProvider provider_email_throws_InvalidArgumentException_if_ruleValue_is_not_a_boolean
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "email" rule must have a boolean as value
     */
    public function email_throws_InvalidArgumentException_if_ruleValue_is_not_a_boolean($ruleValue)
    {
        $validator = $this->getMockForValidatorClass();
        $rules = ['fieldName' => ['email' => $ruleValue]];
        $validator->rules = $rules;

        $result = $validator->email($ruleValue, 'fieldName', 'whateverValue');
    }

    public function provider_email_throws_InvalidArgumentException_if_ruleValue_is_not_a_boolean()
    {
        return [
            ['true'],
            ['false'],
            [1],
            [0],
        ];
    }

    /**
     * @test
     * @covers SlimApp\Validator::email
     * @param boolean|string $expectedResult The result returned by the function
     * @param boolean $ruleValue The value for the rule, stores in the rules property
     * @param mixed $value The value to validate
     * @dataProvider provider_email_returns_true_if_value_is_a_valid_email_address_error_message_otherwise_or_if_value_is_not_a_string
     */
    public function email_returns_true_if_value_is_a_valid_email_address_error_message_otherwise_or_if_value_is_not_a_string($expectedResult, $ruleValue, $value)
    {
        $validator = $this->getMockForValidatorClass();
        $rules = ['fieldName' => ['email' => $ruleValue]];
        $validator->rules = $rules;

        $result = $validator->email($ruleValue, 'fieldName', $value);

        $this->assertEquals($expectedResult, $result);
    }

    public function provider_email_returns_true_if_value_is_a_valid_email_address_error_message_otherwise_or_if_value_is_not_a_string()
    {
        return [
            // expectedResult, ruleValue, value
            ['Value is not a string. Cannot check if it is a valid email address.', true, 234],
            [true, true, 'email@gmail.com'],
            ['fieldName is not a valid email address.', true, 'sdfasdfasdf'],
        ];
    }

    /**
     * @test
     * @covers SlimApp\Validator::in
     * @param mixed $ruleValue The value for the rule, stores in the rules property
     * @expectedException \DomainException
     * @expectedExceptionMessage Empty set of values to check against.
     */
    public function in_throws_InvalidArgumentException_if_ruleValue_is_not_a_boolean()
    {
        $validator = $this->getMockForValidatorClass();
        $rules = ['fieldName' => ['in' => []]];
        $validator->rules = $rules;

        $result = $validator->in([], 'fieldName', 'whateverValue');
    }

    /**
     * @test
     * @covers SlimApp\Validator::in
     * @param boolean|string $expectedResult The result returned by the function
     * @param boolean $ruleValue The value for the rule, stores in the rules property
     * @param mixed $value The value to validate
     * @dataProvider provider_in_returns_true_if_value_in_set_of_accepted_value_error_message_otherwise
     */
    public function in_returns_true_if_value_in_set_of_accepted_value_error_message_otherwise($expectedResult, $ruleValue, $value)
    {
        $validator = $this->getMockForValidatorClass();
        $rules = ['fieldName' => ['in' => $ruleValue]];
        $validator->rules = $rules;

        $result = $validator->in($ruleValue, 'fieldName', $value);

        $this->assertEquals($expectedResult, $result);
    }

    public function provider_in_returns_true_if_value_in_set_of_accepted_value_error_message_otherwise()
    {
        return [
            // expectedResult, ruleValue, value
            [true, ['one', 'two', 'three'], 'one'],
            [true, ['one', 'two', 'three'], 'two'],
            [true, ['one', 'two', 'three'], 'three'],
            [true, [1, 2, 3], 1],
            [true, [1, 2, 3], 2],
            [true, [1, 2, 3], 3],
            ['fieldName does not match any of the accepted values.', ['one', 'two', 'three'], 'four'],
            ['fieldName does not match any of the accepted values.', [1, 2, 3], 4],
        ];
    }

    /**
     * @test
     * @covers SlimApp\Validator::alpha
     * @param mixed $ruleValue The value for the rule, stores in the rules property
     * @dataProvider provider_alpha_throws_InvalidArgumentException_if_ruleValue_is_not_a_boolean
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "alpha" rule must have a boolean as value
     */
    public function alpha_throws_InvalidArgumentException_if_ruleValue_is_not_a_boolean($ruleValue)
    {
        $validator = $this->getMockForValidatorClass();
        $rules = ['fieldName' => ['alpha' => $ruleValue]];
        $validator->rules = $rules;

        $result = $validator->alpha($ruleValue, 'fieldName', 'whateverValue');
    }

    public function provider_alpha_throws_InvalidArgumentException_if_ruleValue_is_not_a_boolean()
    {
        return [
            ['true'],
            ['false'],
            [1],
            [0],
        ];
    }

    /**
     * @test
     * @covers SlimApp\Validator::alpha
     * @param boolean|string $expectedResult The result returned by the function
     * @param boolean $ruleValue The value for the rule, stores in the rules property
     * @param mixed $value The value to validate
     * @dataProvider provider_alpha_returns_true_if_value_is_a_valid_alpha_address_error_message_otherwise_or_if_value_is_not_a_string
     */
    public function alpha_returns_true_if_value_is_a_valid_alpha_address_error_message_otherwise_or_if_value_is_not_a_string($expectedResult, $ruleValue, $value)
    {
        $validator = $this->getMockForValidatorClass();
        $rules = ['fieldName' => ['alpha' => $ruleValue]];
        $validator->rules = $rules;

        $result = $validator->alpha($ruleValue, 'fieldName', $value);

        $this->assertEquals($expectedResult, $result);
    }

    public function provider_alpha_returns_true_if_value_is_a_valid_alpha_address_error_message_otherwise_or_if_value_is_not_a_string()
    {
        return [
            // expectedResult, ruleValue, value
            ['Value is not a string. Cannot check if it contains only alphabetic characters.', true, 234],
            [true, true, 'asdfjldldsfkj'],
            ['fieldName does not contain only alphabetic characters.', true, 'aasdfw23498'],
        ];
    }

    /**
     * @test
     * @covers SlimApp\Validator::alphanum
     * @param mixed $ruleValue The value for the rule, stores in the rules property
     * @dataProvider provider_alphanum_throws_InvalidArgumentException_if_ruleValue_is_not_a_boolean
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "alphanum" rule must have a boolean as value
     */
    public function alphanum_throws_InvalidArgumentException_if_ruleValue_is_not_a_boolean($ruleValue)
    {
        $validator = $this->getMockForValidatorClass();
        $rules = ['fieldName' => ['alphanum' => $ruleValue]];
        $validator->rules = $rules;

        $result = $validator->alphanum($ruleValue, 'fieldName', 'whateverValue');
    }

    public function provider_alphanum_throws_InvalidArgumentException_if_ruleValue_is_not_a_boolean()
    {
        return [
            ['true'],
            ['false'],
            [1],
            [0],
        ];
    }

    /**
     * @test
     * @covers SlimApp\Validator::alphanum
     * @param boolean|string $expectedResult The result returned by the function
     * @param boolean $ruleValue The value for the rule, stores in the rules property
     * @param mixed $value The value to validate
     * @dataProvider provider_alphanum_returns_true_if_value_is_a_valid_alphanum_address_error_message_otherwise_or_if_value_is_not_a_string
     */
    public function alphanum_returns_true_if_value_is_a_valid_alphanum_address_error_message_otherwise_or_if_value_is_not_a_string($expectedResult, $ruleValue, $value)
    {
        $validator = $this->getMockForValidatorClass();
        $rules = ['fieldName' => ['alphanum' => $ruleValue]];
        $validator->rules = $rules;

        $result = $validator->alphanum($ruleValue, 'fieldName', $value);

        $this->assertEquals($expectedResult, $result);
    }

    public function provider_alphanum_returns_true_if_value_is_a_valid_alphanum_address_error_message_otherwise_or_if_value_is_not_a_string()
    {
        return [
            // expectedResult, ruleValue, value
            ['Value is not a string. Cannot check if it contains only alphanumeric characters.', true, 234],
            [true, true, 'asdfjldldsfkj'],
            ['fieldName does not contain only alphanumeric characters.', true, 'aasdfw23498___'],
        ];
    }

    /**
     * @test
     * @covers SlimApp\Validator::url
     * @param boolean|string $expectedResult The result returned by the function
     * @param boolean $ruleValue The value for the rule, stores in the rules property
     * @param mixed $value The value to validate
     * @dataProvider provider_url_returns_true_if_value_is_a_valid_url_error_message_otherwise_or_if_value_is_not_a_string
     */
    public function url_returns_true_if_value_is_a_valid_url_error_message_otherwise_or_if_value_is_not_a_string($expectedResult, $ruleValue, $value)
    {
        $validator = $this->getMockForValidatorClass();
        $rules = ['fieldName' => ['url' => $ruleValue]];
        $validator->rules = $rules;

        $result = $validator->url($ruleValue, 'fieldName', $value);

        $this->assertEquals($expectedResult, $result);
    }

    public function provider_url_returns_true_if_value_is_a_valid_url_error_message_otherwise_or_if_value_is_not_a_string()
    {
        return [
            // expectedResult, ruleValue, value
            ['Value is not a string. Cannot check if it is a valid url.', [], 123],
            [true, [], 'http://example.com'],
            [true, ['schemeRequired'], 'http://127.0.0.1'],
            [true, ['schemeRequired'], 'http://example'],
            [true, ['schemeRequired', 'hostRequired'], 'http://127.0.0.1'],
            [true, ['schemeRequired', 'hostRequired'], 'http://example'],
            [true, ['schemeRequired', 'hostRequired'], 'http://example.com'],
            [true, ['schemeRequired', 'hostRequired', 'pathRequired'], 'http://127.0.0.1/'],
            [true, ['schemeRequired', 'hostRequired', 'pathRequired'], 'http://127.0.0.1/whatever'],
            [true, ['schemeRequired', 'hostRequired', 'pathRequired'], 'http://example.com/whatever'],
            [true, ['schemeRequired', 'hostRequired', 'pathRequired', 'queryRequired'], 'http://127.0.0.1/whatever?name=value'],
            [true, ['schemeRequired', 'hostRequired', 'pathRequired', 'queryRequired'], 'http://example.com/whatever?name=value'],
            ['fieldName is not a valid url.', [], 'aasdfw23498___'],
            ['fieldName is not a valid url.', [], 'aasdf@w23498___'],
            ['fieldName is not a valid url.', [], '127.0.0.1'],
            ['fieldName is not a valid url.', [], 'example'],
            ['fieldName is not a valid url.', [], 'example.com'],
            ['fieldName is not a valid url.', [], 'http://'],
            ['fieldName is not a valid url.', ['schemeRequired'], 'example.com'],
            ['fieldName is not a valid url.', ['schemeRequired', 'hostRequired'], 'http://'],
            ['fieldName is not a valid url.', ['schemeRequired', 'hostRequired', 'pathRequired'], 'http://127.0.0.1'],
            ['fieldName is not a valid url.', ['schemeRequired', 'hostRequired', 'pathRequired'], 'http://example.com'],
            ['fieldName is not a valid url.', ['schemeRequired', 'hostRequired', 'pathRequired', 'queryRequired'], 'http://127.0.0.1'],
            ['fieldName is not a valid url.', ['schemeRequired', 'hostRequired', 'pathRequired', 'queryRequired'], 'http://example.com'],
        ];
    }

    /**
     * @test
     * @covers SlimApp\Validator::unique
     * @param mixed $ruleValue The value for the rule, stores in the rules property
     * @dataProvider provider_unique_throws_InvalidArgumentException_if_ruleValue_is_not_a_boolean
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "unique" rule must have a string as value
     */
    public function unique_throws_InvalidArgumentException_if_ruleValue_is_not_a_string($ruleValue)
    {
        $validator = $this->getMockForValidatorClass();
        $rules = ['fieldName' => ['unique' => $ruleValue]];
        $validator->rules = $rules;

        $result = $validator->unique($ruleValue, 'fieldName', 'whateverValue');
    }

    public function provider_unique_throws_InvalidArgumentException_if_ruleValue_is_not_a_boolean()
    {
        return [
            [true],
            [false],
            [1],
            [0],
        ];
    }

    /**
     * @test
     * @covers SlimApp\Validator::unique
     * @expectedException \DomainException
     * @expectedExceptionMessage No data mapper set.
     */
    public function unique_throws_DomainException_if_no_mapper_set()
    {
        $validatorNoMapper = $this->getMockForValidatorClass();
        $rules = ['fieldName' => ['unique' => 'whateverRuleValue']];
        $validatorNoMapper->rules = $rules;

        $result = $validatorNoMapper->unique('whateverRuleValue', 'fieldName', 'whateverValue');
    }

    /**
     * @test
     * @covers SlimApp\Validator::unique
     * @expectedException \DomainException
     * @expectedExceptionMessage No DbTable set in data mapper.
     */
    public function unique_throws_DomainException_if_no_DbTable_set_in_mapper()
    {
        $mapper = $this->getMockBuilder('SlimApp\Db\Mapper')
                       ->setMethods(['getDbTable'])
                       ->getMock();
        $mockMapper = $this->getMockBuilder('SlimApp\Validator')
                           ->setConstructorArgs([$mapper])
                           ->getMockForAbstractClass();
        $mapper->method('getDbTable')
               ->will($this->returnValue(null));
        $validatorMapper = new \SebastianBergmann\PeekAndPoke\Proxy($mockMapper);
        $rules = ['fieldName' => ['unique' => 'whateverRuleValue']];
        $validatorMapper->rules = $rules;

        $result = $validatorMapper->unique('whateverRuleValue', 'fieldName', 'whateverValue');
    }

    /**
     * @test
     * @covers SlimApp\Validator::unique
     * @expectedException \DomainException
     * @expectedExceptionMessage No model set in data mapper.
     */
    public function unique_throws_DomainException_if_no_Model_set_in_mapper()
    {
        $dbTable = $this->getMockBuilder('SlimApp\Db\DbTable')
                        ->getMock();
        $mapper = $this->getMockBuilder('SlimApp\Db\Mapper')
                       ->setMethods(['getDbTable', 'getModel'])
                       ->getMock();
        $mockMapper = $this->getMockBuilder('SlimApp\Validator')
                           ->setConstructorArgs([$mapper])
                           ->getMockForAbstractClass();
        $mapper->method('getDbTable')
               ->will($this->returnValue($dbTable));
        $mapper->method('getModel')
               ->will($this->returnValue(null));
        $validatorMapper = new \SebastianBergmann\PeekAndPoke\Proxy($mockMapper);
        $rules = ['fieldName' => ['unique' => 'whateverRuleValue']];
        $validatorMapper->rules = $rules;

        $result = $validatorMapper->unique('whateverRuleValue', 'fieldName', 'whateverValue');
    }

    /**
     * @test
     * @covers SlimApp\Validator::unique
     * @expectedException \DomainException
     * @expectedExceptionMessage Model set in Validator's mapper not matching model set in rule (validator: "
     */
    public function unique_throws_DomainException_if_value_does_not_match_the_name_of_the_Model_set_in_mapper()
    {
        $dbTable = $this->getMockBuilder('SlimApp\Db\DbTable')
                        ->getMock();
        $model = $this->getMockBuilder('SlimApp\Model')
                        ->getMock();
        $mapper = $this->getMockBuilder('SlimApp\Db\Mapper')
                       ->setMethods(['getDbTable', 'getModel'])
                       ->getMock();
        $mockMapper = $this->getMockBuilder('SlimApp\Validator')
                           ->setConstructorArgs([$mapper])
                           ->getMockForAbstractClass();
        $mapper->method('getDbTable')
               ->will($this->returnValue($dbTable));
        $mapper->method('getModel')
               ->will($this->returnValue($model));
        $validatorMapper = new \SebastianBergmann\PeekAndPoke\Proxy($mockMapper);
        $rules = ['fieldName' => ['unique' => 'whateverRuleValue']];
        $validatorMapper->rules = $rules;

        $result = $validatorMapper->unique('whateverRuleValue', 'fieldName', 'whateverValue');
    }

    /**
     * @test
     * @covers SlimApp\Validator::unique
     * @expectedException \DomainException
     * @expectedExceptionMessage Table name set in the Validator's mapper's not corresponding to model set in rule (validator: "
     */
    public function unique_throws_DomainException_if_tableName_generated_with_ruleValue_does_not_match_the_tableName_set_in_mapper()
    {
        $table = $this->getMockBuilder('SlimApp\Db\DbTable')
                       ->setMethods(['getTableName'])
                       ->getMock();
        $dbTable = new \SebastianBergmann\PeekAndPoke\Proxy($table);
        $model = $this->getMockBuilder('SlimApp\Model')
                      ->setMockClassName('User')
                      ->getMock();
        $mapper = $this->getMockBuilder('SlimApp\Db\Mapper')
                       ->setMethods(['getDbTable', 'getModel'])
                       ->getMock();
        $mockMapper = $this->getMockBuilder('SlimApp\Validator')
                           ->setConstructorArgs([$mapper])
                           ->getMockForAbstractClass();
        $mapper->method('getDbTable')
               ->will($this->returnValue($dbTable));
        // tableName set in DbTable is not Users but Pages
        $dbTable->method('getTableName')
                ->will($this->returnValue('Pages'));
        $mapper->method('getModel')
               ->will($this->returnValue($model));
        $validatorMapper = new \SebastianBergmann\PeekAndPoke\Proxy($mockMapper);
        $rules = ['fieldName' => ['unique' => 'User']];
        $validatorMapper->rules = $rules;

        $result = $validatorMapper->unique('User', 'fieldName', 'whateverValue');
    }

    /**
     * @test
     * @covers SlimApp\Validator::unique
     * @param boolean|string $expectedResult The result returned by the function
     * @param boolean $ruleValue The value for the rule, stores in the rules property
     * @param mixed $value The value to validate
     * @param mixed $resultFindRow The result returned by findRow
     * @dataProvider provider_unique_returns_true_if_value_is_unique_error_message_otherwise
     */
    public function unique_returns_true_if_value_is_unique_error_message_otherwise($expectedResult, $ruleValue, $value, $resultFindRow)
    {
        $table = $this->getMockBuilder('SlimApp\Db\DbTable')
                       ->setMethods(['getTableName'])
                       ->getMock();
        $dbTable = new \SebastianBergmann\PeekAndPoke\Proxy($table);
        $model = $this->getMockBuilder('SlimApp\Model')
                      ->setMockClassName('User')
                      ->getMock();
        $mapper = $this->getMockBuilder('SlimApp\Db\Mapper')
                       ->setMethods(['getDbTable', 'getModel', 'findRow'])
                       ->getMock();
        $mockMapper = $this->getMockBuilder('SlimApp\Validator')
                           ->setConstructorArgs([$mapper])
                           ->getMockForAbstractClass();
        $mapper->method('getDbTable')
               ->will($this->returnValue($dbTable));
        $dbTable->method('getTableName')
                ->will($this->returnValue('Users'));
        $mapper->method('getModel')
               ->will($this->returnValue($model));
        $mapper->method('findRow')
               ->will($this->returnValue($resultFindRow));
        $validator = new \SebastianBergmann\PeekAndPoke\Proxy($mockMapper);

        $rules = ['fieldName' => ['unique' => $ruleValue]];
        $validator->rules = $rules;

        $result = $validator->unique($ruleValue, 'fieldName', $value);

        $this->assertEquals($expectedResult, $result);
    }

    public function provider_unique_returns_true_if_value_is_unique_error_message_otherwise()
    {
        return [
            // expectedResult, ruleValue, value, resultFindRow
            [true, 'User', 'leoUsername', false],
            ['fieldName already exists.', 'User', 'leoUsername', new \SlimApp\User],
        ];
    }
}

