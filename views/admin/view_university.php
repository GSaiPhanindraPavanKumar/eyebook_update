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
                        <div class="text-center mb-4">
                            <?php if (!empty($university['logo_url'])): ?>
                                <img src="<?php echo htmlspecialchars($university['logo_url']); ?>" alt="University Logo" style="max-width: 200px;">
                            <?php else: ?>
                                <p>No logo available.</p>
                            <?php endif; ?>
                        </div>
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
                                    <td><?php echo isset($spoc['name']) ? htmlspecialchars($spoc['name']) : 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <th>SPOC Email:</th>
                                    <td><?php echo isset($spoc['email']) ? htmlspecialchars($spoc['email']) : 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <th>SPOC Phone:</th>
                                    <td><?php echo isset($spoc['phone']) ? htmlspecialchars($spoc['phone']) : 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <th>Student Count:</th>
                                    <td><?php echo htmlspecialchars($student_count); ?></td>
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