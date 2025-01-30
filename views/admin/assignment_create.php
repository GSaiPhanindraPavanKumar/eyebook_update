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
                                <label for="courses">Select Courses</label>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Select</th>
                                            <th>Course Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($courses as $course): ?>
                                            <tr>
                                                <td><input type="checkbox" name="course_id[]" value="<?php echo $course['id']; ?>"></td>
                                                <td><?php echo htmlspecialchars($course['name']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="form-group">
                                <label for="assignment_file">Upload Assignment File:</label>
                                <input type="file" class="form-control" id="assignment_file" name="assignment_file" accept=".pdf">
                                <small class="form-text text-muted">Allowed file formats: .pdf</small>
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

<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

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