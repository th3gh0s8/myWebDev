<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XPOWER - Your Trusted Technology Partner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@latest/build/css/intlTelInput.min.css">
    <link rel="icon" href="images/metaXlogo.png" type="image/png">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top navbar-transparent">
        <div class="container-fluid">
            <!-- Left: Company Logo -->
            <a class="navbar-brand" href="#">
                <img src="images/logo.png" alt="POWERSOFT Logo" class="navbar-logo">
            </a>

            <!-- Center: Product Logo (visible on larger screens) -->
            <img src="images/xLogo.png" alt="XPOWER Logo" class="navbar-center-logo d-none d-lg-block">


        </div>
    </nav>
    <script src="main.js"></script>
    
    <!-- WhatsApp Chat Button -->
    <a href="https://wa.me/94775656798" target="_blank" class="whatsapp-float" title="Chat with us on WhatsApp">
        <i class="bi bi-whatsapp"></i>
    </a>
    
    <!-- WhatsApp Chat Button CSS -->
    <style>
        .whatsapp-float {
            position: fixed;
            width: 60px;
            height: 60px;
            bottom: 40px;
            right: 40px;
            background-color: #25d366;
            color: white;
            border-radius: 50px;
            text-align: center;
            font-size: 30px;
            box-shadow: 2px 2px 3px #999;
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .whatsapp-float:hover {
            background-color: #128C7E;
            transform: scale(1.1);
        }

        .whatsapp-float i {
            font-size: 30px;
            color: white;
        }
    </style>

<?php
require_once '../connection_log.php'; // Include the database connection

// Process form if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrations'])) {
    // Validate and sanitize input data
    $registrations = [];
    if (isset($_POST['registrations']) && is_array($_POST['registrations'])) {
        foreach ($_POST['registrations'] as $index => $registration) {
            if (is_array($registration)) {
                $registrations[$index] = [
                    'name' => isset($registration['name']) ? htmlspecialchars(trim($registration['name']), ENT_QUOTES, 'UTF-8') : '',
                    'email' => isset($registration['email']) ? filter_var(trim($registration['email']), FILTER_SANITIZE_EMAIL) : '',
                    'mobile' => isset($registration['mobile']) ? htmlspecialchars(trim($registration['mobile']), ENT_QUOTES, 'UTF-8') : '',
                    'company_name' => isset($registration['company_name']) ? htmlspecialchars(trim($registration['company_name']), ENT_QUOTES, 'UTF-8') : '',
                    'company_address' => isset($registration['company_address']) ? htmlspecialchars(trim($registration['company_address']), ENT_QUOTES, 'UTF-8') : ''
                ];
            }
        }
    }

    // Validate email format and required fields for each registration
    foreach ($registrations as $key => $reg) {
        // Check if required fields are present
        if (empty($reg['name']) || empty($reg['email'])) {
            http_response_code(400); // Bad Request
            die("Name and email are required for registration " . ($key + 1) . ". Please ensure all required fields are completed.");
        }
        
        // Validate email format
        if (!empty($reg['email']) && !filter_var($reg['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400); // Bad Request
            die("The email format is invalid for registration " . ($key + 1) . ". Please enter a valid email address.");
        }
    }

    // Insert registrations only into the xuser table
    $successfulInserts = 0;
    $duplicates = [];
    try {
        // The connection $xdatalogin is from connection_log.php
        if ($xdatalogin) {
            // Insert into xpower_buy_users table with default values for new columns
            $stmt = $xdatalogin->prepare("INSERT INTO xpower_buy_users (fullName, email_address, mobile_number, com_name, com_address, is_paid, ip_address, user_country, rDateTime) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt === false) {
                throw new Exception("Prepare failed for xpower_buy_users table: " . $xdatalogin->error);
            }
            foreach ($registrations as $reg) {
                // Only insert if name and email are provided
                if (!empty($reg['name']) && !empty($reg['email'])) {
                    // Check if the email already exists
                    $checkStmt = $xdatalogin->prepare("SELECT ID FROM xpower_buy_users WHERE email_address = ? LIMIT 1");
                    $checkStmt->bind_param("s", $reg['email']);
                    $checkStmt->execute();
                    $result = $checkStmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        // Email already exists, add to duplicates array
                        $duplicates[] = $reg['email'];
                        error_log("Duplicate email attempted: " . $reg['email']);
                        $checkStmt->close();
                        continue; // Skip to the next registration
                    }
                    $checkStmt->close();
                    
                    // Set default values for new columns
                    $is_paid = 0; // Default to not paid
                    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown'; // Get user's IP address
                    $user_country = 'unknown'; // Default value, could implement geolocation if required
                    $rDateTime = date('Y-m-d H:i:s'); // Current date and time

                    // Insert into xpower_buy_users table with all required parameters
                    $stmt->bind_param("ssssssiss", $reg['name'], $reg['email'], $reg['mobile'], $reg['company_name'], $reg['company_address'], $is_paid, $ip_address, $user_country, $rDateTime);
                    if ($stmt->execute()) {
                        $successfulInserts++;
                        $totalRegistrations = count($registrations);
                        
                        // Determine discount percentage based on total registrations
                        $discount_percentage = 0;
                        if ($totalRegistrations == 1) {
                            $discount_percentage = 35;
                        } elseif ($totalRegistrations == 2) {
                            $discount_percentage = 40;
                        } elseif ($totalRegistrations == 3) {
                            $discount_percentage = 40;
                        } elseif ($totalRegistrations == 4) {
                            $discount_percentage = 50;
                        } elseif ($totalRegistrations == 5) {
                            $discount_percentage = 50;
                        } else {
                            $discount_percentage = 60; // 6 or more registrations
                        }
                        
                        // Calculate discounted price (original price is 165,000)
                        $original_price = 165000;
                        $discounted_price = $original_price * (1 - $discount_percentage / 100);
                        
                        // Send email to each registration with the applicable discount
                        $emailSent = send_thank_you_email($reg['email'], $reg['name'], 'XPOWER Software Suite', number_format($discounted_price, 0, '', ''), $totalRegistrations);
                        error_log("Email sent status for {$reg['email']}: " . ($emailSent ? 'SUCCESS' : 'FAILED'));
                    } else {
                        // Log execution error but continue with other registrations
                        error_log("Execute failed for email " . $reg['email'] . " in xuser table: " . $stmt->error);
                    }
                }
            }
            $stmt->close();
        } else {
            throw new Exception("Database connection is not available.");
        }
    } catch (Exception $e) {
        error_log("Database error in index.php: " . $e->getMessage());
        http_response_code(500); // Internal Server Error
        // Show a professional error to the user, details are logged
        die("We apologize, but an issue occurred while processing your registration. Our team has been notified and will address the matter promptly. Please try again later.");
    }

    // Use the first registration's name for the welcome message, fallback to 'Guest'
    $first_name = 'Guest';
    foreach ($registrations as $reg) {
        if (!empty($reg['name'])) {
            $first_name = $reg['name'];
            break;
        }
    }

    // Display success message
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Registration Confirmation - XPOWER</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-light fixed-top navbar-transparent">
            <div class="container-fluid">
                <!-- Left: Company Logo -->
                <a class="navbar-brand" href="#">
                    <img src="images/logo.png" alt="POWERSOFT Logo" class="navbar-logo">
                </a>

                <!-- Center: Product Logo (visible on larger screens) -->
                <img src="images/xLogo.png" alt="XPOWER Logo" class="navbar-center-logo d-none d-lg-block">
            </div>
        </nav>

        <div class="container" style="padding-top: 8rem; padding-bottom: 4rem;">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <div class="card shadow-lg">
                        <div class="card-body p-5">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                            <h1 class="display-4 fw-bold mt-3">Registration Confirmation</h1>
                            <p class="lead mt-3">Dear <?php echo htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8'); ?>,</p>
                            <?php if ($successfulInserts > 0 && empty($duplicates)): ?>
                                <p class="lead my-4">Thank you for registering for the 11.11 Mega Sale. Your registration has been successfully processed. We will contact you with additional information shortly.</p>
                            <?php elseif ($successfulInserts > 0 && !empty($duplicates)): ?>
                                <p class="lead my-4">Thank you for registering for the 11.11 Mega Sale. Your new registration has been successfully processed. However, some of the email addresses you provided were already registered.</p>
                            <?php elseif ($successfulInserts == 0 && !empty($duplicates)): ?>
                                <p class="lead my-4 text-warning">All provided email addresses are already registered for the 11.11 Mega Sale. No new registrations were added.</p>
                            <?php else: ?>
                                <p class="lead my-4 text-warning">An issue occurred while processing your registration. Please try again or contact our support team for assistance.</p>
                            <?php endif; ?>
                            
                            <?php if (!empty($duplicates)): ?>
                                <div class="alert alert-info mt-3">
                                    <p class="mb-1"><strong>Registration Details:</strong> The following email addresses were already registered and have been excluded from this submission:</p>
                                    <ul class="mb-0">
                                        <?php foreach ($duplicates as $duplicate_email): ?>
                                            <li><?php echo htmlspecialchars($duplicate_email, ENT_QUOTES, 'UTF-8'); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <a href="index.php" class="btn btn-primary btn-lg">Back to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="bg-dark text-white text-center p-4">
            <div class="container">
                <p class="mb-0">Copyright by Powersoft Pvt Ltd. All rights reserved.</p>
            </div>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
    exit;
}

// Initialize claimed count and progress
$claimedCount = 80; // Default value as bait
$progressPercentage = 0; // Will be calculated from actual data
$totalSpots = 200;

if ($xdatalogin) {
    try {
        // Query to get the number of registrations from xpower_buy_users table
        $result = $xdatalogin->query("SELECT COUNT(*) as count FROM xpower_buy_users");
        if ($result) {
            $row = $result->fetch_assoc();
            $registrationCount = $row['count'];

            // The "claimed" count starts at 80 (bait) and increases with each registration
            $claimedCount = 80 + $registrationCount;

            // Calculate the progress percentage
            if ($totalSpots > 0) {
                $progressPercentage = min(($claimedCount / $totalSpots) * 100, 100); // Cap at 100%
            }
        }
        // No need to close connection here if it's used elsewhere, but if not: $conn->close();
    } catch (Exception $e) {
        // Log error, but don't block the page from rendering
        error_log("Error fetching registration count in index.php: " . $e->getMessage());
        // The page will render with default values
        $claimedCount = 80; // Default with bait
        $progressPercentage = (80 / $totalSpots) * 100;
    }
}

// Email function
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

<!-- Hero Section -->
<header class="hero-promotion">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 text-center text-lg-start hero-sticky-content">
                <h1 class="display-3 fw-bold text-white mb-3">11.11 Mega Offer!</h1>
                <p class="lead text-white-75 mb-4">Don't miss out on our biggest promotion of the year. Limited spots available!</p>
                <div id="countdown" class="countdown-container mb-4"></div>
                <div class="progress-container">
                    <div class="progress-info d-flex justify-content-between mb-2 text-white">
                        <span><i class="bi bi-people-fill"></i> <?php echo $claimedCount; ?> Claimed</span>
                        <span><?php echo $totalSpots; ?> Total</span>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $progressPercentage; ?>%;" aria-valuenow="<?php echo $claimedCount; ?>" aria-valuemin="0" aria-valuemax="<?php echo $totalSpots; ?>"><?php echo round($progressPercentage); ?>% Claimed</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mt-5 mt-lg-0">
                <div class="card shadow-lg">
                    <div class="price-banner text-center text-white">
                        <h4 class="offer-title mb-0">SPECIAL 11.11 OFFER</h4>
                        <div class="price-content my-2">
                            <span class="final-price">Rs 107,250*</span>
                            <span class="original-price">was Rs 165,000</span>
                        </div>
                        <span class="badge bg-warning text-dark rounded-pill fs-5">35% OFF + More Discounts!</span>
                        <div class="mt-2">
                            <span class="badge bg-info text-white rounded-pill">Add more for bigger discounts!</span>
                            <div class="discount-tiers mt-4">
                                <div class="row">
                                                                            <div class="col text-center">
                                                                                <div class="tier-card tier-1 p-3 rounded">
                                                                                    <p class="fw-bold mb-1">2-3 Registrations</p>
                                                                                    <p class="display-6 fw-bold">40%</p>
                                                                                    <p class="small mb-0">OFF</p>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col text-center">
                                                                                <div class="tier-card tier-2 p-3 rounded">
                                                                                    <p class="fw-bold mb-1">4-5 Registrations</p>
                                                                                    <p class="display-6 fw-bold">50%</p>
                                                                                    <p class="small mb-0">OFF</p>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col text-center">
                                                                                <div class="tier-card tier-3 p-3 rounded">                                            <p class="fw-bold mb-1">6 Registrations</p>
                                            <p class="display-6 fw-bold">60%</p>
                                            <p class="small mb-0">OFF</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body py-5 px-3">
                        <h3 class="card-title text-center mb-4">Register & Buy Now</h3>
                        <div class="text-center mb-3">
                            <span class="badge bg-success fs-5">Current Discount: <span id="current-discount">35%</span> OFF</span>
                        </div>
                        <form action="index.php" method="POST" id="promo-form">

                            <hr class="my-2">

                            <div id="registration-forms-container" class="accordion"></div>

                            <div class="d-grid my-2">
                                <button type="button" id="add-form-btn" class="btn btn-lg btn-outline-success">+ Add More for Bigger Discounts!</button>
                            </div>



                            <div id="total-summary" class="p-3 bg-light rounded">
                                <div class="d-flex justify-content-between">
                                    <span>Base Price (Rs 165,000 each)</span>
                                    <span id="subtotal-display">Rs 165,000</span>
                                </div>
                                <div class="d-flex justify-content-between text-danger">
                                    <span>Discount <span id="discount-percentage" class="badge bg-danger rounded-pill">35% OFF</span></span>
                                    <span id="discount-display">- Rs 57,750</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between h4 fw-bold">
                                    <span>Total</span>
                                    <span id="total-price-display">Rs 107,250</span>
                                </div>
                            </div>

                            <div class="d-grid mt-2">
                                <button type="submit" class="btn btn-primary btn-lg">Claim Offer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- 365 Days Customer Care Section -->
<section id="customer-care" class="section-padding">
    <div class="container">
        <h2 class="section-title">365 Days Customer Care</h2>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card feature-card text-center p-5">
                    <div class="feature-icon mb-4 mx-auto">
                        <i class="bi bi-headset" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="h4">Dedicated Support, Every Day of the Year</h3>
                    <p class="card-text lead">Our commitment to you extends all 365 days of the year. No matter when you need assistance, our customer care team is here to support you with any questions, concerns, or technical issues you may encounter.</p>
                    
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Trusted By Section -->
<section id="clients" class="section-padding bg-light">
    <div class="container">
        <h2 class="section-title">Trusted By</h2>
        <div class="row text-center justify-content-center align-items-center gy-4">
            <div class="col-lg-2 col-md-4 col-6">
                <img src="images/customer-logos/airtel.png" alt="Airtel" class="client-logo img-fluid">
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <img src="images/customer-logos/mobitel.png" alt="Mobitel" class="client-logo img-fluid">
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <img src="images/customer-logos/lakTyles.png" alt="Lak Tyles" class="client-logo img-fluid">
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <img src="images/customer-logos/hucth.png" alt="HUCTH" class="client-logo img-fluid">
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <img src="images/customer-logos/malibung.png" alt="Malibung" class="client-logo img-fluid">
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <img src="images/customer-logos/s_lon.png" alt="Sri Lanka Online" class="client-logo img-fluid">
            </div>
        </div>
        <div class="row text-center justify-content-center align-items-center gy-4 mt-3">
            <div class="col-lg-2 col-md-4 col-6">
                <img src="images/customer-logos/Muchee.png" alt="Muchee" class="client-logo img-fluid">
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <img src="images/customer-logos/InSee.png" alt="InSee" class="client-logo img-fluid">
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <img src="images/customer-logos/XTigi.png" alt="XTigi" class="client-logo img-fluid">
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <img src="images/customer-logos/ORange.png" alt="ORange" class="client-logo img-fluid">
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <img src="images/customer-logos/Ceylon_Steel_Corp.png" alt="Ceylon Steel Corp" class="client-logo img-fluid">
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <img src="images/customer-logos/kechaodax.png" alt="Kechaodax" class="client-logo img-fluid">
            </div>
        </div>
    </div>
</section>



<!-- Testimonials Section -->
<section id="testimonials" class="section-padding">
    <div class="container">
        <h2 class="section-title">What Our Customers Say</h2>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card testimonial-card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-buildings h1 text-primary"></i>
                        <p class="fst-italic my-3">"This software has transformed our workflow. It's intuitive, powerful, and the support is second to none. Highly recommended!"</p>
                        <footer class="blockquote-footer mt-3">John Doe, CEO of <span class="fw-bold">InnoTech</span></footer>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card testimonial-card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-briefcase h1 text-primary"></i>
                        <p class="fst-italic my-3">"We were up and running in minutes. The user interface is clean and easy to navigate. A fantastic product for any growing business."</p>
                        <footer class="blockquote-footer mt-3">Jane Smith, Project Manager at <span class="fw-bold">Solutions Inc.</span></footer>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

    <footer class="bg-dark text-white text-center p-4">
        <div class="container">
            <p class="mb-0">Copyright by Powersoft Pvt Ltd. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@latest/build/js/intlTelInput.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navbar = document.querySelector('.navbar');
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    navbar.classList.add('bg-light', 'shadow-sm');
                    navbar.classList.remove('navbar-transparent');
                } else {
                    navbar.classList.remove('bg-light', 'shadow-sm');
                    navbar.classList.add('navbar-transparent');
                }
            });
            
            // Navbar hiding functionality from main.js
            window.addEventListener('scroll', function() {
                if (window.scrollY > 100) {
                    navbar.classList.add('navbar-hidden');
                } else {
                    navbar.classList.remove('navbar-hidden');
                }
            });

            // Countdown Timer
            const countdownElement = document.getElementById('countdown');
            if (countdownElement) {
                const targetDate = new Date('2025-11-11T00:00:00').getTime();
                const interval = setInterval(() => {
                    const now = new Date().getTime();
                    const distance = targetDate - now;
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    countdownElement.innerHTML = `<div class=\"countdown-item\"><span>${days}</span> Days</div><div class=\"countdown-item\"><span>${hours}</span> Hours</div><div class=\"countdown-item\"><span>${minutes}</span> Minutes</div><div class=\"countdown-item\"><span>${seconds}</span> Seconds</div>`;
                    if (distance < 0) {
                        clearInterval(interval);
                        countdownElement.innerHTML = '<div class=\"countdown-item\"><span>EXPIRED</span></div>';
                    }
                }, 1000);
            }

            // Multi-form Registration Logic
            const form = document.getElementById('promo-form');
            const container = document.getElementById('registration-forms-container');
            const addBtn = document.getElementById('add-form-btn');
            const subtotalDisplay = document.getElementById('subtotal-display');
            const discountDisplay = document.getElementById('discount-display');
            const totalDisplay = document.getElementById('total-price-display');

            const basePrice = 165000;
            let formCount = 0;
            let phoneInstances = [];

            const getDiscountRate = (count) => {
                if (count >= 6) return 0.60; // 60% discount
                if (count === 5) return 0.50; // 50% discount
                if (count === 4) return 0.50; // 50% discount
                if (count === 3) return 0.40; // 40% discount
                if (count === 2) return 0.40; // 40% discount
                if (count === 1) return 0.35; // 35% discount
                return 0;
            };

            const updateTotal = () => {
                const count = container.children.length;
                const discountRate = getDiscountRate(count);
                const discountPercentage = Math.round(discountRate * 100); // Convert to percentage
                const subtotal = count * basePrice;
                const discountAmount = subtotal * discountRate;
                const finalTotal = subtotal - discountAmount;

                subtotalDisplay.textContent = `Rs ${subtotal.toLocaleString('en-US')}`;
                discountDisplay.textContent = `- Rs ${discountAmount.toLocaleString('en-US')}`;
                totalDisplay.textContent = `Rs ${finalTotal.toLocaleString('en-US')}`;
                
                // Update discount percentage display
                document.getElementById('discount-percentage').textContent = `${discountPercentage}% OFF`;
                
                // Also update the current discount badge
                document.getElementById('current-discount').textContent = `${discountPercentage}%`;
            };

            const addRegistrationForm = () => {
                if (formCount >= 6) return;
                formCount++;

                const formId = `reg-form-${formCount}`;
                const isFirst = formCount === 1;

                const template = document.createElement('div');
                template.className = 'accordion-item';
                template.innerHTML = `
                    <h2 class=\"accordion-header\" id=\"heading-${formId}\">
                        <button class=\"accordion-button ${isFirst ? '' : 'collapsed'}\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapse-${formId}\" aria-expanded=\"${isFirst}\" aria-controls=\"collapse-${formId}\">
                            Registration #${formCount}
                        </button>
                    </h2>
                    <div id=\"collapse-${formId}\" class=\"accordion-collapse collapse ${isFirst ? 'show' : ''}\" aria-labelledby=\"heading-${formId}\">
                        <div class=\"accordion-body\">
                            ${!isFirst ? '<button type="button" class="btn btn-sm btn-danger float-end remove-form-btn">Remove</button>' : ''}
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="registrations[${formCount}][name]" required maxlength="100">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" name="registrations[${formCount}][email]" required maxlength="100">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mobile Number</label>
                                <input type="tel" class="form-control phone-input" name="registrations[${formCount}][mobile]" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Company Name</label>
                                <input type="text" class="form-control" name="registrations[${formCount}][company_name]" maxlength="100">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Company Address</label>
                                <textarea class="form-control" name="registrations[${formCount}][company_address]" rows="3" maxlength="255"></textarea>
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(template);

                const phoneInputEl = template.querySelector('.phone-input');
                const phoneInput = window.intlTelInput(phoneInputEl, {
                    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@latest/build/js/utils.js",
                    nationalMode: true, initialCountry: "auto",
                    geoIpLookup: cb => fetch("https://ipapi.co/json").then(r => r.json()).then(d => cb(d.country_code)).catch(() => cb("us")),
                });
                phoneInstances.push({ id: formId, instance: phoneInput, element: phoneInputEl });

                const updateMaxLength = () => {
                    const countryData = phoneInput.getSelectedCountryData();
                    phoneInputEl.maxLength = (countryData.iso2 === 'lk') ? 10 : phoneInput.getNumberPlaceholder().replace(/\D/g, '').length;
                };
                phoneInput.promise.then(updateMaxLength);
                phoneInputEl.addEventListener('countrychange', updateMaxLength);

                if (!isFirst) {
                    template.querySelector('.remove-form-btn').addEventListener('click', () => {
                        phoneInstances = phoneInstances.filter(p => p.id !== formId);
                        template.remove();
                        formCount--;
                        addBtn.disabled = false;
                        updateTotal();
                    });
                }

                if (formCount >= 6) {
                    addBtn.disabled = true;
                }
                updateTotal();
            };

            addBtn.addEventListener('click', addRegistrationForm);

            // Initial form
            addRegistrationForm();

            // Final validation on submit
            form.addEventListener('submit', function(e) {
                let allValid = true;
                phoneInstances.forEach(p => {
                    const countryData = p.instance.getSelectedCountryData();
                    let expectedLength = (countryData.iso2 === 'lk') ? 10 : p.instance.getNumberPlaceholder().replace(/\D/g, '').length;
                    if (p.element.value.length !== expectedLength) {
                        allValid = false;
                        alert(`Invalid phone number in Registration #${p.id.split('-')[2]}. Please enter a number with ${expectedLength} digits.`);
                    }
                });

                if (!allValid) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
