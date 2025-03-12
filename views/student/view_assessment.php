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
                        // Decode the JSON string into an array
                        $questions = $assessment['questions'];

                        $jsonString = $questions;

                        // Remove newline characters from the JSON string
                        $jsonString = preg_replace('/\s+/', ' ', $jsonString);
                        $jsonString = trim($jsonString);
                        $questions = json_decode($jsonString, true);
                        print_r(html_entity_decode($questions));
                        // print_r($questions);
                        print_r($questions[0]);
                        // print_r($questions[0]);
                        // Check if the decoded value is an array
                        if (is_array($questions)) {
                            // Loop through the questions array
                            foreach ($questions as $index => $question) {
                                ?>
                                <div class="card mt-3">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">Question <?php echo $index + 1; ?></h6>
                                        <p class="card-text"><?php echo htmlspecialchars($question['question']); ?></p>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="question_<?php echo $index; ?>" id="option_<?php echo $index; ?>_a" value="a">
                                            <label class="form-check-label" for="option_<?php echo $index; ?>_a">
                                                <?php echo htmlspecialchars($question['a']); ?>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="question_<?php echo $index; ?>" id="option_<?php echo $index; ?>_b" value="b">
                                            <label class="form-check-label" for="option_<?php echo $index; ?>_b">
                                                <?php echo htmlspecialchars($question['b']); ?>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="question_<?php echo $index; ?>" id="option_<?php echo $index; ?>_c" value="c">
                                            <label class="form-check-label" for="option_<?php echo $index; ?>_c">
                                                <?php echo htmlspecialchars($question['c']); ?>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="question_<?php echo $index; ?>" id="option_<?php echo $index; ?>_d" value="d">
                                            <label class="form-check-label" for="option_<?php echo $index; ?>_d">
                                                <?php echo htmlspecialchars($question['d']); ?>
                                            </label>
                                        </div>
                                        <button type="button" class="btn btn-primary mt-3" onclick="submitAnswer(<?php echo $index; ?>, '<?php echo $question['ans']; ?>')">Submit</button>
                                    </div>
                                </div>
                                <?php
                            }
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

var elem = document.documentElement;
function openFullscreen() {
    if (elem.requestFullscreen) {
        elem.requestFullscreen();
    } else if (elem.webkitRequestFullscreen) { /* Safari */
        elem.webkitRequestFullscreen();
    } else if (elem.msRequestFullscreen) { /* IE11 */
        elem.msRequestFullscreen();
    }
}

openFullscreen();

var endTime = new Date().getTime() + <?php echo $assessment['duration'] * 60 * 1000; ?>;
var timerInterval = setInterval(function() {
    var now = new Date().getTime();
    var distance = endTime - now;

    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    var seconds = Math.floor((distance % (1000 * 60)) / 1000);

    hours = hours < 10 ? "0" + hours : hours;
    minutes = minutes < 10 ? "0" + minutes : minutes;
    seconds = seconds < 10 ? "0" + seconds : seconds;

    document.getElementById("timer").innerHTML = hours + ":" + minutes + ":" + seconds;

    if (distance < 0) {
        clearInterval(timerInterval);
        document.getElementById("timer").innerHTML = "00:00:00";
        autoSubmit();
    }
}, 1000);

function submitAnswer(questionIndex, correctAnswer) {
    var selectedAnswer = document.querySelector('input[name="question_' + questionIndex + '"]:checked');
    if(selectedAnswer) {
        if(selectedAnswer.value === correctAnswer) {
            Swal.fire({
                icon: 'success',
                title: 'Correct!',
                text: 'Your answer is correct.',
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Incorrect!',
                text: 'Your answer is incorrect.',
            });
        }
    } else {
        Swal.fire({
            icon: 'warning',
            title: 'No Answer Selected',
            text: 'Please select an answer.',
        });
    }
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
            // TODO: Submit the assessment
            window.location.href = '/student/assessments';
        }
    });
}
</script>