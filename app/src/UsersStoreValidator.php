<?php

namespace SlimApp;

class UsersStoreValidator extends Validator
{

    /**
     * Validation rules for the login form
     *
     * @see SlimApp\Action\AuthenticationAction::store
     * @var array 
     */
    protected $rules = [
        'username' => [
            'required' => true,
            'min' => 4,
            'max' => 24,
            'alphanum' => true,
            'unique' => 'User',
        ],
        'email' => [
            'required' => true,
            'email' => true,
            'unique' => 'User',
        ],
        'password' => [
            'required' => true,
            'min' => 8,
            'max' => 24,
        ],
        'passwordConfirm' => [
            'required' => true,
            'matches' => 'password'
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

