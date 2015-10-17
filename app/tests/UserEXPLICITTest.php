<?php

namespace SlimApp\Test;

use SlimApp\User;

class UserEXPLICITTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @covers SlimApp\User::__construct
     */
    public function constructor_sets_required_parameters()
    {
        $user = new User;

        $expectedRequiredParams = ['UserId', 'username', 'password', 'email'];
        $requiredParams = $user->getRequiredParams();

        $this->assertEquals($expectedRequiredParams, $requiredParams);
    }

    /**
     * @test
     * @covers SlimApp\User::setUserId
     * @covers SlimApp\User::getUserId
     * @covers SlimApp\User::setUsername
     * @covers SlimApp\User::getUsername
     * @covers SlimApp\User::setPassword
     * @covers SlimApp\User::getPassword
     * @covers SlimApp\User::setEmail
     * @covers SlimApp\User::getEmail
     * @param string $userId
     * @param string $username
     * @param string $password
     * @param string $email
     * @dataProvider provider_getter_and_setters_correctly_getting_and_setting
     */
    public function getter_and_setters_correctly_getting_and_setting($userId, $username, $password, $email)
    {
        $user = new User;

        $user->setUserId($userId);
        $user->setUsername($username);
        $user->setPassword($password);
        $user->setEmail($email);

        $resultUserId = $user->getUserId();
        $resultUsername = $user->getUsername();
        $resultPassword = $user->getPassword();
        $resultEmail = $user->getEmail();

        $this->assertEquals($userId, $resultUserId);
        $this->assertEquals($username, $resultUsername);
        $this->assertEquals($password, $resultPassword);
        $this->assertEquals($email, $resultEmail);
    }

    public function provider_getter_and_setters_correctly_getting_and_setting()
    {
        return [
            [null, null, null, null],
            [1, 'siggy', 'siggypw', 'siggy@asdf.df'],
        ];
    }

    /**
     * @test
     * @covers SlimApp\User::populate
     * @uses SlimApp\User::getUserId
     * @uses SlimApp\User::getUsername
     * @uses SlimApp\User::getPassword
     * @uses SlimApp\User::getEmail
     */
    public function populate_populates_object_and_returns_self_if_correct_row_data()
    {
        $userId = 234;
        $username = 'lily';
        $password = 'lilypw';
        $email = 'lily@asdf.df';
        $data = ['UserId' => $userId, 'username' => $username, 'password' => $password, 'email' => $email];

        $user = new User;
        $result = $user->populate($data);

        $resultUserId = $user->getUserId();
        $resultUsername = $user->getUsername();
        $resultPassword = $user->getPassword();
        $resultEmail = $user->getEmail();

        $this->assertEquals($userId, $resultUserId);
        $this->assertEquals($username, $resultUsername);
        $this->assertEquals($password, $resultPassword);
        $this->assertEquals($email, $resultEmail);

        $this->assertInstanceOf('SlimApp\User', $user);
        $this->assertSame($result, $user);
    }

    /**
     * @test
     * @covers SlimApp\User::toArray
     */
    public function toArray_returns_object_properties_as_array()
    {
        $userId = 234;
        $username = 'lily';
        $password = 'lilypw';
        $email = 'lily@asdf.df';
        $data = ['UserId' => $userId, 'username' => $username, 'password' => $password, 'email' => $email];

        $user = new User($data);
        $result = $user->toArray();

        $this->assertEquals($data, $result);
    }

}

