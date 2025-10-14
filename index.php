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

<?php
require_once 'db.php'; // Include the database connection

// Initialize claimed count and progress
$claimedCount = 80; // Default value as bait
$progressPercentage = 0; // Will be calculated from actual data
$totalSpots = 200;

if ($conn) {
    try {
        // Query to get the number of registrations from xuser table
        $result = $conn->query("SELECT COUNT(*) as count FROM xuser");
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
                        <form action="process_form.php" method="POST" id="promo-form">

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
                                    <span>Discount</span>
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
                <img src="images/bbc.jpg" alt="BBC" class="client-logo img-fluid">
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <img src="images/bmv.jpg" alt="BMW" class="client-logo img-fluid">
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <img src="images/taco-bell.png" alt="Taco Bell" class="client-logo img-fluid">
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <img src="images/horsy.png" alt="Horsy" class="client-logo img-fluid">
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <img src="images/putsy.png" alt="Putsy" class="client-logo img-fluid">
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <img src="images/circle-woman-.png" alt="Circle Woman" class="client-logo img-fluid">
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
                const subtotal = count * basePrice;
                const discountAmount = subtotal * discountRate;
                const finalTotal = subtotal - discountAmount;

                subtotalDisplay.textContent = `Rs ${subtotal.toLocaleString('en-US')}`;
                discountDisplay.textContent = `- Rs ${discountAmount.toLocaleString('en-US')}`;
                totalDisplay.textContent = `Rs ${finalTotal.toLocaleString('en-US')}`;
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
                            ${!isFirst ? '<button type=\"button\" class=\"btn btn-sm btn-danger float-end remove-form-btn\">Remove</button>' : ''}
                            <div class=\"mb-3\">
                                <label class=\"form-label\">Full Name</label>
                                <input type=\"text\" class=\"form-control\" name=\"registrations[${formCount}][name]\" required maxlength=\"100\">
                            </div>
                            <div class=\"mb-3\">
                                <label class=\"form-label\">Email Address</label>
                                <input type=\"email\" class=\"form-control\" name=\"registrations[${formCount}][email]\" required maxlength=\"100\">
                            </div>
                            <div class=\"mb-3\">
                                <label class=\"form-label\">Mobile Number</label>
                                <input type=\"tel\" class=\"form-control phone-input\" name=\"registrations[${formCount}][mobile]\" required oninput=\"this.value = this.value.replace(/[^0-9]/g, '')\">
                            </div>
                            <div class=\"mb-3\">
                                <label class=\"form-label\">Company Name</label>
                                <input type=\"text\" class=\"form-control\" name=\"registrations[${formCount}][company_name]\" maxlength=\"100\">
                            </div>
                            <div class=\"mb-3\">
                                <label class=\"form-label\">Company Address</label>
                                <textarea class=\"form-control\" name=\"registrations[${formCount}][company_address]\" rows=\"3\" maxlength=\"255\"></textarea>
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
