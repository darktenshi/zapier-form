<?php
class Zapier_Form {
    private $submission_count = array();

    public function init() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('zapier_form', array($this, 'render_form_button'));
        add_action('wp_footer', array($this, 'render_modal'));
        add_action('rest_api_init', array($this, 'register_rest_route'));
    }

    public function enqueue_scripts() {
        wp_enqueue_style('zapier-form-style', ZFI_PLUGIN_URL . 'assets/css/zapier-form.css', array(), ZFI_VERSION);
        wp_enqueue_script('zapier-form-script', ZFI_PLUGIN_URL . 'assets/js/zapier-form.js', array('jquery'), ZFI_VERSION, true);
        wp_localize_script('zapier-form-script', 'zapier_form_rest', array(
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest')
        ));

        $this->add_dynamic_styles();
    }

    public function add_dynamic_styles() {
        $options = get_option('zapier_form_options');
        $button_color = isset($options['zapier_button_color']) ? esc_attr($options['zapier_button_color']) : '#db2777';
        $button_hover_color = isset($options['zapier_button_hover_color']) ? esc_attr($options['zapier_button_hover_color']) : '#9d174d';
        $button_active_color = isset($options['zapier_button_active_color']) ? esc_attr($options['zapier_button_active_color']) : '#7f1d4e';

        $custom_css = "
            .zapier-form button[type='submit'],
            .zapier-form-button {
                background-color: {$button_color} !important;
            }
            .zapier-form button[type='submit']:hover,
            .zapier-form-button:hover {
                background-color: {$button_hover_color} !important;
            }
            .zapier-form button[type='submit']:active,
            .zapier-form-button:active {
                background-color: {$button_active_color} !important;
            }
            .zapier-form .form-field input:focus {
                border-color: {$button_color} !important;
                box-shadow: 0 0 0 1px {$button_color} !important;
            }
            .zapier-form .form-field input:focus ~ label,
            .zapier-form .form-field input:not(:placeholder-shown) ~ label {
                color: {$button_color} !important;
            }
        ";
        wp_add_inline_style('zapier-form-style', $custom_css);
    }

    public function register_rest_route() {
        register_rest_route('zapier-form/v1', '/submit', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_form_submission'),
            'permission_callback' => '__return_true'
        ));
    }

    public function handle_form_submission($request) {
        
        if (!wp_verify_nonce($request->get_header('X-WP-Nonce'), 'wp_rest')) {
            return new WP_Error('invalid_nonce', 'Invalid nonce', array('status' => 403));
        }

        
        $ip = $_SERVER['REMOTE_ADDR'];
        $current_time = time();
        if (!isset($this->submission_count[$ip])) {
            $this->submission_count[$ip] = array('count' => 0, 'time' => $current_time);
        }
        if ($current_time - $this->submission_count[$ip]['time'] > 3600) {
            $this->submission_count[$ip] = array('count' => 1, 'time' => $current_time);
        } else {
            $this->submission_count[$ip]['count']++;
        }
        if ($this->submission_count[$ip]['count'] > 5) {
            return new WP_Error('rate_limit_exceeded', 'Too many submissions. Please try again later.', array('status' => 429));
        }

        $params = $request->get_params();

        
        if (!empty($params['website'])) {
            
            return new WP_Error('bot_detected', 'Bot submission detected', array('status' => 403));
        }

        $fields = array(
            'FirstName' => 'First Name',
            'LastName' => 'Last Name',
            'Email' => 'Email',
            'Phone' => 'Phone',
            'Zip' => 'Zip Code'
        );
        $data = array();
        $errors = array();

        foreach ($fields as $field => $label) {
            if (!isset($params[$field]) || empty($params[$field])) {
                $errors[$field] = "$label is required.";
            } else {
                $data[$field] = sanitize_text_field($params[$field]);
            }
        }

        
        if (!empty($data['Email'])) {
            $data['Email'] = sanitize_email($data['Email']);
            if (!is_email($data['Email'])) {
                $errors['Email'] = "Please enter a valid email address.";
            }
        }

        if (!empty($data['Phone'])) {
            $data['Phone'] = preg_replace('/[^0-9]/', '', $data['Phone']);
            if (strlen($data['Phone']) !== 10) {
                $errors['Phone'] = "Please enter a valid 10-digit phone number.";
            }
        }

        if (!empty($data['Zip'])) {
            if (!preg_match('/^\d{5}(-\d{4})?$/', $data['Zip'])) {
                $errors['Zip'] = "Please enter a valid ZIP code.";
            }
        }

        if (!empty($errors)) {
            return new WP_Error('validation_failed', 'Validation errors', array(
                'status' => 400,
                'errors' => $errors
            ));
        }

        $zapier_webhook = get_option('zapier_form_options')['zapier_key'];
        if (!$zapier_webhook) {
            return new WP_Error('webhook_not_configured', 'Zapier webhook not configured', array('status' => 500));
        }

        $response = wp_remote_post($zapier_webhook, array(
            'body' => json_encode($data),
            'headers' => array('Content-Type' => 'application/json'),
        ));

        if (is_wp_error($response)) {
            return new WP_Error('submission_failed', 'Error submitting form', array('status' => 500));
        }

        
        $this->log_submission($data);

        $options = get_option('zapier_form_options');
        $redirect_url = isset($options['zapier_redirect_url']) ? esc_url($options['zapier_redirect_url']) : '';

        return new WP_REST_Response(array(
            'message' => 'Form submitted successfully',
            'redirect_url' => $redirect_url
        ), 200);
    }

    private function log_submission($data) {
        
        $log_data = array(
            'timestamp' => current_time('mysql'),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'form_data' => array(
                'FirstName' => substr($data['FirstName'], 0, 1) . '****',
                'LastName' => substr($data['LastName'], 0, 1) . '****',
                'Email' => '****@' . substr(strrchr($data['Email'], "@"), 1),
                'Phone' => '******' . substr($data['Phone'], -4),
                'Zip' => $data['Zip']
            )
        );
        error_log('Form submission: ' . json_encode($log_data));
    }

    public function render_form_button() {
        $options = get_option('zapier_form_options');
        $button_text = isset($options['zapier_open_button_text']) ? esc_html($options['zapier_open_button_text']) : 'Request an Estimate';
        $show_arrow = isset($options['zapier_open_button_show_arrow']) ? $options['zapier_open_button_show_arrow'] : '1';

        $arrow_svg = $show_arrow === '1' ? '<svg xmlns="http://www.w3.org/2000/svg" height="1.1em" viewBox="0 -960 960 960" width="1.1em" fill="currentColor" class="ml-2">
            <path d="M584-412H182q-29 0-48.5-19.5T114-480q0-28 19.5-48t48.5-20h402L431-701q-20-20-20.5-48.5T431-798q20-20 48.5-20t48.5 21l270 269q9 10 14.5 22.5T818-480q0 14-5.5 26.5T798-431L528-162q-20 21-48 21t-48-21q-21-20-21-48.5t21-48.5l152-153Z"></path>
        </svg>' : '';

        return '
        <button id="open-zapier-form" class="zapier-form-button">
            ' . $button_text . $arrow_svg . '
        </button>';
    }
    
    public function render_modal() {
        $options = get_option('zapier_form_options');
        $heading_text_1 = isset($options['zapier_heading_text_1']) ? esc_html($options['zapier_heading_text_1']) : 'Get your free, easy';
        $heading_text_2 = isset($options['zapier_heading_text_2']) ? esc_html($options['zapier_heading_text_2']) : 'online estimate';
        $gradient_start = isset($options['zapier_gradient_start']) ? esc_attr($options['zapier_gradient_start']) : '#6b21a8';
        $gradient_end = isset($options['zapier_gradient_end']) ? esc_attr($options['zapier_gradient_end']) : '#ff007f';
        $gradient_style = "background: linear-gradient(90deg, {$gradient_start} 0%, {$gradient_end} 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;";
        $submit_button_text = isset($options['zapier_submit_button_text']) ? esc_html($options['zapier_submit_button_text']) : 'Submit Estimate';
        $show_arrow = isset($options['zapier_submit_button_show_arrow']) ? $options['zapier_submit_button_show_arrow'] : '1';
        
        $arrow_svg = $show_arrow === '1' ? '<svg xmlns="http://www.w3.org/2000/svg" height="1.1em" viewBox="0 -960 960 960" width="1.1em" fill="currentColor" class="ml-2">
            <path d="M584-412H182q-29 0-48.5-19.5T114-480q0-28 19.5-48t48.5-20h402L431-701q-20-20-20.5-48.5T431-798q20-20 48.5-20t48.5 21l270 269q9 10 14.5 22.5T818-480q0 14-5.5 26.5T798-431L528-162q-20 21-48 21t-48-21q-21-20-21-48.5t21-48.5l152-153Z"></path>
        </svg>' : '';
        ?>
        <div id="zapier-form-modal" class="zapier-modal">
            <div class="zapier-modal-content">
                <button class="zapier-modal-close absolute top-[-24px] right-2 text-white">close</button>
                <h2 class="font-size-large font-bold-extra margin-bottom-medium align-center text-black">
                    <?php echo $heading_text_1; ?>
                    <span class="text-gradient" style="<?php echo $gradient_style; ?>"><?php echo $heading_text_2; ?></span>
                </h2>
                <div class="form-message" role="alert"></div>
                <form id="zapier-form" class="zapier-form" novalidate>
                    <noscript>
                        <div class="error-message">
                            JavaScript is required for this form to function properly. Please enable JavaScript in your browser settings and reload the page.
                        </div>
                    </noscript>
                    <div class="form-grid">
                        <div class="form-field">
                            <input type="text" id="FirstName" name="FirstName" required placeholder=" " aria-required="true">
                            <label for="FirstName">First Name</label>
                        </div>
                        <div class="form-field">
                            <input type="text" id="LastName" name="LastName" required placeholder=" " aria-required="true">
                            <label for="LastName">Last Name</label>
                        </div>
                        <div class="form-field">
                            <input type="email" id="Email" name="Email" required placeholder=" " aria-required="true">
                            <label for="Email">Email</label>
                        </div>
                        <div class="form-field">
                            <input type="tel" id="Phone" name="Phone" required placeholder=" " aria-required="true">
                            <label for="Phone">Phone</label>
                        </div>
                        <div class="form-field">
                            <input type="text" id="Zip" name="Zip" required placeholder=" " aria-required="true">
                            <label for="Zip">Zip code</label>
                        </div>
                        <div class="form-field" style="display:none;">
                            <input type="text" id="website" name="website" autocomplete="off" tabindex="-1">
                        </div>
                    </div>
                    <div class="form-submit">
                        <button type="submit" class="zapier-form-button">
                            <?php echo $submit_button_text . $arrow_svg; ?>
                        </button>
                    </div>
                </form>
                
            </div>
        </div>
        <?php
    }
}