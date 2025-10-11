<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function send_thank_you_email($to, $name, $product_name, $price) {
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.example.com'; // Set the SMTP server to send through
        $mail->SMTPAuth   = true;
        $mail->Username   = 'user@example.com'; // SMTP username
        $mail->Password   = 'secret'; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        //Recipients
        $mail->setFrom('from@example.com', 'Mailer');
        $mail->addAddress($to, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Thank you for your purchase!';
        $mail->Body    = "<h1>Thank you for your purchase, $name!</h1>
                          <p>You have purchased the following product:</p>
                          <p><b>Product:</b> $product_name</p>
                          <p><b>Price:</b> $$price</p>
                          <p>We will contact you shortly with more details.</p>";
        $mail->AltBody = "Thank you for your purchase, $name!\n\nYou have purchased the following product:\nProduct: $product_name\nPrice: $$price\n\nWe will contact you shortly with more details.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
