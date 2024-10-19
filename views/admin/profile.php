<?php
include 'sidebar.php';

// Check if the user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login");
    exit;
}

// Get the username from the session
$username = $_SESSION['username'];

// Use prepared statements to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
if (!$stmt) {
    die('Error in preparing statement: ' . $conn->error);
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Fetch user data
$userData = $result->fetch_assoc();
?>

<!-- HTML Content -->
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title mb-0" style="font-size:x-large">Profile</p><br>
                        <h3 class="font-weight-bold">
                            <i class="fas fa-user-circle"></i> Hello, <em><?php echo htmlspecialchars($userData['name']); ?></em>
                        </h3>
                        
                        <!-- Options for updating password and logging out -->
                        <div class="options">
                            <a href="update_password.php" class="option-link">
                                <i class="fas fa-lock"></i> Update Password
                            </a>
                            <br>
                            <a href="logout.php" class="option-link">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
