<?php
/*
Plugin Name: Zapier Form Modal
Description: A plugin that adds a customizable multi-step form with Zapier and MaidCentral integration.
Version: 3.2.0
Author: Managing Maids
*/

if (!defined('ABSPATH')) {
    exit;
}

define('ZFI_VERSION', '3.2.0');
define('ZFI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ZFI_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once ZFI_PLUGIN_DIR . 'includes/class-zapier-form.php';
require_once ZFI_PLUGIN_DIR . 'includes/class-zapier-form-admin.php';
require_once ZFI_PLUGIN_DIR . 'includes/class-zapier-form-multistep.php';

function run_zapier_form_integration() {
    $plugin = new Zapier_Form();
    $plugin->init();

    $admin = new Zapier_Form_Admin();
    $admin->init();

    $multistep = new Zapier_Form_Multistep();
    add_action('rest_api_init', array($multistep, 'register_rest_routes'));
}
add_action('plugins_loaded', 'run_zapier_form_integration');

// Register REST routes for Zapier_Form_Multistep
function register_zapier_form_rest_routes() {
    $multistep = new Zapier_Form_Multistep();
    $multistep->register_rest_routes();
}
add_action('rest_api_init', 'register_zapier_form_rest_routes');
