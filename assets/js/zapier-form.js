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
                leadId = data.lead_id;
                loadStep2(leadId);
                startSubmissionTimer();
            } else {
                showMessage(data.message || 'An error occurred. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred. Please try again.', 'error');
        });
    }

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

    function startSubmissionTimer() {
        submissionTimer = setTimeout(() => {
            finalizeSubmission(leadId);
        }, 300000); // 5-minute timeout
    }

    function finalizeSubmission(leadId) {
        fetch(`${zapier_form_rest.root}zapier-form/v1/finalize-submission`, {
            method: 'POST',
            headers: {
                'X-WP-Nonce': zapier_form_rest.nonce,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ lead_id: leadId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Submission finalized:', data.message);
                resetForm();
                modal.style.display = 'none';
            } else {
                console.error('Failed to finalize submission:', data.message);
            }
        })
        .catch(error => {
            console.error('Error finalizing submission:', error);
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
