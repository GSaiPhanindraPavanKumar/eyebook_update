<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="font-weight-bold">Edit Assignment</h3>
                    <div>
                        <!-- <a href="/admin/manage_assignments" class="btn btn-secondary">Back to Assignments</a> -->
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <form id="edit-assignment-form" method="post" action="/admin/edit_assignment/<?php echo $assignment['id']; ?>" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($assignment['title']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($assignment['description']); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="start_time">Start Time</label>
                                <input type="datetime-local" class="form-control" id="start_time" name="start_time" value="<?php echo date('Y-m-d\TH:i', strtotime($assignment['start_time'])); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="due_date">Due Date</label>
                                <input type="datetime-local" class="form-control" id="due_date" name="due_date" value="<?php echo date('Y-m-d\TH:i', strtotime($assignment['due_date'])); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="courses">Select Courses</label>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Select</th>
                                            <th>Course Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $selected_courses = json_decode($assignment['course_id'], true);
                                        foreach ($courses as $course): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="course_id[]" value="<?php echo $course['id']; ?>" <?php echo in_array($course['id'], $selected_courses) ? 'checked' : ''; ?>>
                                                </td>
                                                <td><?php echo htmlspecialchars($course['name']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="form-group">
                                <label for="file_content">File Content</label>
                                <input type="file" class="form-control" id="file_content" name="file_content">
                                <?php if (!empty($assignment['file_content'])): ?>
                                    <p>Current file: <a href="<?php echo htmlspecialchars($assignment['file_content']); ?>" target="_blank">View File</a></p>
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Assignment</button>
                            <a href="/admin/manage_assignments" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<script>
document.getElementById('edit-assignment-form').addEventListener('submit', function(event) {
    const currentDateTime = new Date();
    const startTimeInput = document.getElementById('start_time');
    const dueDateInput = document.getElementById('due_date');
    const originalStartTime = new Date('<?php echo date('Y-m-d\TH:i', strtotime($assignment['start_time'])); ?>');
    const originalDueDate = new Date('<?php echo date('Y-m-d\TH:i', strtotime($assignment['due_date'])); ?>');
    const newStartTime = new Date(startTimeInput.value);
    const newDueDate = new Date(dueDateInput.value);

    if (currentDateTime > originalStartTime && newStartTime < originalStartTime) {
        alert('You cannot set the start time to an earlier date.');
        event.preventDefault();
    }

    if (currentDateTime > originalDueDate && newDueDate < originalDueDate) {
        alert('You cannot set the due date to an earlier date.');
        event.preventDefault();
    }
});
</script>