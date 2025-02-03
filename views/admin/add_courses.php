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
                        <h5 class="card-title">Create Course</h5>
                        <?php if (isset($message)): ?>
                            <div class="alert alert-<?php echo $message_type; ?>" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                            <?php if ($message_type == 'success'): ?>
                                <script>
                                    setTimeout(function() {
                                        window.location.href = 'manage_courses';
                                    }, 3000);
                                </script>
                            <?php endif; ?>
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
                            <div class="form-group">
                                <label for="public_course">Public Course</label>
                                <input type="checkbox" id="public_course" name="public_course" onchange="togglePriceField()">
                            </div>
                            <div class="form-group" id="price_field" style="display: none;">
                                <label for="price">Course Price</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01">
                            </div>
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
<!-- <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet"> -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    function togglePriceField() {
        var checkBox = document.getElementById("public_course");
        var priceField = document.getElementById("price_field");
        if (checkBox.checked == true){
            priceField.style.display = "block";
        } else {
            priceField.style.display = "none";
        }
    }

    <?php if (isset($message)): ?>
        toastr.<?php echo $message_type; ?>("<?php echo htmlspecialchars($message); ?>");
        <?php if ($message_type == 'success'): ?>
            setTimeout(function() {
                window.location.href = 'manage_courses';
            }, 3000);
        <?php endif; ?>
    <?php endif; ?>
</script>