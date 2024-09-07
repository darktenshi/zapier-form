<?php

if (!defined('ABSPATH')) {
    exit; 
}

class Zapier_Form_Admin {
    private $options;
    private $active_tab;

    public function init() {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    public function add_plugin_page() {
        add_options_page(
            'Zapier Form Settings', 
            'Zapier Form', 
            'manage_options', 
            'zapier-form', 
            array($this, 'create_admin_page')
        );
    }

    public function create_admin_page() {
        $this->options = get_option('zapier_form_options');
        $this->active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general_settings';
        ?>
        <div class="wrap">
            <h1>Zapier Form Settings</h1>
            <h2 class="nav-tab-wrapper">
                <a href="?page=zapier-form&tab=general_settings" class="nav-tab <?php echo $this->active_tab == 'general_settings' ? 'nav-tab-active' : ''; ?>">General Settings</a>
                <a href="?page=zapier-form&tab=setup_instructions" class="nav-tab <?php echo $this->active_tab == 'setup_instructions' ? 'nav-tab-active' : ''; ?>">Setup Instructions</a>
                <a href="?page=zapier-form&tab=plugin_information" class="nav-tab <?php echo $this->active_tab == 'plugin_information' ? 'nav-tab-active' : ''; ?>">Plugin Information</a>
            </h2>
            <?php
            if ($this->active_tab == 'general_settings') {
                $this->render_general_settings();
            } elseif ($this->active_tab == 'setup_instructions') {
                $this->render_setup_instructions();
            } elseif ($this->active_tab == 'plugin_information') {
                $this->render_plugin_information();
            }
            ?>
        </div>
        <?php
    }

    private function render_general_settings() {
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('zapier_form_option_group');
            do_settings_sections('zapier-form');
            submit_button();
            ?>
        </form>
        <h3>Gradient Preview</h3>
        <div id="gradient-preview"></div>
        <?php
    }

    private function render_setup_instructions() {
        include(plugin_dir_path(__FILE__) . 'admin-setup-instructions.php');
    }

    private function render_plugin_information() {
        include(plugin_dir_path(__FILE__) . 'admin-plugin-information.php');
    }

    public function print_section_info() {
        echo 'Enter your settings below:';
    }

    public function page_init() {
        register_setting(
            'zapier_form_option_group', 
            'zapier_form_options', 
            array($this, 'sanitize')
        );
    
        add_settings_section(
            'setting_section_id', 
            'Settings', 
            array($this, 'print_section_info'), 
            'zapier-form'
        );
    
        add_settings_field(
            'zapier_key', 
            'Zapier Key', 
            array($this, 'zapier_key_callback'), 
            'zapier-form', 
            'setting_section_id'
        );

        add_settings_field(
            'maidcentral_api_link', 
            'MaidCentral API Link', 
            array($this, 'maidcentral_api_link_callback'), 
            'zapier-form', 
            'setting_section_id'
        );

        add_settings_field(
            'submission_options', 
            'Submit To', 
            array($this, 'submission_options_callback'), 
            'zapier-form', 
            'setting_section_id'
        );

        add_settings_field(
            'zapier_button_color', 
            'Button Color', 
            array($this, 'zapier_button_color_callback'), 
            'zapier-form', 
            'setting_section_id'
        );
    
        add_settings_field(
            'zapier_button_hover_color', 
            'Button Hover Color', 
            array($this, 'zapier_button_hover_color_callback'), 
            'zapier-form', 
            'setting_section_id'
        );
    
        add_settings_field(
            'zapier_button_active_color', 
            'Button Active Color', 
            array($this, 'zapier_button_active_color_callback'), 
            'zapier-form', 
            'setting_section_id'
        );

        add_settings_field(
            'zapier_gradient_start', 
            'Gradient Start Color', 
            array($this, 'zapier_gradient_start_callback'), 
            'zapier-form', 
            'setting_section_id'
        );
        add_settings_field(
            'zapier_gradient_end', 
            'Gradient End Color', 
            array($this, 'zapier_gradient_end_callback'), 
            'zapier-form', 
            'setting_section_id'
        );
        add_settings_field(
            'zapier_heading_text_1', 
            'Heading Text (Part 1)', 
            array($this, 'zapier_heading_text_1_callback'), 
            'zapier-form', 
            'setting_section_id'
        );
        add_settings_field(
            'zapier_heading_text_2', 
            'Heading Text (Part 2 - Gradient)', 
            array($this, 'zapier_heading_text_2_callback'), 
            'zapier-form', 
            'setting_section_id'
        );
        add_settings_field(
            'zapier_redirect_url', 
            'Thank You Page URL (Optional)', 
            array($this, 'zapier_redirect_url_callback'), 
            'zapier-form', 
            'setting_section_id'
        );
        add_settings_field(
            'zapier_open_button_settings', 
            'Open Form Button', 
            array($this, 'zapier_open_button_settings_callback'), 
            'zapier-form', 
            'setting_section_id'
        );
        add_settings_field(
            'zapier_submit_button_settings', 
            'Submit Form Button', 
            array($this, 'zapier_submit_button_settings_callback'), 
            'zapier-form', 
            'setting_section_id'
        );
        add_settings_section(
            'setting_section_id',
            'Settings',
            array($this, 'print_section_info'),
            'zapier-form'
        );
        
    }
    public function sanitize($input) {
        $new_input = array();
        if (isset($input['zapier_key']))
            $new_input['zapier_key'] = sanitize_text_field($input['zapier_key']);
        
        if (isset($input['zapier_button_color']))
            $new_input['zapier_button_color'] = sanitize_hex_color($input['zapier_button_color']);
        
        if (isset($input['zapier_button_hover_color']))
            $new_input['zapier_button_hover_color'] = sanitize_hex_color($input['zapier_button_hover_color']);
        
        if (isset($input['zapier_button_active_color']))
            $new_input['zapier_button_active_color'] = sanitize_hex_color($input['zapier_button_active_color']);
    
        if (isset($input['zapier_gradient_start']))
            $new_input['zapier_gradient_start'] = sanitize_hex_color($input['zapier_gradient_start']);
    
        if (isset($input['zapier_gradient_end']))
            $new_input['zapier_gradient_end'] = sanitize_hex_color($input['zapier_gradient_end']);
    
        if (isset($input['zapier_heading_text_1']))
            $new_input['zapier_heading_text_1'] = sanitize_text_field($input['zapier_heading_text_1']);
    
        if (isset($input['zapier_heading_text_2']))
            $new_input['zapier_heading_text_2'] = sanitize_text_field($input['zapier_heading_text_2']);   
    
        if (isset($input['zapier_redirect_url']))
            $new_input['zapier_redirect_url'] = esc_url_raw($input['zapier_redirect_url']);    
    
        if (isset($input['zapier_open_button_text']))
            $new_input['zapier_open_button_text'] = sanitize_text_field($input['zapier_open_button_text']);
    
        if (isset($input['zapier_submit_button_text']))
            $new_input['zapier_submit_button_text'] = sanitize_text_field($input['zapier_submit_button_text']);
    
        $new_input['zapier_open_button_show_arrow'] = isset($input['zapier_open_button_show_arrow']) ? '1' : '0';
        $new_input['zapier_submit_button_show_arrow'] = isset($input['zapier_submit_button_show_arrow']) ? '1' : '0';
        
        if (isset($input['maidcentral_api_link']))
            $new_input['maidcentral_api_link'] = esc_url_raw($input['maidcentral_api_link']);

        $new_input['submit_to_zapier'] = isset($input['submit_to_zapier']) ? '1' : '0';
        $new_input['submit_to_maidcentral'] = isset($input['submit_to_maidcentral']) ? '1' : '0';
           
        return $new_input;
    }
    
    public function zapier_key_callback() {
        printf(
            '<input type="text" id="zapier_key" name="zapier_form_options[zapier_key]" value="%s" style="width: 33%%;" />',
            isset($this->options['zapier_key']) ? esc_attr($this->options['zapier_key']) : ''
        );
    }
    public function maidcentral_api_link_callback() {
        printf(
            '<input type="text" id="maidcentral_api_link" name="zapier_form_options[maidcentral_api_link]" value="%s" style="width: 33%%;" />',
            isset($this->options['maidcentral_api_link']) ? esc_attr($this->options['maidcentral_api_link']) : ''
        );
        echo '<p class="description">Enter the full URL for the MaidCentral API (e.g., https://castlekeepers.maidcentral.net/quoting/lead)</p>';
    }
    
    public function submission_options_callback() {
        $submit_to_zapier = isset($this->options['submit_to_zapier']) ? $this->options['submit_to_zapier'] : '1';
        $submit_to_maidcentral = isset($this->options['submit_to_maidcentral']) ? $this->options['submit_to_maidcentral'] : '0';
        
        echo '<label style="margin-right: 20px;">
            <input type="checkbox" id="submit_to_zapier" name="zapier_form_options[submit_to_zapier]" value="1"' . checked('1', $submit_to_zapier, false) . '/>
            Zapier
        </label>';
        
        echo '<label>
            <input type="checkbox" id="submit_to_maidcentral" name="zapier_form_options[submit_to_maidcentral]" value="1"' . checked('1', $submit_to_maidcentral, false) . '/>
            MaidCentral
        </label>';
    }
    public function render_color_picker_field($id, $name, $value, $default_color) {
        ?>
        <div class="zapier-color-field-wrapper">
            <input type="text" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" class="zapier-color-picker" data-default-color="<?php echo esc_attr($default_color); ?>" />
        </div>
        <?php
    }
    
    public function zapier_button_color_callback() {
        $this->render_color_picker_field(
            'zapier_button_color',
            'zapier_form_options[zapier_button_color]',
            isset($this->options['zapier_button_color']) ? esc_attr($this->options['zapier_button_color']) : '#db2777',
            '#db2777'
        );
    }
    
    public function zapier_button_hover_color_callback() {
        $this->render_color_picker_field(
            'zapier_button_hover_color',
            'zapier_form_options[zapier_button_hover_color]',
            isset($this->options['zapier_button_hover_color']) ? esc_attr($this->options['zapier_button_hover_color']) : '#9d174d',
            '#9d174d'
        );
    }
    
    public function zapier_button_active_color_callback() {
        $this->render_color_picker_field(
            'zapier_button_active_color',
            'zapier_form_options[zapier_button_active_color]',
            isset($this->options['zapier_button_active_color']) ? esc_attr($this->options['zapier_button_active_color']) : '#7f1d4e',
            '#7f1d4e'
        );
    }
    
    public function zapier_gradient_start_callback() {
        $this->render_color_picker_field(
            'zapier_gradient_start',
            'zapier_form_options[zapier_gradient_start]',
            isset($this->options['zapier_gradient_start']) ? esc_attr($this->options['zapier_gradient_start']) : '#6b21a8',
            '#6b21a8'
        );
    }
    
    public function zapier_gradient_end_callback() {
        $this->render_color_picker_field(
            'zapier_gradient_end',
            'zapier_form_options[zapier_gradient_end]',
            isset($this->options['zapier_gradient_end']) ? esc_attr($this->options['zapier_gradient_end']) : '#ff007f',
            '#ff007f'
        );
    }
    
    public function zapier_heading_text_1_callback() {
        printf(
            '<input type="text" id="zapier_heading_text_1" name="zapier_form_options[zapier_heading_text_1]" value="%s" style="width: 33%%;" />',
            isset($this->options['zapier_heading_text_1']) ? esc_attr($this->options['zapier_heading_text_1']) : 'Get your free, easy'
        );
    }
    
    public function zapier_heading_text_2_callback() {
        printf(
            '<input type="text" id="zapier_heading_text_2" name="zapier_form_options[zapier_heading_text_2]" value="%s" style="width: 33%%;" />',
            isset($this->options['zapier_heading_text_2']) ? esc_attr($this->options['zapier_heading_text_2']) : 'online estimate'
        );
    }
    
    public function zapier_redirect_url_callback() {
        $redirect_url = isset($this->options['zapier_redirect_url']) ? esc_url($this->options['zapier_redirect_url']) : '';
        ?>
        <input 
            type="url" 
            id="zapier_redirect_url" 
            name="zapier_form_options[zapier_redirect_url]" 
            value="<?php echo $redirect_url; ?>" 
            style="width: 33%" 
            placeholder="https://example.com/thank-you"
        />
        <p class="description">
            Enter the full URL (including https://) where users should be redirected after successful form submission. 
            Leave blank if you don't want to redirect users.
        </p>
        <p class="description">
            Example: https://yourwebsite.com/thank-you-page
        </p>
        <?php
    }
    
    public function enqueue_admin_scripts($hook_suffix) {
        if ('settings_page_zapier-form' !== $hook_suffix) {
            return;
        }
        
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        
        wp_enqueue_style(
            'zapier-form-admin',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/zapier-form.css',
            array(),
            '1.0.0'
        );

        
        wp_enqueue_script(
            'zapier-form-admin',
            plugin_dir_url(dirname(__FILE__)) . 'assets/js/zapier-form.js',
            array('jquery'),
            '1.0.0',
            true
        );

        
        wp_enqueue_script(
            'zapier-form-color-picker',
            plugin_dir_url(dirname(__FILE__)) . 'assets/js/zapier-form-color-picker.js',
            array('jquery', 'wp-color-picker'),
            '1.0.0',
            true
        );

       
        wp_add_inline_style('zapier-form-admin', '
            .nav-tab-wrapper {
                margin-bottom: 1em;
            }
            .nav-tab-wrapper .nav-tab {
                font-size: 14px;
            }
            .nav-tab-wrapper .nav-tab-active {
                background: #fff;
                border-bottom: 1px solid #fff;
            }
        ');
    }
    public function zapier_open_button_settings_callback() {
        $button_text = isset($this->options['zapier_open_button_text']) ? esc_attr($this->options['zapier_open_button_text']) : 'Request an Estimate';
        $show_arrow = isset($this->options['zapier_open_button_show_arrow']) ? $this->options['zapier_open_button_show_arrow'] : '1';
        ?>
        <input type="text" id="zapier_open_button_text" name="zapier_form_options[zapier_open_button_text]" value="<?php echo $button_text; ?>" style="width: 33%;" />
        <label style="margin-left: 10px;">
            <input type="checkbox" id="zapier_open_button_show_arrow" name="zapier_form_options[zapier_open_button_show_arrow]" value="1" <?php checked('1', $show_arrow); ?> />
            Show Arrow
        </label>
        <?php
    }

    public function zapier_submit_button_settings_callback() {
        $button_text = isset($this->options['zapier_submit_button_text']) ? esc_attr($this->options['zapier_submit_button_text']) : 'Submit Estimate';
        $show_arrow = isset($this->options['zapier_submit_button_show_arrow']) ? $this->options['zapier_submit_button_show_arrow'] : '1';
        ?>
        <input type="text" id="zapier_submit_button_text" name="zapier_form_options[zapier_submit_button_text]" value="<?php echo $button_text; ?>" style="width: 33%;" />
        <label style="margin-left: 10px;">
            <input type="checkbox" id="zapier_submit_button_show_arrow" name="zapier_form_options[zapier_submit_button_show_arrow]" value="1" <?php checked('1', $show_arrow); ?> />
            Show Arrow
        </label>
        <?php
    }

}

