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
                        <h3 class="font-weight-bold">Edit University</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Edit University</h5>
                        <?php if ($message): ?>
                            <div class="alert alert-info"><?php echo $message; ?></div>
                        <?php endif; ?>
                        <form action="/admin/edit_university/<?php echo $university['id']; ?>" method="post">
                            <div class="form-group">
                                <label for="long_name">Long Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="long_name" name="long_name" value="<?php echo htmlspecialchars($university['long_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="short_name">Short Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="short_name" name="short_name" value="<?php echo htmlspecialchars($university['short_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="location">Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($university['location']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="country">Country <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="country" name="country" value="<?php echo htmlspecialchars($university['country']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="spoc_name">SPOC Name</label>
                                <input type="text" class="form-control" id="spoc_name" name="spoc_name" value="<?php echo htmlspecialchars($spoc['name']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="spoc_email">SPOC Email</label>
                                <input type="email" class="form-control" id="spoc_email" name="spoc_email" value="<?php echo htmlspecialchars($spoc['email']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="spoc_phone">SPOC Phone</label>
                                <input type="text" class="form-control" id="spoc_phone" name="spoc_phone" value="<?php echo htmlspecialchars($spoc['phone']); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Update University</button>
                            <a href="/admin/view_university/<?php echo $university['id']; ?>" class="btn btn-secondary">Cancel</a>
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
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">