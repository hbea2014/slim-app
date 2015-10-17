<?php

namespace SlimApp;

class Session
{
    
    /**
     * Checks if a session variable exists
     *
     * @param string $name
     * @return boolean
     */
    public static function exists($name)
    {
        return isset($_SESSION[$name]);
    }

    /**
     * Sets a session variable and its value
     *
     * @param string $name
     * @param mixed $value
     */
    public static function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * Gets the value of a session variable
     *
     * @param string $name
     * @return boolean|mixed The value of the session variable if it exists, false instead
     */
    public static function get($name)
    {
        if ( ! self::exists($name) ) {
            return false;
        }

        return $_SESSION[$name];
    }

    /**
     * Deletes the session variable
     *
     * @param string $name
     */
    public static function delete($name)
    {
        if (self::exists($name)) {
            unset($_SESSION[$name]);
        }
    }
}

