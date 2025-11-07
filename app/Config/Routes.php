<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index');

$routes->group('api', function($routes) {
    // 🔹 Authentication Routes
    $routes->post('auth/login', 'AuthController::login');
    $routes->post('auth/forgot', 'AuthController::forgot');
    $routes->post('auth/verify-otp', 'AuthController::verifyOtp');
    $routes->post('auth/reset', 'AuthController::reset');
    $routes->post('auth/register', 'AuthController::register'); 

    // 🔹 Role & Module Routes
    $routes->post('modules', 'RoleModuleController::modules');
    $routes->post('role-modules/(:any)', 'RoleModuleController::getByRole/$1');
    $routes->post('role-modules/update', 'RoleModuleController::updateRoleModules');

    // this is for test email
    $routes->get('test-email', 'TestEmailController::index');


    // 🔹 Protected routes (JWT required)
    $routes->group('', ['filter' => 'auth'], function($routes) {

        // Example: Authenticated user detailshere
        $routes->post('auth/me', 'AuthController::me');

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
        $routes->post('projects/priority/(:any)', 'ProjectController::getByPriority/$1');


        // Task Management Routes (CRUD)
        $routes->post('tasks', 'TaskController::index');                // All tasks
        $routes->post('tasks/(:num)', 'TaskController::show/$1');       // Single task
        $routes->post('tasks/developer/(:num)', 'TaskController::getByDeveloper/$1'); // Tasks by developer
        $routes->post('tasks', 'TaskController::create');              // Create new task
        $routes->put('tasks/(:num)', 'TaskController::update/$1');     // Update
        $routes->delete('tasks/(:num)', 'TaskController::delete/$1');  // Delete




    // this is for add task comments 
    // Task comments
    $routes->post('tasks/comments', 'TaskCommentController::create');
    $routes->get('tasks/comments/(:num)', 'TaskCommentController::getByTask/$1');
    // Task attachments
    $routes->post('tasks/attachments/upload', 'TaskAttachmentController::upload');
    $routes->get('tasks/attachments/(:num)', 'TaskAttachmentController::getByTask/$1');
    
        // Task time logs
    $routes->post('tasks/time-log', 'TaskTimeLogController::logTime');
    $routes->get('tasks/time-log/(:num)', 'TaskTimeLogController::getByTask/$1');


            // ✅ 🔹 Comments (Threaded) & Activity Logs
    $routes->group('comments', function($routes) {
        $routes->post('create', 'TaskCommentController::create');
        $routes->get('task/(:num)', 'TaskCommentController::getByTask/$1');
    });


    $routes->group('activity', function($routes) {
        $routes->get('/', 'ActivityLogController::index');
        $routes->get('task/(:num)', 'ActivityLogController::getByTask/$1');
    });



    // Dashboard Routes


$routes->post('dashboard/overview', 'DashboardController::overview');
$routes->post('dashboard/productivity', 'DashboardController::productivityGraph');
$routes->post('dashboard/project-progress', 'DashboardController::projectProgressGraph');

// this is for report generation pf pdf and excel
// Reports
$routes->group('reports', function($routes) {
    $routes->post('projects/excel', 'ReportController::exportProjectsExcel');
    $routes->post('tasks/pdf', 'ReportController::exportTasksPDF');
});




// here i add this for notification system

// Notifications routes
$routes->post('notifications', 'NotificationController::index');
$routes->post('notifications/unread-count', 'NotificationController::unreadCount');
$routes->put('notifications/read/(:num)', 'NotificationController::markRead/$1');


// this is for user punch in/out system

// Attendance routes
$routes->post('attendance/punch-in', 'AttendanceController::punchIn');
$routes->post('attendance/punch-out', 'AttendanceController::punchOut');
$routes->get('attendance/status/(:num)', 'AttendanceController::status/$1');
$routes->get('attendance/report', 'AttendanceController::report');

// this is for monitoring system 
$routes->group('monitor', ['filter' => 'auth'], function($routes) {
    $routes->post('screenshot/upload', 'MonitorController::upload');
    $routes->post('activity-log', 'MonitorController::logActivity');
    $routes->get('reports/(:num)', 'MonitorController::getReports/$1');
    $routes->get('summary/(:segment)', 'MonitorController::summary/$1');
});



    });
});


