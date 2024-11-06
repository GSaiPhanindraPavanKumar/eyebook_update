<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title mb-0" style="font-size:x-large">Upload Students</p><br>
                        <?php if (isset($message)): ?>
                            <div class="alert alert-<?php echo $message_type; ?>" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php endif; ?>
                        <form method="POST" action="/admin/uploadStudents" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="university_id">Select University</label>
                                <select id="university_id" name="university_id" class="form-control" required>
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
                                <button type="submit" class="btn btn-primary">Upload Students</button>
                            </div>
                        </form>
                        <?php if (!empty($duplicateRecords)): ?>
                            <div class="alert alert-warning" role="alert">
                                <p>Duplicate records found:</p>
                                <ul>
                                    <?php foreach ($duplicateRecords as $record): ?>
                                        <li><?php echo htmlspecialchars($record['regd_no'] . ' - ' . $record['email'] . ' - ' .$record['name']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
                        <script>
                            <?php if (isset($message)): ?>
                                toastr.<?php echo $message_type; ?>("<?php echo htmlspecialchars($message); ?>");
                            <?php endif; ?>
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