document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('zapier-form-modal');
    const openButton = document.getElementById('open-zapier-form');
    const closeButton = document.querySelector('.zapier-modal-close');
    let currentStep = 1;
    let formSubmitted = false;

    openButton.addEventListener('click', () => {
        modal.style.display = 'block';
        document.body.classList.add('no-scroll');
        resetForm();
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

    function validateForm() {
        const form = document.getElementById(`zapier-form-step${currentStep}`);
        let isValid = true;
        Array.from(form.elements).forEach(field => {
            if (!validateField(field)) {
                isValid = false;
            }
        });
        return isValid;
    }

    function submitStep1() {
        const form = document.getElementById('zapier-form-step1');
        const formData = new FormData(form);
        formData.append('action', 'zapier_form_step1');
        formData.append('nonce', zapier_form_rest.nonce);

        fetch(zapier_form_rest.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentStep = 2;
                loadStep2(data.data.transient_key);
            } else {
                showMessage(data.data.message || 'An error occurred. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred. Please try again.', 'error');
        });
    }

    function loadStep2(transientKey) {
        fetch(`${zapier_form_rest.ajax_url}?action=zapier_form_load_step2&transient_key=${transientKey}&nonce=${zapier_form_rest.nonce}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modalContent = document.querySelector('.zapier-modal-content');
                modalContent.innerHTML = data.data.html;
                initializeForm();
            } else {
                showMessage(data.data.message || 'An error occurred. Please try again.', 'error');
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
        formData.append('action', 'zapier_form_step2');
        formData.append('nonce', zapier_form_rest.nonce);

        fetch(zapier_form_rest.ajax_url, {
            method: 'POST',
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
        const modalContent = document.querySelector('.zapier-modal-content');
        modalContent.innerHTML = ''; // Clear the modal content
        // Load the first step form
        fetch(`${zapier_form_rest.ajax_url}?action=zapier_form_load_step1&nonce=${zapier_form_rest.nonce}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modalContent.innerHTML = data.data.html;
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

    // Initialize the form when the page loads
    initializeForm();

    function validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        let errorMessage = '';

        if (field.required && value === '') {
            isValid = false;
            errorMessage = 'This field is required';
        } else {
            switch (field.name) {
                case 'FirstName':
                case 'LastName':
                    if (value.length < 2) {
                        isValid = false;
                        errorMessage = 'Name must be at least 2 characters long';
                    }
                    break;
                case 'Email':
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid email address';
                    }
                    break;
                case 'Phone':
                    const phoneRegex = /^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/;
                    if (!phoneRegex.test(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid phone number';
                    }
                    break;
                case 'Zip':
                    const zipRegex = /^\d{5}(-\d{4})?$/;
                    if (!zipRegex.test(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid ZIP code';
                    }
                    break;
            }
        }

        toggleFieldError(field, !isValid, errorMessage);
        return isValid;
    }

    function toggleFieldError(field, showError, message) {
        const parent = field.closest('.form-field');
        parent.classList.toggle('error', showError);

        let errorElement = parent.querySelector('.error-message');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'error-message';
            errorElement.setAttribute('aria-live', 'polite');
            parent.appendChild(errorElement);
        }

        errorElement.textContent = showError ? message : '';
        errorElement.style.display = showError ? 'block' : 'none';
        field.setAttribute('aria-invalid', showError);
    }

    function shakeInvalidFields() {
        const form = document.getElementById(`zapier-form-step${currentStep}`);
        const errorFields = form.querySelectorAll('.form-field.error');
        errorFields.forEach(field => {
            field.classList.add('shake');
        });
        setTimeout(() => {
            errorFields.forEach(field => {
                field.classList.remove('shake');
            });
        }, 820);
    }

    function focusFirstInvalidField() {
        const form = document.getElementById(`zapier-form-step${currentStep}`);
        const firstErrorField = form.querySelector('.form-field.error input');
        if (firstErrorField) {
            firstErrorField.focus();
        }
    }

    function showMessage(message, type) {
        let messageContainer = document.querySelector('.form-message');
        if (!messageContainer) {
            messageContainer = document.createElement('div');
            messageContainer.className = 'form-message';
            messageContainer.setAttribute('role', 'alert');
            const form = document.getElementById(`zapier-form-step${currentStep}`);
            form.parentElement.insertBefore(messageContainer, form);
        }

        messageContainer.textContent = message;
        messageContainer.className = `form-message ${type}`;
    }

    document.addEventListener('input', (e) => {
        if (e.target.id === 'Phone') {
            const cleaned = e.target.value.replace(/\D/g, '');
            const match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/);
            if (match) {
                e.target.value = `(${match[1]}) ${match[2]}-${match[3]}`;
            }
        } else if (e.target.id === 'Email') {
            e.target.value = e.target.value.replace(/\s/g, '');
        } else if (e.target.id === 'FirstName' || e.target.id === 'LastName') {
            const val = e.target.value;
            if (val.length > 0) {
                e.target.value = val.charAt(0).toUpperCase() + val.slice(1);
            }
        }
    });
});
