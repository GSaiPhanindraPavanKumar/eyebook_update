<?php
include("sidebar.php");
use Models\Database;
$conn = Database::getConnection();

$sql = "SELECT c.id, c.name, c.description, c.universities 
        FROM courses c";
$result = $conn->query($sql);
$courses = [];

if ($result->rowCount() > 0) {
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        // Decode the universities JSON field if it's not null
        $university_ids = !is_null($row['universities']) ? json_decode($row['universities'], true) : [];
        if (is_array($university_ids) && !empty($university_ids)) {
            // Fetch the university names
            $placeholders = implode(',', array_fill(0, count($university_ids), '?'));
            $sql = "SELECT long_name FROM universities WHERE id IN ($placeholders)";
            $stmt = $conn->prepare($sql);
            $stmt->execute($university_ids);
            $universities = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $row['universities'] = implode(', ', $universities);
        } else {
            $row['universities'] = '';
        }
        $courses[] = $row;
    }
}
?>

<!-- HTML Content -->
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Manage Courses</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Courses</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Select</th>
                                        <th>Course Name</th>
                                        <th>Description</th>
                                        <th>Universities</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($courses as $course): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="selected_courses[]" value="<?php echo $course['id']; ?>">
                                            </td>
                                            <td><?php echo htmlspecialchars($course['name']); ?></td>
                                            <td><?php echo htmlspecialchars($course['description']); ?></td>
                                            <td><?php echo htmlspecialchars($course['universities']); ?></td>
                                            <td>
                                            <a href="/admin/view_course/<?php echo $course['id']; ?>" class="btn btn-primary btn-sm">View</a>
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
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<!-- Include Bootstrap CSS -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">