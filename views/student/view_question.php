<?php include 'sidebar-content.php'; ?>

<!-- Main Content -->
<div id="main-content" class="main-content-expanded min-h-screen bg-gray-50 dark:bg-gray-900 pt-20">
    <!-- Timer Display -->
    <div id="timer-container" class="hidden fixed top-20 right-4 z-50">
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-4 transform transition-transform duration-300 hover:scale-105">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Time Remaining</h3>
            <div id="time-remaining" class="text-2xl font-bold text-primary"></div>
        </div>
    </div>

    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                Question Details
            </h1>
        </div>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Question Details Card -->
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-6">
                        <?php echo htmlspecialchars($question['question']); ?>
                    </h2>
                    
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</h3>
                            <p class="mt-1 text-gray-900 dark:text-gray-300">
                                <?php echo htmlspecialchars($question['description']); ?>
                            </p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Input</h3>
                            <p class="mt-1 text-gray-900 dark:text-gray-300">
                                <?php echo htmlspecialchars($question['input']); ?>
                            </p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Output</h3>
                            <p class="mt-1 text-gray-900 dark:text-gray-300">
                                <?php echo htmlspecialchars($question['output']); ?>
                            </p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Grade</h3>
                            <p class="mt-1 text-gray-900 dark:text-gray-300">
                                <?php echo htmlspecialchars($question['grade']); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Code Compiler Card -->
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-6">Code Compiler</h2>
                    
                    <form id="compiler-form" class="space-y-6">
                        <div>
                            <label for="language" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Select Language:
                            </label>
                            <select id="language" 
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="python">Python</option>
                                <option value="javascript">JavaScript</option>
                                <option value="java">Java</option>
                                <option value="c">C</option>
                                <option value="cpp">C++</option>
                            </select>
                        </div>

                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Write your code here:
                            </label>
                            <textarea id="code" 
                                      name="code" 
                                      rows="10"
                                      class="mt-1 block w-full px-3 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                        </div>

                        <div>
                            <label for="input" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Input (optional):
                            </label>
                            <textarea id="input" 
                                      name="input" 
                                      rows="3"
                                      class="mt-1 block w-full px-3 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white"><?php echo htmlspecialchars($question['input']); ?></textarea>
                        </div>

                        <div class="flex space-x-4">
                            <button type="button" 
                                    id="run-code"
                                    class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary-hover transition-colors">
                                Run Code
                            </button>
                            <button type="button" 
                                    id="submit-code"
                                    class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 transition-colors">
                                Submit Code
                            </button>
                        </div>
                    </form>

                    <div id="output" class="mt-6">
                        <pre id="output-content" 
                             class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg text-sm text-gray-900 dark:text-gray-300 overflow-x-auto">Your output will appear here...</pre>
                        <input type="text" 
                               id="program-input" 
                               class="hidden mt-2 w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                               placeholder="Press Enter to submit input..." />
                    </div>

                    <div id="message" class="mt-4"></div>
                </div>
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
                document.getElementById('timer-container').style.display = 'block';
                this.startTimer(attempt.startTime, attempt.timeLimit);
            } else {
                window.location.href = `/student/view_contest/${this.contestId}`;
            }
        } catch (error) {
            console.error('Error checking contest status:', error);
            window.location.href = `/student/view_contest/${this.contestId}`;
        }
    },

    startTimer(startTime, timeLimit) {
        const timerContainer = document.getElementById('timer-container');
        const timeRemainingElement = document.getElementById('time-remaining');
        
        function updateTimer() {
            const now = Date.now();
            const elapsed = now - startTime;
            const remaining = timeLimit - elapsed;
            
            if (remaining <= 0) {
                window.location.href = `/student/view_contest/${contestManager.contestId}`;
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
    }
};

// Initialize on page load
contestManager.checkStatus();

document.getElementById('run-code').addEventListener('click', async () => {
    const endDate = new Date('<?php echo $contest['end_date']; ?>');
    const currentDate = new Date();

    if (currentDate > endDate) {
        document.getElementById('message').textContent = 'The end date has passed. Running code is no longer allowed.';
        document.getElementById('message').classList.add('alert', 'alert-danger');
        return;
    }

    const languageIdMap = {
        python: 71,
        javascript: 63,
        java: 62,
        c: 50,
        cpp: 54,
    };

    const language = document.getElementById('language').value;
    const code = document.getElementById('code').value;
    const input = document.getElementById('input').value;
    const outputDiv = document.getElementById('output');
    const outputContent = document.getElementById('output-content');
    const messageDiv = document.getElementById('message');

    // Reset output and message
    outputContent.textContent = 'Running your code...';
    outputDiv.classList.remove('error');
    messageDiv.textContent = '';

    if (!code.trim()) {
        outputContent.textContent = 'Please enter some code to run.';
        outputDiv.classList.add('error');
        return;
    }

    if (!languageIdMap[language]) {
        outputContent.textContent = `Unsupported language: ${language}`;
        outputDiv.classList.add('error');
        return;
    }

    try {
        // Submit code for execution
        const response = await fetch('https://judge0-ce.p.rapidapi.com/submissions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'x-rapidapi-host': 'judge0-ce.p.rapidapi.com',
                'x-rapidapi-key': 'ddc70ba740msh6da21e7d2602ceap1d940cjsn53f6ddc352e5'
            },
            body: JSON.stringify({
                source_code: code,
                language_id: languageIdMap[language],
                stdin: input
            })
        });

        if (!response.ok) {
            throw new Error(`Submission Error: ${response.statusText}`);
        }

        const { token } = await response.json();

        // Poll for result
        let result;
        while (true) {
            const resultResponse = await fetch(`https://judge0-ce.p.rapidapi.com/submissions/${token}`, {
                method: 'GET',
                headers: {
                    'x-rapidapi-host': 'judge0-ce.p.rapidapi.com',
                    'x-rapidapi-key': 'ddc70ba740msh6da21e7d2602ceap1d940cjsn53f6ddc352e5'
                }
            });

            if (!resultResponse.ok) {
                throw new Error(`Result Fetch Error: ${resultResponse.statusText}`);
            }

            result = await resultResponse.json();

            if (result.status.id === 1 || result.status.id === 2) {
                await new Promise((resolve) => setTimeout(resolve, 1000));
            } else {
                break;
            }
        }

        // Display result
        if (result.status.id === 3) {
            outputContent.textContent = result.stdout || 'No output.';
        } else {
            outputContent.textContent = result.stderr || result.compile_output || 'An error occurred.';
            outputDiv.classList.add('error');
        }
    } catch (error) {
        outputContent.textContent = `Error: ${error.message}`;
        outputDiv.classList.add('error');
    }
});

document.getElementById('submit-code').addEventListener('click', async () => {
    const endDate = new Date('<?php echo $contest['end_date']; ?>');
    const currentDate = new Date();

    if (currentDate > endDate) {
        document.getElementById('message').textContent = 'The end date has passed. Submissions are no longer allowed.';
        document.getElementById('message').classList.add('alert', 'alert-danger');
        return;
    }

    const languageIdMap = {
        python: 71,
        javascript: 63,
        java: 62,
        c: 50,
        cpp: 54,
    };

    const language = document.getElementById('language').value;
    const code = document.getElementById('code').value;
    const input = document.getElementById('input').value;
    const outputDiv = document.getElementById('output');
    const outputContent = document.getElementById('output-content');
    const messageDiv = document.getElementById('message');

    // Reset output and message
    outputContent.textContent = 'Submitting your code...';
    outputDiv.classList.remove('error');
    messageDiv.textContent = '';

    if (!code.trim()) {
        outputContent.textContent = 'Please enter some code to submit.';
        outputDiv.classList.add('error');
        return;
    }

    if (!languageIdMap[language]) {
        outputContent.textContent = `Unsupported language: ${language}`;
        outputDiv.classList.add('error');
        return;
    }

    try {
        // Submit code for execution
        const response = await fetch('https://judge0-ce.p.rapidapi.com/submissions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'x-rapidapi-host': 'judge0-ce.p.rapidapi.com',
                'x-rapidapi-key': 'ddc70ba740msh6da21e7d2602ceap1d940cjsn53f6ddc352e5'
            },
            body: JSON.stringify({
                source_code: code,
                language_id: languageIdMap[language],
                stdin: input
            })
        });

        if (!response.ok) {
            throw new Error(`Submission Error: ${response.statusText}`);
        }

        const { token } = await response.json();

        // Poll for result
        let result;
        while (true) {
            const resultResponse = await fetch(`https://judge0-ce.p.rapidapi.com/submissions/${token}`, {
                method: 'GET',
                headers: {
                    'x-rapidapi-host': 'judge0-ce.p.rapidapi.com',
                    'x-rapidapi-key': 'ddc70ba740msh6da21e7d2602ceap1d940cjsn53f6ddc352e5'
                }
            });

            if (!resultResponse.ok) {
                throw new Error(`Result Fetch Error: ${resultResponse.statusText}`);
            }

            result = await resultResponse.json();

            if (result.status.id === 1 || result.status.id === 2) {
                await new Promise((resolve) => setTimeout(resolve, 1000));
            } else {
                break;
            }
        }

        // Display result
        if (result.status.id === 3) {
            outputContent.textContent = result.stdout || 'No output.';
            if (result.stdout.trim() === '<?php echo addslashes($question['output']); ?>') {
                messageDiv.textContent = 'Success: All test cases passed!';
                messageDiv.classList.add('alert', 'alert-success');

                // Update the database to indicate that all test cases are passed
                const updateResponse = await fetch('/student/update_question_submission', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        question_id: <?php echo $question['id']; ?>,
                        student_id: <?php echo $_SESSION['student_id']; ?>,
                        submission_date: new Date().toISOString(),
                        runtime: result.time // Capture the runtime from the result
                    })
                });

                if (!updateResponse.ok) {
                    throw new Error(`Update Error: ${updateResponse.statusText}`);
                }
            } else {
                messageDiv.textContent = 'Error: Test cases not passed.';
                messageDiv.classList.add('alert', 'alert-danger');
            }
        } else {
            outputContent.textContent = result.stderr || result.compile_output || 'An error occurred.';
            outputDiv.classList.add('error');
        }
    } catch (error) {
        outputContent.textContent = `Error: ${error.message}`;
        outputDiv.classList.add('error');
    }
});

function showMessage(message, type) {
    const messageDiv = document.getElementById('message');
    messageDiv.textContent = message;
    messageDiv.className = `mt-4 p-4 rounded-lg ${
        type === 'success' 
            ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
            : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
    }`;
}

// Update error display
function showError(outputContent, message) {
    outputContent.textContent = message;
    outputContent.classList.add('text-red-600', 'dark:text-red-400');
}
</script>

<style>
.error {
    @apply text-red-600 dark:text-red-400;
}
</style>