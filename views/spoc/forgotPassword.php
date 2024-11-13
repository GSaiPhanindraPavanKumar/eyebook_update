<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <style>
        /* Custom styles */
        body {
            background-image: url('your-cartoon-image.jpg'); /* Replace 'your-cartoon-image.jpg' with the path to your cartoon image */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
        }

        .center-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }

        .form-container {
            background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent white background */
            padding: 20px;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- Site favicon -->
	<link rel="apple-touch-icon" sizes="180x180" href="assets/images/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="assets/images/android-chrome-512x512.png">
	<link rel="icon" type="image/png" sizes="16x16" href="assets/images/android-chrome-192x192.png">

	<!-- Mobile Specific Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<!-- Google Font -->
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
	<!-- CSS -->
	<link rel="stylesheet" type="text/css" href="assets/styles/core.css">
	<link rel="stylesheet" type="text/css" href="assets/styles/icon-font.min.css">
	<link rel="stylesheet" type="text/css" href="assets/styles/style.css">
    <style>
        /* Custom styles */
        body {
            background-image: url('assets/images/login.jpg'); /* Replace 'your-cartoon-image.jpg' with the path to your cartoon image */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card-form {
            max-width: 400px;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent white background */
            border-radius: 15px;
            padding: 20px;
        }
    </style>
</head>
<body>

<div class="card card-form">
    <div class="card-body">
        <h1 class="card-title text-center mb-2">Reset Password</h1>

        <form action="forgot_password_process.php" method="post">
            <div class="mb-3">
                <input type="email" name="email" placeholder="Email address" class="form-control">
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-primary" name="reset">Reset</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>