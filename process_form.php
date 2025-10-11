<?php
// process_form.php

// Include database connection
require_once 'db.php';
require_once 'send_email.php';

// Prevent direct access
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Set a proper response code
    http_response_code(405);
    die('Direct access not permitted');
}

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
    // The connection $conn is from db.php
    if ($conn) {
        // Insert into xuser table
        $stmt = $conn->prepare("INSERT INTO xuser (name, email, mobile, company_name, company_address) VALUES (?, ?, ?, ?, ?)");
        if ($stmt === false) {
            throw new Exception("Prepare failed for xuser table: " . $conn->error);
        }
        foreach ($registrations as $reg) {
            // Only insert if name and email are provided
            if (!empty($reg['name']) && !empty($reg['email'])) {
                // Check if the email already exists
                $checkStmt = $conn->prepare("SELECT id FROM xuser WHERE email = ? LIMIT 1");
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
                
                // Insert into xuser table
                $stmt->bind_param("sssss", $reg['name'], $reg['email'], $reg['mobile'], $reg['company_name'], $reg['company_address']);
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
    error_log("Database error in process_form.php: " . $e->getMessage());
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

?>
<?php include 'header.php'; ?>

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

<?php include 'footer.php'; ?>