<?php

/**
 * Purolator WooComerce Get Shipping Package Class
 * @package     Woocommerce Purolator Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Purolator Class For Quote Settings Tab
 */
class Purolator_LTL_Quote_Settings {

    /**
     * Quote Setting Fields
     * @return Quote Setting Fields Array
     */
    function purolator_ltl_quote_settings_tab() {


        $disable_residential_delivery = "";
        $residential_delivery_required = "";
        $disable_tailgate_delivery = "";
        $tailgate_delivery_required = "";

        $ltl_enable = get_option('en_plugins_return_LTL_quotes');
        $weight_threshold_class = $ltl_enable == 'yes' ? 'show_en_weight_threshold_lfq' : 'hide_en_weight_threshold_lfq';
        $weight_threshold = get_option('en_weight_threshold_lfq');
        $weight_threshold = isset($weight_threshold) && $weight_threshold > 0 ? $weight_threshold : 150;

//      check the current plan on store and disable/enable residential delivery option
        $action_residential_delivery = apply_filters('purolator_ltl_quotes_quotes_plans_suscription_and_features', 'residential_delivery');
      
        if (is_array($action_residential_delivery)) {
            $disable_residential_delivery = "disabled_me";
            $residential_delivery_required = apply_filters('purolator_ltl_quotes_plans_notification_link', $action_residential_delivery);
        }

//      check the current plan on store and disable/enable tailgate delivery option
        $action_tailgate_delivery = apply_filters('purolator_ltl_quotes_quotes_plans_suscription_and_features', 'tailgate_delivery');
        if (is_array($action_tailgate_delivery)) {
            $disable_tailgate_delivery = "disabled_me";
            $tailgate_delivery_required = apply_filters('purolator_ltl_quotes_plans_notification_link', $action_tailgate_delivery);
        }
        
        echo '<div class="quote_section_class_purolator_ltl">';
        $settings = array(
            'section_title_quote' => array(
                'title' => __('Service Types', 'woocommerce-settings-purolator_ltl_quotes'),
                'type' => 'title',
                'desc' => '',
                'id' => 'purolator_ltl_section_title_quote'
            ),
            'select_purolator_ltl_services' => array(
                'name' => __('Select All', 'woocommerce-settings-purolator_ltl_quotes'),
                'type' => 'checkbox',
                'id' => 'select_all_purolator_ltl_services',
                'class' => 'purolator_ltl_all_services',
            ),
            'service_purolator_ltl_standered' => array(
                'name' => __('Freight Standard', 'woocommerce-settings-purolator_ltl_quotes'),
                'type' => 'checkbox',
                'id' => 'service_purolator_ltl_stndrd_quotes',
                'class' => 'purolator_ltl_quotes_services',
            ),
            'service_purolator_ltl_expedited' => array(
                'name' => __('Freight Expedited', 'woocommerce-settings-purolator_ltl_quotes'),
                'type' => 'checkbox',
                'id' => 'service_purolator_ltl_expedited_quotes',
                'class' => 'purolator_ltl_quotes_services',
            ),
//          Residential delivery options    
            'accessorial_residential_delivery_purolator_ltl' => array(
                'name' => __('', 'woocommerce-settings-purolator_ltl_quotes'),
                'type' => 'title',
            ),
            'accessorial_always_residential_delivery_purolator_ltl' => array(
                'name' => __('Always include the residential delivery fee', 'woocommerce-settings-purolator_ltl_quotes'),
                'type' => 'checkbox',
                'id' => 'purolator_ltl_always_include_residential',
                'class' => 'purolator_ltl_residential',
            ),
            'accessorial_option_residential_delivery_purolator_ltl' => array(
                'name' => __('Provide an option for residential delivery', 'woocommerce-settings-purolator_ltl_quotes'),
                'type' => 'checkbox',
                'id' => 'purolator_ltl_option_residential',
                'desc' => $residential_delivery_required,
                'class' => 'purolator_ltl_residential ' . $disable_residential_delivery,
            ),
            'accessorial_tailgate_delivery_purolator_ltl' => array(
                'name' => __('Residential Delivery Options', 'woocommerce-settings-purolator_ltl_quotes'),
                'type' => 'title',
            ),
//          tailgate delivery options
            'accessorial_always_tailgate_delivery_purolator_ltl' => array(
                'name' => __('Always include the tailgate delivery fee', 'woocommerce-settings-purolator_ltl_quotes'),
                'type' => 'checkbox',
                'id' => 'purolator_ltl_always_tailgate',
                'class' => 'purolator_ltl_tailgate',
            ),
            'accessorial_option_liftgate_delivery_purolator_ltl' => array(
                'name' => __('Provide an option for tailgate delivery', 'woocommerce-settings-purolator_ltl_quotes'),
                'type' => 'checkbox',
                'id' => 'purolator_ltl_option_tailgate',
                'desc' => $tailgate_delivery_required,
                'class' => 'purolator_ltl_tailgate '.$disable_tailgate_delivery,
            ),
            'accessorial_tailgate_delivery_purolator_ltl1' => array(
                'name' => __('Tailgate Delivery Options', 'woocommerce-settings-purolator_ltl_quotes'),
                'type' => 'title',
            ),
            'purolator_ltl_show_delivery_estimates' => array(
                'name' => __('Show Delivery Estimates ', 'woocommerce-settings-purolator_ltl_quotes'),
                'type' => 'checkbox',
                'id' => 'purolator_ltl_delivey_estimate'
            ),
            'label_as_stndrd_purolator_ltl' => array(
                'name' => __('Label As (Standard)', 'woocommerce-settings-purolator_ltl_quotes'),
                'type' => 'text',
                'desc' => 'What the user sees during checkout, e.g. "LTL Freight". If left blank, "Freight Standard" will display as the shipping method.',
                'id' => 'purolator_ltl_label_as_stndrd'
            ),
            'label_as_expedited_purolator_ltl' => array(
                'name' => __('Label As (Expedited)', 'woocommerce-settings-purolator_ltl_quotes'),
                'type' => 'text',
                'desc' => 'What the user sees during checkout, e.g. "LTL Freight". If left blank, "Freight Expedited" will display as the shipping method.',
                'id' => 'purolator_ltl_label_as_expedited'
            ),
            // Handling Weight
            'handling_unit_purolator_ltl' => array(
                'name' => __('Handling Unit ', 'estes_freight_wc_settings'),
                'type' => 'text',
                'class' => 'hidden',
                'id' => 'handling_unit_purolator_ltl'
            ),
            'handling_weight_purolator_ltl' => array(
                'name' => __('Weight of Handling Unit  ', 'estes_freight_wc_settings'),
                'type' => 'text',
                'desc' => 'Enter in pounds the weight of your pallet, skid, crate or other type of handling unit.',
                'id' => 'handling_weight_purolator_ltl'
            ),
            // max Handling Weight
            'maximum_handling_weight_purolator_ltl' => array(
                'name' => __('Maximum Weight per Handling Unit  ', 'estes_freight_wc_settings'),
                'type' => 'text',
                'desc' => 'Enter in pounds the maximum weight that can be placed on the handling unit.',
                'id' => 'maximum_handling_weight_purolator_ltl'
            ),
            'handing_fee_markup_purolator_ltl' => array(
                'name' => __('Handling Fee / Markup ', 'woocommerce-settings-purolator_ltl_quotes'),
                'type' => 'text',
                'desc' => 'Amount excluding tax. Enter an amount, e.g 3.75, or a percentage, e.g, 5%. Leave blank to disable.',
                'id' => 'purolator_ltl_handling_fee'
            ),
            'enable_logs_purolator_ltl' => array(
                'name' => __("Enable Logs  ", 'woocommerce-settings-fedex_ltl_quotes'),
                'type' => 'checkbox',
                'desc' => 'When checked, the Logs page will contain up to 25 of the most recent transactions.',
                'id' => 'enable_logs_purolator_ltl'
            ),
            'allow_other_plugins_purolator_ltl' => array(
                'name' => __('Show WooCommerce Shipping Options ', 'woocommerce-settings-purolator_ltl_quotes'),
                'type' => 'select',
                'default' => '3',
                'desc' => __('Enabled options on WooCommerce Shipping page are included in quote results.', 'woocommerce-settings-purolator_ltl_quotes'),
                'id' => 'purolator_ltl_allow_other_plugins',
                'options' => array(
                    'no' => __('NO', 'NO'),
                    'yes' => __('YES', 'YES')
                )
            ),
            'return_purolator_ltl_quotes' => array(
                'name' => __('Return LTL quotes when an order\'s parcel shipment weight exceeds the weight threshold', 'woocommerce-settings-purolator_ltl_quetes'),
                'type' => 'checkbox',
                'desc' => '<span class="description" >When checked, the LTL Freight Quote will return quotes when an order’s total weight exceeds the weight threshold (the maximum permitted by WWE and UPS), even if none of the products have settings to indicate that it will ship LTL Freight. To increase the accuracy of the returned quote(s), all products should have accurate weights and dimensions.</span>',
                'id' => 'en_plugins_return_LTL_quotes'
            ),
            // Weight threshold for LTL freight
            'en_weight_threshold_lfq' => [
                'name' => __('Weight threshold for LTL Freight Quotes  ', 'woocommerce-settings-purolator_ltl_quetes'),
                'type' => 'text',
                'default' => $weight_threshold,
                'class' => $weight_threshold_class,
                'id' => 'en_weight_threshold_lfq'
            ],
            'en_suppress_parcel_rates' => array(
                'name' => __("", 'woocommerce-settings-purolator_ltl_quetes'),
                'type' => 'radio',
                'default' => 'display_parcel_rates',
                'options' => array(
                    'display_parcel_rates' => __("Continue to display parcel rates when the weight threshold is met.", 'woocommerce-settings-purolator_ltl_quetes'),
                    'suppress_parcel_rates' => __("Suppress parcel rates when the weight threshold is met.", 'woocommerce-settings-purolator_ltl_quetes'),
                ),
                'class' => 'en_suppress_parcel_rates',
                'id' => 'en_suppress_parcel_rates',
            ),
            'section_end_quote' => array(
                'type' => 'sectionend',
                'id' => 'quote_section_end'
            )
        );
        return $settings;
    }

}
