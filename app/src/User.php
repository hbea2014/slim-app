<?php

namespace SlimApp;

class User extends Model
{

    /**
     * @var integer
     */
    private $UserId;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $email;

    public function __construct($data = null)
    {
        $this->setRequiredParams(['UserId', 'username', 'password', 'email']);

        parent::__construct($data);
    }

    /**
     * @var integer $UserId
     */
    public function setUserId($UserId)
    {
        $this->UserId = $UserId;
    }

    /**
     * @return integer $UserId
     */
    public function getUserId()
    {
        return $this->UserId;
    }

    /**
     * @var string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string $username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @var string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string $password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @var string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Populates the user with data
     *
     * @param array $row
     * @return $this
     */
    public function populate(array $row)
    {
        if ($this->hasRequiredParams($row)) {
            $this->setUserId($row['UserId']);
            $this->setUsername($row['username']);
            $this->setPassword($row['password']);
            $this->setEmail($row['email']);
        }

        return $this;
    }

    /**
     * Returns the user data in an array
     *
     * @return array The user data
     */
    public function toArray()
    {
        return [
            'UserId' => $this->getUserId(),
            'username' => $this->getUsername(),
            'password' => $this->getPassword(),
            'email' => $this->getEmail()
        ];
    }
}

