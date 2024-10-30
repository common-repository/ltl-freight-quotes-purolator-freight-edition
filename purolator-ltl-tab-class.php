<?php
/**
 * Purolator Woocommerce Setting Tab Class
 * @package     Woocommerce Purolator Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Woocommerce Setting Tab Class
 */
class WC_Settings_Purolator_LTL_Freight extends WC_Settings_Page
{
    /**
     * Woocommerce Setting Tab Constructor
     */
    public function __construct()
    {
        $this->id = 'purolator_ltl_quotes';
        add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_tab'), 50);
        add_action('woocommerce_sections_' . $this->id, array($this, 'output_sections'));
        add_action('woocommerce_settings_' . $this->id, array($this, 'output'));
        add_action('woocommerce_settings_save_' . $this->id, array($this, 'save'));
    }

    /**
     * purolator_ltl Setting Tab For Woocommerce
     * @param $settings_tabs
     * @return string
     */
    public function add_settings_tab($settings_tabs)
    {
        $settings_tabs[$this->id] = __('Purolator Freight', 'config_settings_purolator_ltl_quotes');
        return $settings_tabs;
    }

    /**
     * purolator_ltl Setting Sections
     * @return array
     */
    public function get_sections()
    {
        $sections = array(
            '' => __('Connection Settings', 'config_settings_purolator_ltl_quotes'),
            'section-1' => __('Quote Settings', 'config_settings_purolator_ltl_quotes'),
            'section-2' => __('Warehouses', 'config_settings_purolator_ltl_quotes'),
            'section-3' => __('User Guide', 'config_settings_purolator_ltl_quotes'),
        );

        // Logs data
        $enable_logs = get_option('enable_logs_purolator_ltl');
        if ($enable_logs == 'yes') {
            $sections['en-logs'] = 'Logs';
        }

        return apply_filters('woocommerce_get_sections_' . $this->id, $sections);
    }

    /**
     * purolator_ltl Warehouse Tab
     */
    public function purolator_ltl_warehouse()
    {
        require_once 'warehouse-dropship/wild/warehouse/warehouse_template.php';
        require_once 'warehouse-dropship/wild/dropship/dropship_template.php';
    }

    /**
     * purolator_ltl User Guide Tab
     */
    public function purolator_ltl_user_guide()
    {
        include_once('template/guide.php');
    }

    /**
     * purolator_ltl settings
     * @param $section
     * @return array
     */
    public function get_settings($section = null)
    {
        ob_start();
        switch ($section) {
            case 'section-0' :
                $settings = Purolator_LTL_Connection_Settings::purolator_ltl_con_setting();
                break;

            case 'section-1' :
                $purolator_ltl_quote_Settings = new Purolator_LTL_Quote_Settings();
                $settings = $purolator_ltl_quote_Settings->purolator_ltl_quote_settings_tab();
                break;

            case 'section-2':
                $this->purolator_ltl_warehouse();
                $settings = array();
                break;

            case 'section-3' :
                $this->purolator_ltl_user_guide();
                $settings = array();
                break;

            case 'en-logs' :
                require_once 'logs/en-logs.php';
                $settings = [];
                break;

            default:
                $purolator_ltl_con_settings = new purolator_ltl_Connection_Settings();
                $settings = $purolator_ltl_con_settings->purolator_ltl_con_setting();

                break;
        }

        return apply_filters('config_settings_purolator_ltl_quotes', $settings, $section);
    }

    /**
     * purolator_ltl setting output
     * @global $current_section
     */
    public function output()
    {
        global $current_section;
        $settings = $this->get_settings($current_section);
        WC_Admin_Settings::output_fields($settings);
    }

    /**
     * purolator_ltl Save Settings
     * @global $current_section
     */
    public function save()
    {
        global $current_section;
        $settings = $this->get_settings($current_section);
        WC_Admin_Settings::save_fields($settings);
    }
}

return new WC_Settings_purolator_ltl_Freight();