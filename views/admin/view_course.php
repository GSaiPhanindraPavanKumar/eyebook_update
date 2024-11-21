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
                                <a class="nav-link active" id="course-details-tab" data-toggle="tab" href="#course-details" role="tab" aria-controls="course-details" aria-selected="true">Course Details</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="add-unit-tab" data-toggle="tab" href="#add-unit" role="tab" aria-controls="add-unit" aria-selected="false">Add Course Book</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="assign-universities-tab" data-toggle="tab" href="#assign-universities" role="tab" aria-controls="assign-universities" aria-selected="false">Assign to Universities</a>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="course-details" role="tabpanel" aria-labelledby="course-details-tab">
                                <?php if ($course): ?>
                                    <ul>
                                        <li><strong>Name:</strong> <?php echo htmlspecialchars($course['name']); ?></li>
                                        <li><strong>Description:</strong> <?php echo htmlspecialchars($course['description']); ?></li>
                                        <li><strong>Created At:</strong> <?php echo htmlspecialchars($course['created_at']); ?></li>
                                    </ul>
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
                                <h5 class="mt-3">Assign Course to Universities</h5>
                                <form method="POST" action="/admin/assign_course">
                                    <div class="form-group">
                                        <label for="universitySelect">Select Universities</label>
                                        <select multiple class="form-control" id="universitySelect" name="universities[]">
                                            <?php foreach ($universities as $university): ?>
                                                <option value="<?php echo htmlspecialchars($university['id']); ?>"><?php echo htmlspecialchars($university['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Assign</button>
                                </form>
                            </div>
                        </div>
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