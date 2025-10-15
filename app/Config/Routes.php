<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index');
$routes->group('api', function($routes) {
    $routes->post('auth/login', 'AuthController::login');
   
    $routes->post('auth/register', 'AuthController::register'); 
    // Protected routes:
    $routes->group('', ['filter' => 'auth'], function($routes) {
        $routes->get('auth/me', 'AuthController::me');

        // Example: admin-only route
        $routes->get('admin/users', 'UserController::index', ['filter' => 'auth']); // check role inside controller or create role filter
    });
});

