<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="font-weight-bold">Manage Labs</h3>
                    <a href="/admin/create_lab" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Create Lab
                    </a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card"><br>
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <table class="table table-responsive">
                            <thead>
                                <tr>
                                    <th class="col-course-name">Course Name</th>
                                    <th class="col-college-name">College Name</th>
                                    <th class="col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($courses)): ?>
                                    <?php foreach ($courses as $course): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($course['name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($course['university'] ?? ''); ?></td>
                                            <td>
                                                <a href="/admin/view_labs_by_course/<?php echo $course['id']; ?>" class="btn btn-info">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3">No courses found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
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

<style>
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }
    .col-course-name { width: 40%; }
    .col-college-name { width: 40%; }
    .col-actions { width: 20%; }
    @media (max-width: 768px) {
        .col-course-name, .col-college-name, .col-actions {
            width: auto;
        }
    }
</style>