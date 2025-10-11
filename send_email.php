<?php
function send_thank_you_email($to, $name, $product_name, $price, $registration_count = 1) {
    // Validate email address
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid email address provided: $to");
        return false;
    }
    
    error_log("Attempting to send email to: $to, Name: $name, Registration Count: $registration_count");

    // Determine discount based on registration count
    $discount_percentage = 0;
    if ($registration_count == 1) {
        $discount_percentage = 35;
    } elseif ($registration_count == 2) {
        $discount_percentage = 40;
    } elseif ($registration_count == 3) {
        $discount_percentage = 40;
    } elseif ($registration_count == 4) {
        $discount_percentage = 50;
    } elseif ($registration_count == 5) {
        $discount_percentage = 50;
    } else {
        $discount_percentage = 60; // 6 or more registrations
    }
    
    // Calculate discounted price based on discount percentage
    $original_price = 165000; // Original price in rupees
    $discounted_price = $original_price * (1 - $discount_percentage / 100);

    // Prepare the email content
    $subject = 'Thank You for Your Purchase! - XPOWER Software';
    
    // HTML email content
    $htmlContent = '<!DOCTYPE html>
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
      <h2>Hi '.$name.',</h2>
      <p>We\'re excited to let you know that your order for <strong>XPOWER</strong> has been successfully completed!</p>

      <div class="price-box">
        <p class="original-price">Original Price: Rs '.$original_price.'</p>
        <p class="discounted-price">Discounted Price: Rs '.number_format($discounted_price).'</p>
        <span class="discount-badge">'.$discount_percentage.'% OFF</span>
      </div>

      <div class="offer">
        <p><strong>Product:</strong> XPOWER Software Suite</p>
        <p><strong>Offer:</strong> 11.11 Mega Sale 🎉</p>
        <p><strong>Discount Applied:</strong> Based on your total of '.$registration_count.' registration(s)</p>
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

    // Plain text version
    $textContent = "Thank You for Your Purchase! - XPOWER Software
Hi $name,

We're excited to let you know that your order for XPOWER has been successfully completed!

Product: XPOWER Software Suite
Original Price: Rs ".$original_price."
Discounted Price: Rs ".number_format($discounted_price)." (".$discount_percentage."% OFF)
Discount Applied: Based on your total of ".$registration_count." registration(s)
Offer: 11.11 Mega Sale 🎉
Company: Powersoft Pvt Ltd

Your purchase details have been sent to your registered email. You can now enjoy the full power of XPOWER — a reliable, efficient, and high-performance solution by Powersoft Pvt Ltd.

If you have any questions or need assistance, feel free to reach out to our support team at support@powersoftt.com.

Thank you for choosing Powersoft Pvt Ltd. We're thrilled to have you on board!

— The Powersoft Team 💙

Powersoft Pvt Ltd | powersoftt.com
© 2025 Powersoft Pvt Ltd. All rights reserved.";

    // Set up headers for HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: noreply@go2webadmin.com' . "\r\n";
    
    error_log("Attempting to send email using PHP mail() function...");
    
    // Try to send the email using PHP's built-in mail() function
    $result = mail($to, $subject, $htmlContent, $headers);
    
    if ($result) {
        error_log("Email sent successfully to: $to");
        return true;
    } else {
        error_log("Email sending failed for: $to, Name: $name");
        return false;
    }
}
?>