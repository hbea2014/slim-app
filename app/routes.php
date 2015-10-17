<?php

// Routes

// Homepage
$app->get('/', 'SlimApp\Action\HomeAction:dispatch')
    ->setName('homepage');

// Authentication
$app->get('/login', 'SlimApp\Action\AuthenticationAction:show')
    ->setName('authentication.show');

$app->post('/login', 'SlimApp\Action\AuthenticationAction:store')
    ->setName('authentication.store');

$app->get('/logout', 'SlimApp\Action\AuthenticationAction:destroy')
    ->setName('authentication.destroy');

// Registration open :)
$app->get('/register', 'SlimApp\Action\UsersAction:create')
    ->setName('users.create');

$app->post('/register', 'SlimApp\Action\UsersAction:store')
    ->setName('users.store');

// Dashboard
$app->group('/admin', function () use ($app) {
    // Dashboard home
    $app->get('/', 'SlimApp\Action\AdminAction:show')
        ->setName('admin.show');

    // Users (RESTful)
    $app->get('/users', 'SlimApp\Action\UsersAction:index')
        ->setName('admin.users.index');

    $app->get('/users/create', 'SlimApp\Action\UsersAction:create')
        ->setName('admin.users.create');

    $app->post('/users', 'SlimApp\Action\UsersAction:store')
        ->setName('admin.users.store');

    $app->get('/users/{username}', 'SlimApp\Action\UsersAction:show')
        ->setName('admin.users.show');

    $app->get('/users/{username}/edit', 'SlimApp\Action\UsersAction:edit')
        ->setName('admin.users.edit');

    $app->put('/users/{username}', 'SlimApp\Action\UsersAction:update')
        ->setName('admin.users.update');

    $app->delete('/users/{username}', 'SlimApp\Action\UsersAction:destroy')
        ->setName('admin.users.destroy');
})->add(function ($request, $response, $next) {
    // Authentication Middleware for the 'admin' group
    // http://www.slimframework.com/docs/concepts/middleware.html

    // Get authentication class 
    $authentication = $this->get('Authentication'); // $this is the container

    if ( $authentication->userNotLoggedIn() ) {
        // Redirect guests to home page
        return $response->withStatus(302)->withHeader('Location', '/login');
    }

    // Don't redirect logged in users
    $response = $next($request, $response);

    return $response;
});

