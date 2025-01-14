<?php
// Function to interact with Gemini API
function askGemini($prompt) {
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';
    $api_key = 'AIzaSyCF50DaE1rLpa-IjLugLTMDCpeD-d420ko'; // Replace with your actual Gemini API key

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
    $response = askGemini("Answer this educational question concisely: " . $user_input);
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
    </style>
</head>
<body>
    <div class="container mt-5" style="max-width: 600px;">
        <h1 class="text-center mb-4">AskGuru - Educational Chatbot</h1>
        <div class="chat-container p-3 mb-3" id="chat-container">
            <div class="message guru-message">
                <strong>AskGuru:</strong> Welcome! I'm here to help with your educational questions. Feel free to ask about topics like science, history, mathematics, literature, or any other subject you're curious about!
            </div>
        </div>
        <div class="input-group mb-3">
            <input type="text" id="user-input" class="form-control" placeholder="Ask an educational question...">
            <button class="btn btn-primary" onclick="sendMessage()">Send</button>
        </div>
    </div>

    <script>
    function sendMessage() {
        var userInput = $('#user-input').val();
        if (userInput.trim() === '') return;

        $('#chat-container').append('<div class="message user-message"><strong>You:</strong> ' + userInput + '</div>');
        $('#user-input').val('');
        
        // Add thinking message with loader
        $('#chat-container').append('<div class="thinking"><div class="loader"></div>Guru is thinking...</div>');
        $('#chat-container').scrollTop($('#chat-container')[0].scrollHeight);

        $.post('askguru.php', {user_input: userInput}, function(data) {
            $('.thinking').remove(); // Remove the thinking message
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
