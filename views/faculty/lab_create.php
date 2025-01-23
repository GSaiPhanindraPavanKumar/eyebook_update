<?php include('sidebar.php'); ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Create Lab</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Create Lab</h5>
                        <?php if (!empty($messages)): ?>
                            <div class="messages">
                                <?php foreach ($messages as $message): ?>
                                    <p><?php echo htmlspecialchars($message); ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <form action="/faculty/create_lab" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="lab_title">Lab Title:</label>
                                <input type="text" class="form-control" id="lab_title" name="lab_title" required>
                            </div>
                            <div class="form-group">
                                <label for="lab_description">Lab Description:</label>
                                <textarea class="form-control" id="lab_description" name="lab_description" required></textarea>
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
                                <label for="input">Input:</label>
                                <textarea class="form-control" id="input" name="input"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="output">Output:</label>
                                <textarea class="form-control" id="output" name="output"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Create Lab</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>

<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">