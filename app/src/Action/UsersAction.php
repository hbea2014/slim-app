<?php 

namespace SlimApp\Action;

use Slim\Views\Twig;
use SlimApp\Db\Mapper;

final class UsersAction extends BaseAction
{

    /**
     * Displays all users
     *
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param Psr\Http\Message\ResponseInterface $response
     * @param array $args
     */
    public function index($request, $response, $args)
    {
        $users = $this->userMapper->findAll();

        $this->view->render($response, 'admin/users/index.twig', [
            'title' => 'Users',
            'username' => $this->user->getUsername(),
            'users' => $users,
        ]);

        return $response;
    }

    /**
     * Displays the profile page of the user
     *
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param Psr\Http\Message\ResponseInterface $response
     * @param array $args
     */
    public function show($request, $response, $args)
    {
        // The can only check his / her own profile
        if ($args['username'] === $this->user->getUsername()) {
            $this->view->render($response, 'admin/users/show.twig', [
                'title' => 'My Profile',
                'appName' => $this->appName,
                'username' => $this->user->getUsername(),
                'email' => $this->user->getEmail()
            ]);

            return $response;
        }

        // Redirect to the user's profile
        return $response->withStatus(301)->withHeader( 'Location', '/admin/users/' . $this->user->getUsername());
    }

    /**
     * Displays the form to add a new user
     *
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param Psr\Http\Message\ResponseInterface $response
     * @param array $args
     */
    public function create($request, $response, $args)
    {
        $data = [
            'appName' => $this->appName,
        ];

        if ( ! isset($this->user) ) {
            // Registration ( /register )
            $template = 'users/create.twig';

            $data['title'] = 'Register';
        } else {
            // Logged in user creating new user ( /admin/users/create )
            $template = 'admin/users/create.twig';

            $data = [
                'title' => 'Add A New User',
                'username' => $this->user->getUsername(),
            ];
        }

        $this->view->render($response, $template, $data);

        return $response;
    }
}

