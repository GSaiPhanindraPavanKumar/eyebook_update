<?php

include 'sidebar.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $long_name = $_POST['long_name'];
    $short_name = $_POST['short_name'];
    $location = $_POST['location'];
    $country = $_POST['country'];
    $spoc_name = $_POST['spoc_name'];
    $spoc_email = $_POST['spoc_email'];
    $spoc_phone = $_POST['spoc_phone'];
    $spoc_password = password_hash($_POST['spoc_password'], PASSWORD_BCRYPT);

    try {
        // Insert the university data into the database
        $query = "INSERT INTO universities (long_name, short_name, location, country) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ssss', $long_name, $short_name, $location, $country);

        if ($stmt->execute()) {
            $university_id = $stmt->insert_id;

            // Insert the SPOC data into the spocs table
            $spoc_query = "INSERT INTO spocs (name, email, phone, password, university_id) VALUES (?, ?, ?, ?, ?)";
            $spoc_stmt = $conn->prepare($spoc_query);
            $spoc_stmt->bind_param('ssssi', $spoc_name, $spoc_email, $spoc_phone, $spoc_password, $university_id);

            if ($spoc_stmt->execute()) {
                $message = "University and SPOC added successfully";
                $message_type = "success";
            } else {
                throw new Exception("Error adding SPOC: " . $spoc_stmt->error);
            }
        } else {
            throw new Exception("Error adding university: " . $stmt->error);
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) { // Duplicate entry error code
            $message = "Duplicate entry for email: " . $spoc_email;
            $message_type = "warning";
        } else {
            $message = "Database error: " . $e->getMessage();
            $message_type = "error";
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $message_type = "error";
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
                        <p class="card-title mb-0" style="font-size:x-large">Create University</p><br>
                        <form method="POST" action="">
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
                                <script>
                                    toastr.success("<?php echo htmlspecialchars($message); ?>");
                                    setTimeout(function() {
                                        window.location.href = 'manage_university.php';
                                    }, 2000); // Redirect after 2 seconds
                                </script>
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
