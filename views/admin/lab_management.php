<?php include "sidebar.php"; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <!-- Add Lab Assignment Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="card-title">Add Lab Assignment</h4>
                        <form action="/admin/add_lab_assignment" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Lab Name</label>
                                <input type="text" class="form-control" name="lab_name" required>
                            </div>
                            <div class="form-group">
                                <label>Lab Description</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Assignment Title</label>
                                <input type="text" class="form-control" name="assignment_title" required>
                            </div>
                            <div class="form-group">
                                <label>Due Date</label>
                                <input type="datetime-local" class="form-control" name="due_date" required>
                            </div>
                            <div class="form-group">
                                <label>Upload Lab File</label>
                                <input type="file" class="form-control" name="lab_file">
                            </div>
                            <button type="submit" class="btn btn-primary">Add Lab Assignment</button>
                        </form>
                    </div>
                </div>

                <!-- Labs List -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Lab Management</h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Lab Name</th>
                                        <th>Assignment Title</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($labs as $lab): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($lab['lab_name']); ?></td>
                                        <td><?php echo htmlspecialchars($lab['assignment_title']); ?></td>
                                        <td><?php echo $lab['due_date'] ? date('Y-m-d H:i', strtotime($lab['due_date'])) : 'N/A'; ?></td>
                                        <td><?php echo htmlspecialchars($lab['status']); ?></td>
                                        <td>
                                            <a href="edit_lab?id=<?php echo $lab['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                            <a href="delete_lab?id=<?php echo $lab['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>