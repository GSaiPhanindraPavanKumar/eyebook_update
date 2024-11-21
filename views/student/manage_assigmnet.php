<?php include('sidebar.php'); ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Assignments</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <?php foreach ($assignments as $assignment): ?>
                <div class="col-md-4 grid-margin stretch-card">
                    <div class="card shadow">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($assignment['title']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($assignment['course_name']) ?></p>
                            <p class="card-text">Deadline: <?= htmlspecialchars($assignment['deadline']) ?></p>
                            <a href="assignment_submit.php?assignment_id=<?= $assignment['id'] ?>" class="btn btn-primary">Submit Assignment</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>