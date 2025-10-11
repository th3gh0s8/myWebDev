<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function send_thank_you_email($to, $name, $product_name, $price) {
    // Validate email address
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid email address provided: $to");
        return false;
    }

    error_log("Attempting to send email to: $to, Name: $name");

    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP(); // Enable SMTP
        error_log("SMTP enabled");
        $mail->Host       = 'smtp.gmail.com'; // Gmail SMTP server
        $mail->SMTPAuth   = true;
        error_log("SMTP auth enabled");
        $mail->Username   = 'chamudithapasindu54@gmail.com'; // Your Gmail address
        $mail->Password   = 'txik ulgz avos soao'; // Your Gmail app password (use app password, not account password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        error_log("SMTP settings configured");

        //Recipients
        $mail->setFrom('chamudithapasindu54@gmail.com', '11.11 Mega Sale'); // Has to be same as Username for Gmail
        $mail->addAddress($to, $name);
        error_log("Recipients set");

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Thank you for your purchase!';
        $mail->Body    = "<h1>Thank you for your purchase, $name!</h1>
                          <p>You have purchased the following product:</p>
                          <p><b>Product:</b> $product_name</p>
                          <p><b>Price:</b> $$price</p>
                          <p>We will contact you shortly with more details.</p>";
        $mail->AltBody = "Thank you for your purchase, $name!\n\nYou have purchased the following product:\nProduct: $product_name\nPrice: $$price\n\nWe will contact you shortly with more details.";

        error_log("Attempting to send email...");
        $mail->send();
        error_log("Email sent successfully to: $to");
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        error_log("Email sending failed for: $to, Name: $name");
        return false;
    }
}
?>
