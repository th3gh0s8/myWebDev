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
            const targetDate = new Date('2025-11-11T00:00:00').getTime();

            const interval = setInterval(() => {
                const now = new Date().getTime();
                const distance = targetDate - now;

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                if (countdownElement) {
                    countdownElement.innerHTML = `
                        <div class="countdown-item"><span>${days}</span> Days</div>
                        <div class="countdown-item"><span>${hours}</span> Hours</div>
                        <div class="countdown-item"><span>${minutes}</span> Minutes</div>
                        <div class="countdown-item"><span>${seconds}</span> Seconds</div>
                    `;
                }

                if (distance < 0) {
                    clearInterval(interval);
                    if (countdownElement) {
                        countdownElement.innerHTML = '<div class="countdown-item"><span>EXPIRED</span></div>';
                    }
                }
            }, 1000);

            // intl-tel-input
            const phoneInputField = document.querySelector("#mobile");
            const form = document.querySelector("#promo-form");
            let phoneInput;

            if(phoneInputField) {
                phoneInput = window.intlTelInput(phoneInputField, {
                    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@latest/build/js/utils.js",
                    nationalMode: true,
                    initialCountry: "auto",
                    geoIpLookup: callback => {
                        fetch("https://ipapi.co/json")
                            .then(res => res.json())
                            .then(data => callback(data.country_code))
                            .catch(() => callback("us"));
                    },
                });

                const updateMaxLength = () => {
                    const countryData = phoneInput.getSelectedCountryData();
                    if (countryData.iso2 === 'lk') {
                        phoneInputField.maxLength = 10;
                    } else {
                        const placeholder = phoneInput.getNumberPlaceholder();
                        // Remove non-digit characters and get the length
                        const maxLength = placeholder.replace(/\D/g, '').length;
                        phoneInputField.maxLength = maxLength;
                    }
                };

                // Set initial max length
                phoneInput.promise.then(updateMaxLength);

                // Update max length on country change
                phoneInputField.addEventListener('countrychange', updateMaxLength);
            }

            if (form && phoneInput) {
                form.addEventListener("submit", function(e) {
                    const countryData = phoneInput.getSelectedCountryData();
                    let expectedLength;

                    if (countryData.iso2 === 'lk') {
                        expectedLength = 10;
                    } else {
                        const placeholder = phoneInput.getNumberPlaceholder();
                        expectedLength = placeholder.replace(/\D/g, '').length;
                    }

                    const actualLength = phoneInputField.value.length;

                    if (actualLength !== expectedLength) {
                        e.preventDefault();
                        alert(`Invalid phone number. Please enter a number with ${expectedLength} digits for the selected country.`);
                    } else {
                        // On valid submission, populate the hidden field with the full international number
                        const hiddenInput = document.querySelector('[name="full_mobile"]');
                        hiddenInput.value = phoneInput.getNumber();
                    }
                });
            }
        });
    </script>
</body>
</html>