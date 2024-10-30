<?php
/**
 * Purolator WooCommerce Update Changes
 * @package     Woocommerce Purolator Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}
/**
 * Purolator WooCommerce Update Changes Class
 */
class Woo_Update_Changes_Purolator_LTL
{
    /**
     * WooVersion
     * @var int 
     */
    public $WooVersion;
    /**
     * Purolator WooCommerce Update Changes Constructor
     */
    function __construct() {
        if (!function_exists('get_plugins'))
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            $plugin_folder = get_plugins('/' . 'woocommerce');
            $plugin_file = 'woocommerce.php';
            $this->WooVersion = $plugin_folder[$plugin_file]['Version'];
    }
    
    /**
     * Purolator WooCommerce Customer Postcode
     * @return string
     */
    function purolator_ltl_postcode(){
        $postcode = "";
        switch ($this->WooVersion) {
            case ($this->WooVersion <= '2.7'):
                $postcode = WC()->customer->get_postcode();
                break;
            case ($this->WooVersion >= '3.0'):
                $postcode = WC()->customer->get_billing_postcode();
                break;
            default:                
                break;
        }
        return $postcode;
    }
    
    /**
     * Purolator WooCommerce Customer State
     * @return string
     */
    function purolator_ltl_state(){
        $postcode = "";
        switch ($this->WooVersion) {
            case ($this->WooVersion <= '2.7'):
                $postcode = WC()->customer->get_state();
                break;
            case ($this->WooVersion >= '3.0'):
                $postcode = WC()->customer->get_billing_state();
                break;
            default:                
                break;
        }
        return $postcode;
    }    
    
    /**
     * Purolator WooCommerce Customer City
     * @return string
     */
    function purolator_ltl_city(){
        $postcode = "";
        switch ($this->WooVersion) {
            case ($this->WooVersion <= '2.7'):
                $postcode = WC()->customer->get_city();
                break;
            case ($this->WooVersion >= '3.0'):
                $postcode = WC()->customer->get_billing_city();
                break;
            default:                
                break;
        }
        return $postcode;
    }
    
    /**
     * Purolator WooCommerce Customer Country
     * @return string
     */
    function purolator_ltl_country(){
        $postcode = "";
        switch ($this->WooVersion) {
            case ($this->WooVersion <= '2.7'):
                $postcode = WC()->customer->get_country();
                break;
            case ($this->WooVersion >= '3.0'):
                $postcode = WC()->customer->get_billing_country();
                break;
            default:                
                break;
        }
        return $postcode;
    }
    
}