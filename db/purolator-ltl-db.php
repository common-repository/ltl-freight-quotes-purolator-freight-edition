<?php
/**
 * Purolator Creating warehouse database table
 * @package     Woocommerce Purolator Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create warehouse database table
 * @global $wpdb
 */
function create_purolator_ltl_wh_db()
{
    global $wpdb;
    $warehouse_table = $wpdb->prefix . "warehouse";
    if ($wpdb->query("SHOW TABLES LIKE '" . $warehouse_table . "'") === 0) {
        $origin = 'CREATE TABLE ' . $warehouse_table . '(
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    city varchar(200) NOT NULL,
                    state varchar(200) NOT NULL,
                    zip varchar(200) NOT NULL,
                    country varchar(200) NOT NULL,
                    location varchar(200) NOT NULL,
                    nickname varchar(200) NOT NULL,
                    enable_store_pickup VARCHAR(255) NOT NULL,
                    miles_store_pickup VARCHAR(255) NOT NULL ,
                    match_postal_store_pickup VARCHAR(255) NOT NULL ,
                    checkout_desc_store_pickup VARCHAR(255) NOT NULL ,
                    enable_local_delivery VARCHAR(255) NOT NULL ,
                    miles_local_delivery VARCHAR(255) NOT NULL ,
                    match_postal_local_delivery VARCHAR(255) NOT NULL ,
                    checkout_desc_local_delivery VARCHAR(255) NOT NULL ,
                    fee_local_delivery VARCHAR(255) NOT NULL ,
                    suppress_local_delivery VARCHAR(255) NOT NULL,                  
                    origin_markup VARCHAR(255),
                    PRIMARY KEY  (id) )';
        dbDelta($origin);
    }
    add_option('abf_db_version', '1.0');

    $myCustomer = $wpdb->get_row("SHOW COLUMNS FROM " . $warehouse_table . " LIKE 'enable_store_pickup'");
    if (!(isset($myCustomer->Field) && $myCustomer->Field == 'enable_store_pickup')) {

        $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN enable_store_pickup VARCHAR(255) NOT NULL , "
            . "ADD COLUMN miles_store_pickup VARCHAR(255) NOT NULL , "
            . "ADD COLUMN match_postal_store_pickup VARCHAR(255) NOT NULL , "
            . "ADD COLUMN checkout_desc_store_pickup VARCHAR(255) NOT NULL , "
            . "ADD COLUMN enable_local_delivery VARCHAR(255) NOT NULL , "
            . "ADD COLUMN miles_local_delivery VARCHAR(255) NOT NULL , "
            . "ADD COLUMN match_postal_local_delivery VARCHAR(255) NOT NULL , "
            . "ADD COLUMN checkout_desc_local_delivery VARCHAR(255) NOT NULL , "
            . "ADD COLUMN fee_local_delivery VARCHAR(255) NOT NULL , "
            . "ADD COLUMN suppress_local_delivery VARCHAR(255) NOT NULL", $warehouse_table));

    }

    $purolator_ltl_origin_markup = $wpdb->get_row("SHOW COLUMNS FROM " . $warehouse_table . " LIKE 'origin_markup'");
    if (!(isset($purolator_ltl_origin_markup->Field) && $purolator_ltl_origin_markup->Field == 'origin_markup')) {
        $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN origin_markup VARCHAR(255) NOT NULL", $warehouse_table));
    }    
}

/**
 * Create LTL Class
 */
function create_ltl_freight_class_purolator()
{
    if (!function_exists('create_ltl_class')) {
        wp_insert_term(
            'LTL Freight', 'product_shipping_class', array(
                'description' => 'The plugin is triggered to provide an LTL freight quote when the shopping cart contains an item that has a designated shipping class. Shipping class? is a standard WooCommerce parameter not to be confused with freight class? or the NMFC classification system.',
                'slug' => 'ltl_freight'
            )
        );
    }
}

/**
 * Add Option For Purolator
 */
function create_purolator_ltl_option()
{
    $eniture_plugins = get_option('EN_Plugins');
    if (!$eniture_plugins) {
        add_option('EN_Plugins', json_encode(array('purolator_ltl')));
    } else {
        $plugins_array = json_decode($eniture_plugins, true);
        if (!in_array('purolator_ltl', $plugins_array)) {
            array_push($plugins_array, 'purolator_ltl');
            update_option('EN_Plugins', json_encode($plugins_array));
        }
    }
}

/**
 * Remove Option For Purolator
 */
if(!function_exists('en_purolater_ltl_deactivate_plugin')) {
    function en_purolater_ltl_deactivate_plugin()
    {
        $eniture_plugins = get_option('EN_Plugins');
        $plugins_array = json_decode($eniture_plugins, true);
        $plugins_array = !empty($plugins_array) && is_array($plugins_array) ? $plugins_array : array();
        $key = array_search('purolator_ltl', $plugins_array);
        if ($key !== false) {
            unset($plugins_array[$key]);
        }
        update_option('EN_Plugins', json_encode($plugins_array));
    }
}