<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="font-weight-bold">View Assignment</h3>
                    <a href="/student/view_course/<?php echo str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($course_id)); ?>" class="btn btn-secondary">Back to Course</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($assignment['title']); ?></h5>
                        <p><strong>Start Time:</strong> <?php echo htmlspecialchars($assignment['start_time']); ?></p>
                        <p><strong>Due Date:</strong> <?php echo htmlspecialchars($assignment['due_date']); ?></p>

                        <?php
                        $current_time = new DateTime();
                        $current_time->modify('+5 hours +30 minutes'); // Adjust for -5 hours 30 minutes offset
                        $start_time = new DateTime($assignment['start_time']);
                        $due_date = new DateTime($assignment['due_date']);
                        if ($current_time < $start_time): ?>
                            <p><strong>Status:</strong> The assignment has not yet started.</p>
                        <?php else: ?>
                            <p><strong>Description:</strong> <?php echo htmlspecialchars($assignment['description']); ?></p>
                            <p><strong>File Content:</strong></p>
                            <?php if (!empty($assignment['file_content'])): ?>
                                <button id="viewButton" class="btn btn-info mb-3" onclick="toggleFileContent()">View File</button>
                                <div id="fileContent" style="display: none; margin-top: 20px;">
                                    <embed src="<?php echo htmlspecialchars($assignment['file_content']); ?>" type="application/pdf" width="100%" height="600px" />
                                </div>
                            <?php else: ?>
                                <p>No file attached.</p>
                            <?php endif; ?>

                            <?php if (!empty($student_submission)): ?>
                                <h5 class="mt-4">Your Submission</h5>
                                <button id="viewSubmissionButton" class="btn btn-info mb-3" onclick="toggleSubmissionContent()">View Submission</button>
                                <div id="submissionContent" style="display: none; margin-top: 20px;">
                                    <embed src="<?php echo htmlspecialchars($student_submission['file']); ?>" type="application/pdf" width="100%" height="600px" />
                                </div>
                                <?php if ($current_time <= $due_date && empty($student_submission['grade']) && empty($student_submission['feedback'])): ?>
                                    <form action="/student/delete_submission/<?php echo $assignment['id']; ?>" method="post" style="margin-top: 20px;">
                                        <button type="submit" class="btn btn-danger">Delete Submission</button>
                                    </form>
                                <?php endif; ?>
                                <?php if (!empty($student_submission['grade']) || !empty($student_submission['feedback'])): ?>
                                    <h5 class="mt-4">Grading</h5>
                                    <p><strong>Grade:</strong> <?php echo htmlspecialchars($student_submission['grade']); ?></p>
                                    <p><strong>Feedback:</strong> <?php echo htmlspecialchars($student_submission['feedback']); ?></p>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if ($current_time <= $due_date): ?>
                                    <form action="/student/submit_assignment/<?php echo $assignment['id']; ?>" method="post" enctype="multipart/form-data" style="margin-top: 20px;">
                                        <div class="form-group">
                                            <div class="drag-drop-zone" id="assignmentDragZone">
                                                <input type="file" id="submission_file" name="submission_file" 
                                                       accept="application/pdf" style="display: none;" required>
                                                <div class="icon">
                                                    <i class="fas fa-cloud-upload-alt"></i>
                                                </div>
                                                <p>Drag and drop your PDF file here or <span style="color: var(--menu-icon);">browse</span></p>
                                                <div class="file-requirements">
                                                    Allowed format: PDF only<br>
                                                    Maximum file size: 32MB
                                                </div>
                                                <div id="selectedFile" class="selected-file" style="display: none;">
                                                    <span class="file-name"></span>
                                                    <span class="remove-file">
                                                        <i class="fas fa-times"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Submit Assignment</button>
                                    </form>
                                <?php else: ?>
                                    <p><strong>Status:</strong> The assignment due date has passed. You can no longer submit or delete your submission.</p>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<script>
function toggleFileContent() {
    var fileContent = document.getElementById('fileContent');
    var viewButton = document.getElementById('viewButton');
    if (fileContent.style.display === 'none') {
        fileContent.style.display = 'block';
        viewButton.textContent = 'Hide File';
    } else {
        fileContent.style.display = 'none';
        viewButton.textContent = 'View File';
    }
}

function toggleSubmissionContent() {
    var submissionContent = document.getElementById('submissionContent');
    var viewSubmissionButton = document.getElementById('viewSubmissionButton');
    if (submissionContent.style.display === 'none') {
        submissionContent.style.display = 'block';
        viewSubmissionButton.textContent = 'Hide Submission';
    } else {
        submissionContent.style.display = 'none';
        viewSubmissionButton.textContent = 'View Submission';
    }
}
</script>

<!-- Add this modal for file format warning -->
<div class="modal fade" id="fileFormatModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Invalid File Format</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Please upload a PDF file only. Other file formats are not accepted.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add this modal for file size warning -->
<div class="modal fade" id="fileSizeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">File Too Large</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>The selected file exceeds the maximum size limit of 32MB. Please select a smaller file.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add these styles -->
<style>
.drag-drop-zone {
    border: 2px dashed var(--border-color);
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    background: var(--card-bg);
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
}

.drag-drop-zone.dragover {
    background: var(--hover-bg);
    border-color: var(--menu-icon);
}

.drag-drop-zone .icon {
    font-size: 2em;
    color: var(--text-color);
    margin-bottom: 10px;
}

.selected-file {
    margin-top: 10px;
    padding: 8px;
    background: var(--hover-bg);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.selected-file .file-name {
    margin-right: 10px;
    word-break: break-all;
}

.selected-file .remove-file {
    cursor: pointer;
    color: var(--text-color);
    opacity: 0.7;
}

.selected-file .remove-file:hover {
    opacity: 1;
}

.file-requirements {
    margin-top: 8px;
    font-size: 0.85em;
    color: var(--text-color);
    opacity: 0.7;
}
</style>

<!-- Add this script -->
<script>
// File upload handling
const dragZone = document.getElementById('assignmentDragZone');
const fileInput = document.getElementById('submission_file');
const selectedFileDiv = document.getElementById('selectedFile');
const selectedFileName = selectedFileDiv.querySelector('.file-name');
const removeFileBtn = selectedFileDiv.querySelector('.remove-file');

// Maximum file size in bytes (32MB)
const MAX_FILE_SIZE = 32 * 1024 * 1024;

function handleFile(file) {
    // Check file type
    if (file.type !== 'application/pdf') {
        $('#fileFormatModal').modal('show');
        return false;
    }

    // Check file size
    if (file.size > MAX_FILE_SIZE) {
        $('#fileSizeModal').modal('show');
        return false;
    }

    // Display selected file
    selectedFileName.textContent = file.name;
    selectedFileDiv.style.display = 'flex';
    return true;
}

// Drag and drop handlers
dragZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dragZone.classList.add('dragover');
});

dragZone.addEventListener('dragleave', () => {
    dragZone.classList.remove('dragover');
});

dragZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dragZone.classList.remove('dragover');
    
    const file = e.dataTransfer.files[0];
    if (file && handleFile(file)) {
        fileInput.files = e.dataTransfer.files;
    }
});

// Click to browse
dragZone.addEventListener('click', () => {
    fileInput.click();
});

// File input change
fileInput.addEventListener('change', () => {
    const file = fileInput.files[0];
    if (file) {
        handleFile(file);
    }
});

// Remove selected file
removeFileBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    fileInput.value = '';
    selectedFileDiv.style.display = 'none';
});
</script>