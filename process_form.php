<?php
// process_form.php

// In a real application, you would process the form data here.
// 1. Validate and sanitize the input.
// 2. Save the data to a database.
// 3. Increment the 'claimed' count.
// 4. Send a confirmation email to the user.

$name = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : 'Guest';

?>
<?php include 'header.php'; ?>

<div class="container" style="padding-top: 8rem; padding-bottom: 4rem;">
    <div class="row">
        <div class="col-lg-8 mx-auto text-center">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    <h1 class="display-4 fw-bold mt-3">Thank You, <?php echo $name; ?>!</h1>
                    <p class="lead my-4">Your registration for the 11.11 Mega Sale has been received. We will contact you shortly with more details.</p>
                    <a href="index.php" class="btn btn-primary btn-lg">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
