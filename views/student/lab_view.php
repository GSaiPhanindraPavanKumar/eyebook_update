<?php include "sidebar.php";
use Models\Lab;
use Models\Database;

// Get student ID and database connection
$studentId = $_SESSION['student_id'];
$conn = Database::getConnection();

// Fetch all labs with course details
$sql = "SELECT l.*, c.name as course_name 
        FROM labs l 
        JOIN courses c ON l.course_id = c.id 
        WHERE l.status = 'active'
        ORDER BY l.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$labs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch student's submissions
$sql = "SELECT * FROM lab_submissions WHERE student_id = :student_id";
$stmt = $conn->prepare($sql);
$stmt->execute(['student_id' => $studentId]);
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create submissions lookup
$submissionStatus = [];
foreach ($submissions as $submission) {
    $submissionStatus[$submission['lab_id']] = $submission;
}
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Lab Assignments</h4>
                        
                        <!-- Lab Content Table -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Course</th>
                                        <th>Lab Name</th>
                                        <th>Description</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($labs)): ?>
                                        <?php foreach ($labs as $lab): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($lab['course_name']); ?></td>
                                                <td><?php echo htmlspecialchars($lab['lab_name']); ?></td>
                                                <td><?php echo htmlspecialchars($lab['description']); ?></td>
                                                <td><?php echo $lab['due_date'] ? date('Y-m-d H:i', strtotime($lab['due_date'])) : 'N/A'; ?></td>
                                                <td>
                                                    <?php 
                                                    if (isset($submissionStatus[$lab['id']])) {
                                                        $status = $submissionStatus[$lab['id']]['status'];
                                                        echo "<span class='badge badge-" . 
                                                            ($status == 'completed' ? 'success' : 
                                                            ($status == 'failed' ? 'danger' : 'warning')) . 
                                                            "'>" . htmlspecialchars($status) . "</span>";
                                                    } else {
                                                        echo '<span class="badge badge-warning">Pending</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <a href="/views/student/i-Lab/index.html" class="btn btn-primary btn-sm">Open Lab</a>
                                                    <?php if ($lab['file_path']): ?>
                                                        <a href="<?php echo htmlspecialchars($lab['file_path']); ?>" 
                                                           class="btn btn-info btn-sm ml-1">Download</a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No labs available.</td>
                                        </tr>
                                    <?php endif; ?>
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