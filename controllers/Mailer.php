<?php
namespace Models;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->setup();
    }

    private function setup() {
        try {
            //Server settings
            $this->mail->isSMTP();
            $this->mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
            $this->mail->SMTPAuth = true;
            $this->mail->Username = 'eyebook.contact1234@gmail.com'; // SMTP username
            $this->mail->Password = 'nxsopdrffjidxvle'; // SMTP password
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port = 587;
            $this->mail->setFrom('eyebook.contact1234@gmail.com', 'EyeBook');
        } catch (Exception $e) {
            throw new Exception("Mailer setup failed: {$this->mail->ErrorInfo}");
        }
    }

    public function sendMail($to, $subject, $body) {
        try {
            //Recipients
            $this->mail->addAddress($to);

            // Content
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;

            $this->mail->send();
        } catch (Exception $e) {
            throw new Exception("Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
        }
    }

    public function sendForgotPasswordMail($to, $resetLink) {
        $subject = 'Password Reset Request';
        $body = "Click <a href='$resetLink'>here</a> to reset your password.";
        $this->sendMail($to, $subject, $body);
    }

    public function sendUpdateMail($to, $updateMessage) {
        $subject = 'Update Notification';
        $body = $updateMessage;
        $this->sendMail($to, $subject, $body);
    }
} 