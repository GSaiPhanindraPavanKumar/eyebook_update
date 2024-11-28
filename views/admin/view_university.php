<?php 
include('sidebar.php');
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">University Profile</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card shadow">
                    <div class="card-body">
                        <p class="card-title mb-0" style="font-size:larger">Profile Details</p><br>
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Long Name:</th>
                                    <td><?php echo htmlspecialchars($university['long_name']); ?></td>
                                </tr>
                                <tr>
                                    <th>Short Name:</th>
                                    <td><?php echo htmlspecialchars($university['short_name']); ?></td>
                                </tr>
                                <tr>
                                    <th>Location:</th>
                                    <td><?php echo htmlspecialchars($university['location']); ?></td>
                                </tr>
                                <tr>
                                    <th>Country:</th>
                                    <td><?php echo htmlspecialchars($university['country']); ?></td>
                                </tr>
                                <tr>
                                    <th>SPOC Name:</th>
                                    <td><?php echo htmlspecialchars($spoc['name']); ?></td>
                                </tr>
                                <tr>
                                    <th>SPOC Email:</th>
                                    <td><?php echo htmlspecialchars($spoc['email']); ?></td>
                                </tr>
                                <tr>
                                    <th>SPOC Phone:</th>
                                    <td><?php echo htmlspecialchars($spoc['phone']); ?></td>
                                </tr>
                                <tr>
                                    <th>Student Count:</th>
                                    <td><?php echo htmlspecialchars($student_count); ?></td>
                                </tr>
                                <tr>
                                    <th>Course Count:</th>
                                    <td><?php echo htmlspecialchars($course_count); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="actions mt-4">
                            <a href="/admin/edit_university/<?php echo $university_id; ?>" class="btn btn-primary">Edit University</a>
                            <a href="/admin/delete_university/<?php echo $university_id; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this university?');">Delete University</a>
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