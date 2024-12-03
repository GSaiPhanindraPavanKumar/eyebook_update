<?php

use Models\Database;
use Models\Mailer;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $token = md5(uniqid(rand(), true));
    $resetLink = "https://eyebook.phemesoft.com/reset_password?token=" . $token;

    // Database connection
    $database = new Database();
    $conn = $database->getConnection();

    // Check email in different tables
    $tables = ['students', 'faculty', 'admins', 'spocs'];
    $found = false;

    foreach ($tables as $table) {
        $sql = "SELECT email FROM $table WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $found = true;
            break;
        }
    }

    if ($found) {
        // Store the token in the database
        $sql = "INSERT INTO password_resets (email, token) VALUES (:email, :token)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        // Send reset link to email using Mailer
        $subject = "Password Reset Request";
        $message = "
            <html>
            <head>
                <title>Password Reset Request</title>
            </head>
            <body>
                <p>Dear User,</p>
                <p>We received a request to reset your password for your account associated with this email address. Click the link below to reset your password:</p>
                <p><a href='$resetLink'>Reset Password</a></p>
                <p>If you did not request a password reset, please ignore this email or contact support if you have questions.</p>
                <p>Thank you,<br>The EyeBook Team</p>
            </body>
            </html>
        ";

        $mailer = new Mailer();
        try {
            $mailer->sendMail($email, $subject, $message);
            echo "<div class='alert alert-success'>A reset link has been sent to your email address.</div>";
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Failed to send reset link: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Email address not found.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Forgot Password</h3>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="form-group">
                                <label for="email">Email:</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>