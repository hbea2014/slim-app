<?php

namespace SlimApp\Test;

use SlimApp\Session;

class SessionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @covers SlimApp\Session::exists
     */
    public function exists_returns_true_if_session_variable_exists_false_instead()
    {
        $resultNotSet = Session::exists('UserId');

        $_SESSION['UserId'] = 10;
        $resultSet = Session::exists('UserId');

        $this->assertFalse($resultNotSet);
        $this->assertTrue($resultSet);
    }

    /**
     * @test
     * @cover SlimApp\Session::set
     */
    public function set_sets_the_session_variable()
    {
        Session::set('UserId', 15);
        $userId = $_SESSION['UserId'];

        $this->assertEquals(15, $userId);

    }

    /**
     * @test
     * @covers SlimApp\Session::get
     * @uses SlimApp\Session::set
     */
    public function get_returns_session_value_if_session_variable_set_false_instead()
    {
        $resultNotSet = Session::get('UserId');

        Session::set('UserId', 10);
        $resultSet = Session::get('UserId');

        $this->assertFalse($resultNotSet);
        $this->assertEquals(10, $resultSet);
    }

    /**
     * @test
     * @covers SlimApp\Session::delete
     * @uses SlimApp\Session::set
     */
    public function delete_deletes_the_session_variable()
    {
        Session::set('UserId', 100);
        Session::delete('UserId');
        $result = Session::get('UserId');

        $this->assertFalse($result);
    }
}

