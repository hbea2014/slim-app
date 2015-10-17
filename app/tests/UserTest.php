<?php

namespace SlimApp\Test;

class UserTest extends SlimApp_Tests_ModelTestCase
{

    /**
     * Data for superclass' test getter_and_setter_correctly_gettings_and_setting
     *
     * @var array
     * @see SlimApp\Test\SlimApp_Tests_ModelTestCase::getter_and_setter_correctly_gettings_and_setting
     */
    public $provider_getter_and_setters_correctly_getting_and_setting = [
        [
            'UserId' => null,
            'username' => null,
            'password' => null,
            'email' => null
        ],
        [
            'UserId' => 12,
            'username' => 'lily',
            'password' => 'lilypw',
            'email' => 'lily@asdf.df'
        ],
    ];

    /**
     * Data for superclass' tests populate_populates_object_and_returns_self_if_correct_row_data and toArray_returns_object_properties_as_array
     *
     * @var array
     * @see SlimApp\Test\SlimApp_Tests_ModelTestCase::populate_populates_object_and_returns_self_if_correct_row_data
     * @see SlimApp\Test\SlimApp_Tests_ModelTestCase::toArray_returns_object_properties_as_array
     */
    public $provider_populate_and_toArray_tests = [
        'UserId' => 12,
        'username' => 'lily',
        'password' => 'lilypw',
        'email' => 'lily@asdf.df'
    ];
}

