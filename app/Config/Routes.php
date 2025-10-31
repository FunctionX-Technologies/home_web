<?php

use CodeIgniter\Router\RouteCollection;

$routes->options('(:any)', function() {
    $response = service('response');
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';

    return $response
        ->setStatusCode(200)
        ->setHeader('Access-Control-Allow-Origin', $origin)
        ->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE')
        ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
        ->setHeader('Access-Control-Allow-Credentials', 'true');
});

$routes->group('api', function($routes) {
    // 🔹 Authentication Routes
    $routes->post('auth/login', 'AuthController::login');
    $routes->post('auth/forgot', 'AuthController::forgot');
    $routes->post('auth/verify-otp', 'AuthController::verifyOtp');
    $routes->post('auth/reset', 'AuthController::reset');
    $routes->post('auth/register', 'AuthController::register'); 

    // 🔹 Role & Module Routes
    $routes->get('modules', 'RoleModuleController::modules');
    $routes->get('role-modules/(:any)', 'RoleModuleController::getByRole/$1');
    $routes->post('role-modules/update', 'RoleModuleController::updateRoleModules');

    // 🔹 Protected routes (JWT required)
    $routes->group('', ['filter' => 'auth'], function($routes) {

        // Example: Authenticated user details
        $routes->get('auth/me', 'AuthController::me');

        // Example: admin-only (check inside controller)
        $routes->get('admin/users', 'UserController::index', ['filter' => 'auth']);

        // 🔹 Project Management Routes (CRUD)
        $routes->get('projects', 'ProjectController::index');          // List all projects
        $routes->get('projects/(:num)', 'ProjectController::show/$1'); // View single project
        $routes->post('projects', 'ProjectController::create');        // Create project
        $routes->put('projects/(:num)', 'ProjectController::update/$1'); // Update project
        $routes->patch('projects/(:num)', 'ProjectController::update/$1'); // Partial update
        $routes->delete('projects/(:num)', 'ProjectController::delete/$1'); // Delete project



        //update project priorities here
        $routes->put('projects/priority/update/(:num)', 'ProjectController::updatePriority/$1');
        $routes->get('projects/priority/(:any)', 'ProjectController::getByPriority/$1');


        // Task Management Routes (CRUD)
          $routes->get('tasks', 'TaskController::index');                // All tasks
    $routes->get('tasks/(:num)', 'TaskController::show/$1');       // Single task
    $routes->get('tasks/developer/(:num)', 'TaskController::getByDeveloper/$1'); // Tasks by developer
    $routes->post('tasks', 'TaskController::create');              // Create new task
    $routes->put('tasks/(:num)', 'TaskController::update/$1');     // Update
    $routes->delete('tasks/(:num)', 'TaskController::delete/$1');  // Delete

    });
});
