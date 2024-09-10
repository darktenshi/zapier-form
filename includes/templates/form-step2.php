<form id="zapier-form-step2" class="zapier-form">
    <?php wp_nonce_field('zapier_form_nonce', 'zapier_form_nonce'); ?>
    <input type="hidden" name="lead_id" value="<?php echo esc_attr($lead_id); ?>">
    <h3>Additional Information</h3>
    <p>Thank you, <?php echo esc_html($step1_data['FirstName']); ?>! Please provide some additional details:</p>
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
            <input type="text" id="HomeZip" name="HomeZip" value="<?php echo esc_attr($step1_data['Zip']); ?>" readonly placeholder=" ">
            <label for="HomeZip">Zip Code</label>
            <div class="error-message"></div>
        </div>
        <div class="form-field">
            <select id="Frequency" name="Frequency" required aria-label="Cleaning Frequency">
                <option value="">Select Frequency</option>
                <?php
                $options = get_option('zapier_form_options');
                $frequencies = array(
                    'E1' => 'Every Week',
                    'E2' => 'Every Two Weeks',
                    'E3' => 'Every Three Weeks',
                    'E4' => 'Every Four Weeks',
                    'S' => 'One Time Clean',
                    'OD' => 'On Demand',
                    'OR' => 'Other Recurring'
                );
                foreach ($frequencies as $value => $label) {
                    if (isset($options['frequencies'][$value]) && $options['frequencies'][$value] == '1') {
                        echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
                    }
                }
                ?>
            </select>
            <div class="error-message"></div>
        </div>
        <div class="form-field">
            <input type="number" id="HomeSquareFeet" name="HomeSquareFeet" required placeholder=" " min="1">
            <label for="HomeSquareFeet">Square Footage</label>
            <div class="error-message"></div>
        </div>
        <div class="form-field">
            <select id="HomeBedrooms" name="HomeBedrooms" required aria-label="Number of Bedrooms">
                <?php for ($i = 1; $i <= 10; $i++) : ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?> Bedroom<?php echo $i > 1 ? 's' : ''; ?></option>
                <?php endfor; ?>
            </select>
            <div class="error-message"></div>
        </div>
        <div class="form-field">
            <select id="HomeFullBathrooms" name="HomeFullBathrooms" required aria-label="Number of Full Bathrooms">
                <?php for ($i = 1; $i <= 10; $i++) : ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?> Bathroom<?php echo $i > 1 ? 's' : ''; ?></option>
                <?php endfor; ?>
            </select>
            <div class="error-message"></div>
        </div>
        <?php
        $options = get_option('zapier_form_options');
        $show_half_bathrooms = isset($options['show_half_bathrooms']) ? $options['show_half_bathrooms'] : '0';
        if ($show_half_bathrooms === '1') :
        ?>
        <div class="form-field">
            <select id="HomeHalfBathrooms" name="HomeHalfBathrooms" required aria-label="Number of Half Bathrooms">
                <?php for ($i = 0; $i <= 10; $i++) : ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?> Half Bathroom<?php echo $i != 1 ? 's' : ''; ?></option>
                <?php endfor; ?>
            </select>
            <div class="error-message"></div>
        </div>
        <?php endif; ?>
        <input type="hidden" id="HomeHalfBathrooms" name="HomeHalfBathrooms" value="0">
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
