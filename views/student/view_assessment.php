<?php include "sidebar-content.php"; ?>

<!-- Main Content -->
<div id="main-content" class="main-content-expanded min-h-screen bg-gray-50 dark:bg-gray-900 pt-20">
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Assessment Card -->
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
            <div class="p-6">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                        <?php echo htmlspecialchars($assessment['title']); ?>
                    </h1>
                    <div class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                        Time Remaining: <span id="timer" class="text-primary">00:00:00</span>
                    </div>
                </div>

                <?php 
                // Clean and decode the JSON string
                $jsonString = $assessment['questions'];
                $jsonString = trim($jsonString, '"');
                $jsonString = preg_replace('/\s+/', ' ', $jsonString);
                $jsonString = stripslashes($jsonString);
                
                $questions = json_decode($jsonString, true);
                
                // Debug JSON errors if needed
                if (json_last_error() !== JSON_ERROR_NONE) {
                    echo '<div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg dark:bg-red-900/20 dark:border-red-600 dark:text-red-400">';
                    echo '<p>JSON Error: ' . json_last_error_msg() . '</p>';
                    echo '<pre class="mt-2 text-sm">' . htmlspecialchars($jsonString) . '</pre>';
                    echo '<pre class="mt-2 text-sm">' . htmlspecialchars($assessment['questions']) . '</pre>';
                    echo '</div>';
                }

                // Check if the decoded value is an array
                if (is_array($questions)) {
                    // Randomize questions order
                    shuffle($questions);
                    echo '<form id="assessment-form" class="space-y-6">';
                    // Loop through the questions array
                    foreach ($questions as $index => $question) {
                        ?>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6">
                            <div class="mb-4">
                                <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">
                                    Question <?php echo $index + 1; ?>
                                </h2>
                                <p class="text-gray-900 dark:text-white text-lg">
                                    <?php echo htmlspecialchars($question['question']); ?>
                                </p>
                            </div>

                            <div class="space-y-3">
                                <?php
                                $options = [
                                    'a' => $question['a'],
                                    'b' => $question['b'],
                                    'c' => $question['c'],
                                    'd' => $question['d']
                                ];
                                foreach ($options as $key => $value):
                                ?>
                                <label class="flex items-center p-3 rounded-lg bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors">
                                    <input type="radio" 
                                           name="question_<?php echo $index; ?>" 
                                           value="<?php echo $key; ?>" 
                                           data-correct="<?php echo $question['ans']; ?>"
                                           class="w-4 h-4 text-primary bg-gray-100 border-gray-300 focus:ring-primary dark:bg-gray-700 dark:border-gray-600">
                                    <span class="ml-3 text-gray-700 dark:text-gray-300">
                                        <?php echo htmlspecialchars($value); ?>
                                    </span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                    <button type="button" 
                            onclick="submitAssessment()" 
                            class="w-full px-4 py-3 text-sm font-medium text-white bg-primary hover:bg-primary-hover rounded-lg transition-colors">
                        Submit Assessment
                    </button>
                    <?php
                    echo '</form>';
                } else {
                    echo '<div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg dark:bg-red-900/20 dark:border-red-600 dark:text-red-400">';
                    echo '<p>Error: Invalid question data.</p>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
});

document.onkeydown = function(e) {
    if(e.keyCode == 123) {
        return false;
    }
    if(e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)) {
        return false;
    }
    if(e.ctrlKey && e.shiftKey && e.keyCode == 'C'.charCodeAt(0)) {
        return false;
    }
    if(e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)) {
        return false;
    }
    if(e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) {
        return false;
    }
}

// Initialize variables for timer
const timerVars = {
    dbName: "AssessmentDB",
    storeName: "assessmentStore",
    assessmentId: <?php echo $assessment['id']; ?>,
    assessmentDuration: <?php echo $assessment['duration']; ?>,
    startTime: null,
    timerInterval: null
};

// Show start prompt when page loads
window.onload = function() {
    Swal.fire({
        title: 'Start Assessment',
        text: 'Click Start to begin the assessment in fullscreen mode',
        icon: 'info',
        allowOutsideClick: false,
        confirmButtonText: 'Start',
        confirmButtonColor: '#4B49AC',
        allowEscapeKey: false
    }).then((result) => {
        if (result.isConfirmed) {
            const elem = document.documentElement;
            if (elem.requestFullscreen) {
                elem.requestFullscreen();
            } else if (elem.webkitRequestFullscreen) {
                elem.webkitRequestFullscreen();
            } else if (elem.msRequestFullscreen) {
                elem.msRequestFullscreen();
            }
            startAssessment();
        }
    });
};

function startAssessment() {
    // Set start time if not already set
    if (!timerVars.startTime) {
        timerVars.startTime = new Date().getTime();
        // Store in IndexedDB
        storeStartTime(timerVars.startTime);
    }
    
    // Calculate end time
    const endTime = timerVars.startTime + (timerVars.assessmentDuration * 60 * 1000);
    
    // Start timer
    timerVars.timerInterval = setInterval(() => {
        const now = new Date().getTime();
        const distance = endTime - now;
        
        if (distance < 0) {
            clearInterval(timerVars.timerInterval);
            document.getElementById("timer").innerHTML = "00:00:00";
            autoSubmit();
            return;
        }
        
        const hours = Math.floor(distance / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        document.getElementById("timer").innerHTML = 
            `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }, 1000);
}

function storeStartTime(startTime) {
    const request = indexedDB.open(timerVars.dbName, 1);
    
    request.onupgradeneeded = function(event) {
        const db = event.target.result;
        if (!db.objectStoreNames.contains(timerVars.storeName)) {
            db.createObjectStore(timerVars.storeName, { keyPath: "id" });
        }
    };
    
    request.onsuccess = function(event) {
        const db = event.target.result;
        const transaction = db.transaction([timerVars.storeName], "readwrite");
        const store = transaction.objectStore(timerVars.storeName);
        store.put({ id: timerVars.assessmentId, startTime: startTime });
    };
}

function submitAssessment() {
    const submitButton = document.querySelector('button[onclick="submitAssessment()"]');
    submitButton.disabled = true;
    submitButton.classList.add('opacity-50', 'cursor-not-allowed');
    
    var totalQuestions = <?php echo count($questions); ?>;
    var answeredQuestions = 0;
    var correctAnswers = 0;
    
    // Check all questions
    for(var i = 0; i < totalQuestions; i++) {
        var selectedAnswer = document.querySelector('input[name="question_' + i + '"]:checked');
        if(selectedAnswer) {
            answeredQuestions++;
            if(selectedAnswer.value === selectedAnswer.dataset.correct) {
                correctAnswers++;
            }
        }
    }
    
    if(answeredQuestions < totalQuestions) {
        submitButton.disabled = false;
        submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
        Swal.fire({
            icon: 'warning',
            title: 'Incomplete Assessment',
            text: 'Please answer all questions before submitting.',
            confirmButtonColor: '#4B49AC'
        });
        return;
    }
    
    var score = (correctAnswers / totalQuestions) * 100;
    
    // Send results to server
    const formData = new URLSearchParams();
    formData.append('assessment_id', <?php echo $assessment['id']; ?>);
    formData.append('score', score);
    formData.append('total_questions', totalQuestions);
    formData.append('correct_answers', correctAnswers);

    fetch('/student/submit_assessment_result', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: formData.toString()
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            clearInterval(timerVars.timerInterval);
            Swal.fire({
                icon: 'success',
                title: 'Assessment Completed!',
                html: `
                    <p class="mb-2">Your Score: ${score}%</p>
                    <p>Correct Answers: ${correctAnswers}/${totalQuestions}</p>
                `,
                confirmButtonText: 'OK',
                confirmButtonColor: '#4B49AC',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    clearAssessmentData();
                    window.location.href = '/student/assessments';
                }
            });
        } else {
            submitButton.disabled = false;
            submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
            Swal.fire({
                icon: 'error',
                title: 'Submission Error',
                text: 'Failed to save assessment results. Please try again.',
                confirmButtonColor: '#4B49AC'
            });
        }
    })
    .catch(error => {
        submitButton.disabled = false;
        submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
        Swal.fire({
            icon: 'error',
            title: 'Submission Error',
            text: 'An error occurred while submitting. Please try again.',
            confirmButtonColor: '#4B49AC'
        });
    });
}

function autoSubmit() {
    Swal.fire({
        title: 'Time is up!',
        text: "The assessment will be automatically submitted.",
        icon: 'warning',
        showCancelButton: false,
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'OK'
    }).then((result) => {
        if (result.isConfirmed) {
            submitAssessment();
        }
    });
}

// Function to clear timer data
function clearAssessmentData() {
    const request = indexedDB.open(timerVars.dbName, 1);
    request.onsuccess = function(event) {
        const db = event.target.result;
        const transaction = db.transaction([timerVars.storeName], "readwrite");
        const store = transaction.objectStore(timerVars.storeName);
        store.delete(timerVars.assessmentId);
    };
}
</script>