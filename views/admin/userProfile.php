<?php include("sidebar.php"); ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Hello, <em><?php echo htmlspecialchars($admin['name'] ?? ''); ?></em></h3>
                        <h6 class="font-weight-normal mb-0">Welcome to your profile page!</h6>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Profile Information</h4>
                        <div class="media">
                            <img src="/views/public/images/user.jpg" class="mr-3" alt="profile" width="64" height="64">
                            <div class="media-body">
                                <h5 class="mt-0"><?php echo htmlspecialchars($admin['name'] ?? ''); ?></h5>
                                <p><?php echo htmlspecialchars($admin['username'] ?? ''); ?></p>
                                <!-- <p><?php echo htmlspecialchars($admin['phone'] ?? ''); ?></p> -->
                            </div>
                        </div>
                        <a href="/admin/updatePassword" class="btn btn-primary mt-3">Change Password</a>
                    </div>
                </div>
            </div>
            <div class="col-md-8 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Recent Activities</h4>
                        <ul class="list-unstyled">
                            <li class="media">
                                <i class="ti-check-box text-success mr-3"></i>
                                <div class="media-body">
                                    <h5 class="mt-0 mb-1">Logged in</h5>
                                    <p>Last login: <?php echo htmlspecialchars($admin['last_login'] ?? ''); ?></p>
                                </div>
                            </li>
                            <!-- Add more activities here -->
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include("footer.html"); ?>
</div>

