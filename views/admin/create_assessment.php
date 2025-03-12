<?php include "sidebar.php"; ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Create New Assessment</h4>
                        <form method="post" action="/admin/create_assessment">
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <!-- NEW: Input field for Topic -->
                            <div class="form-group">
                                <label for="topic">Topic</label>
                                <input type="text" class="form-control" id="topic" name="topic" required>
                            </div>
                            <!-- NEW: Input field for Number of Questions -->
                            <div class="form-group">
                                <label for="num_questions">Number of Questions to Generate</label>
                                <input type="number" class="form-control" id="num_questions" name="num_questions" required>
                            </div>
                            <!-- NEW: Select field for Difficulty -->
                            <div class="form-group">
                                <label for="difficulty">Difficulty</label>
                                <select class="form-control" id="difficulty" name="difficulty" required>
                                    <option value="">Select Difficulty</option>
                                    <option value="easy">Easy</option>
                                    <option value="medium">Medium</option>
                                    <option value="hard">Hard</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="start_time">Start Time</label>
                                <input type="datetime-local" class="form-control" id="start_time" name="start_time" required>
                            </div>
                            <div class="form-group">
                                <label for="end_time">End Time</label>
                                <input type="datetime-local" class="form-control" id="end_time" name="end_time" required>
                            </div>
                            <div class="form-group">
                                <label for="duration">Duration (minutes)</label>
                                <input type="number" class="form-control" id="duration" name="duration" required>
                            </div>
                            <div class="form-group">
                                <label for="questions">Questions (JSON format)</label>
                                <textarea class="form-control" id="questions" name="questions" rows="5" required></textarea>
                            </div>
                            <!-- NEW: Button to trigger AI generation -->
                            <button type="button" id="generate_questions" class="btn btn-secondary">Generate Questions with AI</button>
                            <button type="submit" class="btn btn-primary">Create Assessment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>

<!-- NEW: Include jQuery and SweetAlert2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function(){
    $('#generate_questions').click(function(e){
        e.preventDefault();
        var topic = $('#topic').val().trim();
        var numQuestions = parseInt($('#num_questions').val());
        var difficulty = $('#difficulty').val();

        if(numQuestions > 15) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Maximum 15 questions allowed.'
            });
            return;
        }

        if (!topic || !numQuestions || !difficulty) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Data',
                text: 'Please fill in the topic, number of questions, and difficulty.'
            });
            return;
        }

        // Build the proper prompt for Gemini API
        var prompt = "Generate " + numQuestions + " multiple choice questions on the topic '" + topic + "' with difficulty '" + difficulty + "'. Each question should be formatted as a JSON object with keys: 'question', 'a', 'b', 'c', 'd', 'ans'. Return the JSON output as an array of questions.";

        // Send AJAX request directly to the Google Gemini API
        $.ajax({
            url: 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=AIzaSyCF50DaE1rLpa-IjLugLTMDCpeD-d420ko',
            type: 'POST',
            contentType: 'application/json',
            headers: {
                "x-goog-api-key": "AIzaSyCF50DaE1rLpa-IjLugLTMDCpeD-d420ko"
            },
            data: JSON.stringify({
                contents: [
                    {
                        parts: [
                            { text: prompt }
                        ]
                    }
                ]
            }),
            success: function(response) {
                // Ensure the response is parsed as a JSON object
                if (typeof response === 'string') {
                    try {
                        response = JSON.parse(response);
                    } catch(err) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Parsing Error',
                            text: 'Could not parse response as JSON.'
                        });
                        return;
                    }
                }

                // Parse content from the response structure
                if (response.candidates && response.candidates.length > 0) {
                    var candidate = response.candidates[0];
                    if (candidate.content && candidate.content.parts && candidate.content.parts.length > 0) {
                        var textContent = candidate.content.parts[0].text;
                        // Remove markdown code block formatting if present (```json ... ```)
                        var match = textContent.match(/```(?:json)?\s*([\s\S]*?)\s*```/);
                        var jsonString = match ? match[1] : textContent;
                        try {
                            var parsedQuestions = JSON.parse(jsonString);
                            // Populate the textarea with the generated JSON (pretty printed)
                            $('#questions').val(JSON.stringify(parsedQuestions, null, 2));
                            Swal.fire({
                                icon: 'success',
                                title: 'Questions Generated',
                                text: 'Questions generated successfully using AI.'
                            });
                        } catch(e) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Parsing Error',
                                text: 'Unable to parse the generated questions JSON.'
                            });
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid Response',
                            text: 'No content parts found in the API response.'
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Response',
                        text: 'No candidates found in the API response.'
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'AJAX Error',
                    text: error
                });
            }
        });
    });
});
</script>