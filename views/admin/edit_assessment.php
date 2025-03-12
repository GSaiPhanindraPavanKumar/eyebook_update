<?php include "sidebar.php"; ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Edit Assessment</h4>
                        <form method="post" action="/admin/edit_assessment/<?php echo $assessment['id']; ?>">
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($assessment['title']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="start_time">Start Time</label>
                                <input type="datetime-local" class="form-control" id="start_time" name="start_time" value="<?php echo htmlspecialchars($assessment['start_time']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="end_time">End Time</label>
                                <input type="datetime-local" class="form-control" id="end_time" name="end_time" value="<?php echo htmlspecialchars($assessment['end_time']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="duration">Duration (minutes)</label>
                                <input type="number" class="form-control" id="duration" name="duration" value="<?php echo htmlspecialchars($assessment['duration']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="questions">Questions (JSON format)</label>
                                <textarea class="form-control" id="questions" name="questions" rows="5" required><?php echo htmlspecialchars($assessment['questions']); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Assessment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>