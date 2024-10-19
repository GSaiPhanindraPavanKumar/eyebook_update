<?php

include 'sidebar.php';

// Allowed file types
$allowed_file_types = ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $university_id = filter_input(INPUT_POST, 'university_id', FILTER_SANITIZE_NUMBER_INT);
    $file = $_FILES['file'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $file['tmp_name'];
        $file_name = basename($file['name']);
        $file_size = $file['size'];
        $file_type = $file['type'];

        // Validate file type
        if (!in_array($file_type, $allowed_file_types)) {
            $message = "Invalid file type. Only CSV and Excel files are allowed.";
            $message_type = "error";
        } elseif ($file_size > 5000000) { // Limit file size to 5MB
            $message = "File size exceeds the limit of 5MB.";
            $message_type = "error";
        } else {
            $upload_dir = 'uploads/';
            $upload_file = $upload_dir . $file_name;

            if (move_uploaded_file($file_tmp, $upload_file)) {
                $message = "File uploaded successfully";
                $message_type = "success";
            } else {
                $message = "Error uploading file";
                $message_type = "error";
            }
        }
    } else {
        $message = "Error uploading file: " . $file['error'];
        $message_type = "error";
    }
}

// Fetch universities from the database
$query = "SELECT id, long_name FROM universities";
$result = $conn->query($query);
$universities = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $universities[] = $row;
    }
}
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
                        <p class="card-title mb-0" style="font-size:x-large">Dashboard</p><br>
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="university_id">Select University</label>
                                <select id="university_id" name="university_id" class="form-control" required>
                                    <option value="">Select a university</option>
                                    <?php foreach ($universities as $university): ?>
                                        <option value="<?php echo htmlspecialchars($university['id']); ?>">
                                            <?php echo htmlspecialchars($university['long_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="file">Upload File</label>
                                <input type="file" id="file" name="file" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Upload Students</button>
                            </div>
                        </form>
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
                        <script>
                            <?php if (isset($message)): ?>
                                toastr.<?php echo $message_type; ?>("<?php echo htmlspecialchars($message); ?>");
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


<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">