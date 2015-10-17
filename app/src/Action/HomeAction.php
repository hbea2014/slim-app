<?php

namespace SlimApp\Action;

final class HomeAction
{

    /**
     * Displays some content on the home page
     *
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param Psr\Http\Message\ResponseInterface $response
     * @param array
     */
    public function dispatch($request, $response, $args)
    {
        return 'Home page';
    }
}
