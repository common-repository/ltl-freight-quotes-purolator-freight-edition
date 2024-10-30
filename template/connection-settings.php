<?php
/**
 * Purolator Connection Settings Tab Class
 * @package     Woocommerce Purolator Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}
/**
 * Purolator Connection Settings Tab Class
 */
class Purolator_LTL_Connection_Settings
{
    /**
     * Connection Settings Fields
     * @return Connection Settings Array
     */
    public function purolator_ltl_con_setting() 
    {
        echo '<div class="connection_section_class_purolator_ltl">';
        $settings = array(
            'section_title_purolator_ltl' => array(
                'name'          => __('', 'config_settings_purolator_ltl_quotes'),
                'type'          => 'title',
                'desc'          => '<br> ',
                'id'            => 'purolator_ltl_title_section_connection',
            ),

            'production_key_purolator_ltl' => array(
                'name'          => __('Production Key', 'config_settings_purolator_ltl_quotes'),
                'type'          => 'text',
                'desc'          => __('', 'config_settings_purolator_ltl_quotes'),
                'id'            => 'purolator_ltl_production_key'
            ),

            'production_password_purolator_ltl' => array(
                'name'          => __('Production Password', 'config_settings_purolator_ltl_quotes'),
                'type'          => 'text',
                'desc'          => __('', 'config_settings_purolator_ltl_quotes'),
                'id'            => 'purolator_ltl_production_password'
            ),

            'acc_number_purolator_ltl' => array(
                'name'          => __('Registered Account Number', 'config_settings_purolator_ltl_quotes'),
                'type'          => 'text',
                'desc'          => __('', 'config_settings_purolator_ltl_quotes'),
                'id'            => 'purolator_ltl_reg_acc_num'
            ),

            'plugin_licence_key_purolator_ltl' => array(
                'name'          => __('Eniture API Key ', 'config_settings_purolator_ltl_quotes'),
                'type'          => 'text',
                'desc'          => __('Obtain a Eniture API Key Key from <a href="https://eniture.com/products/" target="_blank" >eniture.com </a>', 'config_settings_purolator_ltl_quotes'),
                'id'            => 'purolator_ltl_plugin_licence_key'
            ),

            'purolator_ltl_access_level' => array(
                'name'       => __('Access Level', 'ups_freight_wc_settings'),
                'id'         => 'purolator_ltl_setting_acccess_level',
                'class'      => 'purolator_ltl_setting_acccess_level',
                'type'       => 'radio',
                'default'    => 'pro',
                'options'    => array(
                    'test'      => __('Testing', 'woocommerce'),
                    'pro'       => __('Production', 'woocommerce')
                )
            ),

            'section_end_purolator_ltl'   => array(
                'type'          => 'sectionend',
                'id'            => 'purolator_ltl_plugin_licence_key'
            ),
        );

        return $settings;
    }
}