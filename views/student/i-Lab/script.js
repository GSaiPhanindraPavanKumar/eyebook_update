document.getElementById('compiler-form').addEventListener('submit', async (event) => {
    event.preventDefault();

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

    // Reset output
    outputDiv.textContent = 'Running your code...';
    outputDiv.style.border = '2px solid transparent';

    if (!code.trim()) {
        outputDiv.textContent = 'Please enter some code to run.';
        outputDiv.style.border = '2px solid red';
        return;
    }

    if (!languageIdMap[language]) {
        outputDiv.textContent = `Unsupported language: ${language}`;
        outputDiv.style.border = '2px solid red';
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
            outputDiv.textContent = result.stdout || 'No output.';
        } else {
            outputDiv.textContent = result.stderr || result.compile_output || 'An error occurred.';
            outputDiv.style.border = '2px solid red';
        }
    } catch (error) {
        outputDiv.textContent = `Error: ${error.message}`;
        outputDiv.style.border = '2px solid red';
    }
});
