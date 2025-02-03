<?php
include 'sidebar.php';

use Models\Database;
use Models\Discussion;

$conn = Database::getConnection();

// Get student info from database
$student_id = $_SESSION['student_id']; 
$stmt = $conn->prepare("SELECT name, university_id FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student not found.");
}

// Set session variables
$name = $student['name'];
$university_id = $student['university_id'];

// At the top of the file, after getting the connection
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle the post submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save'])) {
    $msg = filter_input(INPUT_POST, 'msg', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $parent_post_id = filter_input(INPUT_POST, 'parent_post_id', FILTER_VALIDATE_INT);
    $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $type = filter_input(INPUT_POST, 'post_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 'question';

    // Debug output
    echo "POST Data:<pre>";
    print_r($_POST);
    echo "</pre>";

    if ($csrf_token !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }

    if (!empty($msg)) {
        try {
            // Use the fetched name directly
            Discussion::addDiscussion($conn, $name, $msg, $university_id, $type, $parent_post_id);
            header('Location: ' . $_SERVER['PHP_SELF']); // Redirect to prevent resubmission
            exit;
        } catch (Exception $e) {
            echo "Error adding discussion: " . $e->getMessage();
        }
    }
}

// Fetch all discussions for the specific university
$discussions = Discussion::getDiscussionsByUniversity($conn, $university_id);
// echo "Fetched Discussions:<pre>";
// print_r($discussions);
// echo "</pre>";

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

/* Message box styles */
.message-box {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin: 10px 0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.message-box.reply {
    margin-left: 40px;
    border-left: 3px solid #007bff;
}

.message-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 0.9em;
    color: #666;
}

.message-content {
    word-wrap: break-word;
    overflow-wrap: break-word;
    max-height: 300px;
    overflow-y: auto;
    padding: 10px 0;
}

.message-content.collapsed {
    max-height: 100px;
    position: relative;
}

.message-content.collapsed::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 40px;
    background: linear-gradient(transparent, #f8f9fa);
}

.expand-button {
    color: #007bff;
    cursor: pointer;
    margin-top: 5px;
    display: none;
}

.message-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #eee;
}

.message-type-badge {
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: 500;
}

.type-question {
    background: #e3f2fd;
    color: #1976d2;
}

.type-answer {
    background: #e8f5e9;
    color: #2e7d32;
}

.type-message {
    background: #f5f5f5;
    color: #616161;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

.level-badge {
    background: #ffd700;
    color: #000;
    padding: 2px 6px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: 500;
}

.stars {
    color: #ffd700;
    font-size: 0.9em;
}

.badge-secondary {
    background-color: #6c757d;
    color: white;
    margin-left: 5px;
}

/* Hide badge if count is 0 */
.badge:empty {
    display: none;
}

/* Loading animation */
.loading-spinner {
    display: inline-block;
    width: 1em;
    height: 1em;
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 0.6s linear infinite;
    margin-right: 5px;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Disabled button styles */
.btn:disabled {
    cursor: not-allowed;
    opacity: 0.7;
}

.is-invalid {
    border-color: #dc3545;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 80%;
    color: #dc3545;
}

/* Blur overlay styles */
.blur-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    backdrop-filter: blur(5px);
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 9998;
    transition: all 0.3s ease;
}

/* Level up celebration styles */
.level-up-celebration {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 9999;
    background: white;
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
    text-align: center;
    animation: popIn 0.5s ease;
}

.level-up-title {
    color: #1976d2;
    font-size: 2rem;
    margin-bottom: 1rem;
}

.level-up-text {
    color: #666;
    font-size: 1.2rem;
    margin-bottom: 1rem;
}

@keyframes popIn {
    0% {
        transform: translate(-50%, -50%) scale(0.5);
        opacity: 0;
    }
    100% {
        transform: translate(-50%, -50%) scale(1);
        opacity: 1;
    }
}

/* Add new style for confetti canvas */
#confetti-canvas {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 10000; /* Higher than both overlay and celebration box */
}
</style>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="container">
                        <!-- Main post form -->
                        <form method="post" action="/student/discussion_forum">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="parent_post_id" value="0">
                            <input type="hidden" name="post_type" value="question">
                            <div class="form-group">
                                <label for="comment">Write your question:</label>
                                <textarea class="form-control" rows="5" name="msg" required></textarea>
                            </div>
                            <input type="submit" name="save" class="btn btn-primary" value="Send">
                        </form>

                        <h4 class="margin-top">Recent Questions</h4>
                        <div class="discussions-container">
                            <?php foreach ($discussions as $discussion): ?>
                                <div class="message-box">
                                    <div class="message-header">
                                        <span class="user-info">
                                            <strong><?php echo htmlspecialchars($discussion['name']); ?></strong>
                                            <?php if (isset($discussion['level'])): ?>
                                                <span class="level-badge">Lv. <?php echo $discussion['level']; ?></span>
                                                <?php 
                                                $stars = floor($discussion['level'] / 10);
                                                if ($stars > 0): ?>
                                                    <span class="stars">
                                                        <?php for($i = 0; $i < $stars; $i++): ?>
                                                            <i class="fas fa-star"></i>
                                                        <?php endfor; ?>
                                                    </span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <span class="message-type-badge type-<?php echo htmlspecialchars($discussion['type'] ?? 'question'); ?>">
                                                <?php echo ucfirst(htmlspecialchars($discussion['type'] ?? 'question')); ?>
                                            </span>
                                        </span>
                                        <span><?php echo date('M d, Y H:i', strtotime($discussion['created'])); ?></span>
                                    </div>
                                    <div class="message-content" id="content-<?php echo $discussion['id']; ?>">
                                        <?php echo nl2br(htmlspecialchars($discussion['post'])); ?>
                                    </div>
                                    <div class="expand-button" id="expand-<?php echo $discussion['id']; ?>" 
                                         onclick="toggleContent(<?php echo $discussion['id']; ?>)">
                                        Show More
                                    </div>
                                    <div class="message-footer">
                                        <div>
                                            <button class="btn btn-outline-primary btn-sm" 
                                                    onclick="toggleReplyForm(<?php echo $discussion['id']; ?>)">
                                                <i class="fas fa-reply"></i> Reply
                                            </button>
                                            <?php 
                                            $replyCount = Discussion::getReplyCount($conn, $discussion['id']);
                                            $likeCount = Discussion::getLikeCount($conn, $discussion['id']);
                                            $hasLiked = Discussion::hasUserLiked($conn, $discussion['id'], $_SESSION['student_id']);
                                            ?>
                                            <button class="btn btn-outline-secondary btn-sm" 
                                                    onclick="toggleViewReplies(<?php echo $discussion['id']; ?>)">
                                                <i class="fas fa-comments"></i> View Replies 
                                                <span class="badge badge-pill badge-secondary"><?php echo $replyCount; ?></span>
                                            </button>
                                            <?php if ($discussion['can_like']): ?>
                                                <button class="btn <?php echo $hasLiked ? 'btn-primary' : 'btn-outline-primary'; ?> btn-sm like-button" 
                                                        onclick="toggleLike(<?php echo $discussion['id']; ?>)" 
                                                        id="like-btn-<?php echo $discussion['id']; ?>">
                                                    <span class="button-content">
                                                        <i class="fas fa-thumbs-up"></i> 
                                                        <span id="like-count-<?php echo $discussion['id']; ?>"><?php echo $likeCount; ?></span>
                                                    </span>
                                                    <span class="loading-content" style="display: none;">
                                                        <span class="loading-spinner"></span>
                                                        <span>Loading...</span>
                                                    </span>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Replies Container -->
                                <div id="replies-<?php echo $discussion['id']; ?>" style="display: none;">
                                    <?php
                                    $replies = Discussion::getReplies($conn, $discussion['id']);
                                    foreach ($replies as $reply): ?>
                                        <div class="message-box reply">
                                            <div class="message-header">
                                                <span class="user-info">
                                                    <strong><?php echo htmlspecialchars($reply['name']); ?></strong>
                                                    <?php if (isset($reply['level'])): ?>
                                                        <span class="level-badge">Lv. <?php echo $reply['level']; ?></span>
                                                        <?php 
                                                        $stars = floor($reply['level'] / 10);
                                                        if ($stars > 0): ?>
                                                            <span class="stars">
                                                                <?php for($i = 0; $i < $stars; $i++): ?>
                                                                    <i class="fas fa-star"></i>
                                                                <?php endfor; ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    <span class="message-type-badge type-<?php echo htmlspecialchars($reply['type'] ?? 'answer'); ?>">
                                                        <?php echo ucfirst(htmlspecialchars($reply['type'] ?? 'answer')); ?>
                                                    </span>
                                                </span>
                                                <span><?php echo date('M d, Y H:i', strtotime($reply['created'])); ?></span>
                                            </div>
                                            <div class="message-content" id="content-reply-<?php echo $reply['id']; ?>">
                                                <?php echo nl2br(htmlspecialchars($reply['post'])); ?>
                                            </div>
                                            <div class="expand-button" id="expand-reply-<?php echo $reply['id']; ?>" 
                                                 onclick="toggleContent('reply-<?php echo $reply['id']; ?>')">
                                                Show More
                                            </div>
                                            <div class="message-footer">
                                                <div>
                                                    <?php 
                                                    $replyLikeCount = Discussion::getLikeCount($conn, $reply['id']);
                                                    $hasLikedReply = Discussion::hasUserLiked($conn, $reply['id'], $_SESSION['student_id']);
                                                    ?>
                                                    <button class="btn <?php echo $hasLikedReply ? 'btn-primary' : 'btn-outline-primary'; ?> btn-sm like-button" 
                                                            onclick="toggleLike(<?php echo $reply['id']; ?>)" 
                                                            id="like-btn-<?php echo $reply['id']; ?>">
                                                        <span class="button-content">
                                                            <i class="fas fa-thumbs-up"></i> 
                                                            <span id="like-count-<?php echo $reply['id']; ?>"><?php echo $replyLikeCount; ?></span>
                                                        </span>
                                                        <span class="loading-content" style="display: none;">
                                                            <span class="loading-spinner"></span>
                                                            <span>Loading...</span>
                                                        </span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Reply Form -->
                                <div id="reply-form-<?php echo $discussion['id']; ?>" style="display: none;" class="message-box reply">
                                    <form method="post" action="/student/reply_discussion">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="parent_post_id" value="<?php echo $discussion['id']; ?>">
                                        <div class="form-group">
                                            <label for="post_type">Reply Type:</label>
                                            <select class="form-control" name="post_type" required>
                                                <option value="answer">Answer</option>
                                                <option value="question">Follow-up Question</option>
                                                <option value="message">Comment</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="reply">Your Reply:</label>
                                            <textarea class="form-control" rows="3" name="msg" required></textarea>
                                        </div>
                                        <input type="submit" name="save_reply" class="btn btn-primary" value="Reply">
                                    </form>
                                </div>
                            <?php endforeach; ?>
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

// Function to check and handle long content
function checkContentHeight() {
    document.querySelectorAll('.message-content').forEach(content => {
        const expandButton = document.getElementById('expand-' + content.id.replace('content-', ''));
        if (content.scrollHeight > 100) {
            content.classList.add('collapsed');
            expandButton.style.display = 'block';
        }
    });
}

// Function to toggle content expansion
function toggleContent(id) {
    const content = document.getElementById('content-' + id);
    const expandButton = document.getElementById('expand-' + id);
    
    if (content.classList.contains('collapsed')) {
        content.classList.remove('collapsed');
        expandButton.textContent = 'Show Less';
    } else {
        content.classList.add('collapsed');
        expandButton.textContent = 'Show More';
    }
}

// Call checkContentHeight when page loads
document.addEventListener('DOMContentLoaded', checkContentHeight);

function toggleLike(discussionId) {
    const btn = $(`#like-btn-${discussionId}`);
    const buttonContent = btn.find('.button-content');
    const loadingContent = btn.find('.loading-content');
    
    // Disable button and show loading
    btn.prop('disabled', true);
    buttonContent.hide();
    loadingContent.show();
    
    $.ajax({
        url: '/student/toggle_like',
        method: 'POST',
        data: { discussion_id: discussionId },
        dataType: 'json'
    })
    .done(function(response) {
        if (response.status === 'success') {
            const count = $(`#like-count-${discussionId}`);
            
            // Update like count
            count.text(response.likeCount);
            
            // Toggle button appearance
            if (response.action === 'liked') {
                btn.removeClass('btn-outline-primary').addClass('btn-primary');
            } else {
                btn.removeClass('btn-primary').addClass('btn-outline-primary');
            }
            
            // Show XP notifications
            if (response.authorXpGained) {
                showXPNotification(response.authorXpGained);
            }
            if (response.authorLevelUp) {
                celebrateLevelUp(response.authorNewLevel);
            }
        } else {
            console.error('Error:', response.message);
            alert('Error: ' + response.message);
        }
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
        console.error('AJAX Error:', textStatus, errorThrown);
        alert('Failed to process like. Please try again.');
    })
    .always(function() {
        // Re-enable button and hide loading
        btn.prop('disabled', false);
        loadingContent.hide();
        buttonContent.show();
    });
}

// Profane words list (abbreviated for readability)
const profaneWords = ["2g1c", "4r5e", "5h1t", "5hit", /* ... */]; // We'll load this dynamically

// Function to check for profanity
function containsProfanity(text) {
    // Convert text to lowercase for case-insensitive matching
    const lowerText = text.toLowerCase();
    
    // Check each word in the text
    const words = lowerText.split(/\s+/);
    for (const word of words) {
        if (profaneWords.includes(word)) {
            return true;
        }
    }
    
    // Also check for partial matches within words
    return profaneWords.some(profaneWord => 
        lowerText.includes(profaneWord.toLowerCase())
    );
}

// Load profane words list when document loads
document.addEventListener('DOMContentLoaded', async function() {
    try {
        const response = await fetch('https://raw.githubusercontent.com/zacanger/profane-words/refs/heads/master/words.json');
        const words = await response.json();
        profaneWords.push(...words);
    } catch (error) {
        console.error('Error loading profane words:', error);
    }
});

// Update form submission to include profanity check
$('form').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const messageText = form.find('textarea[name="msg"]').val();
    
    // Check for profanity
    if (containsProfanity(messageText)) {
        alert('Your message contains inappropriate language. Please revise and try again.');
        return false;
    }
    
    // If no profanity is found, proceed with the AJAX submission
    $.ajax({
        url: form.attr('action'),
        method: 'POST',
        data: form.serialize(),
        dataType: 'json'
    })
    .done(function(response) {
        console.log('Response:', response);
        
        if (response.status === 'error') {
            console.error('Error:', response.message);
            alert('Error: ' + response.message);
            return;
        }
        
        if (response.xpGained) {
            showXPNotification(response.xpGained);
        }
        
        if (response.levelUp) {
            celebrateLevelUp(response.newLevel);
        }
        
        // Reload after a short delay to show notifications
        setTimeout(function() {
            window.location.reload();
        }, 1500);
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
        console.error('AJAX Error:', textStatus, errorThrown);
        // Fall back to regular form submission
        form.off('submit').submit();
    });
});

// Add real-time validation as user types
$('textarea[name="msg"]').on('input', function() {
    const messageText = $(this).val();
    const submitButton = $(this).closest('form').find('button[type="submit"]');
    
    if (containsProfanity(messageText)) {
        $(this).addClass('is-invalid');
        submitButton.prop('disabled', true);
        
        // Show warning message
        if (!$(this).next('.invalid-feedback').length) {
            $(this).after('<div class="invalid-feedback">Message contains inappropriate language.</div>');
        }
    } else {
        $(this).removeClass('is-invalid');
        submitButton.prop('disabled', false);
        $(this).next('.invalid-feedback').remove();
    }
});
</script>

<!-- Add this for XP notifications -->
<div id="xp-notification" class="toast" style="position: fixed; bottom: 20px; right: 20px; display: none;">
    <div class="toast-header">
        <strong class="mr-auto">XP Gained!</strong>
        <button type="button" class="ml-2 mb-1 close" data-dismiss="toast">&times;</button>
    </div>
    <div class="toast-body">
        You earned <span id="xp-amount"></span> XP!
    </div>
</div>

<!-- Replace the existing level up celebration div with this -->
<div class="blur-overlay"></div>
<div class="level-up-celebration">
    <h2 class="level-up-title">Level Up!</h2>
    <p class="level-up-text">Congratulations! You've reached level <span id="new-level"></span>!</p>
</div>

<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.4.0/dist/confetti.browser.min.js"></script>
<script>
// Add this to handle XP notifications and level up celebrations
function showXPNotification(xp) {
    $('#xp-amount').text(xp);
    $('#xp-notification').toast('show');
}

function celebrateLevelUp(level) {
    $('#new-level').text(level);
    $('.blur-overlay').fadeIn(300);
    $('.level-up-celebration').fadeIn(300);
    
    // Create confetti with custom options
    const confettiSettings = {
        particleCount: 100,
        spread: 70,
        origin: { y: 0.6 },
        zIndex: 10000, // Set high z-index for confetti
        disableForReducedMotion: true // Accessibility consideration
    };
    
    confetti({
        ...confettiSettings,
        particleCount: 100,
        spread: 70,
        origin: { y: 0.6 }
    });
    
    // Add more confetti for a better effect
    setTimeout(() => {
        confetti({
            ...confettiSettings,
            particleCount: 50,
            angle: 60,
            spread: 55,
            origin: { x: 0 }
        });
        confetti({
            ...confettiSettings,
            particleCount: 50,
            angle: 120,
            spread: 55,
            origin: { x: 1 }
        });
    }, 250);
    
    setTimeout(() => {
        $('.level-up-celebration').fadeOut(300);
        $('.blur-overlay').fadeOut(300);
        confetti.reset(); // Clean up confetti
    }, 3000);
}
</script>