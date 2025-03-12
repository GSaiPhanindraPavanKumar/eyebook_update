<?php include "sidebar.php"; ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Create New Assessment</h4>
                        <form method="post" action="/admin/create_assessment">
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="form-group">
                                <label for="start_time">Start Time</label>
                                <input type="datetime-local" class="form-control" id="start_time" name="start_time" required>
                            </div>
                            <div class="form-group">
                                <label for="end_time">End Time</label>
                                <input type="datetime-local" class="form-control" id="end_time" name="end_time" required>
                            </div>
                            <div class="form-group">
                                <label for="duration">Duration (minutes)</label>
                                <input type="number" class="form-control" id="duration" name="duration" required>
                            </div>
                            <div class="form-group">
                                <label for="questions">Questions (JSON format)</label>
                                <textarea class="form-control" id="questions" name="questions" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Create Assessment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>