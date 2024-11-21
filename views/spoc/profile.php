<?php
include 'sidebar.php';
use Models\Database;

// Check if the user is not logged in
if (!isset($_SESSION['email'])) {
    header("Location: login");
    exit;
}

// Get the email from the session
$email = $_SESSION['email'];

// Get the database connection
$conn = Database::getConnection();

// Use prepared statements to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM spocs WHERE email = :email");
if (!$stmt) {
    die('Error in preparing statement.');
}

$stmt->execute(['email' => $email]);

// Fetch user data
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userData) {
    die('Invalid Credentials');
}

// Check if user data is available; if not, set default placeholder values
$name = isset($userData['name']) ? htmlspecialchars($userData['name']) : "John Doe";
$profileImage = isset($userData['profileImage']) ? $userData['profileImage'] : null;
?>

<!-- HTML Content -->
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <!-- Profile Image -->
                        <div style="display: flex; justify-content: center; margin-bottom: 20px;">
                            <?php if ($profileImage): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($profileImage); ?>" alt="Profile Image" style="border-radius: 50%; width: 100px; height: 100px;">
                            <?php else: ?>
                                <i class="fas fa-user-circle" style="font-size: 100px; color: gray;"></i>
                            <?php endif; ?>
                        </div>

                        <!-- User Info -->
                        <h2 style="margin: 0;"><?php echo $name; ?></h2>

                        <!-- Edit Profile and Update Password Buttons -->
                        <div style="margin-top: 20px;">
                            <button class="btn btn-secondary" onclick="document.getElementById('editProfileForm').style.display='block'">Edit Profile</button>
                            <a href="updatePassword" class="btn btn-primary">Update Password</a>
                        </div>

                        <!-- Edit Profile Form -->
                        <div id="editProfileForm" style="display: none; margin-top: 20px;">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="profileImage">Profile Image</label>
                                    <input type="file" class="form-control" id="profileImage" name="profileImage">
                                </div>
                                <button type="submit" class="btn btn-success">Save Changes</button>
                                <button type="button" class="btn btn-danger" onclick="document.getElementById('editProfileForm').style.display='none'">Cancel</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>