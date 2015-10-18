<?php 

namespace SlimApp\Action;

use Slim\Views\Twig;
use SlimApp\UsersStoreValidator;
use SlimApp\Db\Mapper;

final class UsersAction extends BaseAction
{
    /**
     * @var SlimApp\UsersStoreValidator
     */
    protected $usersStoreValidator;

    /**
     * Constructor
     *
     * @param string $app
     * @param Slim\Views\Twig $twig
     * @param SlimApp\Db\Mapper $userMapper
     * @param SlimApp\UsersStoreValidator $usersStoreValidator
     */
    public function __construct(
        $app, 
        Twig $view, 
        Mapper $userMapper,
        UsersStoreValidator $usersStoreValidator
    )
    {
        parent::__construct($app, $view, $userMapper);
        $this->usersStoreValidator = $usersStoreValidator;
    }

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
            'app' => $this->context['app'],
            'title' => 'Users',
            'username' => $this->context['user']['username'],
            'users' => $users,
        ]);

        $this->resetContextValues();

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
        if ($args['username'] === $this->context['user']['username']) {
            $this->view->render($response, 'admin/users/show.twig', [
                'app' => $this->context['app'],
                'title' => 'My Profile',
                'user' => $this->context['user'],
            ]);

            $this->resetContextValues();

            return $response;
        }

        // Redirect to the user's profile
        return $response->withStatus(301)->withHeader( 'Location', '/admin/users/' . $this->context['user']['username']);
    }

    /**
     * Displays the form to add a new user
     *
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param Psr\Http\Message\ResponseInterface $response
     * @param array $args
     * @param null|array $context An array of parameters to pass to the page
     */
    public function create($request, $response, $args)
    {
        $data = [
            'app' => $this->context['app'],
            'errors' => $this->context['errors'],
            'submitted' => $this->context['submitted'],
        ];

        if ( empty($this->context['user']) ) {
            // Registration ( /register )
            $template = 'users/create.twig';

            $data['title'] = 'Register';
        } else {
            // Logged in user creating new user ( /admin/users/create )
            $template = 'admin/users/create.twig';

            $data = [
                'title' => 'Add A New User',
                'username' => $this->context['user']['username'],
            ];
        }

        $this->view->render($response, $template, $data);

        $this->resetContextValues();

        return $response;
    }

    /**
     * Creates a new user or displays the form again with errors
     *
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param Psr\Http\Message\ResponseInterface $response
     * @param array $args
     */
    public function store($request, $response, $args)
    {
        $data = $request->getParsedBody();

        // Pass only username, email, password and passwordConfirm to the validator
        if (isset($data['username'], $data['email'], $data['password'], $data['passwordConfirm'])) {
            $formData = [
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
                'passwordConfirm' => $data['passwordConfirm']
            ];

            // Submitted data to display on the form in case of errors
            $this->context['submitted'] = [
                'username' => htmlspecialchars(strip_tags($data['username'])),
                'email' => htmlspecialchars(strip_tags($data['email']))
            ];

            $validation = $this->usersStoreValidator->validate($formData);

            if ($validation->passed()) {
                // Create a hash of the password
                $password = password_hash($formData['password'], PASSWORD_DEFAULT);

                $columnNames = ['username', 'password', 'email'];
                $values = [$formData['username'], $password, $formData['email']];

                $newUserStored = $this->userMapper->insert($columnNames, $values);

                if ( ! empty($this->context['user']) ) {
                    // Registration ( /register )
                    $template = 'users/store.twig';

                    $data['title'] = 'Registration successful';
                } else {
                    // Logged in user creating new user ( /admin/users/create )
                    $template = 'admin/users/store.twig';

                    $data['title'] = 'New User Created';
                }

                if (true === $newUserStored) {
                    $data['user'] = [
                        'username' => $formData['username'],
                        'email' => $formData['email'],
                    ];

                    // Display confirmation message
                    $this->view->render($response, $template, $data);

                    $this->resetContextValues();

                    return $response;
                } else {
                    // Set error message that login data not correct
                    $this->context['errors']['form'][] = 'Could not create new user. Please try again!';
                }
            } else {
                // Set errors from validation class
                $this->context['errors'] = $validation->getErrors();
            }
        } else {
            $this->context['errors']['form'][] = 'Each field is required';
        }

        // Display the register / add new user page with errors
        return $this->create($request, $response, $args);
    }
}

