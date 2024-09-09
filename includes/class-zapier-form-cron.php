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

        foreach ($expired_transients as $transient) {
            $transient_key = str_replace('_transient_', '', $transient->option_name);
            $step1_data = get_transient($transient_key);
            
            if ($step1_data) {
                $this->submit_form_data($step1_data);
                delete_transient($transient_key);
            }
        }
    }

    private function submit_form_data($data) {
        $zapier_form = new Zapier_Form();
        $zapier_form->handle_form_submission(new WP_REST_Request('POST', '/zapier-form/v1/submit', $data));
    }
}

// Add custom cron interval
add_filter('cron_schedules', 'zapier_form_add_cron_interval');
function zapier_form_add_cron_interval($schedules) {
    $schedules['zapier_form_cron_interval'] = array(
        'interval' => ZFI_CRON_INTERVAL,
        'display'  => esc_html__('Zapier Form Cron Interval'),
    );
    return $schedules;
}
