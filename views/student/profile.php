<?php
include 'sidebar.php';
require 'vendor/autoload.php';
require_once __DIR__ . '/../../aws_config.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Models\Database;

$bucketName = AWS_BUCKET_NAME;
$region = AWS_REGION;
$accessKey = AWS_ACCESS_KEY_ID;
$secretKey = AWS_SECRET_ACCESS_KEY;

// Debugging: Log the values of the configuration variables
// error_log('AWS_BUCKET_NAME: ' . $bucketName);
// error_log('AWS_REGION: ' . $region);
// error_log('AWS_ACCESS_KEY_ID: ' . $accessKey);
// error_log('AWS_SECRET_ACCESS_KEY: ' . $secretKey);

if (!$bucketName || !$region || !$accessKey || !$secretKey) {
    throw new Exception('Missing AWS configuration in aws_config.php file');
}

$s3Client = new S3Client([
    'region' => 'us-east-1',
    'version' => 'latest',
    'credentials' => [
        'key' => $accessKey,
        'secret' => $secretKey,
    ],
]);

// Check if the user is not logged in
if (!isset($_SESSION['email'])) {
    header("Location: login");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['upload_image'])) {
        $bucketName = AWS_BUCKET_NAME;
        $keyName = 'profile-images/' . basename($_FILES['profile_image']['name']);
        $filePath = $_FILES['profile_image']['tmp_name'];

        // Initialize S3 client
        $s3 = new S3Client([
            'version' => 'latest',
            'region'  => AWS_REGION,
            'credentials' => [
                'key'    => AWS_ACCESS_KEY_ID,
                'secret' => AWS_SECRET_ACCESS_KEY,
            ],
        ]);

        try {
            // Upload the image to S3
            $result = $s3->putObject([
                'Bucket' => $bucketName,
                'Key'    => $keyName,
                'SourceFile' => $filePath,
                'ACL'    => 'public-read',
            ]);

            // Get the URL of the uploaded image
            $profileImageUrl = $result['ObjectURL'];

            // Save the URL to the database
            $conn = Database::getConnection();
            $stmt = $conn->prepare("UPDATE students SET profile_image_url = ? WHERE email = ?");
            $stmt->execute([$profileImageUrl, $_SESSION['email']]);

            // Update the userData array for display
            $userData['profile_image_url'] = $profileImageUrl;
        } catch (AwsException $e) {
            echo "Error uploading image: " . $e->getMessage();
        }
    } else {
        // Update user data with the submitted form data
        $name = htmlspecialchars($_POST['name']);
        $email = htmlspecialchars($_POST['email']);
        $regd_no = htmlspecialchars($_POST['regd_no']);
        $section = htmlspecialchars($_POST['section']);
        $stream = htmlspecialchars($_POST['stream']);
        $year = htmlspecialchars($_POST['year']);
        $dept = htmlspecialchars($_POST['dept']);

        // Save the updated data to the database
        $stmt = $conn->prepare("UPDATE students SET name=?, email=?, regd_no=?, section=?, stream=?, year=?, dept=? WHERE email=?");
        $stmt->bindParam(1, $name);
        $stmt->bindParam(2, $email);
        $stmt->bindParam(3, $regd_no);
        $stmt->bindParam(4, $section);
        $stmt->bindParam(5, $stream);
        $stmt->bindParam(6, $year);
        $stmt->bindParam(7, $dept);
        $stmt->bindParam(8, $email);
        $stmt->execute();

        // Update the userData array for display
        $userData['name'] = $name;
        $userData['email'] = $email;
        $userData['regd_no'] = $regd_no;
        $userData['section'] = $section;
        $userData['stream'] = $stream;
        $userData['year'] = $year;
        $userData['dept'] = $dept;
    }
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
$profileImage = isset($userData['profile_image_url']) ? $userData['profile_image_url'] : null;
$email = isset($userData['email']) ? htmlspecialchars($userData['email']) : "danny@example.com";
$regd_no = isset($userData['regd_no']) ? htmlspecialchars($userData['regd_no']) : "123456";
$section = isset($userData['section']) ? htmlspecialchars($userData['section']) : "A";
$stream = isset($userData['stream']) ? htmlspecialchars($userData['stream']) : "Science";
$year = isset($userData['year']) ? htmlspecialchars($userData['year']) : "1st Year";
$dept = isset($userData['dept']) ? htmlspecialchars($userData['dept']) : "Journalism";
$university = isset($userData['university_name']) ? htmlspecialchars($userData['university_name']) : "Unknown University";
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
                                <img src="<?php echo $profileImage; ?>" alt="Profile Image" style="border-radius: 50%; width: 100px; height: 100px;">
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
                            <p><strong>Registration Number:</strong> <?php echo $regd_no; ?></p>
                            <p><strong>Section:</strong> <?php echo $section; ?></p>
                            <p><strong>Stream:</strong> <?php echo $stream; ?></p>
                            <p><strong>Year:</strong> <?php echo $year; ?></p>
                            <p><strong>Dept:</strong> <?php echo $dept; ?></p>
                            <p><strong>University:</strong> <?php echo $university; ?></p>
                        </div>

                        <!-- Edit Profile and Update Password Buttons -->
                        <div style="margin-top: 20px;">
                            <button class="btn btn-secondary" onclick="document.getElementById('editProfileForm').style.display='block'">Edit Profile</button>
                            <a href="updatePassword" class="btn btn-primary">Update Password</a>
                        </div>

                        <!-- Edit Profile Form -->
                        <div id="editProfileForm" style="display: none; margin-top: 20px;">
                            <form action="/student/profile" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="name">Name:</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email:</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="regd_no">Registration Number:</label>
                                    <input type="text" class="form-control" id="regd_no" name="regd_no" value="<?php echo $regd_no; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="section">Section:</label>
                                    <input type="text" class="form-control" id="section" name="section" value="<?php echo $section; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="stream">Stream:</label>
                                    <input type="text" class="form-control" id="stream" name="stream" value="<?php echo $stream; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="year">Year:</label>
                                    <input type="text" class="form-control" id="year" name="year" value="<?php echo $year; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="department">Dept:</label>
                                    <input type="text" class="form-control" id="department" name="dept" value="<?php echo $dept; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="profile_image">Profile Image:</label>
                                    <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                    <small class="form-text text-muted">Allowed file formats: .png, .jpeg, .jpg</small>
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