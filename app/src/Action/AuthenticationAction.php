<?php 

namespace SlimApp\Action;

use Slim\Views\Twig;
use SlimApp\Authentication;
use SlimApp\LoginValidator;

final class AuthenticationAction
{
    /**
     * @var \Slim\Views\Twig
     */
    private $view;

    /**
     * @var \SlimApp\LoginValidator
     */
    private $loginValidator;

    /**
     * @var \SlimApp\Authentication
     */
    private $authentication;

    /**
     * Constructor
     *
     * @param string $appName
     * @param \Slim\Views\Twig $view
     * @param \SlimApp\LoginValidator $loginValidator
     * @param \SlimApp\Authentication $authentication
     */
    public function __construct(
        $appName,
        Twig $view, 
        LoginValidator $loginValidator, 
        Authentication $authentication
    )
    {
        $this->appName = $appName;
        $this->view = $view;
        $this->loginValidator = $loginValidator;
        $this->authentication = $authentication;
    }

    /**
     * Displays the login page
     *
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param Psr\Http\Message\ResponseInterface $response
     * @param array $args
     * @param null|array $errors The errors to display on the page
     */
    public function show($request, $response, $args, $errors = null)
    {
        $this->view->render($response, 'authentication/show.twig', [
            'title' => 'Login',
            'appName' => $this->appName,
            'errors' => $errors
        ]);

        return $response;
    }

    /**
     * Logs the user in, redirecting to /admin if success, displaying errors otherwise
     *
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param Psr\Http\Message\ResponseInterface $response
     * @param array $args
     */
    public function store($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $errors = [];

        // Pass only the username and password to the validator
        if (isset($data['username'], $data['password'])) {
            $formData = [
                'username' => $data['username'],
                'password' => $data['password']
            ];

            $validation = $this->loginValidator->validate($formData);

            if ($validation->passed()) {
                $login = $this->authentication->login($formData['username'], $formData['password']);

                if (true === $login) {
                    // Redirect to admin
                    return $response->withStatus(303)->withHeader('Location', '/admin');
                } else {
                    // Set error message that login data not correct
                    $errors['form'][] = 'Cannot log you in. Please try again!';
                }
            } else {
                // Set errors from validation class
                $errors = $validation->getErrors();
            }
        } else {
            $errors['form'][] = 'Each field is required';
        }

        // Display the login page with errors
        return $this->show($request, $response, $args, $errors);
    }

    /**
     * Logs the user out and redirects to the login page
     *
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param Psr\Http\Message\ResponseInterface $response
     * @param array $args
     */
    public function destroy($request, $response, $args)
    {
        $this->authentication->logout();

        // Redirect to the login page
        return $response->withStatus(301)->withHeader('Location', '/login');
    }
}

