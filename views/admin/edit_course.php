<?php 
include "sidebar.php"; 
?>

<!-- HTML Content -->
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Edit Course</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Edit Course</h5>
                        <?php if ($message): ?>
                            <div class="alert alert-info"><?php echo $message; ?></div>
                        <?php endif; ?>
                        <form action="/admin/edit_course/<?php echo $course['id']; ?>" method="post">
                            <div class="form-group">
                                <label for="name">Course Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($course['name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="description">Course Description</label>
                                <textarea class="form-control" id="description" name="description" required><?php echo htmlspecialchars($course['description']); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Course</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<!-- Include Bootstrap CSS -->
<!-- <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet"> -->