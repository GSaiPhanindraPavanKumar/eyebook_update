<?php
include 'sidebar.php';

use Models\Database;
use Models\Discussion;

$conn = Database::getConnection();

$username = $_SESSION['email'];
$faculty_id = $_SESSION['faculty_id']; // Assuming faculty_id is stored in session

// Ensure university_id and name are set in session
if (!isset($_SESSION['university_id']) || !isset($_SESSION['name'])) {
    $sql = "SELECT university_id, name FROM faculty WHERE id = :faculty_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':faculty_id' => $faculty_id]);
    $faculty = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$faculty) {
        die("Faculty not found.");
    }

    $_SESSION['university_id'] = $faculty['university_id'];
    $_SESSION['name'] = $faculty['name'];
}

$university_id = $_SESSION['university_id'];
$name = $_SESSION['name'];

// Handle the post submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $msg = filter_input(INPUT_POST, 'msg', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $parent_post_id = filter_input(INPUT_POST, 'parent_post_id', FILTER_VALIDATE_INT);
    $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if ($csrf_token !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }

    if (!empty($msg)) {
        // Insert data into the discussion table
        Discussion::addDiscussion($conn, $name, $msg, $university_id, $parent_post_id);
    }
}

// Fetch all discussions for the specific university
$discussions = Discussion::getDiscussionsByUniversity($conn, $university_id);

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

/* Add margin between the Send button and Recent Questions heading */
.margin-top {
    margin-top: 20px;
}
</style>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="container">
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="parent_post_id" value="0">
                            <div class="form-group">
                                <label for="comment">Write your question:</label>
                                <textarea class="form-control" rows="5" name="msg" required></textarea>
                            </div>
                            <input type="submit" name="save" class="btn btn-primary" value="Send">
                        </form>

                        <h4 class="margin-top">Recent Questions</h4>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Question</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($discussions as $discussion): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($discussion['name']); ?></td>
                                        <td><?php echo htmlspecialchars($discussion['post']); ?></td>
                                        <td><?php echo htmlspecialchars($discussion['created']); ?></td>
                                        <td>
                                            <button class="btn btn-info btn-sm" onclick="toggleReplyForm(<?php echo $discussion['id']; ?>)">Reply</button>
                                            <button class="btn btn-secondary btn-sm" onclick="toggleViewReplies(<?php echo $discussion['id']; ?>)">View Replies</button>
                                        </td>
                                    </tr>
                                    <tr id="replies-<?php echo $discussion['id']; ?>" style="display: none;">
                                        <td colspan="4">
                                            <table class="table">
                                                <?php
                                                $replies = Discussion::getReplies($conn, $discussion['id']);
                                                foreach ($replies as $reply): ?>
                                                    <tr>
                                                        <td style="padding-left: 40px;">↳ <?php echo htmlspecialchars($reply['name']); ?></td>
                                                        <td><?php echo htmlspecialchars($reply['post']); ?></td>
                                                        <td><?php echo htmlspecialchars($reply['created']); ?></td>
                                                        <td></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr id="reply-form-<?php echo $discussion['id']; ?>" style="display: none;">
                                        <td colspan="4">
                                            <form method="post">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                <input type="hidden" name="parent_post_id" value="<?php echo $discussion['id']; ?>">
                                                <div class="form-group">
                                                    <label for="reply">Your Reply:</label>
                                                    <textarea class="form-control" rows="3" name="msg" required></textarea>
                                                </div>
                                                <input type="submit" name="save" class="btn btn-primary" value="Reply">
                                            </form>
                                        </td>
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

<script>
function toggleReplyForm(discussionId) {
    var replyForm = document.getElementById('reply-form-' + discussionId);
    if (replyForm.style.display === 'none') {
        replyForm.style.display = 'table-row';
    } else {
        replyForm.style.display = 'none';
    }
}

function toggleViewReplies(discussionId) {
    var replies = document.getElementById('replies-' + discussionId);
    if (replies.style.display === 'none') {
        replies.style.display = 'table-row';
    } else {
        replies.style.display = 'none';
    }
}
</script>