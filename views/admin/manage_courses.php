<?php
include("sidebar.php");
use Models\Database;
$conn = Database::getConnection();

$sql = "SELECT c.id, c.name, c.university_id 
        FROM courses c";
$result = $conn->query($sql);
$courses = [];

if ($result->rowCount() > 0) {
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        // Fetch the university names
        if (!is_null($row['university_id'])) {
            $sql = "SELECT long_name FROM universities WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$row['university_id']]);
            $university = $stmt->fetch(PDO::FETCH_ASSOC);
            $row['university'] = $university['long_name'] ?? '';
        } else {
            $row['university'] = '';
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
                    <div class="col-12 col-xl-4 text-right">
                        <a href="add_courses" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Create Course
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card shadow">
                    <div class="card-body">
                        <p class="card-title mb-0" style="font-size:larger">Courses</p><br>
                        <div class="table-responsive">
                            <input class="form-control mb-3" id="searchInput" type="text" placeholder="🔍 Search Courses...">
                            <form id="courseForm" method="post" action="/admin/view_course">
                                <table class="table table-hover table-borderless table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th data-sort="serialNumber">S.no <i class="fas fa-sort"></i></th>
                                            <th data-sort="courseName">Course Name <i class="fas fa-sort"></i></th>
                                            <th data-sort="university">University <i class="fas fa-sort"></i></th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="courseTable">
                                        <?php
                                        $serialNumber = 1;
                                        foreach ($courses as $course): ?>
                                            <tr>
                                                <td><?= $serialNumber++ ?></td>
                                                <td><?= htmlspecialchars($course['name']) ?></td>
                                                <td><?= htmlspecialchars($course['university']) ?></td>
                                                <td>
                                                    <a href="/admin/view_course/<?= $course['id'] ?>" class="btn btn-outline-info btn-sm"><i class="fas fa-eye"></i> View</a>
                                                    <a href="/admin/edit_course/<?= $course['id'] ?>" class="btn btn-outline-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                                    <a href="/admin/delete_course/<?= $course['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this course?');"><i class="fas fa-trash"></i> Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <div id="noRecords" style="display: none;" class="text-center">No records found</div>
                            </form>
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

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
<script>
    $(document).ready(function() {
        $('#searchInput').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            var visibleRows = 0;
            $('#courseTable tr').filter(function() {
                var isVisible = $(this).text().toLowerCase().indexOf(value) > -1;
                $(this).toggle(isVisible);
                if (isVisible) visibleRows++;
            });
            $('#noRecords').toggle(visibleRows === 0);
        });

        $('th[data-sort]').on('click', function() {
            var table = $(this).parents('table').eq(0);
            var rows = table.find('tbody tr').toArray().sort(comparer($(this).index()));
            this.asc = !this.asc;
            if (!this.asc) { rows = rows.reverse(); }
            for (var i = 0; i < rows.length; i++) { table.append(rows[i]); }
        });

        function comparer(index) {
            return function(a, b) {
                var valA = getCellValue(a, index), valB = getCellValue(b, index);
                return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.localeCompare(valB);
            };
        }

        function getCellValue(row, index) {
            return $(row).children('td').eq(index).text();
        }
    });
</script>