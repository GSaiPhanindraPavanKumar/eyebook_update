<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Expired</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f5f5f5;
        }
        .timeout-container {
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        h1 {
            color: #e74c3c;
            margin-bottom: 20px;
        }
        .message {
            color: #666;
            margin-bottom: 30px;
        }
        .login-button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
            font-weight: bold;
        }
        .login-button:hover {
            background-color: #2980b9;
        }
        .icon {
            font-size: 48px;
            color: #e74c3c;
            margin-bottom: 20px;
        }
        .timer {
            font-size: 20px;
            color: #666;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="timeout-container">
        <div class="icon">⏰</div>
        <h1>Unauthorized Access</h1>
        <div class="message">
            <p>You are not authorized to access this page.</p>
            <p>Please log in to continue.</p>
        </div>
        <div class="timer">Redirecting to login page in <span id="countdown">5</span> seconds...</div>
        <a href="/login" class="login-button">Login Now</a>
    </div>

    <script>
        let timeLeft = 5;
        const countdownElement = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            timeLeft--;
            countdownElement.textContent = timeLeft;
            
            if (timeLeft <= 0) {
                clearInterval(timer);
                window.location.href = '/login';
            }
        }, 1000);
    </script>
</body>
</html> 