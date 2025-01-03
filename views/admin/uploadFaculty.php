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
                                    <label for="university_id_single">Select University</label>
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
                                    <label for="name">Name</label>
                                    <input type="text" id="name" name="name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="text" id="phone" name="phone" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="section">Section</label>
                                    <input type="text" id="section" name="section" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="stream">Stream</label>
                                    <input type="text" id="stream" name="stream" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="department">Department</label>
                                    <input type="text" id="department" name="department" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="password">Password</label>
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
                                    <label for="file">Upload File</label>
                                    <input type="file" id="file" name="file" class="form-control" required>
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

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">