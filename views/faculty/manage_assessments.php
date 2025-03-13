<?php include "sidebar.php"; ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Manage Assessments</h4>
                        <a href="/faculty/create_assessment" class="btn btn-primary mb-3">Create New Assessment</a>
                        <a href="/faculty/export_assessment_results" class="btn btn-success mb-3 ml-2">
                            <i class="fas fa-file-excel"></i> Export All Results
                        </a>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Title</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Duration</th>
                                        <th>Attempts</th>
                                        <th>Avg Score</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assessments as $assessment): 
                                        // Get assessment statistics
                                        $stmt = $conn->prepare("SELECT 
                                            COUNT(*) as attempts,
                                            AVG(score) as avg_score
                                            FROM assessment_results 
                                            WHERE assessment_id = ?");
                                        $stmt->execute([$assessment['id']]);
                                        $stats = $stmt->fetch();
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($assessment['title'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($assessment['start_time'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($assessment['end_time'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($assessment['duration'] ?? ''); ?></td>
                                            <td><?php echo $stats['attempts']; ?></td>
                                            <td><?php echo $stats['avg_score'] !== null ? number_format($stats['avg_score'], 2) . '%' : 'N/A'; ?></td>
                                            <td>
                                                <a href="/faculty/edit_assessment/<?php echo $assessment['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                                <button type="button" 
                                                        class="btn btn-info btn-sm"
                                                        onclick="viewResults(<?php echo $assessment['id']; ?>)">
                                                    View Results
                                                </button>
                                                <button type="button"
                                                        class="btn btn-success btn-sm"
                                                        onclick="downloadResults(<?php echo $assessment['id']; ?>, '<?php echo htmlspecialchars($assessment['title'], ENT_QUOTES); ?>')">
                                                    <i class="fas fa-download"></i> Download
                                                </button>
                                                <a href="/faculty/delete_assessment/<?php echo $assessment['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this assessment?');">Delete</a>
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

<!-- Results Modal -->
<div class="modal fade" id="resultsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assessment Results</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="resultsContent"></div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function viewResults(assessmentId) {
    fetch(`/faculty/get_assessment_results/${assessmentId}`)
        .then(response => response.json())
        .then(data => {
            let html = `
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student Email</th>
                            <th>Score</th>
                            <th>Correct Answers</th>
                            <th>Total Questions</th>
                            <th>Submission Date</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            data.forEach(result => {
                html += `
                    <tr>
                        <td>${result.student_email}</td>
                        <td>${result.score}%</td>
                        <td>${result.correct_answers}</td>
                        <td>${result.total_questions}</td>
                        <td>${result.submission_date}</td>
                    </tr>
                `;
            });
            
            html += '</tbody></table>';
            document.getElementById('resultsContent').innerHTML = html;
            $('#resultsModal').modal('show');
        });
}

function downloadResults(assessmentId, title) {
    fetch(`/faculty/get_assessment_results/${assessmentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'No Results',
                    text: 'There are no results to download for this assessment.'
                });
                return;
            }
            
            // Convert data to CSV
            const headers = ['Student Email', 'Score', 'Correct Answers', 'Total Questions', 'Submission Date'];
            const csvContent = [
                headers.join(','),
                ...data.map(row => [
                    row.student_email,
                    row.score + '%',
                    row.correct_answers,
                    row.total_questions,
                    row.submission_date
                ].join(','))
            ].join('\n');
            
            // Create and trigger download
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.setAttribute('href', url);
            a.setAttribute('download', `${title}_results_${new Date().toISOString().split('T')[0]}.csv`);
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        });
}
</script>