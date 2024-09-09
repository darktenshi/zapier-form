<?php

class Zapier_Form_Multistep {
    private $lead_prefix = 'zapier_form_lead_';

    public function register_rest_routes() {
        register_rest_route('zapier-form/v1', '/load-step1', array(
            'methods' => 'GET',
            'callback' => array($this, 'load_step1'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('zapier-form/v1', '/submit-step1', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_step1_submission'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('zapier-form/v1', '/load-step2', array(
            'methods' => 'GET',
            'callback' => array($this, 'load_step2'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('zapier-form/v1', '/submit-step2', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_step2_submission'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('zapier-form/v1', '/finalize-submission', array(
            'methods' => 'POST',
            'callback' => array($this, 'finalize_submission'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('zapier-form/v1', '/log', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_client_log'),
            'permission_callback' => '__return_true'
        ));
    }

    public function load_step1() {
        $this->log_debug('load_step1 called');
        ob_start();
        include(ZFI_PLUGIN_DIR . 'includes/templates/form-step1.php');
        $html = ob_get_clean();
        $response = array('success' => true, 'html' => $html);
        $json_response = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json_response === false) {
            $this->log_debug('JSON encoding failed: ' . json_last_error_msg());
            return new WP_REST_Response(array('success' => false, 'message' => 'Internal server error'), 500);
        }
        $this->log_debug('load_step1 response: ' . $json_response);
        return new WP_REST_Response($response);
    }

    public function handle_client_log($request) {
        $params = $request->get_params();
        $type = isset($params['type']) ? sanitize_text_field($params['type']) : 'Unknown';
        $data = isset($params['data']) ? $params['data'] : array();
        
        $log_message = "Client Log - Type: $type, Data: " . json_encode($data);
        $this->log_debug($log_message);
        
        return new WP_REST_Response(array('success' => true));
    }

    private function log_debug($message) {
        error_log('[Zapier Form Debug] ' . $message);
    }

    public function load_step2($request) {
        $lead_id = sanitize_text_field($request->get_param('lead_id'));
        $step1_data = get_option($this->lead_prefix . $lead_id);

        if (!$step1_data) {
            return new WP_REST_Response(array('success' => false, 'message' => 'Step 1 data not found'));
        }

        ob_start();
        include(ZFI_PLUGIN_DIR . 'includes/templates/form-step2.php');
        $html = ob_get_clean();

        return new WP_REST_Response(array('success' => true, 'html' => $html));
    }

    public function handle_step1_submission($request) {
        $params = $request->get_params();
        $step1_data = array(
            'FirstName' => sanitize_text_field($params['FirstName']),
            'LastName' => sanitize_text_field($params['LastName']),
            'Email' => sanitize_email($params['Email']),
            'Phone' => sanitize_text_field($params['Phone']),
            'Zip' => sanitize_text_field($params['Zip']),
            'timestamp' => time()
        );

        $lead_id = uniqid();
        update_option($this->lead_prefix . $lead_id, $step1_data);

        return new WP_REST_Response(array('success' => true, 'lead_id' => $lead_id));
    }

    public function handle_step2_submission($request) {
        $params = $request->get_params();
        $lead_id = sanitize_text_field($params['lead_id']);
        $step1_data = get_option($this->lead_prefix . $lead_id);

        if (!$step1_data) {
            return new WP_REST_Response(array('success' => false, 'message' => 'Step 1 data not found'));
        }

        $step2_data = array(
            'HomeAddress1' => sanitize_text_field($params['HomeAddress1']),
            'HomeCity' => sanitize_text_field($params['HomeCity']),
            'HomeRegion' => sanitize_text_field($params['HomeRegion']),
            'HomeZip' => $step1_data['Zip'] // Use the ZIP from step 1
        );

        $complete_data = array_merge($step1_data, $step2_data);

        $submission_result = $this->submit_form_data($complete_data);

        delete_option($this->lead_prefix . $lead_id);

        return new WP_REST_Response($submission_result);
    }

    public function finalize_submission($request) {
        $lead_id = sanitize_text_field($request->get_param('lead_id'));
        $lead_data = get_option($this->lead_prefix . $lead_id);

        if (!$lead_data) {
            return new WP_REST_Response(array('success' => false, 'message' => 'Lead data not found'));
        }

        // Check if more than 5 minutes have passed since first submission
        if (time() - $lead_data['timestamp'] > 300) {
            $submission_result = $this->submit_form_data($lead_data);
            delete_option($this->lead_prefix . $lead_id);
            return new WP_REST_Response($submission_result);
        }

        return new WP_REST_Response(array('success' => true, 'message' => 'Submission not yet finalized'));
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

        $maidcentral_data = array(
            'FirstName' => $data['FirstName'],
            'LastName' => $data['LastName'],
            'Email' => $data['Email'],
            'Phone' => $data['Phone'],
            'PostalCode' => $data['Zip'],
            // Add other required fields for MaidCentral here
        );

        $response = wp_remote_post($maidcentral_api_link, array(
            'body' => json_encode($maidcentral_data),
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
}
