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
            <select id="HomeRegion" name="HomeRegion" required>
                <option value="">Select State</option>
                <?php
                $states = array(
                    'AL'=>'Alabama', 'AK'=>'Alaska', 'AZ'=>'Arizona', 'AR'=>'Arkansas', 'CA'=>'California',
                    'CO'=>'Colorado', 'CT'=>'Connecticut', 'DE'=>'Delaware', 'DC'=>'District of Columbia', 'FL'=>'Florida',
                    'GA'=>'Georgia', 'HI'=>'Hawaii', 'ID'=>'Idaho', 'IL'=>'Illinois', 'IN'=>'Indiana',
                    'IA'=>'Iowa', 'KS'=>'Kansas', 'KY'=>'Kentucky', 'LA'=>'Louisiana', 'ME'=>'Maine',
                    'MD'=>'Maryland', 'MA'=>'Massachusetts', 'MI'=>'Michigan', 'MN'=>'Minnesota', 'MS'=>'Mississippi',
                    'MO'=>'Missouri', 'MT'=>'Montana', 'NE'=>'Nebraska', 'NV'=>'Nevada', 'NH'=>'New Hampshire',
                    'NJ'=>'New Jersey', 'NM'=>'New Mexico', 'NY'=>'New York', 'NC'=>'North Carolina', 'ND'=>'North Dakota',
                    'OH'=>'Ohio', 'OK'=>'Oklahoma', 'OR'=>'Oregon', 'PA'=>'Pennsylvania', 'RI'=>'Rhode Island',
                    'SC'=>'South Carolina', 'SD'=>'South Dakota', 'TN'=>'Tennessee', 'TX'=>'Texas', 'UT'=>'Utah',
                    'VT'=>'Vermont', 'VA'=>'Virginia', 'WA'=>'Washington', 'WV'=>'West Virginia', 'WI'=>'Wisconsin',
                    'WY'=>'Wyoming'
                );
                $default_state = isset($options['default_state']) ? $options['default_state'] : '';
                foreach ($states as $abbr => $name) {
                    $selected = ($abbr === $default_state) ? 'selected' : '';
                    echo "<option value=\"$abbr\" $selected>$name</option>";
                }
                ?>
            </select>
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
