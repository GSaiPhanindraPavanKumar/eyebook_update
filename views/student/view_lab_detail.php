<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="font-weight-bold">Lab Details</h3>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($lab['title']); ?></h5>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($lab['description']); ?></p>
                        <p><strong>Input:</strong> <?php echo htmlspecialchars($lab['input']); ?></p>
                        <p><strong>Output:</strong> <?php echo htmlspecialchars($lab['output']); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Code Compiler</h5>
                        <form id="compiler-form">
                            <div class="form-group">
                                <label for="language">Select Language:</label>
                                <select id="language" class="form-control">
                                    <option value="python">Python</option>
                                    <option value="javascript">JavaScript</option>
                                    <option value="java">Java</option>
                                    <option value="c">C</option>
                                    <option value="cpp">C++</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="code">Write your code here:</label>
                                <textarea class="form-control" id="code" name="code" rows="10"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="input">Input (optional):</label>
                                <textarea class="form-control" id="input" name="input" rows="3"><?php echo htmlspecialchars($lab['input']); ?></textarea>
                            </div>
                            <button type="button" class="btn btn-primary" id="run-code">Run Code</button>
                            <button type="button" class="btn btn-success" id="submit-code">Submit Code</button>
                        </form>
                        <div id="output" class="mt-3">
                            <pre id="output-content">Your output will appear here...</pre>
                            <input type="text" id="program-input" placeholder="Press Enter to submit input..." style="display: none;" />
                        </div>
                        <div id="message" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<!-- Include Bootstrap CSS -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

<script>
document.getElementById('run-code').addEventListener('click', async () => {
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
            if (result.stdout.trim() === '<?php echo addslashes($lab['output']); ?>') {
                messageDiv.textContent = 'Success: All test cases passed!';
                messageDiv.classList.add('alert', 'alert-success');

                // Update the database to indicate that all test cases are passed
                const updateResponse = await fetch('/student/update_lab_submission', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        lab_id: <?php echo $lab['id']; ?>,
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
</script>