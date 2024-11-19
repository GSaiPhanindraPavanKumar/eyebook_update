<?php
namespace Models;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mail;
    private $logFile = 'mailer.log';

    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->setup();
    }

    private function log($message) {
        $logMessage = date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL;
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
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
            $this->log('Mailer setup successful');
        } catch (Exception $e) {
            $this->log('Mailer setup failed: ' . $this->mail->ErrorInfo);
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
            $this->log('Email sent to ' . $to . ' with subject ' . $subject);
        } catch (Exception $e) {
            $this->log('Email sending failed: ' . $this->mail->ErrorInfo);
            throw new Exception("Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
        }
    }
}