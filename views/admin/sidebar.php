<?php
// Start the session
// session_start();

// Include the autoload file
require __DIR__ . '/../../vendor/autoload.php';

// Check if the user is not logged in
if (!isset($_SESSION['admin'])) {
    header("Location: /");
    exit;
}

// Get the admin data from the session
$admin = $_SESSION['admin'];

// Use the Admin model to fetch user details
$adminModel = new \Models\Admin();
// $userData = $adminModel->userprofile();

// Check if user details are found
// if (!$userData) {
//     echo "Invalid Credentials";
//     exit;
// }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Knowbots</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="/views/public/vendors/feather/feather.css">
    <link rel="stylesheet" href="/views/public/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="/views/public/vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="/views/public/vendors/datatables.net-bs4/dataTables.bootstrap4.css">
    <link rel="stylesheet" href="/views/public/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" type="text/css" href="../../views/public/js/select.dataTables.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="/views/public/css/vertical-layout-light/style.css">
    <!-- endinject -->
    <link rel="shortcut icon" href="/views/public/assets/images/android-chrome-512x512.png" />
</head>
<body>
<div class="container-scroller">
    <!-- partial:partials/_navbar.html -->
    <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
        <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
            <a class="navbar-brand brand-logo mr-5" href="#"><img src="/views/public/assets/images/logo1.png" class="mr-2" alt="logo" height="100" width="50">Knowbots</a>
            <a class="navbar-brand brand-logo-mini" href="#"><img src="/views/public/assets/images/logo1.png" alt="logo" height="100" width="50"></a>
        </div>
        <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
            <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
                <span class="icon-menu"></span>
            </button>

            <ul class="navbar-nav navbar-nav-right">
                <li class="nav-item nav-profile dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
                        <img src="/views/public/images/user.jpg" alt="profile"/>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
                        <a class="dropdown-item" href="/admin/profile">
                            <i class="ti-user text-primary"></i>
                            Profile
                        </a>
                        <a class="dropdown-item" href="/admin/logout">
                            <i class="ti-power-off text-primary"></i>
                            Logout
                        </a>
                    </div>
                </li>
            </ul>
            <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
                <span class="icon-menu"></span>
            </button>
        </div>
    </nav>
    <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_settings-panel.html -->
        <div class="theme-setting-wrapper">
            <div id="settings-trigger"><i class="ti-settings"></i></div>
            <div id="theme-settings" class="settings-panel">
                <i class="settings-close ti-close"></i>
                <p class="settings-heading">SIDEBAR SKINS</p>
                <div class="sidebar-bg-options selected" id="sidebar-light-theme"><div class="img-ss rounded-circle bg-light border mr-3"></div>Light</div>
                <div class="sidebar-bg-options" id="sidebar-dark-theme"><div class="img-ss rounded-circle bg-dark border mr-3"></div>Dark</div>
                <p class="settings-heading mt-2">HEADER SKINS</p>
                <div class="color-tiles mx-0 px-4">
                    <div class="tiles success"></div>
                    <div class="tiles warning"></div>
                    <div class="tiles danger"></div>
                    <div class="tiles info"></div>
                    <div class="tiles dark"></div>
                    <div class="tiles default"></div>
                </div>
            </div>
        </div>

        <!-- partial -->
        <!-- partial:partials/_sidebar.html -->
        <nav class="sidebar sidebar-offcanvas" id="sidebar">
            <ul class="nav">
                <li class="nav-item">
                    <a class="nav-link" href="/admin/dashboard">
                        <i class="icon-grid menu-icon"></i>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </li>

                <li class="nav-item"><hr></li>

                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#university" aria-expanded="false" aria-controls="university">
                        <i class="icon-layout menu-icon"></i>
                        <span class="menu-title">University</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="university">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="/admin/addCompany" style="font-size: 12px;">Create University</a></li>
                            <li class="nav-item"> <a class="nav-link" href="/admin/manage_company" style="font-size: 12px;">Manage University</a></li>
                            <li class="nav-item"> <a class="nav-link" href="/admin/addUniversity" style="font-size: 12px;">Create Sub-University</a></li>
                            <li class="nav-item"> <a class="nav-link" href="/admin/manage_university" style="font-size: 12px;">Manage Sub-University</a></li>
<!--                            <li class="nav-item"> <a class="nav-link" href="#">Typography</a></li>-->
                        </ul>
                    </div>
                </li>


                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#courses" aria-expanded="false" aria-controls="courses">
                        <i class="icon-columns menu-icon"></i>
                        <span class="menu-title" >Courses</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="courses">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"><a class="nav-link" href="/admin/add_courses" style="font-size: 12px;">Add Courses</a></li>
                            <li class="nav-item"><a class="nav-link" href="/admin/manage_courses" style="font-size: 12px;">Manage Courses</a></li>
                            <li class="nav-item"><a class="nav-link" href="/admin/virtual_classroom" style="font-size: 12px;">Virtual Classroom</a></li>
                            <!-- <li class="nav-item"><a class="nav-link" href="courses">Submission</a></li> -->
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#faculty" aria-expanded="false" aria-controls="faculty">
                        <i class="icon-layout menu-icon"></i>
                        <span class="menu-title">Faculty</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="faculty">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="/admin/uploadFaculty" style="font-size: 12px;">Upload Faculty</a></li>
                            <li class="nav-item"> <a class="nav-link" href="/admin/manage_faculty" style="font-size: 12px;">Manage Faculty</a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#student" aria-expanded="false" aria-controls="student">
                        <i class="icon-bar-graph menu-icon"></i>
                        <span class="menu-title" >Student</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="student">
                        <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="/admin/uploadStudents" style="font-size: 12px;">Upload Students</a></li>
                            <li class="nav-item"> <a class="nav-link" href="/admin/manage_students" style="font-size: 12px;">Manage Student</a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#assessments" aria-expanded="false" aria-controls="assessments">
                        <i class="icon-bar-graph menu-icon"></i>
                        <span class="menu-title">Assignments</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="assessments">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="/admin/create_assignment" style="font-size: 12px;">Create Assignments</a></li>
                            <li class="nav-item"> <a class="nav-link" href="/admin/manage_assignments" style="font-size: 12px;">Manage Assignments</a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#cohort" aria-expanded="false" aria-controls="cohort">
                        <i class="icon-layout menu-icon"></i>
                        <span class="menu-title">Cohort</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="cohort">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="/admin/create_cohort" style="font-size: 12px;">Create Cohort</a></li>
                            <li class="nav-item"> <a class="nav-link" href="/admin/manage_cohort" style="font-size: 12px;">Manage Cohort</a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#labs" aria-expanded="false" aria-controls="labs">
                        <i class="icon-layout menu-icon"></i>
                        <span class="menu-title">Labs</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="labs">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="/admin/create_lab" style="font-size: 12px;">Create Lab</a></li>
                            <li class="nav-item"> <a class="nav-link" href="/admin/manage_labs" style="font-size: 12px;">Manage Labs</a></li>
                            <li class="nav-item"> <a class="nav-link" href="/admin/create_contest" style="font-size: 12px;">Create Contest</a></li>
                            <li class="nav-item"> <a class="nav-link" href="/admin/manage_contest" style="font-size: 12px;">Manage Contest</a></li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/admin/certificate_generations">
                        <i class="icon-grid menu-icon"></i>
                        <span class="menu-title">Certificate Generation</span>
                    </a>
                </li>

                <!-- <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#streams" aria-expanded="false" aria-controls="streams">
                        <i class="icon-grid-2 menu-icon"></i>
                        <span class="menu-title">Public Courses</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="streams">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="streams_team">Create Course</a></li>
                            <li class="nav-item"> <a class="nav-link" href="streams_team">Manage Course</a></li>
                        </ul>
                    </div>
                </li> -->


                <!-- <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#icons" aria-expanded="false" aria-controls="icons">
                        <i class="icon-contract menu-icon"></i>
                        <span class="menu-title">Icons</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="icons">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="pages/icons/mdi.html">Mdi icons</a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#auth" aria-expanded="false" aria-controls="auth">
                        <i class="icon-head menu-icon"></i>
                        <span class="menu-title">User Pages</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="auth">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="pages/samples/login.html"> Login </a></li>
                            <li class="nav-item"> <a class="nav-link" href="pages/samples/register.html"> Register </a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#error" aria-expanded="false" aria-controls="error">
                        <i class="icon-ban menu-icon"></i>
                        <span class="menu-title">Error pages</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="error">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="pages/samples/error-404.html"> 404 </a></li>
                            <li class="nav-item"> <a class="nav-link" href="pages/samples/error-500.html"> 500 </a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../pages/documentation/documentation.html">
                        <i class="icon-paper menu-icon"></i>
                        <span class="menu-title">Documentation</span>
                    </a>
                </li> -->


                <li class="nav-item"><hr></li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/profile">
                        <i class="ti-user menu-icon"></i>
                        <span class="menu-title">Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/updatePassword">
                        <i class="ti-settings menu-icon"></i>
                        <span class="menu-title">Change Password</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/logout">
                        <i class="ti-power-off menu-icon"></i>
                        <span class="menu-title">Logout</span>
                </a>
            </li>
            </ul>

        </nav>
        <!-- partial -->
        
        <!-- Add these script tags before closing body tag
        <script src="/views/public/vendors/js/vendor.bundle.base.js"></script>
        <script src="/views/public/js/off-canvas.js"></script>
        <script src="/views/public/js/hoverable-collapse.js"></script>
        <script src="/views/public/js/template.js"></script>
        <script src="/views/public/js/settings.js"></script>
        <script src="/views/public/js/todolist.js"></script>
        <!-- End custom js for this page-->
    </body>
</html>