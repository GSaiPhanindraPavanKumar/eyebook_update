<?php
include 'sidebar.php'; 
// $conn = Database::getConnection();
// $facultyId = $_SESSION['faculty_id']; // Assuming faculty_id is stored in session
// $students = Student::getAllByFaculty($conn, $facultyId);
?>

<div class="container mt-5">
    <h2>Manage Students</h2>
    <table class="table table-hover table-borderless table-striped">
        <thead class="thead-light">
            <tr>
                <th>S.No</th>
                <th>Registration Number</th>
                <th>Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $index => $student): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($student['regd_no']) ?></td>
                    <td><?= htmlspecialchars($student['name']) ?></td>
                    <td><?= htmlspecialchars($student['email']) ?></td>
                    <td>
                        <a href="viewStudentProfile/<?= $student['id'] ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye"></i> View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>