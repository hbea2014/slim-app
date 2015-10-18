<?php

return [
    'settings' => [
        'app' => [
            'name' => 'SlimApp',
            'version' => '0.0.1',
        ],

        // View settings
        'view' => [
            'template_path' => __DIR__ . '/templates',
            'twig' => [
                'cache' => __DIR__ . '/../cache/twig',
                'debug' => true,
                'auto_reload' => true
            ]
        ],
        // Database settings
        'db' => [
            'config' => [
                'driver' => 'mysql',
                'host' => 'localhost',
                'charset' => 'utf8',
                'dbname' => 'SlimApp',
                'username' => 'root',
                'password' => 'qsdfqsdf'
            ],
        ]
    ]
];

