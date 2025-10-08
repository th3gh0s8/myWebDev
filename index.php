<?php include 'header.php'; ?>

<!-- Hero Section -->
<header class="hero-promotion">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 text-center text-lg-start">
                <h1 class="display-3 fw-bold text-white mb-3">11.11 Mega Sale!</h1>
                <p class="lead text-white-75 mb-4">Don't miss out on our biggest promotion of the year. Limited spots available!</p>
                <div id="countdown" class="countdown-container mb-4"></div>
                <div class="progress-container">
                    <div class="progress-info d-flex justify-content-between mb-2 text-white">
                        <span><i class="bi bi-people-fill"></i> 80 Claimed</span>
                        <span>200 Total</span>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 40%;" aria-valuenow="80" aria-valuemin="0" aria-valuemax="200">40% Claimed</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mt-5 mt-lg-0">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <h3 class="card-title text-center mb-4">Register & Buy Now</h3>
                        <form action="process_form.php" method="POST" id="promo-form">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" required maxlength="100">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required maxlength="100">
                            </div>
                            <div class="mb-3">
                                <label for="mobile" class="form-label">Mobile Number</label>
                                <input type="tel" class="form-control" id="mobile" name="mobile" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>
                            <div class="mb-3">
                                <label for="company_name" class="form-label">Company Name</label>
                                <input type="text" class="form-control" id="company_name" name="company_name" maxlength="100">
                            </div>
                            <div class="mb-3">
                                <label for="company_address" class="form-label">Company Address</label>
                                <textarea class="form-control" id="company_address" name="company_address" rows="3" maxlength="255"></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Claim Offer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Features Section -->
<section id="features" class="section-padding">
    <div class="container">
        <h2 class="section-title">Key Features</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card feature-card text-center p-4">
                    <div class="feature-icon mb-3 mx-auto">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <h3 class="h5">Fast & Efficient</h3>
                    <p class="card-text">Our software is optimized for speed, ensuring a smooth and responsive user experience across all devices.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card text-center p-4">
                    <div class="feature-icon mb-3 mx-auto">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <h3 class="h5">Secure by Design</h3>
                    <p class="card-text">With industry-leading security protocols, your data is always safe and protected from unauthorized access.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card text-center p-4">
                    <div class="feature-icon mb-3 mx-auto">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h3 class="h5">24/7 Support</h3>
                    <p class="card-text">Our dedicated support team is available around the clock to assist you with any questions or issues.</p>
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
                <h3 class="h4 text-muted d-flex align-items-center justify-content-center"><i class="bi bi-building me-2"></i>CompanyOne</h3>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <h3 class="h4 text-muted d-flex align-items-center justify-content-center"><i class="bi bi-bar-chart-line me-2"></i>Statistica</h3>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <h3 class="h4 text-muted d-flex align-items-center justify-content-center"><i class="bi bi-globe me-2"></i>Global Corp</h3>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <h3 class="h4 text-muted d-flex align-items-center justify-content-center"><i class="bi bi-gem me-2"></i>Jewel Co</h3>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <h3 class="h4 text-muted d-flex align-items-center justify-content-center"><i class="bi bi-cone-striped me-2"></i>BuildIt</h3>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <h3 class="h4 text-muted d-flex align-items-center justify-content-center"><i class="bi bi-p-circle me-2"></i>Proactive</h3>
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

<?php include 'footer.php'; ?>
