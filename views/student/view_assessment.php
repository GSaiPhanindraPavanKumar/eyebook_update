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

// Show fullscreen prompt when page loads
Swal.fire({
    title: 'Start Assessment',
    text: 'Click Start to begin the assessment in fullscreen mode',
    icon: 'info',
    allowOutsideClick: false,
    confirmButtonText: 'Start'
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
        // Initialize timer after user interaction
        initializeTimer();
    }
});

// IndexedDB initialization
const dbName = "AssessmentDB";
const storeName = "assessmentStore";
const assessmentId = <?php echo $assessment['id']; ?>;
const assessmentDuration = <?php echo $assessment['duration']; ?>;

// Initialize timer function
function initializeTimer() {
    const request = indexedDB.open(dbName, 1);

    request.onerror = function(event) {
        console.error("Database error: " + event.target.error);
    };

    request.onupgradeneeded = function(event) {
        const db = event.target.result;
        if (!db.objectStoreNames.contains(storeName)) {
            db.createObjectStore(storeName, { keyPath: "id" });
        }
    };

    request.onsuccess = function(event) {
        const db = event.target.result;
        const transaction = db.transaction([storeName], "readwrite");
        const store = transaction.objectStore(storeName);
        
        // Try to get existing start time
        const getRequest = store.get(assessmentId);
        
        getRequest.onsuccess = function(event) {
            let startTime;
            if (!event.target.result) {
                // If no start time exists, create one
                startTime = new Date().getTime();
                // Store the start time
                store.put({ id: assessmentId, startTime: startTime });
                console.log('New start time created:', startTime);
            } else {
                // Use existing start time
                startTime = event.target.result.startTime;
                console.log('Using existing start time:', startTime);
            }
            
            // Calculate end time based on start time and duration
            const endTime = startTime + (assessmentDuration * 60 * 1000);
            console.log('End time:', endTime);
            console.log('Current time:', new Date().getTime());
            console.log('Duration in ms:', assessmentDuration * 60 * 1000);
            
            // Start the timer
            const timerInterval = setInterval(function() {
                const now = new Date().getTime();
                const distance = endTime - now;
                console.log('Distance:', distance);

                if (distance < 0) {
                    clearInterval(timerInterval);
                    document.getElementById("timer").innerHTML = "00:00:00";
                    autoSubmit();
                    return;
                }

                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                const displayHours = hours < 10 ? "0" + hours : hours;
                const displayMinutes = minutes < 10 ? "0" + minutes : minutes;
                const displaySeconds = seconds < 10 ? "0" + seconds : seconds;

                const timerDisplay = `${displayHours}:${displayMinutes}:${displaySeconds}`;
                console.log('Timer display:', timerDisplay);
                document.getElementById("timer").innerHTML = timerDisplay;
            }, 1000);
        };

        getRequest.onerror = function(event) {
            console.error("Error getting start time:", event.target.error);
        };
    };
}

// Function to clear timer data
function clearAssessmentData() {
    const request = indexedDB.open(dbName, 1);
    request.onsuccess = function(event) {
        const db = event.target.result;
        const transaction = db.transaction([storeName], "readwrite");
        const store = transaction.objectStore(storeName);
        store.delete(assessmentId);
    };
}

function submitAssessment() {
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
            clearAssessmentData(); // Clear the stored time when assessment is submitted
            // TODO: Save the results to database
            window.location.href = '/student/assessments';
        }
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
</script>