<?php
include 'sidebar-content.php';
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

<!-- Main Content -->
<div id="main-content" class="main-content-expanded min-h-screen bg-gray-50 dark:bg-gray-900 pt-20">
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Profile Card -->
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
            <div class="p-8">
                <!-- Profile Header -->
                <div class="text-center">
                    <!-- Profile Image -->
                    <div class="mb-6">
                        <?php if ($profileImage): ?>
                            <img src="<?php echo $profileImage; ?>" 
                                 alt="Profile Image" 
                                 class="h-32 w-32 rounded-full mx-auto object-cover border-4 border-white dark:border-gray-700 shadow-lg">
                        <?php else: ?>
                            <div class="h-32 w-32 rounded-full mx-auto bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                <svg class="h-20 w-20 text-gray-400 dark:text-gray-500" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Profile Info -->
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
                        <?php echo $name; ?>
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Student</p>

                    <!-- Action Buttons -->
                    <div class="flex justify-center space-x-4 mb-8">
                        <button onclick="document.getElementById('editProfileForm').style.display='block'" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                            Edit Profile
                        </button>
                        <a href="updatePassword" 
                           class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary-hover transition-colors">
                            Update Password
                        </a>
                    </div>

                    <!-- User Details Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-3xl mx-auto">
                        <div class="text-left">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                            <p class="text-base font-medium text-gray-900 dark:text-white"><?php echo $email; ?></p>
                        </div>
                        <div class="text-left">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Registration Number</p>
                            <p class="text-base font-medium text-gray-900 dark:text-white"><?php echo $regd_no; ?></p>
                        </div>
                        <div class="text-left">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Section</p>
                            <p class="text-base font-medium text-gray-900 dark:text-white"><?php echo $section; ?></p>
                        </div>
                        <div class="text-left">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Stream</p>
                            <p class="text-base font-medium text-gray-900 dark:text-white"><?php echo $stream; ?></p>
                        </div>
                        <div class="text-left">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Year</p>
                            <p class="text-base font-medium text-gray-900 dark:text-white"><?php echo $year; ?></p>
                        </div>
                        <div class="text-left">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Department</p>
                            <p class="text-base font-medium text-gray-900 dark:text-white"><?php echo $dept; ?></p>
                        </div>
                        <div class="text-left md:col-span-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">University</p>
                            <p class="text-base font-medium text-gray-900 dark:text-white"><?php echo $university; ?></p>
                        </div>
                    </div>

                    <!-- Edit Profile Form (Hidden by default) -->
                    <div id="editProfileForm" class="hidden mt-8 max-w-2xl mx-auto">
                        <form action="/student/profile" method="post" enctype="multipart/form-data" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                    <input type="text" id="name" name="name" value="<?php echo $name; ?>" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                    <input type="email" id="email" name="email" value="<?php echo $email; ?>" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                </div>
                                <!-- Add other form fields with similar styling -->
                                <div>
                                    <label for="regd_no" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Registration Number</label>
                                    <input type="text" id="regd_no" name="regd_no" value="<?php echo $regd_no; ?>" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                </div>
                                <div>
                                    <label for="section" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Section</label>
                                    <input type="text" id="section" name="section" value="<?php echo $section; ?>" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                </div>
                                <div>
                                    <label for="stream" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stream</label>
                                    <input type="text" id="stream" name="stream" value="<?php echo $stream; ?>" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                </div>
                                <div>
                                    <label for="year" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Year</label>
                                    <input type="text" id="year" name="year" value="<?php echo $year; ?>" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                </div>
                                <div>
                                    <label for="department" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                                    <input type="text" id="department" name="dept" value="<?php echo $dept; ?>" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                </div>
                                <div class="md:col-span-2">
                                    <label for="profile_image" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Profile Image</label>
                                    <input type="file" id="profile_image" name="profile_image" accept="image/*"
                                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-primary file:text-white hover:file:bg-primary-hover dark:text-gray-400">
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Allowed file formats: .png, .jpeg, .jpg</p>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex justify-end space-x-3">
                                <button type="button" 
                                        onclick="document.getElementById('editProfileForm').style.display='none'"
                                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                                    Cancel
                                </button>
                                <button type="submit" 
                                        class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary-hover transition-colors">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">