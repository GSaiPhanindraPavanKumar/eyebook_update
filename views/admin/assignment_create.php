<?php include('sidebar.php'); ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Create Assignment</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Create Assignment</h5>
                        <?php if (!empty($messages)): ?>
                            <div class="messages">
                                <?php foreach ($messages as $message): ?>
                                    <p><?php echo htmlspecialchars($message); ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <form id="assignmentForm" action="/admin/create_assignment" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="assignment_title">Assignment Title:</label>
                                <input type="text" class="form-control" id="assignment_title" name="assignment_title" required>
                            </div>
                            <div class="form-group">
                                <label for="assignment_description">Assignment Description:</label>
                                <textarea class="form-control" id="assignment_description" name="assignment_description" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="start_date">Start Date:</label>
                                <input type="datetime-local" class="form-control" id="start_date" name="start_date" required>
                            </div>
                            <div class="form-group">
                                <label for="due_date">Due Date:</label>
                                <input type="datetime-local" class="form-control" id="due_date" name="due_date" required>
                            </div>
                            <div class="form-group">
                                <label for="course_id">Select Course</label>
                                <select class="form-control" id="course_id" name="course_id[]" required>
                                    <option value="">Select a course</option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="drag-drop-zone" id="assignmentDragZone">
                                    <input type="file" id="assignment_file" name="assignment_file" 
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
                            <button type="submit" class="btn btn-primary">Create Assignment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>

<!-- <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet"> -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('assignmentForm');
    const startDateInput = document.getElementById('start_date');
    const dueDateInput = document.getElementById('due_date');

    form.addEventListener('submit', function(event) {
        const now = new Date();
        const startDate = new Date(startDateInput.value);
        const dueDate = new Date(dueDateInput.value);

        if (startDate < now) {
            alert('Start date and time cannot be in the past.');
            event.preventDefault();
            return;
        }

        if (dueDate <= startDate) {
            alert('Due date and time must be after the start date and time.');
            event.preventDefault();
            return;
        }
    });
});
</script>

<!-- Add these modals -->
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
const fileInput = document.getElementById('assignment_file');
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