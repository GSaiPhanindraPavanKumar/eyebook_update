<?php include("sidebar.php"); ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="font-weight-bold mb-4">Cohort Management: <?php echo htmlspecialchars($cohort['name']); ?></h2>
                    <span class="badge bg-primary text-white">Cohort ID: <?php echo htmlspecialchars($cohort['id']); ?></span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <!-- Cohort Details Section -->
                        <h4 class="card-title mt-3">Cohort Details</h4>
                        <p><strong>Cohort Name:</strong> <?php echo htmlspecialchars($cohort['name']); ?></p>
                        
                        <!-- Courses Section -->
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mt-3">Courses</h5>
                            <button id="addCourseButton" class="btn btn-primary">Add Courses</button>
                        </div>
                        <ul>
                            <?php if (!empty($cohort['course_ids'])): ?>
                                <?php
                                $course_ids = is_array($cohort['course_ids']) ? $cohort['course_ids'] : json_decode($cohort['course_ids'], true);
                                if (is_array($course_ids)) {
                                    foreach ($course_ids as $course_id) {
                                        $course = array_filter($courses, function($c) use ($course_id) {
                                            return $c['id'] == $course_id;
                                        });
                                        $course = array_shift($course);
                                        if ($course) {
                                            echo '<li>' . htmlspecialchars($course['name']) . ' <a href="/admin/unassign_course_from_cohort/' . $cohort['id'] . '/' . $course_id . '" class="text-danger small">(Unassign)</a></li>';
                                        }
                                    }
                                } else {
                                    echo '<li>No course assigned.</li>';
                                }
                                ?>
                            <?php else: ?>
                                <li>No course assigned.</li>
                            <?php endif; ?>
                        </ul>
                        <div id="addCourseForm" class="mt-3" style="display: none;">
                            <form method="POST" action="/admin/assign_courses_to_cohort">
                                <input type="hidden" name="cohort_id" value="<?php echo htmlspecialchars($cohort['id']); ?>">
                                <div class="table-responsive">
                                    <table class="table table-hover table-borderless table-striped">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Select</th>
                                                <th>Course Name</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($allCourses as $course): ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" name="course_ids[]" value="<?php echo $course['id']; ?>">
                                                    </td>
                                                    <td><?php echo htmlspecialchars($course['name']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit</button>
                                <button type="button" id="cancelAddCourse" class="btn btn-secondary">Cancel</button>
                            </form>
                        </div>
                        
                        <!-- Assigned Students Section -->
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mt-3">Assigned Students</h5>
                            <button id="unassignStudentButton" class="btn btn-danger">Unassign Students</button>
                        </div>
                        <form id="unassignStudentForm" method="POST" action="/admin/unassign_students_from_cohort">
                            <input type="hidden" name="cohort_id" value="<?php echo htmlspecialchars($cohort['id']); ?>">
                            <div class="table-responsive">
                                <table class="table table-hover table-borderless table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Select</th>
                                            <th>Student Name</th>
                                            <th>University</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="student_ids[]" value="<?php echo $student['id']; ?>">
                                                </td>
                                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                                <td>
                                                    <?php
                                                    $university = array_filter($universities, function($u) use ($student) {
                                                        return $u['id'] == $student['university_id'];
                                                    });
                                                    $university = array_shift($university);
                                                    echo htmlspecialchars($university['long_name'] ?? 'Unknown');
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </form>
                        
                        <!-- Add Students Section -->
                        <h5 class="mt-3">Add Students</h5>
                        <form method="POST" action="/admin/add_students_to_cohort/<?php echo $cohort['id']; ?>">
                            <div class="form-group">
                                <label for="student_ids">Select Students to Add</label>
                                <div class="table-responsive">
                                    <table class="table table-hover table-borderless table-striped">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Select</th>
                                                <th>Student Name</th>
                                                <th>University</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($allStudents as $student): ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" name="student_ids[]" value="<?php echo $student['id']; ?>" <?php echo in_array($student['id'], $existing_student_ids) ? 'disabled' : ''; ?>>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                                    <td>
                                                        <?php
                                                        $university = array_filter($universities, function($u) use ($student) {
                                                            return $u['id'] == $student['university_id'];
                                                        });
                                                        $university = array_shift($university);
                                                        echo htmlspecialchars($university['long_name'] ?? 'Unknown');
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Students</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>

<!-- Include Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    $(document).ready(function () {
        $('#addCourseButton').on('click', function () {
            $('#addCourseForm').show();
        });

        $('#cancelAddCourse').on('click', function () {
            $('#addCourseForm').hide();
        });

        $('#unassignStudentButton').on('click', function () {
            $('#unassignStudentForm').submit();
        });
    });
</script>