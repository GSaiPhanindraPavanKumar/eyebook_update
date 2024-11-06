<?php
if (getenv('APP_ENV') === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

require_once 'vendor/autoload.php';

use Bramus\Router\Router;

session_start();

$router = new Router();
$router->setNamespace('Controllers');

$router->get('/', 'HomeController@index');
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

$router->get('/admin', 'AdminController@dashboard');
$router->get('/admin/dashboard', 'AdminController@dashboard');
$router->get('/admin/profile', 'AdminController@userProfile');
$router->get('/admin/addUniversity', 'AdminController@addUniversity');
$router->post('/admin/addUniversity', 'AdminController@addUniversity');
$router->get('/admin/manage_university', 'AdminController@manageUniversity');
$router->post('/admin/updateUniversity', 'AdminController@updateUniversity');
$router->post('/admin/deleteUniversity', 'AdminController@deleteUniversity');
$router->get('/admin/updatePassword', 'AdminController@updatePassword');
$router->post('/admin/updatePassword', 'AdminController@updatePassword');
$router->get('/admin/uploadStudents', 'AdminController@uploadStudents');
$router->post('/admin/uploadStudents', 'AdminController@uploadStudents');
$router->post('/admin/deleteUniversity', 'AdminController@deleteUniversity');
$router->get('/admin/add_courses', 'AdminController@addCourse');
$router->post('/admin/add_courses', 'AdminController@addCourse');
$router->get('/admin/manage_courses', 'AdminController@manageCourse');
$router->get('/admin/view_course/(\d+)', 'AdminController@courseView');



$router->get('/spoc', 'SpocController@dashboard');
$router->get('/spoc/dashboard', 'SpocController@dashboard');
$router->get('/spoc/updatePassword', 'SpocController@updatePassword');
$router->post('/spoc/updatePassword', 'SpocController@updatePassword');
$router->get('/spoc/profile', 'SpocController@userProfile');
$router->get('/spoc/addFaculty', 'SpocController@addFaculty');
$router->post('/spoc/addFaculty', 'SpocController@addFaculty');


$router->get('/faculty/dashboard', 'FacultyController@dashboard');
$router->get('/faculty/updatePassword', 'FacultyController@updatePassword');
$router->get('/faculty/profile', 'FacultyController@userProfile');
$router->get('/faculty/my_courses', 'FacultyController@myCourses');
$router->get('/faculty/view_course/(\d+)', 'FacultyController@viewCourse');

$router->post('/faculty/updatePassword', function(){
    require 'views/faculty/updatePassword.php';
});
$router->get('/student/dashboard', function(){
    require 'views/student/dashboard.php';
});
$router->get('/student/updatePassword', function(){
    require 'views/student/updatePassword.php';
});
$router->post('/student/updatePassword', function(){
    require 'views/student/updatePassword.php';
});

$router->get('/student/profile', function(){
    require 'views/student/profile.php';
});

$router->get('/student/my_courses', function(){
    require 'views/student/my_courses.php';
});

$router->get('/student/view_course/(\d+)', function($course_id) {
    require 'views/student/view_course.php';
});









$router->get('/admin/logout', 'AuthController@logout');
$router->get('/spoc/logout', 'AuthController@logout');
$router->get('/faculty/logout', 'AuthController@logout');


$router->set404(function() {
    header('HTTP/1.0 404 Not Found');
    require 'views/404.html';
});

$router->run();