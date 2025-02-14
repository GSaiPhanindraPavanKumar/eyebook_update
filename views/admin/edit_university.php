<?php 
include "sidebar.php"; 
?>

<!-- HTML Content -->
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Edit University</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Edit University</h5>
                        <?php if ($message): ?>
                            <div class="alert alert-info"><?php echo $message; ?></div>
                            <?php if ($message_type == 'success'): ?>
                                <script>
                                    setTimeout(function() {
                                        window.location.href = '/admin/view_university/<?php echo $university['id']; ?>';
                                    }, 3000);
                                </script>
                            <?php endif; ?>
                        <?php endif; ?>
                        <form action="/admin/edit_university/<?php echo $university['id']; ?>" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="long_name">Long Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="long_name" name="long_name" value="<?php echo htmlspecialchars($university['long_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="short_name">Short Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="short_name" name="short_name" value="<?php echo htmlspecialchars($university['short_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="location">Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($university['location']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="country">Country <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="country" name="country" value="<?php echo htmlspecialchars($university['country']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="spoc_name">SPOC Name</label>
                                <input type="text" class="form-control" id="spoc_name" name="spoc_name" value="<?php echo isset($spoc['name']) ? htmlspecialchars($spoc['name']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="spoc_email">SPOC Email</label>
                                <input type="email" class="form-control" id="spoc_email" name="spoc_email" value="<?php echo isset($spoc['email']) ? htmlspecialchars($spoc['email']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="spoc_phone">SPOC Phone</label>
                                <input type="text" class="form-control" id="spoc_phone" name="spoc_phone" value="<?php echo isset($spoc['phone']) ? htmlspecialchars($spoc['phone']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="spoc_password">SPOC Password</label>
                                <input type="password" class="form-control" id="spoc_password" name="spoc_password">
                            </div>
                            <div class="form-group">
                                <label for="logo">University Logo</label>
                                <div class="drag-drop-zone" id="logoDragZone">
                                    <input type="file" id="logo" name="logo" 
                                           accept="image/png,image/jpeg,image/jpg" style="display: none;">
                                    <div class="icon">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </div>
                                    <p>Drag and drop your image here or <span style="color: var(--menu-icon);">browse</span></p>
                                    <div class="file-requirements">
                                        Allowed formats: PNG, JPEG, JPG<br>
                                        Maximum file size: 5MB
                                    </div>
                                    <?php if (!empty($university['logo'])): ?>
                                        <div id="currentFile" class="selected-file">
                                            <span class="file-name">Current logo: <?= basename($university['logo']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div id="selectedFile" class="selected-file" style="display: none;">
                                        <span class="file-name"></span>
                                        <span class="remove-file">
                                            <i class="fas fa-times"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Update University</button>
                            <a href="/admin/view_university/<?php echo $university['id']; ?>" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<!-- Include Bootstrap CSS -->
<!-- <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet"> -->

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
                <p>Please upload a PNG, JPEG, or JPG file only. Other file formats are not accepted.</p>
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
                <p>The selected file exceeds the maximum size limit of 5MB. Please select a smaller file.</p>
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
const dragZone = document.getElementById('logoDragZone');
const fileInput = document.getElementById('logo');
const selectedFileDiv = document.getElementById('selectedFile');
const selectedFileName = selectedFileDiv.querySelector('.file-name');
const removeFileBtn = selectedFileDiv.querySelector('.remove-file');
const currentFileDiv = document.getElementById('currentFile');

// Maximum file size in bytes (5MB)
const MAX_FILE_SIZE = 5 * 1024 * 1024;

// Allowed file types
const ALLOWED_TYPES = ['image/png', 'image/jpeg', 'image/jpg'];

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
    if (currentFileDiv) {
        currentFileDiv.style.display = 'none';
    }
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
    if (currentFileDiv) {
        currentFileDiv.style.display = 'flex';
    }
});
</script>