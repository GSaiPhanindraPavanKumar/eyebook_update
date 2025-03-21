<?php

require_once 'vendor/autoload.php';

// Custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }

    // Check for undefined "email" key error which indicates user is not logged in
    if (strpos($errstr, 'Undefined array key "email"') !== false) {
        // Log the attempt but suppress all other errors
        error_reporting(0);
        ini_set('display_errors', 0);
        
        // Clear any existing session
        session_start();
        session_destroy();
        
        // Show only the session timeout page
        require 'views/session_timeout.php';
        exit;
    }

    // Regular error handling for other errors...
    error_log("Error ($errno): $errstr in $errfile on line $errline");

    // For development environment, show detailed error
    if (getenv('APP_ENV') !== 'production') {
        echo "<h1>Development Error</h1>";
        echo "<p><strong>Type:</strong> $errno</p>";
        echo "<p><strong>Message:</strong> $errstr</p>";
        echo "<p><strong>File:</strong> $errfile</p>";
        echo "<p><strong>Line:</strong> $errline</p>";
    } else {
        // For production, show user-friendly error page
        require 'views/error.php';
    }

    // Don't execute PHP internal error handler
    return true;
}

// Custom exception handler
function customExceptionHandler($exception) {
    // Check if the error is related to undefined "email" key
    if (strpos($exception->getMessage(), 'Undefined array key "email"') !== false) {
        // Log the attempt but suppress all other errors
        error_reporting(0);
        ini_set('display_errors', 0);
        
        // Clear any existing session
        session_start();
        session_destroy();
        
        // Show only the session timeout page
        require 'views/session_timeout.php';
        exit;
    }

    // Regular exception handling for other exceptions...
    error_log("Exception: " . $exception->getMessage());
    
    // Get the status code
    $statusCode = 500;
    if (method_exists($exception, 'getStatusCode')) {
        $statusCode = $exception->getStatusCode();
    }
    
    // Set the HTTP response code
    http_response_code($statusCode);

    // For development environment, show detailed error
    if (getenv('APP_ENV') !== 'production') {
        echo "<h1>Development Exception</h1>";
        echo "<p><strong>Type:</strong> " . get_class($exception) . "</p>";
        echo "<p><strong>Message:</strong> " . $exception->getMessage() . "</p>";
        echo "<p><strong>File:</strong> " . $exception->getFile() . "</p>";
        echo "<p><strong>Line:</strong> " . $exception->getLine() . "</p>";
        echo "<h2>Stack Trace:</h2>";
        echo "<pre>" . $exception->getTraceAsString() . "</pre>";
    } else {
        // For production, show user-friendly error page
        require 'views/error.php';
    }
}

// Set custom error and exception handlers
set_error_handler("customErrorHandler");
set_exception_handler("customExceptionHandler");

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
if (getenv('APP_ENV') === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}



use Bramus\Router\Router;

session_start();

$router = new Router();
$router->setNamespace('Controllers');

$router->get('/', 'HomeController@index');
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');
// $router->get('/register_student', 'AuthController@showStudentRegisterForm');
$router->post('/register_student', 'AuthController@registerStudent');
$router->get('/register_student', 'AuthController@registerStudent');

// $router->get('/register_student', function() {
//     $conn = Database::getConnection();
//     $universities = University::getAll($conn);
//     require 'views/studentRegisterView.php';
// });
$router->post('/register_student', 'AuthController@registerStudent');

$router->get('/forgot_password', function() {
    require 'views/forgot_password.php';
});
$router->post('/forgot_password', function() {
    require 'views/forgot_password.php';
});
$router->get('/reset_password', function() {
    require 'views/reset_password.php';
});
$router->post('/reset_password', function() {
    require 'views/reset_password.php';
});
$router->get('/force_reset_password', 'AuthController@forceResetPassword');
$router->post('/force_reset_password', 'AuthController@handleForceResetPassword');

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
$router->get('/admin/manage_public_courses', 'AdminController@managepublicCourse');
$router->get('/admin/view_course/(\d+)', 'AdminController@courseView');
$router->post('/admin/add_unit', 'AdminController@addUnit');
// $router->post('/admin/add_unit', function(){
//     require 'views/admin/add_unit.php';
// });
$router->get('/admin/manage_assessments', 'AdminController@manageAssessments');
$router->get('/admin/create_assessment', 'AdminController@createAssessment');
$router->post('/admin/create_assessment', 'AdminController@createAssessment');
$router->get('/admin/edit_assessment/(\d+)', 'AdminController@editAssessment');
$router->post('/admin/edit_assessment/(\d+)', 'AdminController@editAssessment');
$router->get('/admin/delete_assessment/(\d+)', 'AdminController@deleteAssessment');
$router->post('/admin/assign_course', 'AdminController@assignCourse');
$router->post('/admin/unassign_course', 'AdminController@unassignCourse');
$router->get('/admin/manage_students', 'AdminController@manageStudents');
$router->get('/admin/view_student/(\d+)', 'AdminController@viewStudent');
$router->post('/admin/handleTodo', 'AdminController@handleTodo');
$router->get('/admin/viewStudentProfile/(\d+)', 'AdminController@viewStudentProfile');
$router->get('/admin/getUsageData', 'AdminController@getUsageData');
$router->get('/admin/download_usage_report', 'AdminController@downloadUsageReport');
$router->get('/admin/view_university/(\d+)', 'AdminController@viewUniversity');
$router->get('/admin/create_virtual_classroom', 'AdminController@createVirtualClassroom');
$router->post('/admin/create_virtual_classroom', 'AdminController@createVirtualClassroom');
$router->get('/admin/create_public_assignment', 'AdminController@createPublicAssignment');
$router->post('/admin/create_public_assignment', 'AdminController@createPublicAssignment');
$router->get('/admin/view_public_assignment/(\d+)', 'AdminController@viewPublicAssignment');
$router->post('/admin/view_public_assignment/(\d+)', 'AdminController@viewPublicAssignment');
$router->post('/admin/edit_public_assignment/(\d+)', 'AdminController@editPublicAssignment');
$router->get('/admin/edit_public_assignment/(\d+)', 'AdminController@editPublicAssignment');



$router->get('/admin/edit_public_course/(\d+)', 'AdminController@editPublicCourse');
$router->post('/admin/edit_public_course/(\d+)', 'AdminController@editPublicCourse');
$router->post('/admin/archive_public_course', 'AdminController@archivePublicCourse');
$router->post('/admin/unarchive_public_course', 'AdminController@unarchivePublicCourse');
$router->get('/admin/delete_public_course/(\d+)', 'AdminController@deletePublicCourse');
$router->get('/admin/view_public_course/(\d+)', 'AdminController@viewPublicCourse');

$router->get('/admin/virtual_classroom_dashboard', 'AdminController@virtual_classroom_dashboard');
$router->post('/admin/virtual_classroom_dashboard', 'AdminController@virtual_classroom_dashboard');
$router->get('/admin/virtual_classroom', 'AdminController@virtualClassroom');
$router->get('/admin/edit_course/(\d+)', 'AdminController@editCourse');
$router->post('/admin/edit_course/(\d+)', 'AdminController@editCourse');
$router->get('/admin/delete_course/(\d+)', 'AdminController@deleteCourse');
$router->post('/admin/unassign_faculty', 'AdminController@unassignFaculty');
$router->post('/admin/unassign_students', 'AdminController@unassignStudents');
$router->get('/admin/edit_assignment/(\d+)', 'AdminController@editAssignment');
$router->post('/admin/edit_assignment/(\d+)', 'AdminController@editAssignment');
$router->get('/admin/delete_assignment/(\d+)', 'AdminController@deleteAssignment');

$router->get('/admin/upload_public_ec_content', 'AdminController@uploadpublicEcContent');
$router->post('/admin/upload_public_ec_content', 'AdminController@uploadpublicEcContent');
$router->get('/admin/upload_public_course_book', 'AdminController@uploadpublicCourseBook');
$router->post('/admin/upload_public_course_book', 'AdminController@uploadpublicCourseBook');
$router->get('/admin/upload_public_additional_content', 'AdminController@uploadpublicAdditionalContent');
$router->post('/admin/upload_public_additional_content', 'AdminController@uploadpublicAdditionalContent');


$router->get('/admin/edit_university/(\d+)', 'AdminController@editUniversity');
$router->post('/admin/edit_university/(\d+)', 'AdminController@editUniversity');
$router->get('/admin/delete_university/(\d+)', 'AdminController@deleteUniversity');
$router->post('/admin/resetStudentPasswords', 'AdminController@resetStudentPasswords');
$router->get('/admin/edit_student/(\d+)', 'AdminController@editStudent');
$router->post('/admin/edit_student/(\d+)', 'AdminController@editStudent');
$router->get('/admin/delete_student/(\d+)', 'AdminController@deleteStudent');
$router->get('/admin/reset_student_password/(\d+)', 'AdminController@resetStudentPassword');
$router->get('/admin/uploadFaculty', 'AdminController@uploadFaculty');
$router->post('/admin/uploadSingleFaculty', 'AdminController@uploadSingleFaculty');
$router->post('/admin/uploadFaculty', 'AdminController@uploadFaculty');
$router->get('/admin/manage_faculty', 'AdminController@manageFaculty');

$router->get('/admin/manage_spoc', 'AdminController@manageSpoc');
$router->post('/admin/bulk_reset_spoc_password', 'AdminController@bulkResetSpocPassword');
$router->post('/admin/delete_spocs', 'AdminController@deleteSpocs');

$router->get('/admin/manage_transactions', 'AdminController@manageTransactions');

$router->get('/admin/viewFacultyProfile/(\d+)', 'AdminController@viewFacultyProfile');
$router->get('/admin/edit_faculty/(\d+)', 'AdminController@editFaculty');
$router->post('/admin/edit_faculty/(\d+)', 'AdminController@editFaculty');
$router->post('/admin/reset_faculty_password/(\d+)', 'AdminController@resetFacultyPassword');
$router->post('/admin/resetFacultyPasswords', 'AdminController@resetFacultyPasswords');
$router->get('/admin/delete_faculty/(\d+)', 'AdminController@deleteFaculty');
$router->post('/admin/uploadSingleStudent', 'AdminController@uploadSingleStudent');
$router->post('/admin/assign_faculty', 'AdminController@assignFaculty');
$router->post('/admin/assign_students', 'AdminController@assignStudents');
$router->post('/admin/unassign_faculty', 'AdminController@unassignFaculty');
$router->post('/admin/unassign_students', 'AdminController@unassignStudents');
$router->get('/admin/view_book/([a-zA-Z0-9]+)', 'AdminController@viewBook');
$router->get('/admin/viewECbook/([a-zA-Z0-9]+)', 'AdminController@viewECBook');
$router->get('/admin/manage_assignments', 'AdminController@manageAssignments');
$router->get('/admin/create_assignment', 'AdminController@createAssignment');
$router->post('/admin/create_assignment', 'AdminController@createAssignment');
$router->get('/admin/view_assignment/(\d+)', 'AdminController@viewAssignment');
$router->get('/admin/grade_assignment/(\d+)/(\d+)', 'AdminController@gradeAssignment');
$router->post('/admin/grade_assignment/(\d+)/(\d+)', 'AdminController@gradeAssignment');
$router->get('/admin/view_assignment/(\d+)', 'AdminController@viewAssignment');
$router->get('/admin/grade_submission/(\d+)/(\d+)', 'AdminController@gradeSubmissionPage');
$router->post('/admin/grade_submission/(\d+)/(\d+)', 'AdminController@gradeSubmission');
$router->post('/admin/archive_course', 'adminController@archiveCourse');
$router->post('/admin/unarchive_course', 'adminController@unarchiveCourse');
$router->post('/admin/toggle_feedback', 'AdminController@toggleFeedback');
$router->post('/admin/toggle_public_feedback', 'AdminController@togglePublicFeedback');
$router->get('/admin/view_ec_content/([a-zA-Z0-9]+)', 'AdminController@viewECBook');


$router->post('/admin/reset_student_password/(\d+)', 'AdminController@resetStudentPassword');
$router->post('/admin/bulk_reset_student_password', 'AdminController@bulkResetStudentPassword');
$router->post('/admin/bulk_reset_faculty_password', 'AdminController@bulkResetFacultyPassword');
$router->post('/admin/add_additional_content', 'AdminController@addAdditionalContent');
$router->post('/admin/upload_ec_content', 'AdminController@uploadEcContent');
$router->get('/admin/create_cohort', 'AdminController@createCohort');
$router->post('/admin/create_cohort', 'AdminController@createCohort');
$router->get('/admin/manage_cohort', 'AdminController@manageCohort');
$router->get('/admin/edit_cohort/(\d+)', 'AdminController@editCohort');
$router->post('/admin/edit_cohort/(\d+)', 'AdminController@editCohort');
$router->get('/admin/delete_cohort/(\d+)', 'AdminController@deleteCohort');
$router->get('/admin/view_cohort/(\d+)', 'AdminController@viewCohort');
$router->post('/admin/assign_courses_to_cohort', 'AdminController@assignCoursesToCohort');
$router->post('/admin/unassign_students_from_cohort', 'AdminController@unassignStudentsFromCohort');
$router->get('/admin/unassign_course_from_cohort/(\d+)/(\d+)', 'AdminController@unassignCourseFromCohort');
$router->get('/admin/add_students_to_cohort/(\d+)', 'AdminController@addStudentsToCohort');
$router->post('/admin/add_students_to_cohort/(\d+)', 'AdminController@addStudentsToCohort');
$router->post('/admin/remove_content', 'AdminController@removeContent');
$router->post('/admin/remove_public_content', 'AdminController@removePublicContent');
$router->get('/admin/addCompany', 'AdminController@addCompany');
$router->post('/admin/addCompany', 'AdminController@addCompany');
$router->get('/admin/manage_company', 'AdminController@manageCompany');
$router->post('/admin/deleteCompany', 'AdminController@deleteCompany');
$router->get('/admin/edit_company/(\d+)', 'AdminController@editCompany');
$router->post('/admin/edit_company/(\d+)', 'AdminController@editCompany');
$router->get('/admin/view_company/(\d+)', 'AdminController@viewCompany');
$router->post('/admin/remove_universities', 'AdminController@removeUniversities');
$router->post('/admin/add_university_to_company', 'AdminController@addUniversityToCompany');
$router->post('/admin/delete_students', 'AdminController@deleteStudents');
$router->post('/admin/delete_facultys', 'AdminController@deleteFacultys');
$router->get('/admin/lab_management', 'AdminController@labManagement');
$router->post('/admin/lab_management', 'AdminController@labManagement');
$router->post('/admin/assign_cohort_to_course', 'AdminController@assignCohortToCourse');
$router->post('/admin/unassign_cohort_from_course', 'AdminController@unassignCohortFromCourse');

// Admin Lab Management Routes
$router->get('/admin/create_lab', 'AdminController@createLab');
$router->post('/admin/create_lab', 'AdminController@createLab');
$router->get('/admin/manage_labs', 'AdminController@manageLabs');
$router->get('/admin/view_labs_by_course/(\d+)', 'AdminController@viewLabsByCourse');
$router->get('/admin/view_lab_detail/([0-9]+)', 'AdminController@viewLabDetail');
$router->get('/admin/edit_lab/(\d+)', 'AdminController@editLab');
$router->post('/admin/edit_lab/(\d+)', 'AdminController@editLab');
$router->get('/admin/delete_lab/(\d+)', 'AdminController@deleteLab');
$router->get('/admin/download_lab_report/([0-9]+)', 'AdminController@downloadLabReport');
$router->get('/admin/create_contest', 'AdminController@createContest');
$router->post('/admin/save_contest', 'AdminController@saveContest');
$router->get('/admin/manage_contest', 'AdminController@manageContest');
$router->get('/admin/view_contest/([0-9]+)', 'AdminController@viewContest');
$router->get('/admin/view_contests_by_university/(\d+)', 'AdminController@viewContestsByUniversity');
$router->get('/admin/edit_contest/([0-9]+)', 'AdminController@editContest');
$router->post('/admin/update_contest/([0-9]+)', 'AdminController@updateContest');
$router->get('/admin/delete_contest/([0-9]+)', 'AdminController@deleteContest');
$router->get('/admin/add_questions/([0-9]+)', 'AdminController@addQuestions');
$router->post('/admin/save_question/([0-9]+)', 'AdminController@saveQuestion');
$router->get('/admin/view_question/([0-9]+)', 'AdminController@viewQuestion');
$router->get('/admin/edit_question/([0-9]+)', 'AdminController@editQuestion');
$router->post('/admin/update_question/([0-9]+)', 'AdminController@updateQuestion');
$router->get('/admin/delete_question/([0-9]+)', 'AdminController@deleteQuestion');
$router->get('/admin/download_feedback/(\d+)', 'AdminController@downloadFeedback');
$router->post('/admin/bulk_add_students_to_cohort/(\d+)', 'AdminController@bulkAddStudentsToCohort');

$router->get('/admin/create_public_lab', 'AdminController@createPublicLab');
$router->post('/admin/create_public_lab', 'AdminController@createPublicLab');
$router->get('/admin/manage_public_labs', 'AdminController@managePublicLabs');
$router->get('/admin/view_labs_by_public_course/(\d+)', 'AdminController@viewLabsByPublicCourse');
$router->get('/admin/view_public_lab_detail/([0-9]+)', 'AdminController@viewPublicLabDetail');
$router->get('/admin/edit_public_lab/(\d+)', 'AdminController@editPublicLab');
$router->post('/admin/edit_public_lab/(\d+)', 'AdminController@editPublicLab');
$router->get('/admin/delete_public_lab/(\d+)', 'AdminController@deletePublicLab');
$router->get('/admin/download_public_lab_report/([0-9]+)', 'AdminController@downloadPublicLabReport');


$router->get('/spoc', 'SpocController@dashboard');
$router->get('/spoc/dashboard', 'SpocController@dashboard');
$router->get('/spoc/updatePassword', 'SpocController@updatePassword');
$router->post('/spoc/updatePassword', 'SpocController@updatePassword');
$router->get('/spoc/profile', 'SpocController@profile');
$router->post('/spoc/profile', 'SpocController@profile');
$router->get('/spoc/addFaculty', 'SpocController@addFaculty');
$router->post('/spoc/addFaculty', 'SpocController@addFaculty');
$router->get('/spoc/manage_faculty', 'SpocController@manageFaculties');
$router->get('/spoc/view_faculty/(\d+)', 'SpocController@viewFaculty');
$router->post('/spoc/deleteFaculty', 'SpocController@deleteFaculty');
$router->get('/spoc/manage_courses', 'SpocController@manageCourses');
$router->get('/spoc/manage_students', 'SpocController@manageStudents');
$router->get('/spoc/view_course/([a-zA-Z0-9]+)', 'SpocController@viewCourse');
$router->post('/spoc/assign_faculty', 'SpocController@assignFaculty');
$router->post('/spoc/assign_students', 'SpocController@assignStudents');
$router->post('/spoc/unassign_faculty', 'SpocController@unassignFaculty');
$router->post('/spoc/unassign_students', 'SpocController@unassignStudents');
$router->get('/spoc/view_book/([a-zA-Z0-9]+)', 'SpocController@viewBook');
$router->get('/spoc/view_labs/([a-zA-Z0-9]+)', 'SpocController@viewLabs');
$router->get('/spoc/view_lab_detail/([0-9]+)', 'SpocController@viewLabDetail');
$router->get('/spoc/download_lab_report/([0-9]+)', 'SpocController@downloadLabReport');
$router->get('/spoc/manage_contests', 'SpocController@manageContests');
$router->get('/spoc/view_contest/([0-9]+)', 'SpocController@viewContest');
$router->get('/spoc/view_contest/([0-9]+)', 'SpocController@viewContest');
$router->get('/spoc/view_question/([0-9]+)', 'SpocController@viewQuestion');
$router->get('/spoc/discussion_forum', 'SpocController@viewDiscussions');
$router->post('/spoc/discussion_forum', 'SpocController@createDiscussion');
$router->post('/spoc/reply_discussion', 'SpocController@replyDiscussion');
$router->get('/spoc/view_assignment/(\d+)', 'SpocController@viewAssignment');



$router->get('/faculty/dashboard', 'FacultyController@dashboard');
$router->get('/faculty/updatePassword', 'FacultyController@updatePassword');
$router->get('/faculty/profile', 'FacultyController@profile');
$router->post('/faculty/profile', 'FacultyController@profile');
$router->get('/faculty/my_courses', 'FacultyController@myCourses');
$router->get('/faculty/view_course/([a-zA-Z0-9]+)', 'FacultyController@viewCourse');
$router->get('/faculty/manage_students', 'FacultyController@manageStudents');
$router->get('/faculty/create_lab', 'FacultyController@createLab');
$router->post('/faculty/create_lab', 'FacultyController@createLab');

$router->get('/faculty/manage_assessments', 'FacultyController@manageAssessments');
$router->get('/faculty/create_assessment', 'FacultyController@createAssessment');
$router->post('/faculty/create_assessment', 'FacultyController@createAssessment');
$router->get('/faculty/edit_assessment/(\d+)', 'FacultyController@editAssessment');
$router->post('/faculty/edit_assessment/(\d+)', 'FacultyController@editAssessment');
$router->get('/faculty/delete_assessment/(\d+)', 'FacultyController@deleteAssessment');

$router->get('/faculty/manage_assignments', 'FacultyController@manageAssignments');
$router->get('/faculty/create_assignment', 'FacultyController@createAssignment');
$router->post('/faculty/create_assignment', 'FacultyController@createAssignment');
$router->get('/faculty/view_assignment/(\d+)', 'FacultyController@viewAssignment');
$router->get('/faculty/grade_assignment/(\d+)/(\d+)', 'FacultyController@gradeAssignment');
$router->post('/faculty/grade_assignment/(\d+)/(\d+)', 'FacultyController@gradeAssignment');

$router->get('/faculty/download_report/(\d+)/(\w+)', 'FacultyController@downloadReport');

$router->post('/faculty/updatePassword', 'FacultyController@updatePassword');
$router->get('/faculty/discussion_forum/(\d+)', function(){
    require 'views/faculty/discussion_forum.php';
});
$router->post('/faculty/discussion_forum/(\d+)', function(){
    require 'views/faculty/discussion_forum.php';
});
$router->get('/faculty/discussion_forum', 'FacultyController@viewDiscussions');
$router->post('/faculty/discussion_forum', 'FacultyController@createDiscussion');
$router->post('/faculty/reply_discussion', 'FacultyController@replyDiscussion');
$router->get('/faculty/create_assessment', function() {
    require 'views/faculty/faculty.php';
});
$router->post('/faculty/create_assessment', function() {
    require 'views/faculty/faculty.php';
});

// $router->get('/faculty/manage_assessments', 'FacultyController@manageAssessments');
$router->post('/faculty/generate_questions', function(){
    require 'views/faculty/api/generate_questions.php';
});

$router->get('/faculty/view_assessment_report/(\d+)', function($assessmentId) {
    require 'views/faculty/view_assessment_report.php';
});
$router->get('/faculty/download_assessment_report/(\d+)', function($assessmentId) {
    require 'views/faculty/download_assessment_report.php';
});

$router->get('/faculty/virtual_classroom', 'FacultyController@virtualClassroom');
$router->get('/faculty/download_attendance', 'FacultyController@downloadAttendance');

$router->post('/faculty/Update_profile','FacultyController@profile');

$router->get('/faculty/manage_students', 'FacultyController@manageStudents');


$router->get('/faculty/take_attendance', 'FacultyController@takeAttendance');
$router->post('/faculty/save_attendance', 'FacultyController@saveAttendance');

$router->get('/faculty/manage_assessments', function(){
    require 'views/faculty/manage_assessments.php';
});

$router->post('/faculty/view_course/upload_course_materials', function(){
    require 'views/faculty/upload_course_materials.php';
});

$router->get('/faculty/view_course_plan/(\w+)', 'FacultyController@viewCoursePlan');
$router->post('/faculty/view_course/upload_course_plan', function(){
    require 'views/faculty/upload_course_plan.php';
});

$router->get('/faculty/view_assignment/(\d+)', 'FacultyController@viewAssignment');
$router->get('/faculty/grade_submission/(\d+)/(\d+)', 'FacultyController@gradeSubmissionPage');
$router->post('/faculty/grade_submission/(\d+)/(\d+)', 'FacultyController@gradeSubmission');


$router->get('/faculty/view_assessment_report/(\d+)', function($assessmentId) {
    require 'views/faculty/view_assessment_report.php';
});
$router->get('/faculty/download_assessment_report/(\d+)', function($assessmentId) {
    require 'views/faculty/download_assessment_report.php';
});
$router->get('/faculty/view_labs/([a-zA-Z0-9]+)', 'FacultyController@viewLabs');
$router->get('/faculty/view_lab_detail/([0-9]+)', 'FacultyController@viewLabDetail');
$router->get('/faculty/download_lab_report/([0-9]+)', 'FacultyController@downloadLabReport');
$router->post('/faculty/archive_course', 'FacultyController@archiveCourse');
$router->get('/faculty/view_book/([a-zA-Z0-9]+)', 'FacultyController@viewBook');
$router->get('/faculty/view_course_plan/(\w+)', 'FacultyController@viewCoursePlan');
$router->get('/faculty/view_material/(\w+)', 'FacultyController@viewMaterial');
$router->get('/faculty/view_reports', 'FacultyController@viewReports');
$router->get('/faculty/download_report/(\d+)', 'FacultyController@downloadReport');
$router->get('/faculty/manage_contests', 'FacultyController@manageContests');
$router->get('/faculty/view_contest/([0-9]+)', 'FacultyController@viewContest');
$router->get('/faculty/view_question/([0-9]+)', 'FacultyController@viewQuestion');

$router->get('/student/manage_public_courses', 'StudentController@managePublicCourses');
$router->post('/student/enroll_in_course', 'StudentController@enrollInCourse');
$router->post('/student/pay_for_course', 'StudentController@payForCourse');
$router->get('/student/razorpay_payment', 'StudentController@razorpayPayment');
$router->post('/student/razorpay_callback', 'StudentController@razorpayCallback');
$router->post('/student/enroll_in_course', 'StudentController@enrollInCourse');
$router->get('/student/view_public_course/([a-zA-Z0-9]+)', 'StudentController@viewPublicCourse');

$router->get('/student/manage_assignments', 'StudentController@manageAssignments');
$router->get('/student/view_assignment/(\d+)', 'StudentController@viewAssignment');
$router->post('/student/submit_assignment/(\d+)', 'StudentController@submitAssignment');
$router->post('/student/delete_submission/(\d+)', 'StudentController@deleteSubmission');
$router->get('/student/view_grades', 'StudentController@viewGrades');

$router->get('/student/dashboard', function(){
    require 'views/student/dashboard.php';
});

// Add the new check-in route here
$router->post('/student/check_in', 'StudentController@checkIn');

$router->get('/student/all_courses', function(){
    require 'views/student/all_courses.php';
});

$router->get('/student/updatePassword', function(){
    require 'views/student/updatePassword.php';
});
$router->post('/student/updatePassword', function(){
    require 'views/student/updatePassword.php';
});

$router->get('/student/profile', 'StudentController@profile');
$router->post('/student/profile', 'StudentController@profile');

$router->get('/student/discussion_forum', 'DiscussionController@studentForum');
$router->post('/student/discussion_forum', 'DiscussionController@studentForum');
$router->post('/student/reply_discussion', 'DiscussionController@studentReply');

$router->get('/student/view_lab/([a-zA-Z0-9]+)', 'StudentController@viewLab');
$router->get('/student/view_lab_detail/(\d+)', 'StudentController@viewLabDetail');
$router->post('/student/submit_lab/(\d+)', 'StudentController@submitLab');
$router->post('/student/update_lab_submission', 'StudentController@updateLabSubmission');

$router->get('/student/view_public_lab/([a-zA-Z0-9]+)', 'StudentController@viewPublicLab');
$router->get('/student/view_public_lab_detail/(\d+)', 'StudentController@viewPublicLabDetail');
$router->post('/student/submit_public_lab/(\d+)', 'StudentController@submitPublicLab');
$router->post('/student/update_public_lab_submission', 'StudentController@updatePublicLabSubmission');


$router->get('/student/my_courses', function(){
    require 'views/student/my_courses.php';
});
$router->post('/student/submit_feedback', 'StudentController@submitFeedback');
$router->get('/student/manage_assignments', 'StudentController@manageAssignments');
$router->get('/student/submit_assignment/(\d+)', 'StudentController@submitAssignment');
$router->post('/student/submit_assignment/(\d+)', 'StudentController@submitAssignment');
$router->get('/student/view_course_plan/(\w+)', 'StudentController@viewCoursePlan');
$router->get('/student/view_material/(\w+)', 'StudentController@viewMaterial');
$router->get('/student/view_book/(\w+)', 'StudentController@viewBook');
$router->post('/student/submit_feedback', 'StudentController@submitFeedback');
$router->post('/student/submit_public_feedback', 'StudentController@submitPublicFeedback');

$router->get('/student/submit_assignment/(\d+)', function($assignment_id){
    $_GET['assignment_id'] = $assignment_id;
    require 'views/student/assignment_submit.php';
});

$router->get('/student/assigments', function(){
    require 'views/student/assignment_submit.php';
});

// Faculty routes
$router->get('/faculty/manage_assignments', 'FacultyController@manageAssignments');

$router->get('/faculty/grade_assignment/(\d+)/(\d+)', 'FacultyController@gradeAssignment');
$router->post('/faculty/grade_assignment/(\d+)/(\d+)', 'FacultyController@gradeAssignment');

$router->get('/faculty/grade_assignment/(\d+)/(\d+)', function($assignment_id, $student_id){
    $_GET['assignment_id'] = $assignment_id;
    $_GET['student_id'] = $student_id;
    require 'views/faculty/grade_assignment.php';
});


$router->post('/faculty/grade_assignment/(\d+)/(\d+)', function($assignment_id, $student_id){
    $_GET['assignment_id'] = $assignment_id;
    $_GET['student_id'] = $student_id;
    require 'views/faculty/grade_assignment.php';
});


$router->post('/student/submit_assignment', 'StudentController@submitAssignment');

$router->post('/student/mark_as_completed', 'StudentController@markAsCompleted');

$router->get('/student/view_course/([a-zA-Z0-9]+)', 'StudentController@viewCourse');
$router->get('/student/view_course_book', 'StudentController@viewCourseBook');
$router->get('/student/view_ec_content/([a-zA-Z0-9]+)', 'StudentController@viewECContent');
// $router->get('/student/askguru','StudentController@askguru');
// $router->post('/student/askguru','StudentController@askguru');

$router->get('/student/askguru', function(){
    require 'views/student/askguru.php';
});

$router->post('/student/askguru', function(){
    require 'views/student/askguru.php';
});

$router->get('/student/virtual_classroom', function(){
    require 'views/student/student_dashboard.php';
});

$router->get('/student/askguru', function(){
    require 'views/student/askguru.php';
});
$router->get('/student/ilab', function(){
    require 'views\student\i-Lab\index.html';
});
$router->post('/student/ilab', function(){
    require 'views\student\i-Lab\index.html';
});

// Add lab routes
$router->get('/student/lab_view', 'StudentController@labView');
$router->get('/student/lab_view/([0-9]+)', 'StudentController@labView');
$router->post('/student/submit_lab/(\d+)', 'StudentController@submitLab');
$router->get('/student/manage_contests', 'StudentController@manageContests');
$router->get('/student/view_contest/([0-9]+)', 'StudentController@viewContest');
$router->get('/student/view_question/([0-9]+)', 'StudentController@viewQuestion');
$router->post('/student/update_question_submission', 'StudentController@updateQuestionSubmission');

$router->get('/student/manage_assessments', 'StudentController@manageAssessments');
$router->get('/student/view_assessments', 'StudentController@viewAssessments');
$router->get('/student/view_assessment/(\d+)', 'StudentController@viewAssessment');
$router->post('/student/submit_assessment_result', 'StudentController@submitAssessmentResult');

$router->get('/logout', 'AuthController@logout');
$router->get('/admin/logout', 'AuthController@logout');
$router->get('/spoc/logout', 'AuthController@logout');
$router->get('/faculty/logout', 'AuthController@logout');
$router->get('/student/logout', 'AuthController@logout');


$router->set404(function() {
    header('HTTP/1.0 404 Not Found');
    require 'views/404.html';
});

// Certificate Generation Routes
$router->get('/admin/certificate_generations', 'CertificateController@index');
$router->get('/admin/certificate_generations/create', 'CertificateController@create');
$router->post('/admin/certificate_generations/store', 'CertificateController@store');
$router->get('/admin/certificate_generations/progress/(\d+)', 'CertificateController@progress');
$router->get('/admin/certificate_generations/check-progress/(\d+)', 'CertificateController@checkProgress');
$router->get('/admin/certificate_generations/download/(\d+)', 'CertificateController@download');
$router->post('/admin/certificate_generations/preview', 'CertificateController@preview');

if (getenv('APP_ENV') !== 'production') {
    $router->get('/admin/certificate_generations/debug/(\d+)', 'CertificateController@debug');
}

$router->post('/admin/certificate_generations/start/(\d+)', 'CertificateController@startGeneration');

$router->get('/session-timeout', function() {
    require 'views/session_timeout.php';
});
$router->get('/student/login', function() {
    require 'views/session_timeout.php';
});

// Student ticket routes
$router->get('/student/tickets', 'StudentController@tickets');
$router->get('/student/view_ticket/(\d+)', 'StudentController@viewTicket');
$router->post('/student/create_ticket', 'StudentController@createTicket');
$router->get('/student/get_ticket_details/(\d+)', 'StudentController@getTicketDetails');
$router->post('/student/add_ticket_reply', 'StudentController@addTicketReply');
$router->post('/student/close_ticket', 'StudentController@closeTicket');

// Faculty ticket routes
$router->get('/faculty/tickets', 'FacultyController@tickets');
$router->get('/faculty/view_ticket/(\d+)', 'FacultyController@viewTicket');
$router->get('/faculty/get_ticket_details/(\d+)', 'FacultyController@getTicketDetails');
$router->post('/faculty/add_ticket_reply', 'FacultyController@addTicketReply');
$router->post('/faculty/close_ticket', 'FacultyController@closeTicket');

// SPOC ticket routes
$router->get('/spoc/tickets', 'SpocController@tickets');
$router->get('/spoc/view_ticket/(\d+)', 'SpocController@viewTicket');
$router->get('/spoc/get_ticket_details/(\d+)', 'SpocController@getTicketDetails');
$router->post('/spoc/add_ticket_reply', 'SpocController@addTicketReply');
$router->post('/spoc/close_ticket', 'SpocController@closeTicket');

// Admin ticket routes
$router->get('/admin/tickets', 'AdminController@tickets');
$router->get('/admin/view_ticket/(\d+)', 'AdminController@viewTicket');
$router->get('/admin/get_ticket_details/(\d+)', 'AdminController@getTicketDetails');
$router->post('/admin/add_ticket_reply', 'AdminController@addTicketReply');
$router->post('/admin/close_ticket', 'AdminController@closeTicket');
$router->get('/admin/export_tickets', 'AdminController@exportTickets');
$router->get('/admin/ticket_analytics', 'AdminController@ticketAnalytics');

// Add this with other student routes
$router->get('/student/view_ticket/(\d+)', 'StudentController@viewTicket');

// Add with other student ticket routes
$router->post('/student/close_ticket', 'StudentController@closeTicket');

// XP Status page route
$router->get('/student/xp_status', 'StudentController@xpStatus');

$router->post('/student/toggle_like', 'DiscussionController@toggleLike');

// Add this with other student routes
$router->post('/student/check_in', 'StudentController@checkIn');
$router->get('/privacy', function(){
    require 'views/privacy.php';
});


$router->get('/admin/get_assessment_results/(\d+)', 'AdminController@getAssessmentResults');
$router->get('/admin/export_assessment_results', 'AdminController@exportAssessmentResults');
$router->get('/faculty/get_assessment_results/(\d+)', 'FacultyController@getAssessmentResults');
$router->get('/faculty/export_assessment_results', 'FacultyController@exportAssessmentResults');

// Add this with other student routes
$router->get('/student/check_in_history', 'StudentController@checkInHistory');

// Add this with other student routes
$router->post('/student/update_profile_field', 'StudentController@updateProfileField');

$router->run();
