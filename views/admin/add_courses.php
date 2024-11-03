<?php
include("sidebar.php");

use Models\Database;
$conn = Database::getConnection();

// Handle form submission
$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];

    $sql = "INSERT INTO courses (name, description) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(1, $name, PDO::PARAM_STR);
    $stmt->bindValue(2, $description, PDO::PARAM_STR);

    if ($stmt->execute()) {
        $message = "Course created successfully!";
    } else {
        $errorInfo = $stmt->errorInfo();
        $message = "Error creating course: " . $errorInfo[2];
    }

}


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
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Course Name</label>
                                <input
                                    type="text"
                                    name="name"
                                    value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                    class="form-control"
                                    required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea
                                    name="description"
                                    class="form-control"
                                    rows="4"
                                    required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            </div>
                            <button
                                type="submit"
                                class="btn btn-primary w-100">
                                Create Course
                            </button>
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