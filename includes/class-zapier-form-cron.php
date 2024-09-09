<?php

class Zapier_Form_Cron {
    public function init() {
        add_action('wp', array($this, 'schedule_cron_job'));
        add_action('zapier_form_cron_event', array($this, 'handle_delayed_submissions'));
    }

    public function schedule_cron_job() {
        if (!wp_next_scheduled('zapier_form_cron_event')) {
            wp_schedule_event(time(), 'zapier_form_cron_interval', 'zapier_form_cron_event');
        }
    }

    public function handle_delayed_submissions() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'options';
        $transient_prefix = '_transient_zapier_form_step1_';
        
        $expired_transients = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name, option_value FROM $table_name
                WHERE option_name LIKE %s
                AND option_value < %d",
                $wpdb->esc_like($transient_prefix) . '%',
                time()
            )
        );

        error_log('Zapier Form Cron: Found ' . count($expired_transients) . ' expired transients');

        foreach ($expired_transients as $transient) {
            $transient_key = str_replace('_transient_', '', $transient->option_name);
            $step1_data = get_transient($transient_key);
            
            if ($step1_data) {
                error_log('Zapier Form Cron: Processing transient ' . $transient_key);
                $result = $this->submit_form_data($step1_data);
                error_log('Zapier Form Cron: Submission result - ' . print_r($result, true));
                delete_transient($transient_key);
            }
        }
    }

    private function submit_form_data($data) {
        $zapier_form = new Zapier_Form();
        $request = new WP_REST_Request('POST', '/zapier-form/v1/submit');
        $request->set_body_params($data);
        return $zapier_form->handle_form_submission($request);
    }
}

// Add custom cron interval
add_filter('cron_schedules', 'zapier_form_add_cron_interval');
function zapier_form_add_cron_interval($schedules) {
    $schedules['zapier_form_cron_interval'] = array(
        'interval' => 60, // Change to 60 seconds for testing
        'display'  => esc_html__('Zapier Form Cron Interval (Every Minute)'),
    );
    return $schedules;
}

// Add a way to manually trigger the cron job for testing
add_action('wp_ajax_trigger_zapier_form_cron', 'trigger_zapier_form_cron');
add_action('wp_ajax_nopriv_trigger_zapier_form_cron', 'trigger_zapier_form_cron');

function trigger_zapier_form_cron() {
    $cron = new Zapier_Form_Cron();
    $cron->handle_delayed_submissions();
    wp_die('Cron job triggered manually');
}
