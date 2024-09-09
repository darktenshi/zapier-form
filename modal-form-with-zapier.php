<?php
/*
Plugin Name: Zapier Form Modal
Description: A plugin that adds a customizable multi-step form with Zapier and MaidCentral integration.
Version: 3.1.0
Author: Managing Maids
*/

if (!defined('ABSPATH')) {
    exit;
}

define('ZFI_VERSION', '3.1.0');
define('ZFI_CRON_INTERVAL', 5 * MINUTE_IN_SECONDS); // 5 minutes
define('ZFI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ZFI_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once ZFI_PLUGIN_DIR . 'includes/class-zapier-form.php';
require_once ZFI_PLUGIN_DIR . 'includes/class-zapier-form-admin.php';
require_once ZFI_PLUGIN_DIR . 'includes/class-zapier-form-multistep.php';
require_once ZFI_PLUGIN_DIR . 'includes/class-zapier-form-cron.php';

function run_zapier_form_integration() {
    $plugin = new Zapier_Form();
    $plugin->init();

    $admin = new Zapier_Form_Admin();
    $admin->init();

    $cron = new Zapier_Form_Cron();
    $cron->init();
}
add_action('plugins_loaded', 'run_zapier_form_integration');
