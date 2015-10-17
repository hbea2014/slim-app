<?php

namespace SlimApp;

use SlimApp\Session;
use SlimApp\Db\Mapper;

class Authentication
{

    /**
     * @var SlimApp\Db\Mapper
     */
    protected $userMapper;

    /**
     * Constructor
     *
     * @param null|SlimApp\Db\Mapper
     */
    public function __construct($userMapper = null)
    {
        if (null !== $userMapper) {
            $this->setUserMapper($userMapper);
        }
    }

    /**
     * Sets the data mapper for the user model
     *
     * @param SlimApp\Db\Mapper $userMapper
     */
    public function setUserMapper(Mapper $userMapper)
    {
        $this->userMapper = $userMapper;
    }

    /**
     * Retrieves the data mapper for the user model
     *
     * @return SlimApp\Db\Mapper
     */
    public function getUserMapper()
    {
        return $this->userMapper;
    }

    /**
     * Checks if user is logged in, ie. if the session variable UserId is set
     *
     * @see SlimApp\Session::exists
     * @return boolean True if user logged in, false instead
     */
    public function userLoggedIn()
    {
        return Session::exists('UserId');
    }

    /**
     * Checks if user is not logged in, ie. if the session variable UserId is not set
     *
     * @see SlimApp\Session::exists
     * @return boolean True if user not logged in, false instead
     */
    public function userNotLoggedIn()
    {
        return ! Session::exists('UserId');
    }

    /**
     * Logs the user in, ie. sets the session variable UserId for correct login data
     *
     * @param string $username
     * @param string $password
     * @return boolean True in case of success, false instead
     */
    public function login($username, $password)
    {
        $where = '`username` = \'' . $username . '\' AND `password` = \'' . $password . '\'';
        $user = $this->userMapper->findRow($where);

        if (false !== $user) {
            Session::set('UserId', $user->getUserId());

            return true;
        }

        return false;
    }

    /**
     * Logs the user out, ie. detroys the session varialbe UserId, if it exists
     */
    public function logout()
    {
        if (Session::exists('UserId')) {
            Session::delete('UserId');
        }
    }
}

