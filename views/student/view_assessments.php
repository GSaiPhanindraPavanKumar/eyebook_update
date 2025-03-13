<?php include "sidebar.php"; ?>
<?php
// Get completed assessments for the current student
$stmt = $conn->prepare("SELECT assessment_id, score FROM assessment_results WHERE student_email = ?");
$stmt->execute([$_SESSION['email']]);
$completedAssessments = [];
while ($row = $stmt->fetch()) {
    $completedAssessments[$row['assessment_id']] = $row['score'];
}
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">View Assessments</h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Title</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assessments as $assessment): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($assessment['title'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($assessment['start_time'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($assessment['end_time'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($assessment['duration'] ?? ''); ?></td>
                                            <td>
                                                <?php if (isset($completedAssessments[$assessment['id']])): ?>
                                                    <span class="badge badge-success">Completed (<?php echo $completedAssessments[$assessment['id']]; ?>%)</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Not Attempted</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (isset($completedAssessments[$assessment['id']])): ?>
                                                    <button type="button" 
                                                            class="btn btn-secondary btn-sm" 
                                                            onclick="showCompletedMessage(<?php echo $completedAssessments[$assessment['id']]; ?>)">
                                                        View Result
                                                    </button>
                                                <?php else: ?>
                                                    <a href="/student/view_assessment/<?php echo $assessment['id']; ?>" 
                                                       class="btn btn-primary btn-sm">
                                                        Start Assessment
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function showCompletedMessage(score) {
    Swal.fire({
        title: 'Assessment Already Completed',
        html: `You have already completed this assessment.<br>Your score: ${score}%`,
        icon: 'info',
        confirmButtonText: 'OK'
    });
}

// Show error message if it exists
<?php if (isset($_SESSION['error'])): ?>
    Swal.fire({
        title: 'Notice',
        text: '<?php echo $_SESSION['error']; ?>',
        icon: 'info',
        confirmButtonText: 'OK'
    });
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>
</script>