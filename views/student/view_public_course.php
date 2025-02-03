<?php include "sidebar.php";
use Models\PublicCourse;
use Models\Assignment;
use Models\feedback;

// Fetch student ID from session
$studentId = $_SESSION['student_id'];

// Fetch the public course details
$courseId = base64_decode($hashedId);
$course = PublicCourse::getById($conn, $courseId);

// Fetch assignments for the course
$assignments = PublicCourse::getAssignments($conn, $courseId);

foreach ($assignments as &$assignment) {
    $assignment['submission_count'] = Assignment::getSubmissionCount($conn, $assignment['id']);
}

usort($assignments, function($a, $b) {
    return strtotime($b['due_date']) - strtotime($a['due_date']);
});

?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Course Management: <?php echo htmlspecialchars($course['name'] ?? ''); ?></h3>
                        <span class="badge bg-primary text-white">Course ID: <?php echo htmlspecialchars($course['id'] ?? ''); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overview and Course Control Panel -->
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Course Overview</h4>
                        <p class="card-description"><?php echo htmlspecialchars($course['description'] ?? ''); ?></p>
                        <h5 class="mt-5">Course Book</h5>
                        <table class="table table-hover mt-2">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">S. No.</th>
                                    <th scope="col">Unit Name</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $courseBook = $course['course_book'] ?? [];
                                if (!empty($courseBook)) {
                                    $serialNumber = 1;
                                    foreach ($courseBook as $unit) {
                                        echo "<tr>";
                                        echo "<td>" . $serialNumber++ . "</td>";
                                        echo "<td>" . htmlspecialchars($unit['unit_name']) . "</td>";
                                        $full_url = $unit['scorm_url'];
                                        echo "<td><a href='/student/view_book/" . $hashedId . "?index_path=" . urlencode($full_url) . "' class='btn btn-primary'>View Course Book</a></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='3'>No course books available.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                        <h5 class="mt-5">EC Content</h5>
                        <table class="table table-hover mt-2">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">S. No.</th>
                                    <th scope="col">Title</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $ECContent = $course['EC_content'] ?? [];
                                if (!empty($ECContent)) {
                                    $serialNumber = 1;
                                    foreach ($ECContent as $content) {
                                        echo "<tr>";
                                        echo "<td>" . $serialNumber++ . "</td>";
                                        echo "<td>" . htmlspecialchars($content['unitTitle'] ?? 'N/A') . "</td>";
                                        $full_url = $content['indexPath'] ?? '#';
                                        echo "<td><a href='/student/view_ec_content/" . $hashedId . "?index_path=" . urlencode($full_url) . "' class='btn btn-primary'>View EC</a></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='3'>No EC content available.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                        <h5 class="mt-5">Additional Content</h5>
                        <table class="table table-hover mt-2">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">S. No.</th>
                                    <th scope="col">Title</th>
                                    <th scope="col">Link</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $additionalContent = $course['additional_content'] ?? [];
                                if (!empty($additionalContent)) {
                                    $serialNumber = 1;
                                    foreach ($additionalContent as $content) {
                                        echo "<tr>";
                                        echo "<td>" . $serialNumber++ . "</td>";
                                        echo "<td>" . htmlspecialchars($content['title'] ?? 'N/A') . "</td>";
                                        echo "<td><a href='" . htmlspecialchars($content['link'] ?? '#') . "' target='_blank' class='btn btn-primary'>View Content</a></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='3'>No additional content available.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                        <h5 class="mt-5">Assignments</h5>
                        <table class="table table-hover mt-2">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">S. No.</th>
                                    <th scope="col">Title</th>
                                    <th scope="col">Due Date</th>
                                    <th scope="col">Submissions</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($assignments)) {
                                    $serialNumber = 1;
                                    foreach ($assignments as $assignment) {
                                        echo "<tr>";
                                        echo "<td>" . $serialNumber++ . "</td>";
                                        echo "<td>" . htmlspecialchars($assignment['title']) . "</td>";
                                        echo "<td>" . htmlspecialchars($assignment['due_date']) . "</td>";
                                        echo "<td>" . htmlspecialchars($assignment['submission_count']) . "</td>";
                                        echo "<td><a href='/student/view_assignment/" . $assignment['id'] . "' class='btn btn-primary'>View Assignment</a></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5'>No assignments available.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>