<?php
include 'sidebar.php';

use Models\Database;
$conn = Database::getConnection();



$username = $_SESSION['email'];


// Get course_id from the query parameter
$course_id = filter_input(INPUT_GET, 'course_id', FILTER_VALIDATE_INT) ?: 0;

// Handle the post submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $msg = filter_input(INPUT_POST, 'msg', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if ($csrf_token !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }

    $date = date("Y-m-d H:i:s");

    if (!empty($msg)) {
        // Prepare JSON data
        $json_data = json_encode(array("name" => $username, "message" => $msg, "date" => $date));
        
        // Insert data into the discussion table
        $sql = "INSERT INTO discussion (student, post, json_data, course_id) 
                VALUES (:student, :post, :json_data, :course_id)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':student', $username);
        $stmt->bindParam(':post', $msg);
        $stmt->bindParam(':json_data', $json_data);
        $stmt->bindParam(':course_id', $course_id);
        
        if (!$stmt->execute()) {
            die("Error inserting data: " . implode(", ", $stmt->errorInfo()));
        }
    }
}

// Fetch all discussions for the specific course
$sql = "SELECT * FROM discussion WHERE course_id = :course_id ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':course_id', $course_id);
$stmt->execute();

$discussions = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if (!empty($row['json_data'])) {
        $discussion = json_decode($row['json_data'], true);
        if ($discussion) {
            $discussions[] = $discussion;
        }
    }
}

// Generate a new CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>


<style>
/* Custom scrollbar styles */
::-webkit-scrollbar {
    width: 12px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                <div class="container">
        <!-- <h3>Discussion Forum for Course ID: <?php echo htmlspecialchars($course_id); ?></h3>
        <p>Welcome, <?php echo htmlspecialchars($username); ?>!</p> -->
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="form-group">
                <label for="comment">Write your question:</label>
                <textarea class="form-control" rows="5" name="msg" required></textarea>
            </div>
            <input type="submit" name="save" class="btn btn-primary" value="Send">
        </form>

        <h4>Recent Questions</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Question</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($discussions as $discussion): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($discussion['name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($discussion['message'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($discussion['date'] ?? ''); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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