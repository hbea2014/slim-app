<?php

// DIC configuration

// Get container
$container = $app->getContainer();

// -----------------
// Service providers
// -----------------

// App name
$container['appName'] = function ($c) {
    $settings = $c->get('settings');

    return $settings['appName'];
};

// Twig
$container['view'] = function ($c) {
    $settings = $c->get('settings');
    $view = new \Slim\Views\Twig($settings['view']['template_path'], $settings['view']['twig']);

    // Add extensions
    $view->addExtension(new \Slim\Views\TwigExtension($c->get('router'), $c->get('request')->getUri()));
    //$view->addExtension(new Twig_Extension_Debug());
    
    // Add global
    //$view->addGlobal('httpBasePath', $c->get('request')->getScriptName());

    return $view;
};

// Mapper
$container['Mapper'] = new \SlimApp\Db\Mapper();

// User model
$container['User'] = new \SlimApp\User();

// UsersTable
$container['UsersTable'] = function ($c) {
    $settings = $c->get('settings');
    $usersDbTable = new \SlimApp\Db\UsersTable($settings['db']['config']);

    return $usersDbTable;
};

// UserMapper
$container['UserMapper'] = function ($c) {
    $mapper = $c->get('Mapper');
    $mapper->setModel($c->get('User'));
    $mapper->setDbTable($c->get('UsersTable'));

    return $mapper;
};

// Authentication class
$container['Authentication'] = function ($c) {
    $userMapper = $c->get('UserMapper');

    return new \SlimApp\Authentication($userMapper);
};

// AuthenticationValidator
$container['LoginValidator'] = function ($c) {
    $userMapper = $c->get('UserMapper');

    return new \SlimApp\LoginValidator($userMapper);
};

// ----------------
// Action factories
// ----------------

$container['SlimApp\Action\AuthenticationAction'] = function ($c) {
    return new SlimApp\Action\AuthenticationAction(
        $c->get('appName'),
        $c->get('view'), 
        $c->get('LoginValidator'),
        $c->get('Authentication')
    );
};

$container['SlimApp\Action\BaseAdminAction'] = function ($c) {
    return new SlimApp\Action\BaseAdminAction(
        $c->get('appName'),
        $c->get('view'), 
        $c->get('UserMapper')
    );
};

$container['SlimApp\Action\AdminAction'] = function ($c) {
    return new SlimApp\Action\AdminAction(
        $c->get('appName'),
        $c->get('view'), 
        $c->get('UserMapper')
    );
};

$container['SlimApp\Action\UsersAction'] = function ($c) {
    return new SlimApp\Action\UsersAction(
        $c->get('appName'),
        $c->get('view'), 
        $c->get('UserMapper')
    );
};
