<?php
include 'sidebar.php';
use Models\Database;
use Models\PublicCourse;

$conn = Database::getConnection();
if (!isset($_SESSION['student_id'])) {
    die('Student ID not set in session.');
}
$studentId = $_SESSION['student_id']; // Assuming student ID is stored in session

// Fetch enrolled courses
$enrolledCourses = PublicCourse::getEnrolledCourses($conn, $studentId);

// Fetch featured courses
$featuredCourses = PublicCourse::getFeaturedCourses($conn);

// Create an array of enrolled course IDs for easy lookup
$enrolledCourseIds = array_map(function($course) {
    return $course['id'];
}, $enrolledCourses);

function custom_base64_encode($data) {
    $encoded = base64_encode($data);
    return str_replace(['+', '/', '='], ['-', '_', ''], $encoded);
}
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Manage Public Courses</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin">
                <p class="card-title mb-0" style="font-size:x-large">Enrolled Courses</p><br>
                <div class="row">
                    <?php if (empty($enrolledCourses)): ?>
                        <p>You are not enrolled in any courses.</p>
                    <?php else: ?>
                        <?php foreach ($enrolledCourses as $course): ?>
                            <div class="col-md-4 d-flex align-items-stretch">
                                <div class="card mb-4" style="width: 100%;">
                                    <div class="card-body">
                                        <h5 class="card-title" style="font-family:cursive"><?php echo htmlspecialchars($course['name']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($course['description']); ?></p>
                                        <?php
                                        $hashedId = custom_base64_encode($course['id']);
                                        ?>
                                        <a href="/student/view_public_course/<?php echo $hashedId; ?>" class="btn btn-primary">View Course</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <p class="card-title mb-0" style="font-size:x-large">Featured Courses</p><br>
                <div class="row">
                    <?php if (empty($featuredCourses)): ?>
                        <p>No featured courses available.</p>
                    <?php else: ?>
                        <?php foreach ($featuredCourses as $course): ?>
                            <div class="col-md-4 d-flex align-items-stretch">
                                <div class="card mb-4" style="width: 100%;">
                                    <div class="card-body">
                                        <h5 class="card-title" style="font-family:cursive"><?php echo htmlspecialchars($course['name']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($course['description']); ?></p>
                                        <p class="card-text">Price: Rs<?php echo htmlspecialchars($course['price']); ?></p>
                                        <?php if (!in_array($course['id'], $enrolledCourseIds)): ?>
                                            <?php if ($course['price'] > 0): ?>
                                                <form action="/student/pay_for_course" method="POST">
                                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                                    <input type="hidden" name="amount" value="<?php echo $course['price']; ?>">
                                                    <button type="submit" class="btn btn-success">Enroll</button>
                                                </form>
                                            <?php else: ?>
                                                <form action="/student/enroll_in_course" method="POST">
                                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                                    <button type="submit" class="btn btn-success">Enroll</button>
                                                </form>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <button class="btn btn-secondary" disabled>Enrolled</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>