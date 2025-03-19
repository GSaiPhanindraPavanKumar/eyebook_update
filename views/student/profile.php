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
                    <div class="grid grid-cols-1 gap-8 max-w-3xl mx-auto">
                        <!-- Personal Information Card -->
                        <div class="bg-gradient-to-br from-blue-100 to-blue-50 dark:from-gray-800/50 dark:to-gray-800/30 rounded-xl overflow-hidden shadow-sm border border-blue-200 dark:border-gray-700 hover:shadow-md transition-all duration-300">
                            <div class="p-6 relative">
                                <!-- Add a subtle pattern overlay -->
                                <div class="absolute inset-0 opacity-5 dark:opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 0h20v20H0V0zm10 17.5c4.142 0 7.5-3.358 7.5-7.5S14.142 2.5 10 2.5 2.5 5.858 2.5 10s3.358 7.5 7.5 7.5z' fill='%234B49AC' fill-opacity='0.4' fill-rule='evenodd'/%3E%3C/svg%3E');"></div>
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <svg class="h-6 w-6 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Personal Information</h3>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="relative group">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Full Name</p>
                                                <p class="text-base font-medium text-gray-900 dark:text-white"><?php echo $name; ?></p>
                                            </div>
                                            <button onclick="editField('name', '<?php echo $name; ?>')" 
                                                    class="opacity-0 group-hover:opacity-100 transition-opacity p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="relative group">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Email Address</p>
                                                <p class="text-base font-medium text-gray-900 dark:text-white"><?php echo $email; ?></p>
                                            </div>
                                            <button onclick="editField('email', '<?php echo $email; ?>')"
                                                    class="opacity-0 group-hover:opacity-100 transition-opacity p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Academic Information Card -->
                        <div class="bg-gradient-to-br from-emerald-100 to-emerald-50 dark:from-gray-800/50 dark:to-gray-800/30 rounded-xl overflow-hidden shadow-sm border border-emerald-200 dark:border-gray-700 hover:shadow-md transition-all duration-300">
                            <div class="p-6 relative">
                                <div class="absolute inset-0 opacity-5 dark:opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 0h20v20H0V0zm10 17.5c4.142 0 7.5-3.358 7.5-7.5S14.142 2.5 10 2.5 2.5 5.858 2.5 10s3.358 7.5 7.5 7.5z' fill='%2322C55E' fill-opacity='0.4' fill-rule='evenodd'/%3E%3C/svg%3E');"></div>
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <svg class="h-6 w-6 text-emerald-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Academic Information</h3>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="relative group">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Registration Number</p>
                                                <p class="text-base font-medium text-gray-900 dark:text-white"><?php echo $regd_no; ?></p>
                                            </div>
                                            <button onclick="editField('regd_no', '<?php echo $regd_no; ?>')"
                                                    class="opacity-0 group-hover:opacity-100 transition-opacity p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="relative group">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Section</p>
                                                <p class="text-base font-medium text-gray-900 dark:text-white"><?php echo $section; ?></p>
                                            </div>
                                            <button onclick="editField('section', '<?php echo $section; ?>')"
                                                    class="opacity-0 group-hover:opacity-100 transition-opacity p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="relative group">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Stream</p>
                                                <p class="text-base font-medium text-gray-900 dark:text-white"><?php echo $stream; ?></p>
                                            </div>
                                            <button onclick="editField('stream', '<?php echo $stream; ?>')"
                                                    class="opacity-0 group-hover:opacity-100 transition-opacity p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="relative group">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Year of Study</p>
                                                <p class="text-base font-medium text-gray-900 dark:text-white"><?php echo $year; ?></p>
                                            </div>
                                            <button onclick="editField('year', '<?php echo $year; ?>')"
                                                    class="opacity-0 group-hover:opacity-100 transition-opacity p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="relative group">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Department</p>
                                                <p class="text-base font-medium text-gray-900 dark:text-white"><?php echo $dept; ?></p>
                                            </div>
                                            <button onclick="editField('dept', '<?php echo $dept; ?>')"
                                                    class="opacity-0 group-hover:opacity-100 transition-opacity p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Institution Information Card -->
                        <div class="bg-gradient-to-br from-violet-100 to-violet-50 dark:from-gray-800/50 dark:to-gray-800/30 rounded-xl overflow-hidden shadow-sm border border-violet-200 dark:border-gray-700 hover:shadow-md transition-all duration-300">
                            <div class="p-6 relative">
                                <div class="absolute inset-0 opacity-5 dark:opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 0h20v20H0V0zm10 17.5c4.142 0 7.5-3.358 7.5-7.5S14.142 2.5 10 2.5 2.5 5.858 2.5 10s3.358 7.5 7.5 7.5z' fill='%238B5CF6' fill-opacity='0.4' fill-rule='evenodd'/%3E%3C/svg%3E');"></div>
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <svg class="h-6 w-6 text-violet-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Institution Information</h3>
                                    </div>
                                </div>
                                <div class="relative group">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">University Name</p>
                                            <p class="text-base font-medium text-gray-900 dark:text-white"><?php echo $university; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
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

<!-- Add this JavaScript for inline editing -->
<script>
function editField(field, currentValue) {
    const fieldTitle = field.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
    
    Swal.fire({
        title: 'Edit ' + fieldTitle,
        input: 'text',
        inputValue: currentValue,
        inputAttributes: {
            autocapitalize: 'off',
            autocomplete: 'off',
            autocorrect: 'off'
        },
        showCancelButton: true,
        confirmButtonText: 'Save',
        confirmButtonColor: '#4B49AC',
        cancelButtonColor: '#d33',
        showClass: {
            popup: 'animate__animated animate__fadeInDown animate__faster'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutUp animate__faster'
        },
        didOpen: (modal) => {
            const input = modal.querySelector('input');
            input.focus();
            input.select();
            // Add subtle highlight animation
            input.classList.add('swal2-input-focus');
        },
        preConfirm: (value) => {
            if (!value) {
                Swal.showValidationMessage('Please enter a value');
                return false;
            }
            return value;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append(field, result.value);
            
            // Add loading state
            Swal.fire({
                title: 'Updating...',
                didOpen: () => {
                    Swal.showLoading();
                },
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });
            
            fetch('/student/update_profile_field', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: fieldTitle + ' has been updated successfully.',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Update failed');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: error.message || 'Something went wrong!',
                    confirmButtonColor: '#4B49AC'
                });
            });
        }
    });
}
</script>

<!-- Add these styles to your head section -->
<style>
@keyframes inputFocus {
    0% { box-shadow: 0 0 0 0 rgba(75, 73, 172, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(75, 73, 172, 0); }
    100% { box-shadow: 0 0 0 0 rgba(75, 73, 172, 0); }
}

.swal2-input-focus {
    animation: inputFocus 1s ease-in-out;
}

/* Add hover effect for edit buttons */
.group:hover button {
    opacity: 1;
    transform: translateX(0);
}

.group button {
    transform: translateX(10px);
    transition: all 0.3s ease;
}

/* Enhance card hover effects */
.card-hover-effect {
    transition: all 0.3s ease;
}

.card-hover-effect:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}
</style>

<!-- Add animate.css for smoother animations -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">