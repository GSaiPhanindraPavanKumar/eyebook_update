<?php include('sidebar.php'); ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12">
                        <h3 class="font-weight-bold">Submit Assignment</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <h2 class="font-weight-bold"><?php echo htmlspecialchars($assignment['title'] ?? ''); ?></h2>
                        <!-- <p><strong>Instructions:</strong> <?php echo nl2br(htmlspecialchars($assignment['instructions'] ?? '')); ?></p>
                        <p><strong>Due Date:</strong> <?php echo htmlspecialchars($assignment['due_date'] ?? ''); ?></p> -->
                        <h3 class="font-weight-bold">Submit Your Assignment</h3>
                        <?php if (!empty($messages)): ?>
                            <div class="alert alert-info">
                                <?php foreach ($messages as $message): ?>
                                    <p><?php echo htmlspecialchars($message); ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <form action="/student/submit_assignment/<?php echo $assignment['id']; ?>" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="assignment_file">Upload Assignment File:</label>
                                <input type="file" class="form-control-file" id="assignment_file" name="assignment_file" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Submit Assignment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">