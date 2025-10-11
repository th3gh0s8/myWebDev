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
        $mail->Subject = 'Thank You for Your Purchase! - XPOWER Software';
        
        $htmlTemplate = '<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Thank You for Your Purchase - XPOWER Software!</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 600px;
      margin: 30px auto;
      background: #ffffff;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      overflow: hidden;
    }
    .header {
      background: linear-gradient(135deg, #007BFF, #0056b3);
      color: white;
      text-align: center;
      padding: 25px;
    }
    .header h1 {
      margin: 0;
      font-size: 28px;
    }
    .content {
      padding: 25px;
      text-align: left;
      color: #333;
    }
    .content h2 {
      color: #007BFF;
      font-size: 22px;
    }
    .offer {
      background: linear-gradient(135deg, #f8f9fa, #e9ecef);
      padding: 20px;
      margin: 20px 0;
      border-left: 4px solid #007BFF;
      border-radius: 6px;
    }
    .price-box {
      text-align: center;
      padding: 20px;
      background-color: #f0f8ff;
      border-radius: 8px;
      margin: 15px 0;
    }
    .original-price {
      text-decoration: line-through;
      color: #999;
      font-size: 18px;
    }
    .discounted-price {
      color: #28a745;
      font-size: 24px;
      font-weight: bold;
      margin: 10px 0;
    }
    .discount-badge {
      display: inline-block;
      background-color: #dc3545;
      color: white;
      padding: 5px 15px;
      border-radius: 20px;
      font-weight: bold;
      margin-top: 10px;
    }
    .footer {
      text-align: center;
      font-size: 13px;
      color: #666;
      padding: 20px;
      background-color: #f8f9fa;
      border-top: 1px solid #eee;
    }
    .footer a {
      color: #007BFF;
      text-decoration: none;
    }
    .footer a:hover {
      text-decoration: underline;
    }
    .cta-button {
      display: inline-block;
      background-color: #007BFF;
      color: white !important;
      padding: 12px 30px;
      text-decoration: none;
      border-radius: 4px;
      font-weight: bold;
      margin: 15px 0;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Thank You for Your Purchase!</h1>
    </div>
    <div class="content">
      <h2>Hi {{customer_name}},</h2>
      <p>We\'re excited to let you know that your order for <strong>XPOWER</strong> has been successfully completed!</p>

      <div class="price-box">
        <p class="original-price">Original Price: Rs 165,000</p>
        <p class="discounted-price">Discounted Price: Rs 132,000</p>
        <span class="discount-badge">20% OFF</span>
      </div>

      <div class="offer">
        <p><strong>Product:</strong> XPOWER Software Suite</p>
        <p><strong>Offer:</strong> 11.11 Mega Offer 🎉</p>
        <p><strong>Company:</strong> Powersoft Pvt Ltd</p>
      </div>

      <p>Your purchase details have been sent to your registered email. You can now enjoy the full power of XPOWER — a reliable, efficient, and high-performance solution by Powersoft Pvt Ltd.</p>

      <p>If you have any questions or need assistance, feel free to reach out to our support team at <a href="mailto:support@powersoftt.com">support@powersoftt.com</a>.</p>

      <p>Thank you for choosing <strong>Powersoft Pvt Ltd</strong>. We\'re thrilled to have you on board!</p>

      <p>— The Powersoft Team 💙</p>
    </div>
    <div class="footer">
      <p><strong>Powersoft Pvt Ltd</strong> | <a href="https://powersoftt.com">powersoftt.com</a></p>
      <p>© 2025 Powersoft Pvt Ltd. All rights reserved.</p>
    </div>
  </div>
</body>
</html>';
        
        // Replace placeholders with actual values
        $htmlContent = str_replace(
            ['{{customer_name}}', '{{product_name}}', '{{price}}'],
            [$name, $product_name, $price],
            $htmlTemplate
        );
        
        $mail->Body = $htmlContent;
        
        // Create plain text version for email clients that don't support HTML
        $mail->AltBody = "Thank You for Your Purchase! - XPOWER Software
Hi $name,

We're excited to let you know that your order for XPOWER has been successfully completed!

Product: XPOWER Software Suite
Original Price: Rs 165,000
Discounted Price: Rs 132,000 (20% OFF)
Offer: 11.11 Mega Offer 🎉
Company: Powersoft Pvt Ltd

Your purchase details have been sent to your registered email. You can now enjoy the full power of XPOWER — a reliable, efficient, and high-performance solution by Powersoft Pvt Ltd.

If you have any questions or need assistance, feel free to reach out to our support team at support@powersoftt.com.

Thank you for choosing Powersoft Pvt Ltd. We're thrilled to have you on board!

— The Powersoft Team 💙

Powersoft Pvt Ltd | powersoftt.com
© 2025 Powersoft Pvt Ltd. All rights reserved.";

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