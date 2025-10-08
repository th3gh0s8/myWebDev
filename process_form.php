<?php
// process_form.php

// Include database connection
require_once 'database.php';

// Prevent direct access
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

// Validate email format for each registration
foreach ($registrations as $key => $reg) {
    if (!empty($reg['email']) && !filter_var($reg['email'], FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format for registration $key");
    }
}

// Insert registrations into the database
try {
    $stmt = $pdo->prepare("INSERT INTO registrations (name, email, mobile, company_name, company_address) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($registrations as $reg) {
        if (!empty($reg['name']) && !empty($reg['email'])) { // Only insert if name and email are provided
            $stmt->execute([$reg['name'], $reg['email'], $reg['mobile'], $reg['company_name'], $reg['company_address']]);
        }
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("An error occurred while saving your data. Please try again later.");
}

// Use the first registration's name for the welcome message, fallback to 'Guest'
$first_name = !empty($registrations) ? $registrations[1]['name'] : 'Guest';
if (empty($first_name)) {
    $first_name = 'Guest';
}

?>
<?php include 'header.php'; ?>

<div class="container" style="padding-top: 8rem; padding-bottom: 4rem;">
    <div class="row">
        <div class="col-lg-8 mx-auto text-center">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    <h1 class="display-4 fw-bold mt-3">Thank You, <?php echo $first_name; ?>!</h1>
                    <p class="lead my-4">Your registration for the 11.11 Mega Sale has been received. We will contact you shortly with more details.</p>
                    <a href="index.php" class="btn btn-primary btn-lg">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
