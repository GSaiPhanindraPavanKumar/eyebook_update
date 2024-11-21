<?php 
include('sidebar.php');
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <!-- <h3 class="font-weight-bold">Hello, <em><?php echo htmlspecialchars($userData['name']); ?></em></h3> -->
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">View University</h5>
                        <div class="details">
                            <label>Long Name:</label>
                            <p><?php echo htmlspecialchars($university['long_name']); ?></p>

                            <label>Short Name:</label>
                            <p><?php echo htmlspecialchars($university['short_name']); ?></p>

                            <label>Location:</label>
                            <p><?php echo htmlspecialchars($university['location']); ?></p>

                            <label>Country:</label>
                            <p><?php echo htmlspecialchars($university['country']); ?></p>

                            <label>SPOC Name:</label>
                            <p><?php echo htmlspecialchars($spoc['name']); ?></p>

                            <label>SPOC Email:</label>
                            <p><?php echo htmlspecialchars($spoc['email']); ?></p>

                            <label>SPOC Phone:</label>
                            <p><?php echo htmlspecialchars($spoc['phone']); ?></p>

                            <label>Student Count:</label>
                            <p><?php echo htmlspecialchars($student_count); ?></p>

                            <label>Course Count:</label>
                            <p><?php echo htmlspecialchars($course_count); ?></p>
                        </div>
                        <div class="actions">
                            <a href="edit_university.php?id=<?php echo $university_id; ?>" class="btn btn-primary">Edit University</a>
                            <a href="delete_university.php?id=<?php echo $university_id; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this university?');">Delete University</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>