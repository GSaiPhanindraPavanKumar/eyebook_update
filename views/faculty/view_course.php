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
                                <?php if ($course['status'] !== 'archived') : ?>
                                    <button type="button" class="btn btn-primary" onclick="toggleCoursePlanForm()">Upload</button>
                                <?php endif; ?>
                                <?php if (!empty($course['course_plan'])) : ?>
                                    <button class="btn btn-primary" onclick="redirectToCoursePlan()">View</button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($course['status'] !== 'archived') : ?>
                            <form id="uploadCoursePlanForm" action="upload_course_plan" method="post" enctype="multipart/form-data" style="display: none; margin-top: 10px;">
                                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                <input type="file" name="course_plan_file" accept="application/pdf" required>
                                <button type="submit" class="btn btn-success">Upload</button>
                                <button type="button" class="btn btn-secondary" onclick="toggleCoursePlanForm()">Cancel</button>
                            </form>
                        <?php endif; ?>

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
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($course['course_book'])) {
                                    $serialNumber = 1; 
                                    foreach ($course['course_book'] as $unit) {
                                        echo "<tr>";
                                        echo "<td>" . $serialNumber++ . "</td>"; // Increment the serial number
                                        echo "<td>" . htmlspecialchars($unit['unit_name']) . "</td>";
                                        $full_url = $unit['scorm_url'];
                                        echo "<td><a href='/student/view_book/" . $hashedId . "?index_path=" . urlencode($full_url) . "' class='btn btn-primary'>View Course Book</a></td>";
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
                            <?php if ($course['status'] !== 'archived') : ?>
                                <button type="button" class="btn btn-primary" onclick="toggleUploadOptions()">Upload</button>
                            <?php endif; ?>
                        </div>
                        <?php if ($course['status'] !== 'archived') : ?>
                            <div id="uploadOptions" class="text-center" style="display: none; margin-top: 10px;">
                                <button type="button" class="btn btn-info" onclick="showSingleUpload()">Single Document</button>
                                <button type="button" class="btn btn-warning" onclick="showBulkUpload()">Bulk Upload</button>
                            </div>
                            <form id="uploadCourseMaterialsForm" action="upload_course_materials" method="post" enctype="multipart/form-data" style="display: none; margin-top: 10px;">
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
                        <?php endif; ?>
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

                        <!-- Discussion Forum Section -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <h5>Discussion Forum</h5>
                            <a href="/faculty/discussion_forum/<?php echo $course['id']; ?>" class="btn btn-primary">Go to Discussion Forum</a>
                        </div>

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
    // var baseUrl = "https://eyebook.phemesoft.com/"; // Replace with your actual base URL
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
    // var baseUrl = "https://eyebook.phemesoft.com/"; // Replace with your actual base URL
    var courseBookUrl = encodeURIComponent(url);

    if (url) {
        window.location.href = courseBookUrl;
    } else {
        alert('Course Book URL not available.');
    }
}

function redirectToCourseMaterial(url) {
    //var baseUrl = "https://eyebook.phemesoft.com/"; // Replace with your actual base URL
    var courseMaterialUrl = url;

    if (url) {
        window.open(courseMaterialUrl, '_blank');
    } else {
        alert('Course Material URL not available.');
    }
}
</script>