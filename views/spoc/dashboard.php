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
                        <h3 class="font-weight-bold">Hello, <em><?php echo htmlspecialchars($userData['name']); ?>,</em></h3>
                    </div>
                </div>
            </div>
        </div>
        <!-- Add more HTML content here -->
    </div>
</div>
<!-- content-wrapper ends -->
<?php include 'footer.html'; ?>