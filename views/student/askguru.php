<?php
// Function to handle predefined queries
function handlePredefinedQuery($query) {
    // Sanitize input more aggressively
    $query = strtolower(trim($query));
    // $query = preg_replace('/[^\w\s]/', '', $query); // Remove punctuation
    // $query = preg_replace('/\s+/', ' ', $query);    // Collapse multiple spaces
    // print_r($query);
    error_log(print_r($query, true));
    // Greetings that should match exactly
    $exactMatches = [
        'hi' => "<div class='response-text'><p>Hello! How can I assist you today?</p></div>",
        'hello' => "<div class='response-text'><p>Hello! How can I assist you today?</p></div>",
        'hey' => "<div class='response-text'><p>Hey there! What can I help you with?</p></div>",
        'good morning' => "<div class='response-text'><p>Good morning! How may I help you today?</p></div>",
        'good afternoon' => "<div class='response-text'><p>Good afternoon! What can I assist you with?</p></div>", 
        'good evening' => "<div class='response-text'><p>Good evening! How can I be of assistance?</p></div>",
        'howdy' => "<div class='response-text'><p>Howdy! What brings you here today?</p></div>",
        'greetings' => "<div class='response-text'><p>Greetings! How may I help you?</p></div>",
        "what's up" => "<div class='response-text'><p>What's up? How can I help you today?</p></div>",
        'yo' => "<div class='response-text'><p>Hello! What do you need help with?</p></div>",
        'sup' => "<div class='response-text'><p>Hello! How can I assist you?</p></div>",
        'hiya' => "<div class='response-text'><p>Hiya! What can I do for you?</p></div>",
        'hey there' => "<div class='response-text'><p>Hey there! How's it going?</p></div>",
        
    ];

    // Check for exact matches first (greetings)
    if (array_key_exists($query, $exactMatches)) {
        return $exactMatches[$query];
    }

    // Other responses that can use partial matching
    $partialMatches = [
        'forgot password' => "<div class='response-text'>
            <p>To reset your password:</p>
            <p>1. Visit the login page<br>
               2. Click 'Forgot Password' link<br>
               3. Enter your registered email address<br>
               4. Check your email for reset instructions<br>
               5. Follow the link to create a new password</p>
            <p>If you don't receive the email, check your spam folder or contact support@eyebook.com</p>
            </div>",
        
        'edit profile' => "<div class='response-text'>
            <p>To edit your profile:</p>
            <p>1. Log into your account<br>
               2. Click on your profile picture/icon<br>
               3. Select 'Profile Settings'<br>
               4. Update your information<br>
               5. Click 'Save Changes'</p>
            </div>",
        
        'upload files' => "<div class='response-text'>
            <p>If you're having trouble uploading files, please:</p>
            <p>1. Check your internet connection<br>
               2. Ensure the file size is within limits<br>
               3. Try again in a few minutes<br>
               4. If the problem persists, contact technical support at support@eyebook.com</p>
            </div>",
        
        'download files' => "<div class='response-text'>
            <p>If you're having trouble downloading files, please:</p>
            <p>1. Check your internet connection<br>
               2. Clear your browser cache<br>
               3. Try again in a few minutes<br>
               4. If the problem persists, contact technical support at support@eyebook.com</p>
            </div>",
        
        'tech support' => "<div class='response-text'>
            <p>For technical support, please contact:</p>
            <p>Email: support@eyebook.com<br>
               Phone: 1-800-LMS-HELP<br>
               Available Monday-Friday, 9 AM - 6 PM IST</p>
            </div>",
        
        'course benefits' => "<div class='response-text'>
            <p><strong>Our courses offer:</strong></p>
            <p>• Industry-recognized certifications<br>
               • Hands-on practical experience<br>
               • Expert faculty guidance<br>
               • Flexible learning schedule<br>
               • Career placement assistance</p>
            <p>For specific course details, please check the course description page.</p>
            </div>",
        
        'talk to support' => "<div class='response-text'>
            <p>To speak with a support executive:</p>
            <p>1. Call: 1-800-LMS-HELP<br>
               2. Email: support@eyebook.com<br>
               3. Create a support ticket through your dashboard</p>
            <p>Support hours: Monday-Friday, 9 AM - 6 PM IST</p>
            </div>",

        // Keep the existing LMS features response as is
        'lms features' => "<div class='feature-list'>
            <p><strong>Our LMS offers comprehensive features including:</strong></p>
            <p><strong>1. Points Accumulation System</strong><br>
               • Competition and coursework points<br>
               • Universal points tracking across activities<br>
               • Rewards for discussions and participation</p>
            <p><strong>2. Virtual Learning Environment</strong><br>
               • Integrated Virtual Classrooms<br>
               • Interactive Virtual Labs<br>
               • Real-time collaboration tools</p>
            <p><strong>3. Automated Learning Support</strong><br>
               • Live session transcription<br>
               • Archived session recordings<br>
               • Searchable content repository</p>
            <p><strong>4. Quality Assurance</strong><br>
               • Faculty certification tracking<br>
               • Comprehensive plagiarism detection<br>
               • Assignment and exam monitoring</p>
            <p><strong>5. Student Support Services</strong><br>
               • AI-powered chatbot assistance<br>
               • Ticket support system<br>
               • Peer learning networks</p>
            <p><strong>6. Advanced Learning Tools</strong><br>
               • Cross-platform virtual labs<br>
               • Unified communication archive<br>
               • Interactive learning materials</p>
            </div>",
    ];
    
    // Check for partial matches for other queries
    foreach ($partialMatches as $key => $response) {
        if (strpos($query, $key) !== false) {
            return $response;
        }
    }
    
    // If no matches found, return null to proceed with Gemini API
    return null;
}

// Modified askGemini function
function askGemini($prompt) {
    // First check for predefined responses
    $predefinedResponse = handlePredefinedQuery($prompt);
    if ($predefinedResponse !== null) {
        return $predefinedResponse;
    }
    
    // If no predefined response, proceed with API call
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-pro-exp-02-05:generateContent';
    $api_key = 'AIzaSyCF50DaE1rLpa-IjLugLTMDCpeD-d420ko';

    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ]
    ];

    $options = [
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                "x-goog-api-key: $api_key"
            ],
            'content' => json_encode($data)
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        return "Sorry, I couldn't process your request.";
    }
    error_log(print_r($result, true));

    $response = json_decode($result, true);
    error_log(print_r($response, true));
    $text = $response['candidates'][0]['content']['parts'][0]['text'];
    $text = str_replace('```html', '', $text);
    $text = str_replace('```', '', $text);
    return $text;
}

// Handle user input
if (php_sapi_name() !== 'cli' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_input = $_POST['user_input'];
    
    // First check for predefined responses
    $predefinedResponse = handlePredefinedQuery($user_input);
    if ($predefinedResponse !== null) {
        echo $predefinedResponse; // Return the HTML directly
        exit;
    }
    
    // If no predefined response, proceed with Gemini API
    $response = askGemini("Provide a concise educational answer without repeating the question: " . $user_input . "\n\n" .
                     "Guidelines:\n" .
                     "- Start with the answer directly, do not repeat the question\n" .
                     "- Use HTML <ul> and <li> tags for any lists\n" .
                     "- Use <b> tags for main headings\n" .
                     "- Use <code> tags for code snippets\n" .
                     "- Use <em> for emphasis instead of italics\n" .
                     "- Keep formatting simple and clean\n" .
                     "- Focus on educational context\n" .
                     "- Maintain professional tone\n" .
                     "- Provide plain text if no formatting is needed\n" .
                     "Ensure none of the guidelines are violated. The response should be in HTML format.");
    
    // Return the formatted response
    echo json_encode(['response' => $response]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AskGuru - Educational Chatbot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --primary-color: #4B49AC;
            --primary-hover: #3f3e91;
            --primary-light: #4B49AC20;
            --primary-border: #4B49AC30;
            --bg-color: #f0f2f5;
            --chat-bg: white;
            --text-color: #2D3748;
            --text-muted: #718096;
            --border-color: #E2E8F0;
            --btn-bg: white;
            --input-bg: white;
            --loader-bg: #E2E8F0;
            --code-bg: #F7FAFC;
            --hover-bg: rgba(0, 0, 0, 0.05);
        }

        .dark {
            --bg-color: #1A202C;
            --chat-bg: #2D3748;
            --text-color: #E2E8F0;
            --text-muted: #A0AEC0;
            --border-color: #4A5568;
            --btn-bg: #2D3748;
            --input-bg: #2D3748;
            --loader-bg: #4A5568;
            --code-bg: #2D3748;
            --hover-bg: rgba(255, 255, 255, 0.05);
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .chat-container {
            height: 500px;
            overflow-y: auto;
            background-color: var(--chat-bg);
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .message {
            padding: 1rem 1.25rem;
            margin: 0.75rem;
            border-radius: 1rem;
            max-width: 85%;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .user-message {
            background-color: var(--primary-light);
            color: var(--text-color);
            margin-left: auto;
            border: 1px solid var(--primary-border);
        }

        .guru-message {
            background-color: var(--message-bg);
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }

        .query-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .query-btn {
            padding: 1rem 1.25rem;
            border: none;
            border-radius: 0.75rem;
            background-color: var(--btn-bg);
            color: var(--text-color);
            font-size: 0.875rem;
            text-align: left;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
        }

        .query-btn:hover {
            background-color: var(--primary-light);
            transform: translateY(-2px);
            border-color: var(--primary-color);
        }

        .query-btn.other-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }

        .query-btn.other-btn:hover {
            background-color: var(--primary-hover);
        }

        .input-container {
            margin-top: 1.5rem;
        }

        .input-group {
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 0.75rem;
            overflow: hidden;
            background-color: var(--input-bg);
            border: 1px solid var(--border-color);
        }

        .form-control {
            border: none;
            padding: 1rem 1.25rem;
            background-color: transparent;
            color: var(--text-color);
        }

        .form-control:focus {
            outline: none;
            box-shadow: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 1rem 1.5rem;
            color: white;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .thinking {
            display: flex;
            align-items: center;
            margin: 0.75rem;
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .loader {
            border: 2px solid var(--loader-bg);
            border-top: 2px solid var(--primary-color);
            border-radius: 50%;
            width: 1.25rem;
            height: 1.25rem;
            margin-right: 0.75rem;
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            :root {
                --bg-color: #1A202C;
                --chat-bg: #2D3748;
                --text-color: #E2E8F0;
                --border-color: #4A5568;
                --primary-light: #4B49AC40;
                --message-bg: #2D3748;
                --btn-bg: #2D3748;
                --input-bg: #2D3748;
                --loader-bg: #4A5568;
                --text-muted: #A0AEC0;
            }
        }

        /* Feature list and response text styles */
        .feature-list, .response-text {
            line-height: 1.6;
        }

        .feature-list p, .response-text p {
            margin-bottom: 1rem;
        }

        .feature-list strong, .response-text strong {
            color: var(--primary-color);
            font-weight: 600;
        }

        /* Code block styling */
        code {
            background-color: var(--code-bg);
            padding: 0.2em 0.4em;
            border-radius: 0.25rem;
            font-size: 0.875em;
            color: var(--primary-color);
        }

        /* List styling */
        ul, ol {
            margin: 1rem 0;
            padding-left: 1.5rem;
        }

        li {
            margin-bottom: 0.5rem;
        }

        /* Theme toggle button styles */
        .theme-switch button {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--btn-bg);
            border: 1px solid var(--border-color);
            color: var(--text-color);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .theme-switch button:hover {
            transform: rotate(12deg);
            background: var(--hover-bg);
        }

        .theme-switch button i {
            font-size: 1.25rem;
        }

        /* Message colors */
        .message.guru-message {
            background-color: var(--message-bg);
            border: 1px solid var(--border-color);
        }

        .message.user-message {
            background-color: var(--primary-light);
            border: 1px solid var(--primary-border);
        }

        /* Text colors */
        h1.text-gray-900.dark\:text-white {
            color: var(--text-color);
        }

        /* Input field colors */
        .input-group {
            background-color: var(--input-bg);
            border: 1px solid var(--border-color);
        }

        .input-group .form-control {
            color: var(--text-color);
        }

        .input-group .form-control::placeholder {
            color: var(--text-muted);
        }

        /* Query buttons */
        .query-btn {
            background-color: var(--btn-bg);
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }

        .query-btn:hover {
            background-color: var(--primary-light);
            border-color: var(--primary-color);
        }

        .query-btn.other-btn {
            background-color: var(--primary-color);
            color: white;
        }

        .query-btn.other-btn:hover {
            background-color: var(--primary-hover);
        }

        /* Thinking animation */
        .thinking {
            color: var(--text-muted);
        }

        .loader {
            border-color: var(--loader-bg);
            border-top-color: var(--primary-color);
        }

        /* Code blocks */
        code {
            background-color: var(--code-bg);
            color: var(--primary-color);
        }

        /* Transitions */
        body, .chat-container, .message, .query-btn, .input-group, .theme-switch button {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="theme-switch fixed top-4 right-4 z-50">
        <button id="themeToggle" class="rounded-full">
            <i id="darkIcon" class="fas fa-moon"></i>
            <i id="lightIcon" class="fas fa-sun hidden"></i>
        </button>
    </div>
    <div class="container mt-5" style="max-width: 800px;">
        <h1 class="text-center mb-4 text-2xl font-semibold text-gray-900 dark:text-white">
            AskGuru - Educational Chatbot
        </h1>
        <div class="chat-container p-4 mb-4" id="chat-container">
            <div class="message guru-message">
                <strong class="text-primary">AskGuru:</strong> 
                Welcome! How can I help you today? Please select a topic or choose "Other" for a custom question.
            </div>
        </div>

        <!-- Predefined Query Buttons -->
        <div class="query-buttons">
            <button class="query-btn hover:shadow-md" onclick="sendPredefinedQuery('forgot password')">
                <i class="fas fa-key mr-2 text-primary"></i>Forgot Password
            </button>
            <button class="query-btn" onclick="sendPredefinedQuery('edit profile')">Edit Profile</button>
            <button class="query-btn" onclick="sendPredefinedQuery('upload files')">Upload Files Help</button>
            <button class="query-btn" onclick="sendPredefinedQuery('download files')">Download Files Help</button>
            <button class="query-btn" onclick="sendPredefinedQuery('lms features')">LMS Features</button>
            <button class="query-btn" onclick="sendPredefinedQuery('tech support')">Technical Support</button>
            <button class="query-btn" onclick="sendPredefinedQuery('course benefits')">Course Benefits</button>
            <button class="query-btn" onclick="sendPredefinedQuery('talk to support')">Talk to Support</button>
            <button class="query-btn other-btn" onclick="toggleCustomInput()">Other Question</button>
        </div>

        <!-- Custom Input Field (Hidden by default) -->
        <div class="input-container hidden" id="input-container">
            <div class="input-group">
                <button class="btn text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 px-4" onclick="showButtons()">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <input type="text" id="user-input" class="form-control flex-grow text-gray-800 dark:text-gray-200 placeholder-gray-500 dark:placeholder-gray-400" 
                       placeholder="Type your question here...">
                <button class="btn btn-primary px-6" onclick="sendMessage()">
                    <i class="fas fa-paper-plane mr-2"></i>Send
                </button>
            </div>
        </div>
    </div>

    <script>
    function toggleCustomInput() {
        const inputContainer = document.getElementById('input-container');
        const queryButtons = document.querySelector('.query-buttons');
        
        // Set initial display state if not set
        if (inputContainer.style.display === '') {
            inputContainer.style.display = 'none';
        }
        
        if (inputContainer.style.display === 'none') {
            inputContainer.style.display = 'block';
            queryButtons.style.display = 'none';
            document.getElementById('user-input').focus();
        } else {
            inputContainer.style.display = 'none';
            queryButtons.style.display = 'grid';
        }
    }

    // Add a function to go back to buttons
    function showButtons() {
        const inputContainer = document.getElementById('input-container');
        const queryButtons = document.querySelector('.query-buttons');
        inputContainer.style.display = 'none';
        queryButtons.style.display = 'grid';
    }

    function sendPredefinedQuery(query) {
        // Add user message to chat with proper text coloring
        $('#chat-container').append(`
            <div class="message user-message">
                <strong class="text-primary">You:</strong> ${query}
            </div>
        `);
        
        // Add thinking message with proper coloring
        $('#chat-container').append(`
            <div class="thinking">
                <div class="loader"></div>
                <span class="text-muted">Guru is thinking...</span>
            </div>
        `);
        $('#chat-container').scrollTop($('#chat-container')[0].scrollHeight);

        // Send the request
        $.ajax({
            url: 'askguru',
            method: 'POST',
            data: { user_input: query },
            success: function(data) {
                $('.thinking').remove();
                try {
                    var response = JSON.parse(data);
                    $('#chat-container').append(`
                        <div class="message guru-message">
                            <strong class="text-primary">AskGuru:</strong> ${response.response}
                        </div>
                    `);
                } catch(e) {
                    $('#chat-container').append(`
                        <div class="message guru-message">
                            <strong class="text-primary">AskGuru:</strong> ${data}
                        </div>
                    `);
                }
                $('#chat-container').scrollTop($('#chat-container')[0].scrollHeight);
            },
            error: function() {
                $('.thinking').remove();
                $('#chat-container').append('<div class="message guru-message"><strong>AskGuru:</strong> Sorry, there was an error processing your request.</div>');
                $('#chat-container').scrollTop($('#chat-container')[0].scrollHeight);
            }
        });
    }

    function sendMessage() {
        var userInput = $('#user-input').val();
        if (userInput.trim() === '') return;

        // Add user message to chat
        $('#chat-container').append('<div class="message user-message"><strong>You:</strong> ' + userInput + '</div>');
        $('#user-input').val('');
        
        // Add thinking message with loader
        $('#chat-container').append('<div class="thinking"><div class="loader"></div>Guru is thinking...</div>');
        $('#chat-container').scrollTop($('#chat-container')[0].scrollHeight);

        $.ajax({
            url: 'askguru',
            method: 'POST',
            data: { user_input: userInput },
            success: function(data) {
                $('.thinking').remove();
                try {
                    var response = JSON.parse(data);
                    $('#chat-container').append('<div class="message guru-message"><strong>AskGuru:</strong> ' + response.response + '</div>');
                } catch(e) {
                    // If response is already HTML
                    $('#chat-container').append('<div class="message guru-message"><strong>AskGuru:</strong> ' + data + '</div>');
                }
                $('#chat-container').scrollTop($('#chat-container')[0].scrollHeight);
            },
            error: function() {
                $('.thinking').remove();
                $('#chat-container').append('<div class="message guru-message"><strong>AskGuru:</strong> Sorry, there was an error processing your request.</div>');
                $('#chat-container').scrollTop($('#chat-container')[0].scrollHeight);
            }
        });
    }

    $('#user-input').keypress(function(e) {
        if(e.which == 13) {
            sendMessage();
        }
    });

    // Theme switching functionality
    document.addEventListener('DOMContentLoaded', () => {
        const themeToggle = document.getElementById('themeToggle');
        const lightIcon = document.getElementById('lightIcon');
        const darkIcon = document.getElementById('darkIcon');
        
        // Check for saved theme preference or default to system preference
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            lightIcon.classList.remove('hidden');
            darkIcon.classList.add('hidden');
        }

        function updateThemeIcons() {
            if (document.documentElement.classList.contains('dark')) {
                lightIcon.classList.remove('hidden');
                darkIcon.classList.add('hidden');
            } else {
                lightIcon.classList.add('hidden');
                darkIcon.classList.remove('hidden');
            }
        }

        themeToggle.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
            updateThemeIcons();
        });
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>