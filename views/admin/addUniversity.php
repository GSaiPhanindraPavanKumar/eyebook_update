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
                        <!-- <h3 class="font-weight-bold">Hello, <em><?php echo htmlspecialchars($userData['name']); ?></em></h3> -->
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Create University</h5>
                        <?php if (isset($message)): ?>
                            <div class="alert alert-<?php echo $message_type; ?>" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                            <?php if ($message_type == 'success'): ?>
                                <script>
                                    setTimeout(function() {
                                        window.location.href = 'manage_university';
                                    }, 3000);
                                </script>
                            <?php endif; ?>
                        <?php endif; ?>
                        <form action="add_university" method="post">
                            <div class="form-group">
                                <label for="long_name">Long Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="long_name" name="long_name" required>
                            </div>
                            <div class="form-group">
                                <label for="short_name">Short Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="short_name" name="short_name" required>
                            </div>
                            <div class="form-group">
                                <label for="location">Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="location" name="location" required>
                            </div>
                            <div class="form-group">
                                <label for="country">Country <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="country" name="country" required>
                            </div>
                            <div class="form-group">
                                <label for="spoc_name">SPOC Name</label>
                                <input type="text" class="form-control" id="spoc_name" name="spoc_name">
                            </div>
                            <div class="form-group">
                                <label for="spoc_email">SPOC Email</label>
                                <input type="email" class="form-control" id="spoc_email" name="spoc_email">
                            </div>
                            <div class="form-group">
                                <label for="spoc_phone">SPOC Phone</label>
                                <input type="text" class="form-control" id="spoc_phone" name="spoc_phone">
                            </div>
                            <button type="submit" class="btn btn-primary">Add University</button>
                        </form>

                        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
                        <script>
                            <?php if (isset($message)): ?>
                                toastr.<?php echo $message_type; ?>("<?php echo htmlspecialchars($message); ?>");
                                <?php if ($message_type == 'success'): ?>
                                    setTimeout(function() {
                                        window.location.href = 'manage_university';
                                    }, 3000);
                                <?php endif; ?>
                            <?php endif; ?>
                        </script>
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