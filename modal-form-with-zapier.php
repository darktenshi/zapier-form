<?php
/*
Plugin Name: Zapier Form Modal
Description: A plugin that adds a customizable form with Zapier integration.
Version: 1.0
Author: Chris Ewers
*/

if (!defined('ABSPATH')) {
    exit;
}

define('ZFI_VERSION', '1.0.0');
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
