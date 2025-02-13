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

/* Thread and Reply Styles */
.thread-container {
    margin-bottom: 2rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--card-bg);
}

.thread {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.thread-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.thread-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--text-color);
}

.thread-meta {
    font-size: 0.9rem;
    color: var(--text-color);
    opacity: 0.7;
}

.thread-content {
    color: var(--text-color);
    margin-bottom: 1rem;
}

.replies-container {
    padding: 1rem 1rem 1rem 3rem;
    background: var(--card-bg);
}

.reply {
    padding: 1rem;
    border-left: 3px solid var(--menu-icon);
    margin-bottom: 1rem;
    background: var(--hover-bg);
    border-radius: 0 8px 8px 0;
}

.reply:last-child {
    margin-bottom: 0;
}

.reply-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.reply-author {
    font-weight: 500;
    color: var(--text-color);
}

.reply-meta {
    font-size: 0.85rem;
    color: var(--text-color);
    opacity: 0.7;
}

.reply-content {
    color: var(--text-color);
}

/* Reply Form */
.reply-form {
    padding: 1rem;
    border-top: 1px solid var(--border-color);
}

.reply-form textarea {
    width: 100%;
    padding: 0.5rem;
    margin-bottom: 1rem;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    background: var(--input-bg);
    color: var(--text-color);
}

.reply-button {
    background: var(--menu-icon);
    color: #fff;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
}

.reply-button:hover {
    opacity: 0.9;
}

.discussion-container {
    background: var(--card-bg);
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.post-form {
    padding: 20px;
    border-bottom: 1px solid var(--border-color);
}

.post-form textarea {
    background: var(--input-bg);
    color: var(--text-color);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 15px;
    resize: none;
}

.post-form textarea:focus {
    border-color: var(--menu-icon);
    box-shadow: 0 0 0 0.2rem rgba(75, 73, 172, 0.25);
}

.discussion-list {
    padding: 20px;
}

.discussion-item {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    margin-bottom: 20px;
    overflow: hidden;
}

.discussion-header {
    padding: 15px 20px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.author-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.author-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--avatar-bg) !important;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--avatar-text) !important;
    font-weight: 600;
    font-size: 1.1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

body.dark-theme .author-avatar {
    color: var(--avatar-text) !important;
}

.author-avatar:hover {
    background: var(--avatar-hover-bg) !important;
    transform: scale(1.05);
}

.author-name {
    font-weight: 500;
    color: var(--text-color);
}

.post-time {
    color: var(--text-color);
    opacity: 0.7;
    font-size: 0.9rem;
}

.discussion-content {
    padding: 20px;
    color: var(--text-color);
}

.discussion-actions {
    padding: 10px 20px;
    border-top: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    gap: 15px;
}

.action-button {
    background: none;
    border: none;
    color: var(--text-color);
    display: flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
    padding: 5px 10px;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.action-button:hover {
    background: var(--hover-bg);
}

.replies-section {
    background: var(--hover-bg);
    padding: 20px;
    margin-top: 1px;
}

.reply-item {
    background: var(--card-bg);
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}

.reply-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.reply-content {
    color: var(--text-color);
}

.reply-form {
    margin-top: 15px;
    padding: 15px;
    background: var(--card-bg);
    border-radius: 8px;
}

.btn-post {
    background: var(--menu-icon);
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 4px;
    cursor: pointer;
    transition: opacity 0.2s;
}

.btn-post:hover {
    opacity: 0.9;
}

.reply-count {
    background: var(--hover-bg);
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.85em;
    margin-left: 5px;
}

.action-button {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.action-button i {
    font-size: 1.1em;
}

/* Update the author avatar styles */
:root {
    /* Add these variables to your existing root */
    --avatar-text-light: #333333;
    --avatar-text-dark: #ffffff;
}

body.dark-theme {
    /* Add to your existing dark theme variables */
    --avatar-text-light: #ffffff;
    --avatar-text-dark: #333333;
}

/* Optional: Add contrast for better visibility */
.author-avatar {
    position: relative;
}

.author-avatar::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 50%;
    box-shadow: inset 0 0 0 1px rgba(0,0,0,0.1);
    pointer-events: none;
}

body.dark-theme .author-avatar::after {
    box-shadow: inset 0 0 0 1px rgba(255,255,255,0.1);
}
</style>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Discussion Forum</h4>
                        
                        <div class="discussion-container">
                            <!-- Post Form -->
                            <div class="post-form">
                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="parent_post_id" value="0">
                                    <div class="form-group">
                                        <textarea class="form-control" name="msg" rows="3" 
                                            placeholder="Share your thoughts or ask a question..." required></textarea>
                                    </div>
                                    <div class="text-right">
                                        <button type="submit" name="save" class="btn-post">
                                            <i class="fas fa-paper-plane"></i> Post
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Discussions List -->
                            <div class="discussion-list">
                                <?php foreach ($discussions as $query): ?>
                                    <div class="discussion-item">
                                        <div class="discussion-header">
                                            <div class="author-info">
                                                <div class="author-avatar">
                                                    <?= strtoupper(substr($query['name'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="author-name">
                                                        <?= htmlspecialchars($query['name']) ?>
                                                    </div>
                                                    <div class="post-time">
                                                        <?= date('M d, Y', strtotime($query['created'])) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="discussion-content">
                                            <?= nl2br(htmlspecialchars($query['post'])) ?>
                                        </div>

                                        <div class="discussion-actions">
                                            <button class="action-button" onclick="toggleReplies(<?= $query['id'] ?>)">
                                                <i class="fas fa-comment"></i> Replies 
                                                <span class="reply-count">(<?= count(Discussion::getReplies($conn, $query['id'])) ?>)</span>
                                            </button>
                                        </div>

                                        <!-- Replies Section -->
                                        <div id="replies-<?= $query['id'] ?>" class="replies-section" style="display: none;">
                                            <?php 
                                            $replies = Discussion::getReplies($conn, $query['id']);
                                            foreach ($replies as $reply): 
                                            ?>
                                                <div class="reply-item">
                                                    <div class="reply-header">
                                                        <div class="author-info">
                                                            <div class="author-avatar">
                                                                <?= strtoupper(substr($reply['name'], 0, 1)) ?>
                                                            </div>
                                                            <div>
                                                                <div class="author-name">
                                                                    <?= htmlspecialchars($reply['name']) ?>
                                                                </div>
                                                                <div class="post-time">
                                                                    <?= date('M d, Y', strtotime($reply['created'])) ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="reply-content">
                                                        <?= nl2br(htmlspecialchars($reply['post'])) ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>

                                            <!-- Reply Form -->
                                            <div class="reply-form">
                                                <form method="POST">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                    <input type="hidden" name="parent_post_id" value="<?= $query['id'] ?>">
                                                    <div class="form-group">
                                                        <textarea class="form-control" name="msg" rows="2" 
                                                            placeholder="Write a reply..." required></textarea>
                                                    </div>
                                                    <div class="text-right">
                                                        <button type="submit" name="save" class="btn-post">
                                                            <i class="fas fa-reply"></i> Reply
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>

<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<script>
function toggleReplies(discussionId) {
    const repliesSection = document.getElementById(`replies-${discussionId}`);
    if (repliesSection.style.display === 'none') {
        repliesSection.style.display = 'block';
    } else {
        repliesSection.style.display = 'none';
    }
}
</script>