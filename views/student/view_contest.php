<?php 
include 'sidebar-content.php'; 
use Models\University;
use Models\Contest;
?>

<!-- Main Content -->
<div id="main-content" class="main-content-expanded min-h-screen bg-gray-50 dark:bg-gray-900 pt-20">
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Contest Details Card -->
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden mb-6">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">
                    <?php echo htmlspecialchars($contest['title']); ?>
                </h1>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</h3>
                            <p class="mt-1 text-gray-900 dark:text-gray-300">
                                <?php echo htmlspecialchars($contest['description']); ?>
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Start Date</h3>
                            <p class="mt-1 text-gray-900 dark:text-gray-300">
                                <?php echo htmlspecialchars($contest['start_date']); ?>
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">End Date</h3>
                            <p class="mt-1 text-gray-900 dark:text-gray-300">
                                <?php echo htmlspecialchars($contest['end_date']); ?>
                            </p>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Participating Universities</h3>
                        <div class="space-y-2">
                            <?php 
                            $university_ids = json_decode($contest['university_id'], true);
                            foreach ($university_ids as $university_id) {
                                $university = University::getById($conn, $university_id);
                                ?>
                                <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700 rounded-md text-gray-900 dark:text-gray-300">
                                    <?php echo htmlspecialchars($university['long_name']); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div id="contest-controls">
                    <?php if (strtotime($contest['start_date']) <= time() && strtotime($contest['end_date']) >= time()): ?>
                        <button id="show-start-modal" 
                                class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary-hover transition-colors">
                            Start Contest
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Questions Card -->
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-6">Questions</h2>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Question</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Grade</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:divide-gray-700 dark:bg-gray-800">
                            <?php 
                            $questions = Contest::getQuestions($conn, $contest['id']);
                            $currentTime = time();
                            $currentTime += 5 * 3600 + 30 * 60;
                            $startTime = strtotime($contest['start_date']);
                            
                            if (!empty($questions)): 
                                foreach ($questions as $question): 
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
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-300">
                                            <?php echo htmlspecialchars($question['question']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <?php echo htmlspecialchars($question['grade']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php echo $status === 'Passed' 
                                                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                    : ($status === 'Failed' 
                                                        ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                                        : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'); ?>">
                                                <?php echo htmlspecialchars($status); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="/student/view_question/<?php echo $question['id']; ?>" 
                                               class="view-question hidden px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary-hover transition-colors"
                                               data-question-id="<?php echo $question['id']; ?>">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                        No questions found for this contest.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Timer Display -->
<div id="timer-container" class="hidden fixed top-20 right-4 z-50">
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-4 transform transition-transform duration-300 hover:scale-105">
        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Time Remaining</h3>
        <div id="time-remaining" class="text-2xl font-bold text-primary"></div>
    </div>
</div>

<!-- Start Contest Modal -->
<div id="start-modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl transform transition-all sm:w-full sm:max-w-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Start Contest</h3>
            </div>
            <div class="px-6 py-4">
                <div class="space-y-4">
                    <p class="text-gray-700 dark:text-gray-300">Contest Details:</p>
                    <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2">
                        <li>Time Limit: <?php echo htmlspecialchars($contest['time_limit']); ?> minutes</li>
                        <li>Total Questions: <?php echo count($questions); ?></li>
                    </ul>
                    <p class="text-yellow-600 dark:text-yellow-500">
                        Note: Once started, the timer cannot be paused!
                    </p>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
                <button type="button" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors"
                        data-dismiss="modal">
                    Cancel
                </button>
                <button type="button" 
                        id="start-contest"
                        class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary-hover transition-colors">
                    Start Now
                </button>
            </div>
        </div>
    </div>
</div>

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
            btn.classList.remove('hidden');
        });
    },

    hideQuestions() {
        document.querySelectorAll('.view-question').forEach(btn => {
            btn.classList.add('hidden');
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