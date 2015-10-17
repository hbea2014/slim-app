<?php

namespace SlimApp;

class LoginValidator extends Validator
{

    /**
     * Validation rules for the login form
     *
     * @see SlimApp\Action\AuthenticationAction::store
     * @var array 
     */
    protected $rules = [
        'username' => [
            'required' => true
        ],
        'password' => [
            'required' => true
        ]
    ];

    /**
     * Custom error messages for given fieldName and ruleName in form
     * [fieldName => [ruleName1 => customErrorMessage1, ruleName2 => customErrorMessage2]]
     *
     * @var array
     */
    protected $customErrorMessages;
}

