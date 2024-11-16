<?php 
include "sidebar.php"; 
$message = '';
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
                        <h5 class="card-title">Create Course</h5>
                        <?php if ($message): ?>
                            <div class="alert alert-info"><?php echo $message; ?></div>
                            <script>
                                setTimeout(function() {
                                    window.location.href = 'add_courses';
                                }, 3000); // Redirect after 3 seconds
                            </script>
                        <?php endif; ?>
                        <form action="add_courses" method="post">
                            <div class="form-group">
                                <label for="name">Course Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="description">Course Description</label>
                                <textarea class="form-control" id="description" name="description" required></textarea>
                            </div>
                            <!-- <div class="form-group">
                                <label for="is_paid">Is Paid Course</label>
                                <input type="checkbox" id="is_paid" name="is_paid" onchange="togglePriceField()">
                            </div>
                            <div class="form-group" id="price-group" style="display: none;">
                                <label for="price">Price</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01">
                            </div> -->
                            <button type="submit" class="btn btn-primary">Add Course</button>
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

<!-- JavaScript to toggle price field -->
<!-- <script>
function togglePriceField() {
    var isPaidCheckbox = document.getElementById('is_paid');
    var priceGroup = document.getElementById('price-group');
    if (isPaidCheckbox.checked) {
        priceGroup.style.display = 'block';
    } else {
        priceGroup.style.display = 'none';
    }
}
</script> -->