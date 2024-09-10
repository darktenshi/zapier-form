/**
 * TODO: Update this script to include the following changes:
 * 1. Add logic to handle the new frequency dropdown in the form
 * 2. Update form submission to include ScopeGroupId, ScopeOfWorkId, and selected frequency
 * 3. Modify form validation to include the new fields
 * 4. Update any relevant UI interactions for the new form fields
 * 5. Ensure that the frequency dropdown only shows enabled frequencies from admin settings
 */
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('zapier-form-modal');
    const openButton = document.getElementById('open-zapier-form');
    const closeButton = document.querySelector('.zapier-modal-close');
    let currentStep = 1;
    let formSubmitted = false;
    let leadId = '';
    let submissionTimer;

    openButton.addEventListener('click', () => {
        modal.style.display = 'block';
        document.body.classList.add('no-scroll');
        loadStep1();
    });

    function loadStep1() {
        console.log('Loading Step 1...');
        fetch(`${zapier_form_rest.root}zapier-form/v1/load-step1`, {
            method: 'GET',
            headers: {
                'X-WP-Nonce': zapier_form_rest.nonce
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', JSON.stringify(Array.from(response.headers.entries())));
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            console.log('Raw response:', text);
            try {
                // Remove any potential HTML tags before parsing JSON
                const cleanedText = text.replace(/<[^>]*>/g, '');
                return JSON.parse(cleanedText);
            } catch (e) {
                console.error('Error parsing JSON:', e);
                console.log('Invalid JSON:', text);
                logToServer('JSON Parse Error', { error: e.toString(), text: text });
                throw new Error('Invalid JSON response');
            }
        })
        .then(data => {
            console.log('Parsed data:', JSON.stringify(data, null, 2));
            if (data.success) {
                const formContainer = document.getElementById('zapier-form-container');
                formContainer.innerHTML = data.html;
                initializeForm();
            } else {
                showMessage(data.message || 'An error occurred. Please try again.', 'error');
                logToServer('Form Load Error', data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred while loading the form. Please try again later.', 'error');
            logToServer('Form Load Exception', { error: error.toString() });
        });
    }

    function logToServer(type, data) {
        fetch(`${zapier_form_rest.root}zapier-form/v1/log`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': zapier_form_rest.nonce
            },
            body: JSON.stringify({ type, data })
        })
        .then(response => response.json())
        .then(result => console.log('Log sent to server:', result))
        .catch(error => console.error('Error logging to server:', error));
    }

    function showMessage(message, type) {
        const messageContainer = document.querySelector('.form-message');
        if (messageContainer) {
            messageContainer.textContent = message;
            messageContainer.className = `form-message ${type}`;
            messageContainer.style.display = 'block';
        }
    }
    
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
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
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
            showMessage('An error occurred while loading the form. Please try again later.', 'error');
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

        // Add event listeners for field-specific formatting
        const phoneInput = form.querySelector('input[name="Phone"]');
        if (phoneInput) {
            phoneInput.addEventListener('input', formatPhoneNumber);
        }

        const emailInput = form.querySelector('input[name="Email"]');
        if (emailInput) {
            emailInput.addEventListener('keypress', preventSpacesInEmail);
        }

        const nameInputs = form.querySelectorAll('input[name="FirstName"], input[name="LastName"]');
        nameInputs.forEach(input => {
            input.addEventListener('input', capitalizeFirstLetter);
        });
    }

    function formatPhoneNumber(e) {
        const cleaned = e.target.value.replace(/\D/g, '');
        const match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/);
        if (match) {
            e.target.value = `(${match[1]}) ${match[2]}-${match[3]}`;
        }
    }

    function preventSpacesInEmail(e) {
        if (e.which === 32) {
            e.preventDefault();
        }
    }

    function capitalizeFirstLetter(e) {
        const val = e.target.value;
        if (val.length > 0) {
            e.target.value = val.charAt(0).toUpperCase() + val.slice(1);
        }
    }

    function validateForm() {
        const form = document.getElementById(`zapier-form-step${currentStep}`);
        const fields = form.querySelectorAll('input, select');
        let isValid = true;

        fields.forEach(field => {
            if (!validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    function validateField(field) {
        const isValid = field.checkValidity();
        const errorElement = field.parentElement.querySelector('.error-message');
        let customErrorMessage = '';

        if (!isValid) {
            switch (field.name) {
                case 'FirstName':
                case 'LastName':
                    if (field.value.trim().length < 2) {
                        customErrorMessage = 'Name must be at least 2 characters long';
                    }
                    break;
                case 'Email':
                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
                        customErrorMessage = 'Please enter a valid email address';
                    }
                    break;
                case 'Phone':
                    if (!/^\(\d{3}\) \d{3}-\d{4}$/.test(field.value)) {
                        customErrorMessage = 'Please enter a valid phone number';
                    }
                    break;
                case 'Zip':
                    if (!/^\d{5}(-\d{4})?$/.test(field.value)) {
                        customErrorMessage = 'Please enter a valid ZIP code';
                    }
                    break;
                case 'Frequency':
                    if (field.value === '') {
                        customErrorMessage = 'Please select a cleaning frequency';
                    }
                    break;
                case 'HomeSquareFeet':
                    if (parseInt(field.value) < 1) {
                        customErrorMessage = 'Please enter a valid square footage';
                    }
                    break;
                case 'HomeBedrooms':
                case 'HomeFullBathrooms':
                case 'HomeHalfBathrooms':
                    if (field.value === '') {
                        customErrorMessage = 'Please select a value';
                    }
                    break;
            }
        }

        if (!isValid) {
            field.parentElement.classList.add('error');
            if (errorElement) {
                errorElement.style.display = 'block';
                errorElement.textContent = customErrorMessage || field.validationMessage;
            }
        } else {
            field.parentElement.classList.remove('error');
            if (errorElement) {
                errorElement.style.display = 'none';
            }
        }

        return isValid;
    }

    function shakeInvalidFields() {
        const form = document.getElementById(`zapier-form-step${currentStep}`);
        const invalidFields = form.querySelectorAll('.error');

        invalidFields.forEach(field => {
            field.classList.add('shake');
            setTimeout(() => {
                field.classList.remove('shake');
            }, 500);
        });
    }

    function focusFirstInvalidField() {
        const form = document.getElementById(`zapier-form-step${currentStep}`);
        const firstInvalidField = form.querySelector('.error input, .error select');

        if (firstInvalidField) {
            firstInvalidField.focus();
        }
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
                leadId = data.lead_id;
                loadStep2(leadId);
                // Remove the startSubmissionTimer() call as it's no longer needed
            } else {
                showMessage(data.message || 'An error occurred. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred. Please try again.', 'error');
        });
    }

    // Remove the startSubmissionTimer function as it's no longer needed

    function loadStep2(leadId) {
        fetch(`${zapier_form_rest.root}zapier-form/v1/load-step2?lead_id=${leadId}`, {
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
                
                // Pre-fill the state dropdown based on the ZIP code
                const zipInput = document.getElementById('HomeZip');
                const stateSelect = document.getElementById('HomeRegion');
                if (zipInput && stateSelect) {
                    const zip = zipInput.value;
                    const state = getStateFromZip(zip);
                    if (state) {
                        stateSelect.value = state;
                    }
                }
            } else {
                showMessage(data.message || 'An error occurred. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred. Please try again.', 'error');
        });
    }

    function getStateFromZip(zip) {
        // This is a simplified version. For a more accurate result, you might want to use a ZIP code API.
        const zipPrefixes = {
            '0': 'CT', '1': 'NY', '2': 'NY', '3': 'NJ', '4': 'PA', '5': 'DE', '6': 'MD', '7': 'VA',
            '8': 'NC', '9': 'SC'
        };
        const prefix = zip.charAt(0);
        return zipPrefixes[prefix] || '';
    }

    function submitStep2() {
        const form = document.getElementById('zapier-form-step2');
        const formData = new FormData(form);
        formData.append('lead_id', leadId);

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
                clearTimeout(submissionTimer);
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
        leadId = '';
        clearTimeout(submissionTimer);
        const formContainer = document.getElementById('zapier-form-container');
        formContainer.innerHTML = '';
    }

    // Keep the validateForm, validateField, toggleFieldError, shakeInvalidFields, focusFirstInvalidField, and showMessage functions as they are

    // Initialize the form when the page loads
    loadStep1();
});
