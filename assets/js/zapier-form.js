document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('zapier-form-modal');
    const openButton = document.getElementById('open-zapier-form');
    const closeButton = document.querySelector('.zapier-modal-close');
    let currentStep = 1;
    let formSubmitted = false;
    let transientKey = '';

    openButton.addEventListener('click', () => {
        modal.style.display = 'block';
        document.body.classList.add('no-scroll');
        loadStep1();
    });
    
    closeButton.addEventListener('click', () => {
        modal.style.display = 'none';
        document.body.classList.remove('no-scroll');
        resetForm();
    });
    
    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
            document.body.classList.remove('no-scroll');
            resetForm();
        }
    });

    function loadStep1() {
        fetch(`${zapier_form_rest.root}zapier-form/v1/load-step1`, {
            method: 'GET',
            headers: {
                'X-WP-Nonce': zapier_form_rest.nonce
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const formContainer = document.getElementById('zapier-form-container');
                formContainer.innerHTML = data.html;
                initializeForm();
            } else {
                showMessage(data.message || 'An error occurred. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred. Please try again.', 'error');
        });
    }

    function initializeForm() {
        const form = document.getElementById(`zapier-form-step${currentStep}`);
        if (!form) return;

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            formSubmitted = true;

            if (validateForm()) {
                if (currentStep === 1) {
                    submitStep1();
                } else {
                    submitStep2();
                }
            } else {
                shakeInvalidFields();
                focusFirstInvalidField();
            }
        });

        form.addEventListener('input', (event) => {
            if (formSubmitted) {
                validateField(event.target);
            }
        });
    }

    function submitStep1() {
        const form = document.getElementById('zapier-form-step1');
        const formData = new FormData(form);

        fetch(`${zapier_form_rest.root}zapier-form/v1/submit-step1`, {
            method: 'POST',
            headers: {
                'X-WP-Nonce': zapier_form_rest.nonce
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentStep = 2;
                transientKey = data.transient_key;
                loadStep2(transientKey);
            } else {
                showMessage(data.message || 'An error occurred. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred. Please try again.', 'error');
        });
    }

    function loadStep2(transientKey) {
        fetch(`${zapier_form_rest.root}zapier-form/v1/load-step2?transient_key=${transientKey}`, {
            method: 'GET',
            headers: {
                'X-WP-Nonce': zapier_form_rest.nonce
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const formContainer = document.getElementById('zapier-form-container');
                formContainer.innerHTML = data.html;
                initializeForm();
            } else {
                showMessage(data.message || 'An error occurred. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred. Please try again.', 'error');
        });
    }

    function submitStep2() {
        const form = document.getElementById('zapier-form-step2');
        const formData = new FormData(form);
        formData.append('transient_key', transientKey);

        fetch(`${zapier_form_rest.root}zapier-form/v1/submit-step2`, {
            method: 'POST',
            headers: {
                'X-WP-Nonce': zapier_form_rest.nonce
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                resetForm();
                setTimeout(() => {
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                    } else {
                        modal.style.display = 'none';
                    }
                }, 2000);
            } else {
                showMessage(data.message || 'An error occurred. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred. Please try again.', 'error');
        });
    }

    function resetForm() {
        currentStep = 1;
        formSubmitted = false;
        transientKey = '';
        const formContainer = document.getElementById('zapier-form-container');
        formContainer.innerHTML = '';
    }

    function validateForm() {
        const form = document.getElementById(`zapier-form-step${currentStep}`);
        const inputs = form.querySelectorAll('input[required], select[required]');
        let isValid = true;

        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });

        return isValid;
    }

    function validateField(field) {
        if (field.checkValidity()) {
            toggleFieldError(field, false);
            return true;
        } else {
            toggleFieldError(field, true);
            return false;
        }
    }

    function toggleFieldError(field, show) {
        const errorElement = field.nextElementSibling;
        if (errorElement && errorElement.classList.contains('error-message')) {
            errorElement.style.display = show ? 'block' : 'none';
        }
        field.classList.toggle('error', show);
    }

    function shakeInvalidFields() {
        const invalidFields = document.querySelectorAll('.error');
        invalidFields.forEach(field => {
            field.classList.add('shake');
            setTimeout(() => field.classList.remove('shake'), 500);
        });
    }

    function focusFirstInvalidField() {
        const firstInvalidField = document.querySelector('.error');
        if (firstInvalidField) {
            firstInvalidField.focus();
        }
    }

    function showMessage(message, type) {
        const messageElement = document.querySelector('.form-message');
        messageElement.textContent = message;
        messageElement.className = `form-message ${type}`;
        messageElement.style.display = 'block';
    }

    // Initialize the form when the page loads
    loadStep1();
});
