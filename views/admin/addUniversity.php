<?php
include "sidebar.php";
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
                        <p class="card-title mb-0" style="font-size:x-large">Create University</p><br>
                        <?php if (isset($message)): ?>
                            <div class="alert alert-<?php echo $message_type; ?>" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                            <?php if ($message_type == 'success'): ?>
                                <script>
                                    setTimeout(function() {
                                        window.location.href = 'manageUniversity';
                                    }, 3000); // Redirect after 3 seconds
                                </script>
                            <?php endif; ?>
                        <?php endif; ?>
                        <form method="POST" action="addUniversity">
                            <div class="form-group">
                                <label for="long_name">Long Name</label>
                                <input type="text" id="long_name" name="long_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="short_name">Short Name</label>
                                <input type="text" id="short_name" name="short_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="location">Location</label>
                                <input type="text" id="location" name="location" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="country">Country</label>
                                <input type="text" id="country" name="country" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="spoc_name">SPOC Name</label>
                                <input type="text" id="spoc_name" name="spoc_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="spoc_email">SPOC Email</label>
                                <input type="email" id="spoc_email" name="spoc_email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="spoc_phone">SPOC Phone</label>
                                <input type="text" id="spoc_phone" name="spoc_phone" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="spoc_password">SPOC Password</label>
                                <input type="password" id="spoc_password" name="spoc_password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Create University</button>
                            </div>
                        </form>
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
                        <script>
                            <?php if (isset($message)): ?>
                                toastr.<?php echo $message_type; ?>("<?php echo htmlspecialchars($message); ?>");
                                setTimeout(function() {
                                    window.location.href = 'manageUniversity.php';
                                }, 3000); // Redirect after 3 seconds
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

<!-- Bootstrap CSS (Make sure to include Bootstrap CSS in your project) -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">