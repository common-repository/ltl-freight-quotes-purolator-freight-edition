<?php
/**
 * Plugin Name:    LTL Freight Quotes - Purolator Edition
 * Plugin URI:     https://eniture.com/products/
 * Description:    Dynamically retrieves your negotiated shipping rates from Purolator LTL Freight and displays the results in the WooCommerce shopping cart.
 * Version:        2.2.3
 * Author:         enituretechnology
 * Author URI:     http://eniture.com/
 * Text Domain:    eniture-technology
 * License:        GPL version 2 or later - http://www.eniture.com/
 * WC requires at least: 6.4
 * WC tested up to: 9.1.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PUROLATOR_FREIGHT_DOMAIN_HITTING_URL', 'https://ws039.eniture.com');
define('PUROLATOR_FREIGHT_FDO_HITTING_URL', 'https://freightdesk.online/api/updatedWoocomData');

add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

if (!function_exists('is_plugin_active')) {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

if (!is_plugin_active('woocommerce/woocommerce.php')) {
    add_action('admin_notices', 'purolator_ltl_wc_avaibility_error');
}

if (!function_exists('en_woo_plans_notification_PD')) {
    function en_woo_plans_notification_PD($product_detail_options)
    {
        $eniture_plugins_id = 'eniture_plugin_';

        for ($e = 1; $e <= 25; $e++) {
            $settings = get_option($eniture_plugins_id . $e);
            if (isset($settings) && (!empty($settings)) && (is_array($settings))) {
                $plugin_detail = current($settings);
                $plugin_name = (isset($plugin_detail['plugin_name'])) ? $plugin_detail['plugin_name'] : "";

                foreach ($plugin_detail as $key => $value) {
                    if ($key != 'plugin_name') {
                        $action = $value === 1 ? 'enable_plugins' : 'disable_plugins';
                        $product_detail_options[$key][$action] = (isset($product_detail_options[$key][$action]) && strlen($product_detail_options[$key][$action]) > 0) ? ", $plugin_name" : "$plugin_name";
                    }

                }

            }

        }

        return $product_detail_options;
    }

    add_filter('en_woo_plans_notification_action', 'en_woo_plans_notification_PD', 10, 1);
}

if (!function_exists('en_woo_plans_notification_message')) {
    function en_woo_plans_notification_message($enable_plugins, $disable_plugins)
    {
        $enable_plugins = (strlen($enable_plugins) > 0) ? "$enable_plugins: <b> Enabled</b>. " : "";
        $disable_plugins = (strlen($disable_plugins) > 0) ? " $disable_plugins: Upgrade to <b>Standard Plan to enable</b>." : "";
        return $enable_plugins . "<br>" . $disable_plugins;
    }

    add_filter('en_woo_plans_notification_message_action', 'en_woo_plans_notification_message', 10, 2);
}

/**
 * Load scripts for Purolator Freight json tree view
 */
if (!function_exists('en_purolator_ltl_jtv_script')) {
    function en_purolator_ltl_jtv_script()
    {
        wp_register_style('en_purolator_ltl_json_tree_view_style', plugin_dir_url(__FILE__) . 'logs/en-json-tree-view/en-jtv-style.css');
        wp_register_script('en_purolator_ltl_json_tree_view_script', plugin_dir_url(__FILE__) . 'logs/en-json-tree-view/en-jtv-script.js', ['jquery'], '1.0.0');

        wp_enqueue_style('en_purolator_ltl_json_tree_view_style');
        wp_enqueue_script('en_purolator_ltl_json_tree_view_script', [
            'en_tree_view_url' => plugins_url(),
        ]);
    }

    add_action('admin_init', 'en_purolator_ltl_jtv_script');
}

/**
 * Check woocommerce installlation
 */

function purolator_ltl_wc_avaibility_error()
{
    $class = "error";
    $message = "LTL Freight Quotes - Purolator Edition is enabled but not effective. It requires WooCommerce in order to work , Please <a target='_blank' href='https://wordpress.org/plugins/woocommerce/installation/'>Install</a> WooCommerce Plugin. Reactive LTL Freight Quotes - Purolator Edition plugin to create LTL shipping class.";
    echo "<div class=\"$class\"> <p>$message</p></div>";
}

add_action('admin_enqueue_scripts', 'en_purolator_freight_script');

/**
 * Load Front-end scripts for purolator_freight
 */
function en_purolator_freight_script()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('en_purolator_freight_script', plugin_dir_url(__FILE__) . 'js/en-purolator-freight.js', array(), '1.0.2');
    wp_localize_script('en_purolator_freight_script', 'en_purolator_freight_admin_script', array(
        'plugins_url' => plugins_url(),
        'allow_proceed_checkout_eniture' => trim(get_option("allow_proceed_checkout_eniture")),
        'prevent_proceed_checkout_eniture' => trim(get_option("prevent_proceed_checkout_eniture")),
    ));
}

/**
 * Inlude Plugin Files
 */
require_once 'purolator-ltl-test-connection.php';
require_once 'purolator-ltl-shipping-class.php';
require_once 'db/purolator-ltl-db.php';
require_once 'purolator-ltl-admin-filter.php';
require_once 'template/product-detail.php';
require_once 'template/purolator-products-options.php';
require_once('warehouse-dropship/wild-delivery.php');
require_once('standard-package-addon/standard-package-addon.php');
require_once('warehouse-dropship/get-distance-request.php');
require_once('update-plan.php');
require_once 'purolator-ltl-group-package.php';
require_once 'purolator-ltl-carrier-service.php';
require_once('template/connection-settings.php');
require_once('template/quote-settings.php');
require_once 'template/csv-export.php';
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
require_once 'purolator-ltl-wc-update-change.php';
require_once('purolator-ltl-curl-class.php');

require_once 'fdo/en-fdo.php';
require_once 'order/en-order-widget.php';

add_action('admin_init', 'purolator_ltl_check_wc_version');
/**
 * Check woocommerce version compatibility
 */
function purolator_ltl_check_wc_version()
{
    $wcPluginVersion = new Purolator_LTL_Get_Shipping_Quotes();
    $woo_version = $wcPluginVersion->purolator_ltl_wc_version_number();
    $version = '2.6';
    if (!version_compare($woo_version["woocommerce_plugin_version"], $version, ">=")) {
        add_action('admin_notices', 'wc_version_incompatibility_purolator');
    }
}

/**
 * Get Host
 * @param type $url
 * @return type
 */
if (!function_exists('getHost')) {
    function getHost($url)
    {
        $parseUrl = parse_url(trim($url));
        if (isset($parseUrl['host'])) {
            $host = $parseUrl['host'];
        } else {
            $path = explode('/', $parseUrl['path']);
            $host = $path[0];
        }
        return trim($host);
    }
}
/**
 * Get Domain Name
 */
if (!function_exists('purolator_ltl_quotes_get_domain')) {
    function purolator_ltl_quotes_get_domain()
    {
        global $wp;
        $url = home_url($wp->request);
        return getHost($url);
    }
}
/**
 * Check woocommerce version incompatibility
 */
function wc_version_incompatibility_purolator()
{
    ?>
    <div class="notice notice-error">
        <p>
            <?php
            _e('LTL Freight Quotes - Purolator Edition plugin requires WooCommerce version 2.6 or higher to work. Functionality may not work properly.', 'wwe-woo-version-failure');
            ?>
        </p>
    </div>
    <?php
}


if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    add_action('admin_enqueue_scripts', 'purolator_ltl_admin_script');

    /**
     * Load scripts for purolator
     */
    function purolator_ltl_admin_script()
    {

        wp_register_style('purolator_ltl_style', plugin_dir_url(__FILE__) . '/css/purolator-ltl-style.css', false, '1.0.2');
        wp_enqueue_style('purolator_ltl_style');
    }

    /**
     * purolator Freight Activation and Deactivation Hook
     */
    register_activation_hook(__FILE__, 'create_ltl_freight_class_purolator');
    register_activation_hook(__FILE__, 'create_purolator_ltl_wh_db');
    register_activation_hook(__FILE__, 'create_purolator_ltl_option');
    register_activation_hook(__FILE__, 'old_store_purolator_ltl_dropship_status');
    register_activation_hook(__FILE__, 'en_purolater_ltl_activate_hit_to_update_plan');
    register_deactivation_hook(__FILE__, 'en_purolater_ltl_deactivate_hit_to_update_plan');
    register_deactivation_hook(__FILE__, 'en_purolater_ltl_deactivate_plugin');

    /**
     * purolator ltl plugin update now
     * @param array type $upgrader_object
     * @param array type $options
     */
    function en_purolator_ltl_update_now()
    {
        $index = 'ltl-freight-quotes-purolator-freight-edition/ltl-freight-quotes-purolator-edition.php';
        $plugin_info = get_plugins();
        $plugin_version = (isset($plugin_info[$index]['Version'])) ? $plugin_info[$index]['Version'] : '';
        $update_now = get_option('en_purolator_ltl_update_now');

        if ($update_now != $plugin_version) {
            if (!function_exists('en_purolater_ltl_activate_hit_to_update_plan')) {
                require_once(__DIR__ . '/update-plan.php');
            }

            en_purolater_ltl_activate_hit_to_update_plan();
            old_store_purolator_ltl_dropship_status();
            create_purolator_ltl_wh_db();
            create_ltl_freight_class_purolator();
            create_purolator_ltl_option();

            update_option('en_purolator_ltl_update_now', $plugin_version);
        }
    }

    add_action('init', 'en_purolator_ltl_update_now');
    add_action( 'upgrader_process_complete', 'en_purolator_ltl_update_now', 10, 2);

    /**
     * purolator Action And Filters
     */
    add_action('woocommerce_shipping_init', 'purolator_ltl_freight_init');
    add_action('woocommerce_process_product_meta', 'purolator_ltl_woo_add_custom_general_fields_save');
    add_action('woocommerce_save_product_variation', 'purolator_ltl_save_variable_fields', 10, 1);
    add_filter('woocommerce_shipping_methods', 'add_purolator_ltl_freight');
    add_filter('woocommerce_get_settings_pages', 'purolator_ltl_shipping_sections');
    add_filter('woocommerce_package_rates', 'purolator_ltl_hide_shipping');
    add_filter('woocommerce_shipping_calculator_enable_city', '__return_true');
    add_filter('plugin_action_links', 'purolator_ltl_freight_add_action_plugin', 10, 5);

    /**
     * purolator action links
     * @staticvar $plugin
     * @param $actions
     * @param $plugin_file
     * @return string/array
     */
    function purolator_ltl_freight_add_action_plugin($actions, $plugin_file)
    {
        static $plugin;
        if (!isset($plugin))
            $plugin = plugin_basename(__FILE__);
        if ($plugin == $plugin_file) {
            $settings = array('settings' => '<a href="admin.php?page=wc-settings&tab=purolator_ltl_quotes">' . __('Settings', 'General') . '</a>');
            $site_link = array('support' => '<a href="https://support.eniture.com/home" target="_blank">Support</a>');
            $actions = array_merge($settings, $actions);
            $actions = array_merge($site_link, $actions);
        }
        return $actions;
    }

    add_filter('woocommerce_cart_no_shipping_available_html', 'purolator_ltl_cart_html_message');
    /**
     * No Quotes Cart Message
     */
    function purolator_ltl_cart_html_message()
    {
        echo "<div><p>There are no shipping methods available. Please double check your address, or contact us if you need any help.</p></div>";
    }
}

/**
 * Plans Common Hooks
 */
add_filter('purolator_ltl_quotes_quotes_plans_suscription_and_features', 'purolator_ltl_quotes_quotes_plans_suscription_and_features', 1);

function purolator_ltl_quotes_quotes_plans_suscription_and_features($feature)
{
    $package = get_option('purolater_ltl_packages_quotes_package');

    $features = array
    (
        'instore_pickup_local_devlivery' => array('3'),
        'hazardous_material' => array('2', '3'),
        'tailgate_delivery' => array('2', '3'),
        'residential_delivery' => array('2', '3'),
    );
    if (get_option('purolater_quotes_store_type') == "1") {
        $features['multi_warehouse'] = array('2', '3');
        $features['multi_dropship'] = array('', '0', '1', '2', '3');
    }
    if (get_option('en_old_user_dropship_status') == "0" && get_option('purolater_quotes_store_type') == "0") {
        $features['multi_dropship'] = array('', '0', '1', '2', '3');
    }
    if (get_option('en_old_user_warehouse_status') == "0" && get_option('purolater_quotes_store_type') == "0") {
        $features['multi_warehouse'] = array('2', '3');
    }

    return (isset($features[$feature]) && (in_array($package, $features[$feature]))) ? TRUE : ((isset($features[$feature])) ? $features[$feature] : '');
}

add_filter('purolator_ltl_quotes_plans_notification_link', 'purolator_ltl_quotes_plans_notification_link', 1);

function purolator_ltl_quotes_plans_notification_link($plans)
{
    $plan = current($plans);
    $plan_to_upgrade = "";
    switch ($plan) {
        case 2:
            $plan_to_upgrade = "<a href='http://eniture.com/plan/woocommerce-purolator-ltl-freight/' target='_blank'>Standard Plan required</a>";
            break;
        case 3:
            $plan_to_upgrade = "<a href='http://eniture.com/plan/woocommerce-purolator-ltl-freight/' target='_blank'>Advanced Plan required</a>";
            break;
    }
    return $plan_to_upgrade;
}

/**
 *
 * old customer check dropship / warehouse status on plugin update
 */
function old_store_purolator_ltl_dropship_status()
{
    global $wpdb;

//  Check total no. of dropships on plugin updation
    $table_name = $wpdb->prefix . 'warehouse';
    $count_query = "select count(*) from $table_name where location = 'dropship' ";
    $num = $wpdb->get_var($count_query);

    if (get_option('en_old_user_dropship_status') == "0" && get_option('purolater_quotes_store_type') == "0") {
        $dropship_status = ($num > 1) ? 1 : 0;
        update_option('en_old_user_dropship_status', "$dropship_status");
    } elseif (get_option('en_old_user_dropship_status') == "" && get_option('purolater_quotes_store_type') == "0") {
        $dropship_status = ($num == 1) ? 0 : 1;
        update_option('en_old_user_dropship_status', "$dropship_status");
    }

//  Check total no. of warehouses on plugin updation
    $table_name = $wpdb->prefix . 'warehouse';
    $warehouse_count_query = "select count(*) from $table_name where location = 'warehouse' ";
    $warehouse_num = $wpdb->get_var($warehouse_count_query);

    if (get_option('en_old_user_warehouse_status') == "0" && get_option('purolater_quotes_store_type') == "0") {
        $warehouse_status = ($warehouse_num > 1) ? 1 : 0;
        update_option('en_old_user_warehouse_status', "$warehouse_status");
    } elseif (get_option('en_old_user_warehouse_status') == "" && get_option('purolater_quotes_store_type') == "0") {
        $warehouse_status = ($warehouse_num == 1) ? 0 : 1;
        update_option('en_old_user_warehouse_status', "$warehouse_status");
    }
}

add_filter('en_suppress_parcel_rates_hook', 'supress_parcel_rates');
if (!function_exists('supress_parcel_rates')) {
    function supress_parcel_rates() {
        $exceedWeight = get_option('en_plugins_return_LTL_quotes') == 'yes';
        $supress_parcel_rates = get_option('en_suppress_parcel_rates') == 'suppress_parcel_rates';
        return ($exceedWeight && $supress_parcel_rates);
    }
}
