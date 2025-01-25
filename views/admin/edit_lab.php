<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="font-weight-bold">Edit Lab</h3>
                    <a href="/admin/view_labs_by_course/<?php echo $course_ids[0]; ?>" class="btn btn-secondary">Back to Labs</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card"><br>
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="/admin/edit_lab/<?php echo $lab['id']; ?>">
                            <div class="form-group">
                                <label for="title">Lab Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($lab['title']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description" required><?php echo htmlspecialchars($lab['description']); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="due_date">Due Date</label>
                                <input type="datetime-local" class="form-control" id="due_date" name="due_date" value="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($lab['due_date']))); ?>" required>
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
                                        $selected_courses = json_decode($lab['course_id'], true);
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
                                <label for="input">Input</label>
                                <textarea class="form-control" id="input" name="input" required><?php echo htmlspecialchars($lab['input']); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="output">Output</label>
                                <textarea class="form-control" id="output" name="output" required><?php echo htmlspecialchars($lab['output']); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Lab</button>
                            <a href="/admin/view_labs_by_course/<?php echo $course_ids[0]; ?>" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>