<?php

class Zapier_Form_Multistep extends Zapier_Form {
    private $step = 1;
    private $transient_prefix = 'zapier_form_step1_';

    public function init() {
        add_action('wp_ajax_zapier_form_step1', array($this, 'handle_step1_submission'));
        add_action('wp_ajax_nopriv_zapier_form_step1', array($this, 'handle_step1_submission'));
        add_action('wp_ajax_zapier_form_step2', array($this, 'handle_step2_submission'));
        add_action('wp_ajax_nopriv_zapier_form_step2', array($this, 'handle_step2_submission'));
        add_action('wp_ajax_zapier_form_load_step2', array($this, 'load_step2'));
        add_action('wp_ajax_nopriv_zapier_form_load_step2', array($this, 'load_step2'));
    }

    public function load_step2() {
        check_ajax_referer('zapier_form_nonce', 'nonce');
        $transient_key = sanitize_text_field($_GET['transient_key']);
        $step1_data = get_transient($transient_key);

        if (!$step1_data) {
            wp_send_json_error('Step 1 data not found or expired');
            return;
        }

        ob_start();
        include(ZFI_PLUGIN_DIR . 'includes/templates/form-step2.php');
        $html = ob_get_clean();

        wp_send_json_success(array('html' => $html));
    }

    public function render_form() {
        if ($this->step === 1) {
            $this->render_step1();
        } else {
            $this->render_step2();
        }
    }

    private function render_step1() {
        // Render the first step of the form (basic information)
        include(ZFI_PLUGIN_DIR . 'templates/form-step1.php');
    }

    private function render_step2() {
        // Render the second step of the form (additional details)
        $step1_data = $this->get_step1_data();
        include(ZFI_PLUGIN_DIR . 'templates/form-step2.php');
    }

    public function handle_step1_submission() {
        check_ajax_referer('zapier_form_nonce', 'nonce');

        $step1_data = array(
            'FirstName' => sanitize_text_field($_POST['FirstName']),
            'LastName' => sanitize_text_field($_POST['LastName']),
            'Email' => sanitize_email($_POST['Email']),
            'Phone' => sanitize_text_field($_POST['Phone']),
            'Zip' => sanitize_text_field($_POST['Zip'])
        );

        $transient_key = $this->transient_prefix . wp_generate_password(32, false);
        set_transient($transient_key, $step1_data, 5 * MINUTE_IN_SECONDS);

        wp_send_json_success(array('transient_key' => $transient_key));
    }

    public function handle_step2_submission() {
        check_ajax_referer('zapier_form_nonce', 'nonce');

        $transient_key = sanitize_text_field($_POST['transient_key']);
        $step1_data = get_transient($transient_key);

        if (!$step1_data) {
            wp_send_json_error('Step 1 data not found or expired');
            return;
        }

        $step2_data = array(
            'HomeAddress1' => sanitize_text_field($_POST['HomeAddress1']),
            'HomeCity' => sanitize_text_field($_POST['HomeCity']),
            'HomeRegion' => sanitize_text_field($_POST['HomeRegion']),
            'ScopeGroupId' => intval($_POST['ScopeGroupId']),
            'ScopeOfWorkId' => intval($_POST['ScopeOfWorkId']),
            'Frequency' => sanitize_text_field($_POST['Frequency']),
            'HomeBedrooms' => intval($_POST['HomeBedrooms']),
            'HomeFullBathrooms' => intval($_POST['HomeFullBathrooms']),
            'HomeSquareFeet' => intval($_POST['HomeSquareFeet'])
        );

        $complete_data = array_merge($step1_data, $step2_data);

        // Submit the complete data to Zapier and/or MaidCentral
        $submission_result = $this->submit_form_data($complete_data);

        // Clean up the transient
        delete_transient($transient_key);

        if ($submission_result['success']) {
            wp_send_json_success($submission_result['message']);
        } else {
            wp_send_json_error($submission_result['message']);
        }
    }

    private function get_step1_data() {
        $transient_key = isset($_GET['transient_key']) ? sanitize_text_field($_GET['transient_key']) : '';
        return get_transient($transient_key);
    }

    private function submit_form_data($data) {
        $options = get_option('zapier_form_options');
        $submit_to_zapier = isset($options['submit_to_zapier']) ? $options['submit_to_zapier'] : '1';
        $submit_to_maidcentral = isset($options['submit_to_maidcentral']) ? $options['submit_to_maidcentral'] : '0';

        $success_messages = array();
        $error_messages = array();

        if ($submit_to_zapier === '1') {
            $zapier_result = $this->submit_to_zapier($data);
            if ($zapier_result === true) {
                $success_messages[] = "Successfully submitted to Zapier.";
            } else {
                $error_messages[] = "Failed to submit to Zapier: " . $zapier_result;
            }
        }

        if ($submit_to_maidcentral === '1') {
            $maidcentral_result = $this->submit_to_maidcentral($data);
            if ($maidcentral_result === true) {
                $success_messages[] = "Successfully submitted to MaidCentral.";
            } else {
                $error_messages[] = "Failed to submit to MaidCentral: " . $maidcentral_result;
            }
        }

        // Log the submission
        $this->log_submission($data);

        $redirect_url = isset($options['zapier_redirect_url']) ? esc_url($options['zapier_redirect_url']) : '';

        if (empty($error_messages)) {
            return array(
                'success' => true,
                'message' => implode(" ", $success_messages),
                'redirect_url' => $redirect_url
            );
        } else {
            return array(
                'success' => false,
                'message' => implode(" ", array_merge($success_messages, $error_messages))
            );
        }
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

    private function submit_to_zapier($data) {
        $zapier_webhook = get_option('zapier_form_options')['zapier_key'];
        if (!$zapier_webhook) {
            return "Zapier webhook not configured";
        }

        $response = wp_remote_post($zapier_webhook, array(
            'body' => json_encode($data),
            'headers' => array('Content-Type' => 'application/json'),
        ));

        if (is_wp_error($response)) {
            return $response->get_error_message();
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            return "Unexpected response code: " . $response_code;
        }

        return true;
    }

    private function submit_to_maidcentral($data) {
        $maidcentral_api_link = get_option('zapier_form_options')['maidcentral_api_link'];
        if (!$maidcentral_api_link) {
            return "MaidCentral API link not configured";
        }

        $response = wp_remote_post($maidcentral_api_link, array(
            'body' => json_encode($data),
            'headers' => array('Content-Type' => 'application/json'),
        ));

        if (is_wp_error($response)) {
            return $response->get_error_message();
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            return "Unexpected response code: " . $response_code;
        }

        return true;
    }
}
