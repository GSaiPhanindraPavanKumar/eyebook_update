<?php include "sidebar.php"; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="font-weight-bold mb-4">Course Management: <?php echo htmlspecialchars($course['name']); ?></h2>
                    <span class="badge bg-primary text-white">Course ID: <?php echo $course['id']; ?></span>
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
                            <div>
                                <button type="button" class="btn btn-primary" onclick="toggleCoursePlanForm()">Upload</button>
                                <?php if (!empty($course['course_plan'])) : ?>
                                    <button class="btn btn-primary" onclick="redirectToCoursePlan()">View</button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <form id="uploadCoursePlanForm" action="upload_course_plan.php" method="post" enctype="multipart/form-data" style="display: none; margin-top: 10px;">
                            <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                            <input type="file" name="course_plan_file" accept="application/pdf" required>
                            <button type="submit" class="btn btn-success">Upload</button>
                            <button type="button" class="btn btn-secondary" onclick="toggleCoursePlanForm()">Cancel</button>
                        </form>

                        <!-- Course Book Section -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <h5>Course Book</h5>
                            <?php if (!empty($course['course_book'])) : ?>
                                <button class="btn btn-primary" onclick="redirectToCourseBook()">View</button>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Course Materials Section -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <h5>Course Materials</h5>
                            <button type="button" class="btn btn-primary" onclick="toggleUploadOptions()">Upload</button>
                        </div>
                        <div id="uploadOptions" class="text-center" style="display: none; margin-top: 10px;">
                            <button type="button" class="btn btn-info" onclick="showSingleUpload()">Single Document</button>
                            <button type="button" class="btn btn-warning" onclick="showBulkUpload()">Bulk Upload</button>
                        </div>
                        <form id="uploadCourseMaterialsForm" action="upload_course_materials.php" method="post" enctype="multipart/form-data" style="display: none; margin-top: 10px;">
                            <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                            <input type="hidden" name="upload_type" id="uploadType">
                            <div id="singleUpload" style="display: none;">
                                <div class="form-group">
                                    <label for="unitNumber">Unit Number</label>
                                    <input type="number" class="form-control" name="unit_number" id="unitNumber">
                                </div>
                                <div class="form-group">
                                    <label for="topic">Topic</label>
                                    <input type="text" class="form-control" name="topic" id="topic">
                                </div>
                                <div class="form-group d-flex justify-content-between align-items-center">
                                    <input type="file" name="course_materials_file" accept="application/pdf">
                                    <div>
                                        <button type="submit" class="btn btn-success">Upload</button>
                                        <button type="button" class="btn btn-secondary" onclick="toggleCourseMaterialsForm()">Cancel</button>
                                    </div>
                                </div>
                            </div>
                            <div id="bulkUpload" style="display: none;">
                                <div class="form-group">
                                    <label for="bulkUnitNumber">Unit Number</label>
                                    <input type="number" class="form-control" name="bulk_unit_number" id="bulkUnitNumber">
                                </div>
                                <div class="form-group d-flex justify-content-between align-items-center">
                                    <input type="file" name="bulk_course_materials_file" accept=".zip">
                                    <div>
                                        <button type="submit" class="btn btn-success">Upload</button>
                                        <button type="button" class="btn btn-secondary" onclick="toggleCourseMaterialsForm()">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </form>
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
                                                $base_url = "http://localhost/eyebook_update/"; // Replace with your actual base URL
                                                $full_url = $base_url . $material['indexPath'];
                                                echo "<td><a href='#' class='btn btn-primary' onclick='redirectToCourseMaterial(\"" . htmlspecialchars($full_url) . "\")'>View</a></td>";
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
function toggleCoursePlanForm() {
    var form = document.getElementById('uploadCoursePlanForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function toggleCourseMaterialsForm() {
    var form = document.getElementById('uploadCourseMaterialsForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function toggleUploadOptions() {
    var options = document.getElementById('uploadOptions');
    var form = document.getElementById('uploadCourseMaterialsForm');
    if (options.style.display === 'none') {
        options.style.display = 'block';
    } else {
        options.style.display = 'none';
        form.style.display = 'none';
    }
}

function showSingleUpload() {
    var singleUpload = document.getElementById('singleUpload');
    var bulkUpload = document.getElementById('bulkUpload');
    singleUpload.style.display = 'block';
    bulkUpload.style.display = 'none';
    var unitNumber = document.getElementById('unitNumber');
    var topic = document.getElementById('topic');
    var courseMaterialsFile = document.getElementById('course_materials_file');
    if (unitNumber && topic && courseMaterialsFile) {
        unitNumber.required = true;
        topic.required = true;
        courseMaterialsFile.required = true;
    }
    var bulkUnitNumber = document.getElementById('bulkUnitNumber');
    var bulkCourseMaterialsFile = document.getElementById('bulk_course_materials_file');
    if (bulkUnitNumber && bulkCourseMaterialsFile) {
        bulkUnitNumber.required = false;
        bulkCourseMaterialsFile.required = false;
    }
    document.getElementById('uploadType').value = 'single';
    document.getElementById('uploadCourseMaterialsForm').style.display = 'block';
}

function showBulkUpload() {
    var singleUpload = document.getElementById('singleUpload');
    var bulkUpload = document.getElementById('bulkUpload');
    singleUpload.style.display = 'none';
    bulkUpload.style.display = 'block';
    var unitNumber = document.getElementById('unitNumber');
    var topic = document.getElementById('topic');
    var courseMaterialsFile = document.getElementById('course_materials_file');
    if (unitNumber && topic && courseMaterialsFile) {
        unitNumber.required = false;
        topic.required = false;
        courseMaterialsFile.required = false;
    }
    var bulkUnitNumber = document.getElementById('bulkUnitNumber');
    var bulkCourseMaterialsFile = document.getElementById('bulk_course_materials_file');
    if (bulkUnitNumber && bulkCourseMaterialsFile) {
        bulkUnitNumber.required = true;
        bulkCourseMaterialsFile.required = true;
    }
    document.getElementById('uploadType').value = 'bulk';
    document.getElementById('uploadCourseMaterialsForm').style.display = 'block';
}

function redirectToCoursePlan() {
    var coursePlan = <?php echo json_encode($course['course_plan']); ?>;
    var baseUrl = "http://localhost/eyebook_update/"; // Replace with your actual base URL
    var coursePlanUrl = "";
    if (coursePlan && coursePlan.url) {
        coursePlanUrl = baseUrl + coursePlan.url;
    }

    if (coursePlanUrl) {
        window.open(coursePlanUrl, '_blank');
    } else {
        alert('Course Plan URL not available.');
    }
}

function redirectToCourseBook() {
    var courseMaterials = <?php echo json_encode($course['course_book']); ?>;
    var baseUrl = "http://localhost/eyebook_update/"; // Replace with your actual base URL
    var courseBookUrl = "";

    if (courseMaterials.length > 0 && courseMaterials[0].materials.length > 0) {
        courseBookUrl = baseUrl + courseMaterials[0].materials[0].indexPath;
    }

    if (courseBookUrl) {
        window.open(courseBookUrl, '_blank');
    } else {
        alert('Course Book URL not available.');
    }
}

function redirectToCourseMaterial(url) {
    var baseUrl = "http://localhost/eyebook_update/"; // Replace with your actual base URL
    var courseMaterialUrl = baseUrl + url;

    if (url) {
        window.open(courseMaterialUrl, '_blank');
    } else {
        alert('Course Material URL not available.');
    }
}
</script>