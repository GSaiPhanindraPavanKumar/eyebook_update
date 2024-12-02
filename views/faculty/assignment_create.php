<?php
include('sidebar.php');
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <!-- <h3 class="font-weight-bold">Hello, <em><?php echo htmlspecialchars($userData['name']); ?></em></h3> -->
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
                        <form action="/faculty/create_assignment" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="assignment_title">Assignment Title:</label>
                                <input type="text" class="form-control" id="assignment_title" name="assignment_title" required>
                            </div>
                            <div class="form-group">
                                <label for="assignment_instructions">Assignment Instructions:</label>
                                <textarea class="form-control" id="assignment_instructions" name="assignment_instructions" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="due_date">Due Date:</label>
                                <input type="datetime-local" class="form-control" id="due_date" name="due_date" required>
                            </div>
                            <div class="form-group">
                                <label for="course_id">Course:</label>
                                <select class="form-control" id="course_id" name="course_id" required>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="section_id">Section:</label>
                                <select class="form-control" id="section_id" name="section_id" required>
                                    <?php foreach ($sections as $section): ?>
                                        <option value="<?php echo $section['id']; ?>"><?php echo htmlspecialchars($section['section']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="assignment_file">Upload Assignment File:</label>
                                <input type="file" class="form-control" id="assignment_file" name="assignment_file">
                            </div>
                            <button type="submit" class="btn btn-primary">Create Assignment</button>
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
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">