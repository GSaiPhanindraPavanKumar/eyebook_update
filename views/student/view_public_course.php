<?php include "sidebar.php";
use Models\PublicCourse;
use Models\Assignment;
use Models\feedback;

// Fetch student ID from session
$studentId = $_SESSION['student_id'];

// Fetch the public course details
$courseId = base64_decode($hashedId);
$course = PublicCourse::getById($conn, $courseId);

// Check if student is enrolled in this course
$isEnrolled = PublicCourse::isStudentEnrolled($conn, $studentId, $courseId);

// If not enrolled, redirect to courses page with message
if (!$isEnrolled) {
    $_SESSION['error'] = "You must be enrolled in this course to view its content.";
    header('Location: /student/manage_public_courses');
    exit;
}


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
                        <!-- Feedback Section for Archived Courses -->
                        <?php if ($course['feedback_enabled'] == 1): ?>
                            <div class="mt-4">
                                <h5>Feedback</h5>
                                <?php if (isset($_SESSION['feedback_submitted']) && $_SESSION['feedback_submitted']): ?>
                                    <p>Thank you for providing the feedback. Your feedback has been recorded.</p>
                                    <?php unset($_SESSION['feedback_submitted']); ?>
                                <?php elseif (feedback::hasFeedback($conn, $course['id'], $student['id'])): ?>
                                    <p>Thank you for providing the feedback. Your feedback is already recorded.</p>
                                <?php else: ?>
                                    <form method="post" action="/student/submit_public_feedback">
                                        <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course['id']); ?>">
                                        <div class="form-group">
                                            <label for="depth_of_coverage">Depth of Coverage</label>
                                            <select class="form-control" id="depth_of_coverage" name="depth_of_coverage">
                                                <option>Basic Level</option>
                                                <option>Intermediate Level</option>
                                                <option>Advance Level</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="emphasis_on_fundamentals">Emphasis on fundamentals</label>
                                            <select class="form-control" id="emphasis_on_fundamentals" name="emphasis_on_fundamentals">
                                                <option>Poor</option>
                                                <option>Satisfactory</option>
                                                <option>Good</option>
                                                <option>Very Good</option>
                                                <option>Excellent</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="coverage_of_modern_topics">Coverage of modern/advanced topics</label>
                                            <select class="form-control" id="coverage_of_modern_topics" name="coverage_of_modern_topics">
                                                <option>Poor</option>
                                                <option>Satisfactory</option>
                                                <option>Good</option>
                                                <option>Very Good</option>
                                                <option>Excellent</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="overall_rating">Overall Rating of the Course</label>
                                            <select class="form-control" id="overall_rating" name="overall_rating">
                                                <option>Poor</option>
                                                <option>Satisfactory</option>
                                                <option>Good</option>
                                                <option>Very Good</option>
                                                <option>Excellent</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="benefits">What benefits you derived from the course? What areas would you like to see improved?</label>
                                            <textarea class="form-control" id="benefits" name="benefits" rows="3"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label>The instructor demonstrated willingness to assist students with course content/Lab exercises when needed.</label>
                                            <select class="form-control" name="instructor_assistance">
                                                <option>Strongly Agree</option>
                                                <option>Agree</option>
                                                <option>Neutral</option>
                                                <option>Disagree</option>
                                                <option>Strongly disagree</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>The instructor provided adequate and timely feedback when I had a question.</label>
                                            <select class="form-control" name="instructor_feedback">
                                                <option>Strongly Agree</option>
                                                <option>Agree</option>
                                                <option>Neutral</option>
                                                <option>Disagree</option>
                                                <option>Strongly disagree</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Motivated me to learn the subject.</label>
                                            <select class="form-control" name="motivation">
                                                <option>Strongly Agree</option>
                                                <option>Agree</option>
                                                <option>Neutral</option>
                                                <option>Disagree</option>
                                                <option>Strongly disagree</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>SME Helped me a lot.</label>
                                            <select class="form-control" name="sme_help">
                                                <option>Strongly Agree</option>
                                                <option>Agree</option>
                                                <option>Neutral</option>
                                                <option>Disagree</option>
                                                <option>Strongly disagree</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Overall Very Good</label>
                                            <select class="form-control" name="overall_very_good">
                                                <option>Strongly Agree</option>
                                                <option>Agree</option>
                                                <option>Neutral</option>
                                                <option>Disagree</option>
                                                <option>Strongly disagree</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary mt-2">Submit Feedback</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>