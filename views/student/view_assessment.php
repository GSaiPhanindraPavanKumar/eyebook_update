<?php include "sidebar.php"; ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title float-left"><?php echo htmlspecialchars($assessment['title']); ?></h4>
                        <h5 class="float-right">Time Remaining: <span id="timer">00:00:00</span></h5>
                        <div class="clearfix"></div>
                        <?php 
                        // Clean and decode the JSON string
                        $jsonString = $assessment['questions'];
                        $jsonString = trim($jsonString, '"');
                        $jsonString = preg_replace('/\s+/', ' ', $jsonString);
                        $jsonString = stripslashes($jsonString);
                        
                        $questions = json_decode($jsonString, true);
                        
                        // Debug JSON errors if needed
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            echo '<p class="text-danger">JSON Error: ' . json_last_error_msg() . '</p>';
                            echo '<pre>Cleaned JSON: ' . htmlspecialchars($jsonString) . '</pre>';
                            echo '<pre>Original JSON: ' . htmlspecialchars($assessment['questions']) . '</pre>';
                        }

                        // Check if the decoded value is an array
                        if (is_array($questions)) {
                            // Randomize questions order
                            shuffle($questions);
                            echo '<form id="assessment-form">';
                            // Loop through the questions array
                            foreach ($questions as $index => $question) {
                                ?>
                                <div class="card mt-3">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">Question <?php echo $index + 1; ?></h6>
                                        <p class="card-text"><?php echo htmlspecialchars($question['question']); ?></p>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="question_<?php echo $index; ?>" id="option_<?php echo $index; ?>_a" value="a" data-correct="<?php echo $question['ans']; ?>">
                                            <label class="form-check-label" for="option_<?php echo $index; ?>_a">
                                                <?php echo htmlspecialchars($question['a']); ?>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="question_<?php echo $index; ?>" id="option_<?php echo $index; ?>_b" value="b" data-correct="<?php echo $question['ans']; ?>">
                                            <label class="form-check-label" for="option_<?php echo $index; ?>_b">
                                                <?php echo htmlspecialchars($question['b']); ?>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="question_<?php echo $index; ?>" id="option_<?php echo $index; ?>_c" value="c" data-correct="<?php echo $question['ans']; ?>">
                                            <label class="form-check-label" for="option_<?php echo $index; ?>_c">
                                                <?php echo htmlspecialchars($question['c']); ?>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="question_<?php echo $index; ?>" id="option_<?php echo $index; ?>_d" value="d" data-correct="<?php echo $question['ans']; ?>">
                                            <label class="form-check-label" for="option_<?php echo $index; ?>_d">
                                                <?php echo htmlspecialchars($question['d']); ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            echo '<button type="button" class="btn btn-primary btn-lg mt-4 w-100" onclick="submitAssessment()">Submit Assessment</button>';
                            echo '</form>';
                        } else {
                            // Display an error message if the decoded value is not an array
                            echo '<p class="text-danger">Error: Invalid question data.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
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
            // Start timer immediately after confirmation
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
    // Disable the submit button to prevent multiple submissions
    const submitButton = document.querySelector('button[onclick="submitAssessment()"]');
    submitButton.disabled = true;
    
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
        Swal.fire({
            icon: 'warning',
            title: 'Incomplete Assessment',
            text: 'Please answer all questions before submitting.',
        });
        return;
    }
    
    var score = (correctAnswers / totalQuestions) * 100;
    
    // Send results to server
    fetch('/student/submit_assessment_result', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            assessment_id: <?php echo $assessment['id']; ?>,
            score: score,
            total_questions: totalQuestions,
            correct_answers: correctAnswers
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            clearInterval(timerVars.timerInterval);  // Clear timer
            Swal.fire({
                icon: 'success',
                title: 'Assessment Completed!',
                html: `
                    <p>Your Score: ${score}%</p>
                    <p>Correct Answers: ${correctAnswers}/${totalQuestions}</p>
                `,
                confirmButtonText: 'OK',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    clearAssessmentData();
                    window.location.href = '/student/assessments';
                }
            });
        } else {
            submitButton.disabled = false;  // Re-enable button on error
            Swal.fire({
                icon: 'error',
                title: 'Submission Error',
                text: 'Failed to save assessment results. Please try again.'
            });
        }
    })
    .catch(error => {
        submitButton.disabled = false;  // Re-enable button on error
        Swal.fire({
            icon: 'error',
            title: 'Submission Error',
            text: 'An error occurred while submitting. Please try again.'
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