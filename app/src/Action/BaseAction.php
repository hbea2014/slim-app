<?php 

namespace SlimApp\Action;

use Slim\Views\Twig;
use SlimApp\User;
use SlimApp\Session;
use SlimApp\Db\Mapper;

/**
 * Base class for all admin actions
 */
abstract class BaseAction
{

    /**
     * @var Slim\Views\Twig
     */
    protected $view;

    /**
     * @var SlimApp\Db\Mapper
     */
    protected $userMapper;

    /**
     * @var SlimApp\User
     */
    protected $user;

    /**
     * @var array Parameters to pass to the page
     */
    protected $context = ['app' => null, 'user' => null, 'errors' => null, 'submitted' => null];

    /**
     * Constructor
     *
     * @param string $app
     * @param Slim\Views\Twig $twig
     * @param null|SlimApp\Db\Mapper $userMapper
     */
    public function __construct($app, Twig $view, $userMapper = null)
    {
        $this->context['app'] = $app;
        $this->view = $view;

        if ( (null !== $userMapper) && ($userMapper instanceof Mapper) ) {
            $this->userMapper = $userMapper;
            $this->addLoggedInUserDataToContext();
        }
    }

    /**
     * Removes temporary context values (eg. errors, submitted), but not permanent values (eg. app, user)
     */
    public function resetContextValues()
    {
        foreach ($this->context as $key => $value) {
            if ( ! in_array($key, ['app', 'user']) ) {
                // Keep app, user
                if ( in_array($key, ['errors', 'submitted']) ) {
                    // Empty errors, submitted but key the keys
                    $this->context[$key] = null;
                } else {
                    // Remove the key and the associated data
                    unset($this->context[$key]);
                }
            }
        }
    }

    /**
     * Sets the current user using the UserId session variable and the data mapper
     */
    public function addLoggedInUserDataToContext()
    {
        if (Session::exists('UserId')) {
            $userId = Session::get('UserId');
            $where = '`UserId` = ' . $userId;

            $user = $this->userMapper->findRow($where);

            $this->context['user'] = $user->toArray();
        }
    }
}

