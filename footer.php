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
                    countdownElement.innerHTML = `<div class="countdown-item"><span>${days}</span> Days</div><div class="countdown-item"><span>${hours}</span> Hours</div><div class="countdown-item"><span>${minutes}</span> Minutes</div><div class="countdown-item"><span>${seconds}</span> Seconds</div>`;
                    if (distance < 0) {
                        clearInterval(interval);
                        countdownElement.innerHTML = '<div class="countdown-item"><span>EXPIRED</span></div>';
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
                if (count >= 6) return 0.15;
                if (count === 5) return 0.125;
                if (count === 4) return 0.10;
                if (count === 3) return 0.075;
                if (count === 2) return 0.05;
                if (count === 1) return 0.20; // Apply 20% discount for single item
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
                    <h2 class="accordion-header" id="heading-${formId}">
                        <button class="accordion-button ${isFirst ? '' : 'collapsed'}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-${formId}" aria-expanded="${isFirst}" aria-controls="collapse-${formId}">
                            Registration #${formCount}
                        </button>
                    </h2>
                    <div id="collapse-${formId}" class="accordion-collapse collapse ${isFirst ? 'show' : ''}" aria-labelledby="heading-${formId}">
                        <div class="accordion-body">
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
