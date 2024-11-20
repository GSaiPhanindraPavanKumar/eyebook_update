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

// Use prepared statements to prevent SQL injection
$conn = Database::getConnection();
$stmt = $conn->prepare("SELECT students.*, universities.long_name AS university_name 
                        FROM students 
                        JOIN universities ON students.university_id = universities.id 
                        WHERE students.email = :email");
$stmt->bindValue(':email', $email);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if user data is available; if not, set default placeholder values
$name = isset($userData['name']) ? htmlspecialchars($userData['name']) : "Danny McLoan";
$profileImage = isset($userData['profileImage']) ? $userData['profileImage'] : null;
$email = isset($userData['email']) ? htmlspecialchars($userData['email']) : "danny@example.com";
$phone = isset($userData['phone']) ? htmlspecialchars($userData['phone']) : "123-456-7890";
$department = isset($userData['department']) ? htmlspecialchars($userData['department']) : "Journalism";
$university = isset($userData['university_name']) ? htmlspecialchars($userData['university_name']) : "Unknown University";

// Handle form submission for profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update user data with the submitted form data
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);
    $department = htmlspecialchars($_POST['department']);
    
    // Handle profile image upload
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['size'] > 0) {
        $profileImage = file_get_contents($_FILES['profileImage']['tmp_name']);
    }
    
    // Save the updated data to the database
    $stmt = $conn->prepare("UPDATE students SET name=?, email=?, phone=?, department=?, profileImage=? WHERE email=?");
    $stmt->bindParam(1, $name);
    $stmt->bindParam(2, $email);
    $stmt->bindParam(3, $phone);
    $stmt->bindParam(4, $department);
    $stmt->bindParam(5, $profileImage, PDO::PARAM_LOB);
    $stmt->bindParam(6, $email);
    $stmt->execute();
    
    // Update the userData array for display
    $userData['name'] = $name;
    $userData['email'] = $email;
    $userData['phone'] = $phone;
    $userData['department'] = $department;
    $userData['profileImage'] = $profileImage;
}
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
                        <p style="color: gray;">Student</p>

                        <!-- Additional User Details -->
                        <div style="margin-top: 20px;">
                            <p><strong>Email:</strong> <?php echo $email; ?></p>
                            <p><strong>Phone:</strong> <?php echo $phone; ?></p>
                            <p><strong>Department:</strong> <?php echo $department; ?></p>
                            <p><strong>University:</strong> <?php echo $university; ?></p>
                        </div>

                        <!-- Edit Profile and Update Password Buttons -->
                        <div style="margin-top: 20px;">
                            <button class="btn btn-secondary" onclick="document.getElementById('editProfileForm').style.display='block'">Edit Profile</button>
                            <a href="updatePassword" class="btn btn-primary">Update Password</a>
                        </div>

                        <!-- Edit Profile Form -->
                        <div id="editProfileForm" style="display: none; margin-top: 20px;">
                            <form action="profile.php" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="name">Name:</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email:</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone:</label>
                                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $phone; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="department">Department:</label>
                                    <input type="text" class="form-control" id="department" name="department" value="<?php echo $department; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="profileImage">Profile Image:</label>
                                    <input type="file" class="form-control" id="profileImage" name="profileImage" accept="image/*">
                                </div>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                <button type="button" class="btn btn-secondary" onclick="document.getElementById('editProfileForm').style.display='none'">Cancel</button>
                            </form>
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