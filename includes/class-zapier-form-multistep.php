<?php

class Zapier_Form_Multistep extends Zapier_Form {
    private $step = 1;
    private $transient_prefix = 'zapier_form_step1_';

    public function init() {
        add_action('wp_ajax_zapier_form_step1', array($this, 'handle_step1_submission'));
        add_action('wp_ajax_nopriv_zapier_form_step1', array($this, 'handle_step1_submission'));
        add_action('wp_ajax_zapier_form_step2', array($this, 'handle_step2_submission'));
        add_action('wp_ajax_nopriv_zapier_form_step2', array($this, 'handle_step2_submission'));
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
        // Implement the logic to submit data to Zapier and/or MaidCentral
        // This method should return an array with 'success' and 'message' keys
    }
}
