<?php
include "sidebar.php";
?>
<!-- HTML Content -->
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
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="details-tab" data-toggle="tab" href="#details" role="tab" aria-controls="details" aria-selected="true">Details</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="add-unit-tab" data-toggle="tab" href="#add-unit" role="tab" aria-controls="add-unit" aria-selected="false">Add Course Book</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="assign-universities-tab" data-toggle="tab" href="#assign-universities" role="tab" aria-controls="assign-universities" aria-selected="false">Assign to Universities</a>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                                <h5 class="mt-3">Course Details</h5>
                                <?php if ($course): ?>
                                    <p><strong>Course Name:</strong> <?php echo htmlspecialchars($course['name'] ?? 'N/A'); ?></p>
                                    <p><strong>Description:</strong> <?php echo htmlspecialchars($course['description'] ?? 'N/A'); ?></p>
                                    <h5 class="mt-3">Assigned University</h5>
                                    <ul>
                                        <?php if (!empty($course['university_id'])): ?>
                                            <?php
                                            $university = array_filter($universities, function($u) use ($course) {
                                                return $u['id'] == $course['university_id'];
                                            });
                                            $university = array_shift($university);
                                            ?>
                                            <li><?php echo htmlspecialchars($university['long_name']); ?></li>
                                        <?php else: ?>
                                            <li>No university assigned.</li>
                                        <?php endif; ?>
                                    </ul>
                                    <div class="actions mt-4">
                                        <a href="/admin/edit_course/<?php echo $course['id']; ?>" class="btn btn-warning">Edit Course</a>
                                        <a href="/admin/delete_course/<?php echo $course['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this course?');">Delete Course</a>
                                    </div>
                                <?php else: ?>
                                    <p>Course not found.</p>
                                <?php endif; ?>
                            </div>
                            <div class="tab-pane fade" id="add-unit" role="tabpanel" aria-labelledby="add-unit-tab">
                                <h5 class="mt-3">Add Course Book</h5>
                                <form method="POST" action="/admin/add_unit" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="unitName">Name</label>
                                        <input type="text" class="form-control" id="unitName" name="unit_name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="scormFile">SCORM File</label>
                                        <input type="file" class="form-control" id="scormFile" name="scorm_file" required>
                                    </div>
                                    <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course['id']); ?>">
                                    <button type="submit" class="btn btn-primary">Add Course Book</button>
                                </form>
                            </div>
                            <div class="tab-pane fade" id="assign-universities" role="tabpanel" aria-labelledby="assign-universities-tab">
                                <h5 class="mt-3">Assign Course to University</h5>
                                <form id="assignCourseForm" method="POST" action="/admin/assign_course">
                                    <div class="form-group">
                                        <label for="university">Select University</label>
                                        <select class="form-control" id="university" name="university_id" required>
                                            <option value="">Select a university</option>
                                            <?php foreach ($universities as $university): ?>
                                                <option value="<?php echo $university['id']; ?>"><?php echo htmlspecialchars($university['long_name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                    <input type="hidden" name="confirm" id="confirm" value="false">
                                    <button type="submit" class="btn btn-primary">Assign Course</button>
                                </form>
                            </div>
                        </div>
                        <?php if (isset($message)): ?>
                            <div class="alert alert-info"><?php echo $message; ?></div>
                            <script>
                                setTimeout(function() {
                                    window.location.href = 'manage_courses.php';
                                }, 3000); // Redirect after 3 seconds
                            </script>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<!-- Include Bootstrap CSS and JS -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
document.getElementById('assignCourseForm').addEventListener('submit', function(event) {
    event.preventDefault();
    var form = this;
    var formData = new FormData(form);
    fetch(form.action, {
        method: form.method,
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.confirm) {
            if (confirm(data.message)) {
                document.getElementById('confirm').value = 'true';
                fetch(form.action, {
                    method: form.method,
                    body: new FormData(form)
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.message === 'Course reassigned to university successfully' || data.message === 'Course assigned to university successfully') {
                        location.reload(); // Refresh the page
                    }
                });
            }
        } else {
            alert(data.message);
            if (data.message === 'Course assigned to university successfully') {
                location.reload(); // Refresh the page
            }
        }
    });
});
</script>