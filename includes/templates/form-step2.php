<form id="zapier-form-step2" class="zapier-form">
    <?php wp_nonce_field('zapier_form_nonce', 'zapier_form_nonce'); ?>
    <input type="hidden" name="transient_key" value="<?php echo esc_attr($transient_key); ?>">
    <h3>Additional Information</h3>
    <p>Thank you, <?php echo esc_html($step1_data['FirstName']); ?>! Please provide some additional details about your cleaning needs:</p>
    <div class="form-grid">
        <div class="form-field">
            <input type="text" id="HomeAddress1" name="HomeAddress1" required placeholder=" ">
            <label for="HomeAddress1">Street Address</label>
            <div class="error-message"></div>
        </div>
        <div class="form-field">
            <input type="text" id="HomeCity" name="HomeCity" required placeholder=" ">
            <label for="HomeCity">City</label>
            <div class="error-message"></div>
        </div>
        <div class="form-field">
            <input type="text" id="HomeRegion" name="HomeRegion" required placeholder=" ">
            <label for="HomeRegion">State</label>
            <div class="error-message"></div>
        </div>
        <div class="form-field">
            <select id="ScopeGroupId" name="ScopeGroupId" required>
                <option value="">Select Service Type</option>
                <option value="1">Recurring Service</option>
                <option value="2">Deep Clean</option>
                <!-- Add more options as needed -->
            </select>
            <div class="error-message"></div>
        </div>
        <div class="form-field">
            <select id="ScopeOfWorkId" name="ScopeOfWorkId" required>
                <option value="">Select Scope of Work</option>
                <!-- Options will be populated dynamically based on ScopeGroupId -->
            </select>
            <div class="error-message"></div>
        </div>
        <div class="form-field">
            <select id="Frequency" name="Frequency" required>
                <option value="">Select Frequency</option>
                <option value="weekly">Weekly</option>
                <option value="biweekly">Bi-weekly</option>
                <option value="monthly">Monthly</option>
            </select>
            <div class="error-message"></div>
        </div>
        <div class="form-field">
            <input type="number" id="HomeBedrooms" name="HomeBedrooms" required placeholder=" ">
            <label for="HomeBedrooms">Number of Bedrooms</label>
            <div class="error-message"></div>
        </div>
        <div class="form-field">
            <input type="number" id="HomeFullBathrooms" name="HomeFullBathrooms" required placeholder=" ">
            <label for="HomeFullBathrooms">Number of Bathrooms</label>
            <div class="error-message"></div>
        </div>
        <div class="form-field">
            <input type="number" id="HomeSquareFeet" name="HomeSquareFeet" required placeholder=" ">
            <label for="HomeSquareFeet">Square Footage</label>
            <div class="error-message"></div>
        </div>
    </div>
    <div class="form-submit">
        <button type="submit" class="zapier-form-button">
            Submit
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
</form>
