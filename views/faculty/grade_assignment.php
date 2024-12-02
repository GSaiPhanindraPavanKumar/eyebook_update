<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12">
                        <h3 class="font-weight-bold">Grade Assignment</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                <form action="/faculty/grade_assignment/<?php echo $assignmentId; ?>/<?php echo $studentId; ?>" method="post">
        <div class="form-group">
            <label for="marks">Marks (out of 100):</label>
            <input type="number" class="form-control" id="marks" name="marks" value="<?php echo htmlspecialchars($submission['marks'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="feedback">Feedback:</label>
            <textarea class="form-control" id="feedback" name="feedback" required><?php echo htmlspecialchars($submission['feedback'] ?? ''); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Grade</button>
    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">