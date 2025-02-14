<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Upload Faculty</h4>
                        <?php if (isset($message)): ?>
                            <div class="alert alert-<?php echo $message_type; ?>" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label for="uploadType">Select Upload Type</label><br>
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-outline-primary active">
                                    <input type="radio" id="single" name="uploadType" value="single" checked> Single Faculty
                                </label>
                                <label class="btn btn-outline-primary">
                                    <input type="radio" id="bulk" name="uploadType" value="bulk"> Bulk Upload
                                </label>
                            </div>
                        </div>
                        <div id="singleUploadForm">
                            <form method="POST" action="/admin/uploadSingleFaculty">
                                <div class="form-group">
                                    <label for="university_id_single">Select University <span style="color: red;">*</span></label>
                                    <select id="university_id_single" name="university_id" class="form-control" required>
                                        <option value="">Select a university</option>
                                        <?php foreach ($universities as $university): ?>
                                            <option value="<?php echo htmlspecialchars($university['id']); ?>">
                                                <?php echo htmlspecialchars($university['long_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="name">Name <span style="color: red;">*</span></label>
                                    <input type="text" id="name" name="name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email <span style="color: red;">*</span></label>
                                    <input type="email" id="email" name="email" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="text" id="phone" name="phone" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="section">Section</label>
                                    <input type="text" id="section" name="section" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="stream">Stream</label>
                                    <input type="text" id="stream" name="stream" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="department">Department</label>
                                    <input type="text" id="department" name="department" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="password">Password <span style="color: red;">*</span></label>
                                    <input type="password" id="password" name="password" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-block">Upload Faculty</button>
                                </div>
                            </form>
                        </div>
                        <div id="bulkUploadForm" style="display: none;">
                            <a href="https://mobileappliaction.s3.us-east-1.amazonaws.com/Templates/Faculty.xlsx" class="btn btn-info mb-3" download>
                                <i class="fas fa-download"></i> Download Template
                            </a>
                            <form method="POST" action="/admin/uploadFaculty" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="university_id_bulk">Select University</label>
                                    <select id="university_id_bulk" name="university_id" class="form-control" required>
                                        <option value="">Select a university</option>
                                        <?php foreach ($universities as $university): ?>
                                            <option value="<?php echo htmlspecialchars($university['id']); ?>">
                                                <?php echo htmlspecialchars($university['long_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <div class="drag-drop-zone" id="excelDragZone">
                                        <input type="file" id="file" name="file" 
                                               accept=".xlsx,.xls" style="display: none;" required>
                                        <div class="icon">
                                            <i class="fas fa-file-excel"></i>
                                        </div>
                                        <p>Drag and drop your Excel file here or <span style="color: var(--menu-icon);">browse</span></p>
                                        <div class="file-requirements">
                                            Allowed formats: XLSX, XLS<br>
                                            Maximum file size: 10MB
                                        </div>
                                        <div id="selectedFile" class="selected-file" style="display: none;">
                                            <span class="file-name"></span>
                                            <span class="remove-file">
                                                <i class="fas fa-times"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-block">Upload Faculty</button>
                                </div>
                            </form>
                        </div>
                        <?php if (!empty($duplicateRecords)): ?>
                            <div class="alert alert-warning" role="alert">
                                <p>Duplicate records found:</p>
                                <ul>
                                    <?php foreach ($duplicateRecords as $record): ?>
                                        <li><?php echo htmlspecialchars($record['email'] . ' - ' . $record['name']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const singleUploadForm = document.getElementById('singleUploadForm');
                                const bulkUploadForm = document.getElementById('bulkUploadForm');
                                const uploadTypeRadios = document.getElementsByName('uploadType');

                                uploadTypeRadios.forEach(radio => {
                                    radio.addEventListener('change', function() {
                                        if (this.value === 'single') {
                                            singleUploadForm.style.display = 'block';
                                            bulkUploadForm.style.display = 'none';
                                        } else {
                                            singleUploadForm.style.display = 'none';
                                            bulkUploadForm.style.display = 'block';
                                        }
                                    });
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

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
                <p>Please upload an Excel file (XLSX or XLS) only. Other file formats are not accepted.</p>
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
                <p>The selected file exceeds the maximum size limit of 10MB. Please select a smaller file.</p>
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
const dragZone = document.getElementById('excelDragZone');
const fileInput = document.getElementById('file');
const selectedFileDiv = document.getElementById('selectedFile');
const selectedFileName = selectedFileDiv.querySelector('.file-name');
const removeFileBtn = selectedFileDiv.querySelector('.remove-file');

// Maximum file size in bytes (10MB)
const MAX_FILE_SIZE = 10 * 1024 * 1024;

// Allowed file types
const ALLOWED_TYPES = [
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];

function handleFile(file) {
    // Check file type
    if (!ALLOWED_TYPES.includes(file.type)) {
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

