/**
 * TODO: Update this script to include the following changes:
 * 1. Add logic to handle the new frequency dropdown in the form
 * 2. Update form submission to include ScopeGroupId, ScopeOfWorkId, and selected frequency
 * 3. Modify form validation to include the new fields
 * 4. Update any relevant UI interactions for the new form fields
 */
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('zapier-form-modal');
    const openButton = document.getElementById('open-zapier-form');
    const closeButton = document.querySelector('.zapier-modal-close');
    const form1 = document.getElementById('zapier-form-step1');
    const form2 = document.getElementById('zapier-form-step2');
    let formSubmitted = false;
    let formData = {};

    openButton.addEventListener('click', () => {
        modal.style.display = 'block';
        document.body.classList.add('no-scroll');
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

    form1.addEventListener('submit', (e) => {
        e.preventDefault();
        formSubmitted = true;

        if (validateForm(form1)) {
            collectFormData(form1);
            form1.style.display = 'none';
            form2.style.display = 'block';
        } else {
            shakeInvalidFields(form1);
            focusFirstInvalidField(form1);
        }
    });

    form2.addEventListener('submit', (e) => {
        e.preventDefault();
        formSubmitted = true;

        if (validateForm(form2)) {
            collectFormData(form2);
            submitForm();
        } else {
            shakeInvalidFields(form2);
            focusFirstInvalidField(form2);
        }
    });

    [form1, form2].forEach(form => {
        form.addEventListener('input', (event) => {
            if (formSubmitted) {
                validateField(event.target);
            }
        });
    });

    function validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            if (input.name !== 'website' && !validateField(input)) {
                isValid = false;
            }
        });
        return isValid;
    }

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
                case 'Frequency':
                    if (value === '') {
                        isValid = false;
                        errorMessage = 'Please select a frequency';
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

    function shakeInvalidFields(form) {
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

    function focusFirstInvalidField(form) {
        const firstErrorField = form.querySelector('.form-field.error input, .form-field.error select');
        if (firstErrorField) {
            firstErrorField.focus();
        }
    }

    function collectFormData(form) {
        const formElements = form.elements;
        Array.from(formElements).forEach(element => {
            if (element.name) {
                if (element.name === 'Phone') {
                    formData[element.name] = element.value.replace(/\D/g, '');
                } else {
                    formData[element.name] = element.value;
                }
            }
        });
    }

    function submitForm() {
        fetch(zapier_form_rest.root + 'zapier-form/v1/submit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': zapier_form_rest.nonce
            },
            body: JSON.stringify(formData)
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

    function showMessage(message, type) {
        let messageContainer = document.querySelector('.form-message');
        if (!messageContainer) {
            messageContainer = document.createElement('div');
            messageContainer.className = 'form-message';
            messageContainer.setAttribute('role', 'alert');
            form1.parentElement.insertBefore(messageContainer, form1);
        }

        messageContainer.textContent = message;
        messageContainer.className = `form-message ${type}`;
    }

    function resetForm() {
        form1.reset();
        form2.reset();
        form1.style.display = 'block';
        form2.style.display = 'none';
        formData = {};

        [form1, form2].forEach(form => {
            const errorFields = form.querySelectorAll('.form-field');
            errorFields.forEach(field => {
                field.classList.remove('error');
            });

            const errorMessages = form.querySelectorAll('.error-message');
            errorMessages.forEach(message => {
                message.remove();
            });
        });

        const formMessage = document.querySelector('.form-message');
        if (formMessage) {
            formMessage.textContent = '';
            formMessage.className = 'form-message';
        }

        formSubmitted = false;
    }

    document.getElementById('Phone').addEventListener('input', (e) => {
        const cleaned = e.target.value.replace(/\D/g, '');
        const match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/);
        if (match) {
            e.target.value = `(${match[1]}) ${match[2]}-${match[3]}`;
        }
    });

    document.getElementById('Email').addEventListener('keypress', (e) => {
        if (e.which === 32) {
            e.preventDefault();
        }
    });

    ['FirstName', 'LastName'].forEach(id => {
        document.getElementById(id).addEventListener('input', (e) => {
            const val = e.target.value;
            if (val.length > 0) {
                e.target.value = val.charAt(0).toUpperCase() + val.slice(1);
            }
        });
    });
});
