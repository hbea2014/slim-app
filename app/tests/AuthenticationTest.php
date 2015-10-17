<?php

namespace SlimApp\Test;

use SlimApp\Authentication;
use SlimApp\Session;

class AuthenticationTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @covers SlimApp\Authentication::setUserMapper
     * @covers SlimApp\Authentication::getUserMapper
     */
    public function setUserMapper_and_getUserMapper_set_and_get_the_userMapper()
    {
        $mapper = $this->getMockBuilder('\SlimApp\Db\Mapper')
                       ->getMock();
        $authentication = new Authentication();

        $noMapperYet = $authentication->getUserMapper();

        $authentication->setUserMapper($mapper);
        $mapperSet = $authentication->getUserMapper();

        $this->assertNull($noMapperYet);
        $this->assertSame($mapper, $mapperSet);
    }


    /**
     * @test
     * @covers SlimApp\Authentication::__construct
     * @uses SlimApp\Authentication::setUserMapper
     * @uses SlimApp\Authentication::getUserMapper
     */
    public function constructor_sets_userMapper_if_given()
    {
        $mapper = $this->getMockBuilder('\SlimApp\Db\Mapper')
                       ->getMock();
        $authenticationNoMapper = new Authentication();

        $authenticationWithMapper = new Authentication($mapper);

        $noMapper = $authenticationNoMapper->getUserMapper();
        $mapperSet = $authenticationWithMapper->getUserMapper();

        $this->assertNull($noMapper);
        $this->assertSame($mapper, $mapperSet);
    }

    /**
     * @test
     * @covers SlimApp\Authentication::userLoggedIn
     * @uses SlimApp\Session::delete
     * @uses SlimApp\Session::set
     */
    public function userLoggedIn_returns_true_if_UserId_session_variable_exists_false_otherwise_userNotLoggedIn_returns_the_opposite()
    {
        // Clean the session before the tests
        Session::delete('UserId');

        $authentication = new Authentication();

        $userLoggedInBefore = $authentication->userLoggedIn();
        $userNotLoggedInBefore = $authentication->userNotLoggedIn();

        Session::set('UserId', 2);
        $userLoggedInAfter = $authentication->userLoggedIn();
        $userNotLoggedInAfter = $authentication->userNotLoggedIn();

        $this->assertFalse($userLoggedInBefore);
        $this->assertTrue($userNotLoggedInBefore);
        $this->assertTrue($userLoggedInAfter);
        $this->assertFalse($userNotLoggedInAfter);
    }

    /**
     * @test
     * @covers SlimApp\Authentication::login
     * @uses SlimApp\Session::delete
     * @uses SlimApp\Session::exists
     * @uses SlimApp\Session::get
     * @param string $findRowReturns
     * @param boolean $loginResult
     * @dataProvider provider_login_sets_UserId_session_variable_and_returns_true_if_login_data_correct_returns_false_otherwise
     */
    public function login_sets_UserId_session_variable_and_returns_true_if_login_data_correct_returns_false_otherwise($findRowReturns, $loginResult)
    {
        // Clean the session before the tests
        Session::delete('UserId');

       if ('user' === $findRowReturns) {
            $userId = 12;
            $user = $this->getMockBuilder('\SlimApp\User')
                         ->setMethods(['getUserId'])
                         ->getMock();
            $user->expects($this->once())
                 ->method('getUserId')
                 ->will($this->returnValue($userId));
            $resultFindRow = $user;
        } else {
            $userId = false;
            $resultFindRow = false;
        }
        $mapper = $this->getMockBuilder('\SlimApp\Db\Mapper')
                       ->setMethods(['findRow'])
                       ->getMock();
        $mapper->expects($this->once())
               ->method('findRow')
               ->will($this->returnValue($resultFindRow));
        $authentication = new Authentication($mapper);

        $login = $authentication->login('username', 'password');

        $this->assertEquals($loginResult, $login);
        $this->assertEquals($loginResult, Session::exists('UserId'));
        $this->assertEquals($loginResult, $authentication->userLoggedIn());
        $this->assertEquals($userId, Session::get('UserId'));
    }

    public function provider_login_sets_UserId_session_variable_and_returns_true_if_login_data_correct_returns_false_otherwise()
    {
        return [
            // findRowReturns, loginResult
            ['user', true],
            [false, false],
        ];
    }

    /**
     * @test
     * @covers SlimApp\Authentication::logout
     * @uses SlimApp\Session::delete
     * @uses SlimApp\Session::set
     */
    public function logout_deletes_the_UserId_session_variable_if_it_was_set()
    {
        // Clean the session before the tests
        Session::delete('UserId');

        // Logout called by a guest user
        $authentication = new Authentication();
        $authentication->logout();
        $userIdAfterGuestLogout = Session::get('UserId');

        // Logged in user
        Session::set('UserId', 123);
        $authentication->logout();
        $userIdAfterLoggedInUserLogout = Session::get('UserId');

        $this->assertFalse($userIdAfterGuestLogout);
        $this->assertFalse($userIdAfterLoggedInUserLogout);
    }
}

