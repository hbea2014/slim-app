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
     * @var string
     */
    protected $appName;

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
     * Constructor
     *
     * @param string $appName
     * @param Slim\Views\Twig $twig
     * @param SlimApp\Db\Mapper $userMapper
     */
    public function __construct($appName, Twig $view, Mapper $userMapper)
    {
        $this->appName = $appName;
        $this->view = $view;
        $this->userMapper = $userMapper;
        $this->setCurrentUserIfAny();
    }

    /**
     * Sets the current user using the UserId session variable and the data mapper
     */
    public function setCurrentUserIfAny()
    {
        if (Session::exists('UserId')) {
            $userId = Session::get('UserId');
            $where = '`UserId` = ' . $userId;

            $this->user = $this->userMapper->findRow($where);
        }
    }
}

