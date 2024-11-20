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
$router->post('/admin/add_unit', 'AdminController@addUnit');
$router->post('/admin/assign_course', 'AdminController@assignCourse');
$router->get('/admin/manage_students', 'AdminController@manageStudents');
$router->get('/admin/view_student/(\d+)', 'AdminController@viewStudent');
$router->post('/admin/handleTodo', 'AdminController@handleTodo');
$router->get('/admin/viewStudentProfile/(\d+)', 'AdminController@viewStudentProfile');



$router->get('/spoc', 'SpocController@dashboard');
$router->get('/spoc/dashboard', 'SpocController@dashboard');
$router->get('/spoc/updatePassword', 'SpocController@updatePassword');
$router->post('/spoc/updatePassword', 'SpocController@updatePassword');
$router->get('/spoc/profile', 'SpocController@userProfile');
$router->get('/spoc/addFaculty', 'SpocController@addFaculty');
$router->post('/spoc/addFaculty', 'SpocController@addFaculty');
$router->get('/spoc/manage_faculties', 'SpocController@manageFaculties');
$router->get('/spoc/view_faculty/(\d+)', 'SpocController@viewFaculty');
$router->post('/spoc/deleteFaculty', 'SpocController@deleteFaculty');

$router->get('/spoc/manage_students', 'SpocController@manageStudents');


$router->get('/faculty/dashboard', 'FacultyController@dashboard');
$router->get('/faculty/updatePassword', 'FacultyController@updatePassword');
$router->get('/faculty/profile', 'FacultyController@profile');
$router->get('/faculty/my_courses', 'FacultyController@myCourses');
$router->get('/faculty/view_course/([a-zA-Z0-9]+)', 'FacultyController@viewCourse');
$router->get('/faculty/manage_students', 'FacultyController@manageStudents');
$router->get('/faculty/create_assignment', 'FacultyController@createAssignment');
$router->post('/faculty/create_assignment', 'FacultyController@createAssignment');
$router->post('/faculty/updatePassword', 'FacultyController@updatePassword');
$router->get('/faculty/discussion_forum/(\d+)', 'FacultyController@discussionForum');
$router->post('/faculty/discussion_forum/(\d+)', 'FacultyController@discussionForum');
$router->get('/faculty/create_assessment', function() {
    require 'views/faculty/create_assessment.php';
});
$router->post('/faculty/create_assessment', function() {
    require 'views/faculty/create_asssessment.php';
});

$router->get('/faculty/manage_assessments', 'FacultyController@manageAssessments');
$router->post('/faculty/generate_questions', 'FacultyController@generateQuestions');

$router->get('/faculty/virtual_classroom', 'FacultyController@virtualClassroom');
$router->post('/faculty/create_virtual_classroom', 'FacultyController@createVirtualClassroom');
$router->get('/faculty/download_attendance', 'FacultyController@downloadAttendance');

$router->post('/faculty/Update_profile','FacultyController@profile');

$router->get('/faculty/manage_students', 'FacultyController@manageStudents');


$router->get('/faculty/take_attendance', 'FacultyController@takeAttendance');
$router->post('/faculty/save_attendance', 'FacultyController@saveAttendance');



$router->get('/faculty/view_book/([a-zA-Z0-9]+)', 'FacultyController@viewBook');
$router->get('/faculty/view_material/([a-zA-Z0-9]+)', 'FacultyController@viewBook');

$router->get('/faculty/view_reports', 'FacultyController@viewReports');
$router->get('/faculty/download_report/(\d+)', 'FacultyController@downloadReport');

$router->get('/student/dashboard', function(){
    require 'views/student/dashboard.php';
});
$router->get('/student/updatePassword', function(){
    require 'views/student/updatePassword.php';
});
$router->get('/student/all_courses', function(){
    require 'views/student/all_courses.php';
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

$router->post('/student/mark_as_completed', function() {
    require 'views/student/mark_as_completed.php';
});

$router->get('/student/view_course/([a-zA-Z0-9]+)', 'StudentController@viewCourse');

$router->get('/student/view_book/([a-zA-Z0-9]+)', 'StudentController@viewBook');

$router->get('/student/askguru', function(){
    require 'views/student/askguru.php';
});

$router->post('/student/askguru', function(){
    require 'views/student/askguru.php';
});

$router->get('/student/virtual_classroom', function(){
    require 'views/student/student_dashboard.php';
});

$router->get('/admin/logout', 'AuthController@logout');
$router->get('/spoc/logout', 'AuthController@logout');
$router->get('/faculty/logout', 'AuthController@logout');
$router->get('/student/logout', 'AuthController@logout');


$router->set404(function() {
    header('HTTP/1.0 404 Not Found');
    require 'views/404.html';
});

$router->run();
