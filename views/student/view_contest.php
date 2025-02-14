<?php 
include 'sidebar.php'; 
use Models\University;
use Models\Contest;
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <h3 class="font-weight-bold">Contest Details</h3>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($contest['title']); ?></h5>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($contest['description']); ?></p>
                        <p><strong>Start Date:</strong> <?php echo htmlspecialchars($contest['start_date']); ?></p>
                        <p><strong>End Date:</strong> <?php echo htmlspecialchars($contest['end_date']); ?></p>
                        <p><strong>Universities:</strong> 
                            <?php 
                            $university_ids = json_decode($contest['university_id'], true);
                            foreach ($university_ids as $university_id) {
                                $university = University::getById($conn, $university_id);
                                echo htmlspecialchars($university['long_name']) . '<br>';
                            }
                            ?>
                        </p>
                        
                        <div id="contest-controls">
                            <?php if (strtotime($contest['start_date']) <= time() && strtotime($contest['end_date']) >= time()): ?>
                                <button id="show-start-modal" class="btn btn-primary">Start Contest</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <h3 class="font-weight-bold mt-4">Questions</h3>
                <div class="card">
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Question</th>
                                    <th>Grade</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $questions = Contest::getQuestions($conn, $contest['id']);
                                $currentTime = time();
                                $currentTime += 5 * 3600 + 30 * 60; // Adjust for -5 hours 30 minutes offset
                                $startTime = strtotime($contest['start_date']);
                                if (!empty($questions)): 
                                    foreach ($questions as $question): 
                                        // Determine the status of the question
                                        $status = 'Not Attempted';
                                        if (!empty($question['submissions'])) {
                                            $submissions = json_decode($question['submissions'], true);
                                            foreach ($submissions as $submission) {
                                                if ($submission['student_id'] == $_SESSION['student_id']) {
                                                    $status = $submission['status'] == 'passed' ? 'Passed' : 'Failed';
                                                    break;
                                                }
                                            }
                                        }
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($question['question']); ?></td>
                                            <td><?php echo htmlspecialchars($question['grade']); ?></td>
                                            <td><?php echo htmlspecialchars($status); ?></td>
                                            <td>
                                                <a href="/student/view_question/<?php echo $question['id']; ?>" 
                                                   class="btn btn-info view-question" 
                                                   data-question-id="<?php echo $question['id']; ?>" 
                                                   style="display: none;">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; 
                                else: ?>
                                    <tr>
                                        <td colspan="4">No questions found for this contest.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>

<!-- Timer Display -->
<div id="timer-container" style="display: none; position: fixed; top: 70px; right: 20px; z-index: 1000;">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Time Remaining</h5>
            <div id="time-remaining" class="h3 mb-0"></div>
        </div>
    </div>
</div>

<!-- Start Contest Modal -->
<div id="start-modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Start Contest</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Contest Details:</p>
                <ul>
                    <li>Time Limit: <?php echo htmlspecialchars($contest['time_limit']); ?> minutes</li>
                    <li>Total Questions: <?php echo count($questions); ?></li>
                </ul>
                <p class="text-warning">Note: Once started, the timer cannot be paused!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="start-contest">Start Now</button>
            </div>
        </div>
    </div>
</div>

<style>
.modal-backdrop {
    background-color: rgba(0, 0, 0, 0.5);
}

#timer-container {
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}

#timer-container:hover {
    transform: scale(1.05);
}

.modal.fade .modal-dialog {
    transition: transform 0.3s ease-out;
    transform: scale(0.8);
}

.modal.show .modal-dialog {
    transform: scale(1);
}
</style>

<script>
// IndexedDB setup and helper functions
const contestDB = (() => {
    const dbName = 'ContestDB';
    const storeName = 'contestAttempts';
    
    const db = new Promise((resolve, reject) => {
        const request = indexedDB.open(dbName, 1);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
        
        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains(storeName)) {
                db.createObjectStore(storeName, { keyPath: 'id' });
            }
        };
    });

    return {
        async getAttempt(attemptId) {
            const database = await db;
            const transaction = database.transaction([storeName], 'readonly');
            const store = transaction.objectStore(storeName);
            return new Promise((resolve, reject) => {
                const request = store.get(attemptId);
                request.onsuccess = () => resolve(request.result);
                request.onerror = () => reject(request.error);
            });
        },

        async saveAttempt(attempt) {
            const database = await db;
            const transaction = database.transaction([storeName], 'readwrite');
            const store = transaction.objectStore(storeName);
            return new Promise((resolve, reject) => {
                const request = store.put(attempt);
                request.onsuccess = () => resolve();
                request.onerror = () => reject(request.error);
            });
        }
    };
})();

// Contest management functions
const contestManager = {
    contestId: <?php echo json_encode($contest['id']); ?>,
    studentEmail: <?php echo json_encode($_SESSION['email']); ?>,
    timeLimit: <?php echo json_encode($contest['time_limit']); ?>,

    get attemptId() {
        return `${this.contestId}-${this.studentEmail}`;
    },

    async checkStatus() {
        try {
            const attempt = await contestDB.getAttempt(this.attemptId);
            if (attempt) {
                document.getElementById('show-start-modal').style.display = 'none';
                this.startTimer(attempt.startTime, attempt.timeLimit);
                this.showQuestions();
            }
        } catch (error) {
            console.error('Error checking contest status:', error);
        }
    },

    async startContest() {
        const attempt = {
            id: this.attemptId,
            contestId: this.contestId,
            studentEmail: this.studentEmail,
            startTime: Date.now(),
            timeLimit: this.timeLimit * 60 * 1000 // Convert minutes to milliseconds
        };

        try {
            await contestDB.saveAttempt(attempt);
            $('#start-modal').modal('hide');
            this.startTimer(attempt.startTime, attempt.timeLimit);
            this.showQuestions();
        } catch (error) {
            console.error('Error starting contest:', error);
            alert('Failed to start contest. Please try again.');
        }
    },

    startTimer(startTime, timeLimit) {
        const timerContainer = document.getElementById('timer-container');
        const timeRemainingElement = document.getElementById('time-remaining');
        document.getElementById('show-start-modal').style.display = 'none';
        timerContainer.style.display = 'block';
        
        function updateTimer() {
            const now = Date.now();
            const elapsed = now - startTime;
            const remaining = timeLimit - elapsed;
            
            if (remaining <= 0) {
                timerContainer.innerHTML = '<div class="alert alert-warning">Time\'s up!</div>';
                contestManager.hideQuestions();
                return;
            }
            
            const hours = Math.floor(remaining / (1000 * 60 * 60));
            const minutes = Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((remaining % (1000 * 60)) / 1000);
            
            timeRemainingElement.textContent = 
                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }
        
        updateTimer();
        const timerInterval = setInterval(updateTimer, 1000);
        timerContainer.dataset.timerInterval = timerInterval;
    },

    showQuestions() {
        document.querySelectorAll('.view-question').forEach(btn => {
            btn.style.display = 'inline-block';
        });
    },

    hideQuestions() {
        document.querySelectorAll('.view-question').forEach(btn => {
            btn.style.display = 'none';
        });
    }
};

// Event Listeners
document.getElementById('show-start-modal').addEventListener('click', () => {
    $('#start-modal').modal('show');
});

document.getElementById('start-contest').addEventListener('click', () => {
    contestManager.startContest();
});

// Initialize on page load
contestManager.checkStatus();
</script>