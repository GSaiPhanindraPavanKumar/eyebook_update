<?php
// Function to handle predefined queries
function handlePredefinedQuery($query) {
    $query = strtolower(trim($query));
    
    // Common responses
    $responses = [
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
    
    // Check for keyword matches
    foreach ($responses as $key => $response) {
        if (strpos($query, $key) !== false) {
            return $response;
        }
    }
    
    // If no predefined response matches, use Gemini API
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
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';
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

    $response = json_decode($result, true);
    return $response['candidates'][0]['content']['parts'][0]['text'];
}

// Handle user input
if (php_sapi_name() !== 'cli' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_input = $_POST['user_input'];
    $response = askGemini("Provide a concise educational answer to this question: " . $user_input . "\n\n" .
                     "Guidelines:\n" .
                     "- Use HTML <ul> and <li> tags for any lists\n" .
                     "- Use <b> tags for main headings\n" .
                     "- Use <code> tags for code snippets\n" .
                     "- Use <em> for emphasis instead of italics\n" .
                     "- Keep formatting simple and clean\n" .
                     "- Focus on educational context\n" .
                     "- Maintain professional tone\n" .
                     "- Provide plain text if no formatting is needed" . "Ensure none of the guidelines are violated temperature:0.2" );
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
        body {
            background-color: #f0f2f5;
            font-family: 'Arial', sans-serif;
        }
        .chat-container {
            height: 400px;
            overflow-y: auto;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .message {
            padding: 12px 15px;
            margin: 10px;
            border-radius: 20px;
            max-width: 80%;
            font-size: 14px;
            line-height: 1.4;
        }
        .user-message {
            background-color: #e3f2fd;
            align-self: flex-end;
            margin-left: auto;
        }
        .guru-message {
            background-color: #f1f3f4;
            white-space: normal;
        }
        .guru-message br {
            display: block;
            margin: 5px 0;
        }
        .query-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin: 20px 0;
        }
        .query-btn {
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            background-color: #fff;
            color: #333;
            font-size: 14px;
            text-align: left;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .query-btn:hover {
            background-color: #e3f2fd;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .query-btn.other-btn {
            background-color: #007bff;
            color: white;
        }
        .query-btn.other-btn:hover {
            background-color: #0056b3;
        }
        .input-container {
            display: none;
            margin-top: 20px;
        }
        .input-group {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 25px;
            overflow: hidden;
        }
        .form-control {
            border: none;
            padding: 15px 20px;
        }
        .btn-primary {
            border-radius: 0 25px 25px 0;
            padding: 15px 30px;
        }
        .thinking {
            display: flex;
            align-items: center;
            margin: 10px;
            font-style: italic;
            color: #666;
        }
        .loader {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .feature-list p {
            margin-bottom: 15px;
            line-height: 1.6;
        }
        
        .feature-list strong {
            color: #007bff;
        }
        
        .response-text p {
            margin-bottom: 12px;
            line-height: 1.6;
        }
        
        .response-text p:last-child {
            margin-bottom: 0;
        }
        
        .response-text strong {
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container mt-5" style="max-width: 800px;">
        <h1 class="text-center mb-4">AskGuru - Educational Chatbot</h1>
        <div class="chat-container p-3 mb-3" id="chat-container">
            <div class="message guru-message">
                <strong>AskGuru:</strong> Welcome! How can I help you today? Please select a topic or choose "Other" for a custom question.
            </div>
        </div>

        <!-- Predefined Query Buttons -->
        <div class="query-buttons">
            <button class="query-btn" onclick="sendPredefinedQuery('forgot password')">Forgot Password</button>
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
        <div class="input-container" id="input-container">
            <div class="input-group mb-3">
                <button class="btn btn-secondary" onclick="showButtons()" style="border-radius: 25px 0 0 25px;">Back</button>
                <input type="text" id="user-input" class="form-control" placeholder="Type your question here...">
                <button class="btn btn-primary" onclick="sendMessage()">Send</button>
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
        $('#chat-container').append('<div class="message user-message"><strong>You:</strong> ' + query + '</div>');
        
        // Add thinking message with loader
        $('#chat-container').append('<div class="thinking"><div class="loader"></div>Guru is thinking...</div>');
        $('#chat-container').scrollTop($('#chat-container')[0].scrollHeight);

        $.post('askguru', {user_input: query}, function(data) {
            $('.thinking').remove();
            var response = JSON.parse(data);
            $('#chat-container').append('<div class="message guru-message"><strong>AskGuru:</strong> ' + response.response + '</div>');
            $('#chat-container').scrollTop($('#chat-container')[0].scrollHeight);
        });
    }

    function sendMessage() {
        var userInput = $('#user-input').val();
        if (userInput.trim() === '') return;

        $('#chat-container').append('<div class="message user-message"><strong>You:</strong> ' + userInput + '</div>');
        $('#user-input').val('');
        
        // Add thinking message with loader
        $('#chat-container').append('<div class="thinking"><div class="loader"></div>Guru is thinking...</div>');
        $('#chat-container').scrollTop($('#chat-container')[0].scrollHeight);

        $.post('askguru', {user_input: userInput}, function(data) {
            $('.thinking').remove();
            var response = JSON.parse(data);
            $('#chat-container').append('<div class="message guru-message"><strong>AskGuru:</strong> ' + response.response + '</div>');
            $('#chat-container').scrollTop($('#chat-container')[0].scrollHeight);
        });
    }

    $('#user-input').keypress(function(e) {
        if(e.which == 13) {
            sendMessage();
        }
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>