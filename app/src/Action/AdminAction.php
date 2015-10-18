<?php

namespace SlimApp\Action;

use Slim\Views\Twig;

class AdminAction extends BaseAction
{

    /**
     * Displays the dashboard index page
     *
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param Psr\Http\Message\ResponseInterface $response
     * @param array $args
     */
    public function show($request, $response, $args)
    {
        $this->view->render($response, 'admin/show.twig', [
            'title' => 'Dashboard',
            'app' => $this->context['app'],
            //'queryParams' => $request->getQueryParams(),
            'user' => $this->context['user']
        ]);

        return $response;
    }
}
