<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">Grade Assignment</h3>
                        <form action="/faculty/grade_assignment/<?= $assignment_id ?>/<?= $student_id ?>" method="post">
                            <div class="form-group">
                                <label for="grade">Grade</label>
                                <input type="text" class="form-control" id="grade" name="grade" value="<?= htmlspecialchars($submission['grade'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="feedback">Feedback</label>
                                <textarea class="form-control" id="feedback" name="feedback" required><?= htmlspecialchars($submission['feedback'] ?? '') ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Grade</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>