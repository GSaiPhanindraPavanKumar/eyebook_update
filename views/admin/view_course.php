<?php
include("sidebar.php");


$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch course details from the database
$sql = "SELECT id, name, description, universities FROM courses WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();

// Ensure universities field is an array
$course['universities'] = isset($course['universities']) ? json_decode($course['universities'], true) : [];

// Fetch universities details
$universities = [];
if (!empty($course['universities'])) {
    $university_ids = implode(',', array_map('intval', $course['universities']));
    $sql_universities = "SELECT id, long_name FROM universities WHERE id IN ($university_ids)";
    $result_universities = $conn->query($sql_universities);
    while ($row = $result_universities->fetch_assoc()) {
        $universities[] = $row;
    }
}

$conn->close();
?>

<!-- HTML Content -->
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <!-- <h3 class="font-weight-bold">Hello, <em><?php echo htmlspecialchars($userData['name']); ?></em></h3> -->
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Course Details</h4>
                        <?php if ($course): ?>
                            <p><strong>Course Name:</strong> <?php echo htmlspecialchars($course['name']); ?></p>
                            <p><strong>Description:</strong> <?php echo htmlspecialchars($course['description']); ?></p>
                        <?php else: ?>
                            <p>Course not found.</p>
                        <?php endif; ?>
                    </div>
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
                                <a class="nav-link" id="add-unit-tab" data-toggle="tab" href="#add-unit" role="tab" aria-controls="add-unit" aria-selected="false">Add Unit</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="assign-universities-tab" data-toggle="tab" href="#assign-universities" role="tab" aria-controls="assign-universities" aria-selected="false">Assign to Universities</a>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                                <h5 class="mt-3">Course Details</h5>
                                <p><?php echo htmlspecialchars($course['description']); ?></p>
                                <h5 class="mt-3">Assigned Universities</h5>
                                <ul>
                                    <?php if (!empty($universities)): ?>
                                        <?php foreach ($universities as $university): ?>
                                            <li><?php echo htmlspecialchars($university['long_name']); ?></li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li>No universities assigned.</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <div class="tab-pane fade" id="add-unit" role="tabpanel" aria-labelledby="add-unit-tab">
                                <h5 class="mt-3">Add Unit</h5>
                                <form method="POST" action="add_unit.php" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="unitName">Unit Name</label>
                                        <input type="text" class="form-control" id="unitName" name="unit_name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="scormFile">SCORM File</label>
                                        <input type="file" class="form-control" id="scormFile" name="scorm_file" required>
                                    </div>
                                    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                                    <button type="submit" class="btn btn-primary">Add Unit</button>
                                </form>
                            </div>
                            <div class="tab-pane fade" id="assign-universities" role="tabpanel" aria-labelledby="assign-universities-tab">
                                <h5 class="mt-3">Assign Course to Universities</h5>
                                <form method="POST" action="assign_course.php">
                                    <div class="form-group">
                                        <label for="university">Select University</label>
                                        <select class="form-control" id="university" name="university_id" required>
                                            <option value="">Select a university</option>
                                            <?php foreach ($universities as $university): ?>
                                                <option value="<?php echo $university['id']; ?>"><?php echo htmlspecialchars($university['long_name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                                    <button type="submit" class="btn btn-primary">Assign Course</button>
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