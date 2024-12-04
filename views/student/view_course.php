<?php include "sidebar.php"; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="font-weight-bold mb-4">Course Management: <?php echo htmlspecialchars($course['name'] ?? ''); ?></h2>
                    <span class="badge bg-primary text-white">Course ID: <?php echo htmlspecialchars($course['id'] ?? ''); ?></span>
                </div>
            </div>
        </div>

        <!-- Overview and Course Control Panel -->
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Course Overview</h4>
                        
                        <!-- Course Plan Section -->
                        <div class="d-flex justify-content-between align-items-center">
                            <h5>Course Plan</h5>
                            <?php if (!empty($course['course_plan'])) : ?>
                                <?php
                                $hashedId = base64_encode($course['id']);
                                $hashedId = str_replace(['+', '/', '='], ['-', '_', ''], $hashedId);
                                ?>
                                <a href="/student/view_course_plan/<?php echo $hashedId; ?>" class="btn btn-primary">View</a>
                            <?php endif; ?>
                        </div>

                        <!-- Course Book Section -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <h5>Course Book</h5>
                            <?php if (!empty($course['course_book'])) : ?>
                                <?php
                                $hashedId = base64_encode($course['id']);
                                $hashedId = str_replace(['+', '/', '='], ['-', '_', ''], $hashedId);
                                ?>
                            <?php endif; ?>
                        </div>

                        <table class="table table-hover mt-2">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">S. No.</th>
                                    <th scope="col">Unit Title</th>
                                    <th scope="col">Action</th>
                                    <th scope="col">Completed</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $studentCompletedBooks = json_decode($student['completed_books'] ?? '[]', true)[$course['id']] ?? [];
                                if (!empty($course['course_book'])) {
                                    $serialNumber = 1; 
                                    foreach ($course['course_book'] as $unit) {
                                        $isCompleted = in_array($unit['scorm_url'], $studentCompletedBooks);
                                        echo "<tr>";
                                        echo "<td>" . $serialNumber++ . "</td>"; // Increment the serial number
                                        echo "<td>" . htmlspecialchars($unit['unit_name']) . "</td>";
                                        $full_url = $unit['scorm_url'];
                                        echo "<td><a href='/student/view_book/" . $hashedId . "?index_path=" . urlencode($full_url) . "' class='btn btn-primary'>View Course Book</a></td>";
                                        if ($course['status'] !== 'archived') {
                                            echo "<td><button class='btn btn-success' onclick='markAsCompleted(\"" . htmlspecialchars($full_url) . "\", " . ($isCompleted ? "true" : "false") . ", this)'>" . ($isCompleted ? "Completed" : "Mark as Completed") . "</button></td>";
                                        } else {
                                            echo "<td>" . ($isCompleted ? "Completed" : "") . "</td>";
                                        }
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4'>No course books available.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                        
                        <!-- Course Materials Section -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <h5>Course Materials</h5>
                        </div>
                        <table class="table table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">Unit Number</th>
                                    <th scope="col">Topic</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($course['course_materials'])) {
                                    foreach ($course['course_materials'] as $unit) {
                                        if (isset($unit['materials'])) {
                                            foreach ($unit['materials'] as $material) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($unit['unitNumber']) . "</td>";
                                                echo "<td>" . htmlspecialchars($unit['topic']) . "</td>";
                                                $full_url = $material['indexPath'];
                                                echo "<td><a href='/student/view_material/" . $hashedId . "?index_path=" . urlencode($full_url) . "' class='btn btn-primary'>View Material</a></td>";
                                                echo "</tr>";
                                            }
                                        }
                                    }
                                } else {
                                    echo "<tr><td colspan='3'>No course materials available.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
<?php include 'footer.html'; ?>
</div>

<script>
function redirectToCoursePlan() {
    var coursePlan = <?php echo json_encode($course['course_plan'] ?? []); ?>;
    var coursePlanUrl = "";
    if (coursePlan && coursePlan.url) {
        coursePlanUrl = coursePlan.url;
    }

    if (coursePlanUrl) {
        window.open(coursePlanUrl, '_blank');
    } else {
        alert('Course Plan URL not available.');
    }
}

function redirectToCourseBook(url) {
    var courseBookUrl = url;

    if (hashedId) {
        window.location.href = courseBookUrl;
    } else {
        alert('Course Book URL not available.');
    }
}

function redirectToCourseMaterial(url) {
    var courseMaterialUrl = url;

    if (url) {
        window.open(courseMaterialUrl, '_blank');
    } else {
        alert('Course Material URL not available.');
    }
}

function markAsCompleted(indexPath, isCompleted, button) {
    if (isCompleted) {
        alert('This course book is already marked as completed.');
        return;
    }

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "/student/mark_as_completed", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            alert('Course book marked as completed.');
            button.innerHTML = 'Completed';
            button.disabled = true;
        }
    };
    xhr.send("indexPath=" + encodeURIComponent(indexPath) + "&course_id=<?php echo $course['id']; ?>");
}
</script>