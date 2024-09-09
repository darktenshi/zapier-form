<?php
/*
Plugin Name: Zapier Form Modal
Description: A plugin that adds a customizable multi-step form with Zapier and MaidCentral integration.
Version: 3.1.0
Author: Managing Maids
*/

// TODO: Add constants for new features (e.g., WP_CRON_INTERVAL)
// TODO: Include new files for multi-step form and WP-Cron functionality

if (!defined('ABSPATH')) {
    exit;
}

define('ZFI_VERSION', '3.0.1');
define('ZFI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ZFI_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once ZFI_PLUGIN_DIR . 'includes/class-zapier-form.php';
require_once ZFI_PLUGIN_DIR . 'includes/class-zapier-form-admin.php';

function run_zapier_form_integration() {
    $plugin = new Zapier_Form();
    $plugin->init();

    $admin = new Zapier_Form_Admin();
    $admin->init();
}
add_action('plugins_loaded', 'run_zapier_form_integration');
