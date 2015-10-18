<?php 

namespace SlimApp\Action;

use Slim\Views\Twig;
use SlimApp\Authentication;
use SlimApp\LoginValidator;

final class AuthenticationAction extends BaseAction
{

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
     * @param string $app
     * @param \Slim\Views\Twig $view
     * @param \SlimApp\LoginValidator $loginValidator
     * @param \SlimApp\Authentication $authentication
     */
    public function __construct(
        $app,
        Twig $view, 
        LoginValidator $loginValidator, 
        Authentication $authentication
    )
    {
        parent::__construct($app, $view);
        $this->loginValidator = $loginValidator;
        $this->authentication = $authentication;
    }

    /**
     * Displays the login page
     *
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param Psr\Http\Message\ResponseInterface $response
     * @param array $args
     */
    public function show($request, $response, $args)
    {
        $data = [
            'title' => 'Login',
            'app' => $this->context['app'],
            'errors' => $this->context['errors'],
            'submitted' => $this->context['submitted']
        ];

        $this->view->render($response, 'authentication/show.twig', $data);

        $this->resetContextValues();

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

        // Pass only username and password to the validator
        if (isset($data['username'], $data['password'])) {
            $formData = [
                'username' => $data['username'],
                'password' => $data['password']
            ];

            // Submitted data to display on the form in case of errors
            $this->context['submitted']['username'] = htmlspecialchars(strip_tags($formData['username']));

            $validation = $this->loginValidator->validate($formData);

            if ($validation->passed()) {
                $login = $this->authentication->login($formData['username'], $formData['password']);

                if (true === $login) {
                    // Redirect to admin
                    return $response->withStatus(303)->withHeader('Location', '/admin');
                } else {
                    // Set error message that login data not correct
                    $this->context['errors']['form'][] = 'Cannot log you in. Please try again!';
                }
            } else {
                // Set errors from validation class
                $this->context['errors'] = $validation->getErrors();
            }
        } else {
            $this->context['errors']['form'][] = 'Each field is required';
        }

        // Display the login page again with errors
        return $this->show($request, $response, $args);
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

        $this->resetContextValues();
        $this->context['user'] = null;

        // Redirect to the login page
        return $response->withStatus(301)->withHeader('Location', '/login');
    }
}

