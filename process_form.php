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
        die("Name and email are required for registration " . ($key + 1));
    }
    
    // Validate email format
    if (!empty($reg['email']) && !filter_var($reg['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400); // Bad Request
        die("Invalid email format for registration " . ($key + 1));
    }
}

// Insert registrations only into the xuser table
$successfulInserts = 0;
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
                // Insert into xuser table
                $stmt->bind_param("sssss", $reg['name'], $reg['email'], $reg['mobile'], $reg['company_name'], $reg['company_address']);
                if ($stmt->execute()) {
                    $successfulInserts++;
                    send_thank_you_email($reg['email'], $reg['name'], '11.11 Mega Sale', '100');
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
    // Show a generic error to the user, details are logged
    die("An error occurred while saving your data. Please try again later.");
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
                    <h1 class="display-4 fw-bold mt-3">Thank You, <?php echo htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8'); ?>!</h1>
                    <?php if ($successfulInserts > 0): ?>
                        <p class="lead my-4">Your registration for the 11.11 Mega Sale has been received. We will contact you shortly with more details.</p>
                    <?php else: ?>
                        <p class="lead my-4 text-warning">There was an issue with your registration. Please try again or contact support.</p>
                    <?php endif; ?>
                    <a href="index.php" class="btn btn-primary btn-lg">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
