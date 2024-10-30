<?php
/**
 * Purolator WooComerce purolator_ltl Shipping Calculation Class
 * @package     Woocommerce Purolator Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Purolator LTL Shipping Calculation Class
 */
function purolator_ltl_freight_init()
{
    if (!class_exists('Purolator_LTL_Freight_Shipping')) {
        /**
         * Purolator LTL Shipping Calculation Class
         */
        class Purolator_LTL_Freight_Shipping extends WC_Shipping_Method
        {
            public $instore_pickup_and_local_delivery;
            public $group_small_shipments;
            public $web_service_inst;
            public $package_plugin;
            public $InstorPickupLocalDelivery;
            public $woocommerce_package_rates;
            public $quote_settings;

            public $purolator_ltl_residential_as_option;

            public $shipment_type;
            public $min_prices;
            public $minPrices;
            // FDO
            public $en_fdo_meta_data = [];
            public $en_fdo_meta_data_third_party = [];

            /**
             * Woocommerce Shipping Field Attributes
             * @param $instance_id
             */
            public function __construct($instance_id = 0)
            {
                error_reporting(0);
                $this->id = 'purolator_ltl';
                $this->instance_id = absint($instance_id);
                $this->method_title = __('Purolator Freight');
                $this->method_description = __('Shipping rates from Purolator Freight.');
                $this->supports = array(
                    'shipping-zones',
                    'instance-settings',
                    'instance-settings-modal',
                );
                $this->enabled = "yes";
                $this->title = 'LTL Freight Quotes - Purolator Edition';
                $this->init();
                add_action('woocommerce_checkout_update_order_review', array($this, 'calculate_shipping'));
            }

            /**
             * Woocommerce Shipping Fields init
             */
            function init()
            {
                $this->init_form_fields();
                $this->init_settings();
                add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
            }

            /**
             * Enable Woocommerce Shipping For Purolator LTL
             */
            function init_form_fields()
            {
                $this->instance_form_fields = array(
                    'enabled' => array(
                        'title' => __('Enable / Disable', 'purolator_ltl'),
                        'type' => 'checkbox',
                        'label' => __('Enable This Shipping Service', 'purolator_ltl'),
                        'default' => 'no',
                        'id' => 'purolator_ltl_enable_disable_shipping'
                    )
                );
            }

            /**
             * Calculate Shipping Rates For Purolator LTL
             * @param string $package
             * @return boolean|string
             */
            public function calculate_shipping($package = array(), $eniture_admin_order_action = false)
            {
                if (is_admin() && !wp_doing_ajax() && !$eniture_admin_order_action) {
                    return [];
                }
                
                $coupn = WC()->cart->get_coupons();
                if (isset($coupn) && !empty($coupn)) {
                    $free_shipping = $this->purolator_shipping_coupon_rate($coupn);
                    if ($free_shipping == 'y') return FALSE;
                }

                $this->instore_pickup_and_local_delivery = FALSE;

                $this->package_plugin = get_option('purolater_ltl_packages_quotes_package');


                $group_package_obj = new Purolator_LTL_Shipping_Get_Package();
                $purolator_ltl_res_inst = new Purolator_LTL_Get_Shipping_Quotes();
                $this->web_service_inst = $purolator_ltl_res_inst;

                $this->purolator_quote_settings();

                $purolator_ltl_package = $group_package_obj->group_purolator_ltl_shipment($package, $purolator_ltl_res_inst);
                $handlng_fee = get_option('purolator_ltl_handling_fee');
                $quotes = array();
                $smallQuotes = array();
                $rate = array();
                $smallPluginExist = 0;
                $calledMethod = array();
                $no_param_multi_ship = 0;
                if (isset($purolator_ltl_package['error'])) {
                    return 'error';
                }
                if (count($purolator_ltl_package) > 1) {
                    foreach ($purolator_ltl_package as $key => $value) {
                        if (isset($value["NOPARAM"]) && $value["NOPARAM"] === 1 && empty($value["items"])) {
                            $no_param_multi_ship = 1;
                            unset($purolator_ltl_package[$key]);
                        }
                    }
                }
                $eniturePluigns = json_decode(get_option('EN_Plugins'));
                if (isset($purolator_ltl_package) && !empty($purolator_ltl_package)) {

                    if ($this->web_service_inst->quote_settings['handling_fee'] == '-100%') {
                        $rates = array(
                            'id' => $this->id . ':' . 'free',
                            'label' => 'Free Shipping',
                            'cost' => 0,
                            'plugin_name' => 'purolator-ltl',
                            'plugin_type' => 'ltl',
                            'owned_by' => 'eniture'
                        );
                        $this->add_rate($rates);
                        
                        return [];    
                    }

                    foreach ($purolator_ltl_package as $locId => $sPackage) {
                        if (array_key_exists('purolator_ltl', $sPackage)) {
                            $web_service_arr = $purolator_ltl_res_inst->purolator_ltl_shipping_array($sPackage, $this->package_plugin);

                            $quotes[] = $purolator_ltl_res_inst->purolator_ltl_get_web_quotes($web_service_arr);
                            continue;
                        }
                        if (array_key_exists('small', $sPackage)) {
                            foreach ($eniturePluigns as $enIndex => $enPlugin) {
                                $freightSmallClassName = 'WC_' . $enPlugin;
                                if (!in_array($freightSmallClassName, $calledMethod)) {

                                    if (class_exists($freightSmallClassName)) {
                                        $smallPluginExist = 1;
                                        $SmallClassNameObj = new $freightSmallClassName();

                                        $package['itemType'] = 'ltl';
                                        $smallQuotesResponse = $SmallClassNameObj->calculate_shipping($package);

                                        (isset($smallQuotesResponse['error'])) ? $this->smpkgFoundErr = 'Small package error' : "";

                                        $smallQuotes[] = $smallQuotesResponse;
                                    }
                                    $calledMethod[] = $freightSmallClassName;
                                }
                            }
                        } else {
                            return FALSE;
                        }
                    }
                }
                if (isset($quotes) && empty($quotes)) {
                    return 'error';
                }
                $smpkgCost = 0;
                $smallMinRate = $group_package_obj->getSmallPackagesCostpurolatorltl($smallQuotes);
                if (isset($smallMinRate['error']) && $smallMinRate['error'] == true) {
                    return FALSE;
                }

                // Virtual products
                $virtual_rate = $this->en_virtual_products();

                //FDO
                if (isset($smallMinRate['meta_data']['en_fdo_meta_data'])) {

                    if (!empty($smallMinRate['meta_data']['en_fdo_meta_data']) && !is_array($smallMinRate['meta_data']['en_fdo_meta_data'])) {
                        $en_third_party_fdo_meta_data = json_decode($smallMinRate['meta_data']['en_fdo_meta_data'], true);
                        isset($en_third_party_fdo_meta_data['data']) ? $smallMinRate['meta_data']['en_fdo_meta_data'] = $en_third_party_fdo_meta_data['data'] : '';
                    }
                    $this->en_fdo_meta_data_third_party = (isset($smallMinRate['meta_data']['en_fdo_meta_data']['address'])) ? [$smallMinRate['meta_data']['en_fdo_meta_data']] : $smallMinRate['meta_data']['en_fdo_meta_data'];
                }

                $smpkgCost = $smallMinRate['price'];

                if (isset($smallMinRate) && (!empty($smallMinRate))) {
                    switch (TRUE) {
                        case (isset($smallMinRate['minPrices'])):
                            $small_quotes = $smallMinRate['minPrices'];
                            break;
                        default :
                            $shipment_zipcode = key($smallQuotes);
                            $small_quotes = array($shipment_zipcode => $smallMinRate);
                            break;
                    }
                }

                if ((count($quotes) > 1 || $smpkgCost > 0) || $no_param_multi_ship == 1 || !empty($virtual_rate)) {

                    $multiship_rates = $multiship_label_suffix = $multiship_label = array();
                    $this->minPrices = array();

                    (isset($small_quotes) && count($small_quotes) > 0) ? $this->minPrices['PUROLATOR_LIFT'] = $small_quotes : "";
                    (isset($small_quotes) && count($small_quotes) > 0) ? $this->minPrices['PUROLATOR_NOTLIFT'] = $small_quotes : "";

                    // Virtual products
                    if (!empty($virtual_rate)) {
                        $en_virtual_fdo_meta_data[] = $virtual_rate['meta_data']['en_fdo_meta_data'];
                        $virtual_meta_rate['virtual_rate'] = $virtual_rate;
                        $this->minPrices['PUROLATOR_LIFT'] = isset($this->minPrices['PUROLATOR_LIFT']) && !empty($this->minPrices['PUROLATOR_LIFT']) ? array_merge($this->minPrices['PUROLATOR_LIFT'], $virtual_meta_rate) : $virtual_meta_rate;
                        $this->minPrices['PUROLATOR_NOTLIFT'] = isset($this->minPrices['PUROLATOR_NOTLIFT']) && !empty($this->minPrices['PUROLATOR_NOTLIFT']) ? array_merge($this->minPrices['PUROLATOR_NOTLIFT'], $virtual_meta_rate) : $virtual_meta_rate;
                        $this->en_fdo_meta_data_third_party = !empty($this->en_fdo_meta_data_third_party) ? array_merge($this->en_fdo_meta_data_third_party, $en_virtual_fdo_meta_data) : $en_virtual_fdo_meta_data;
                    }

                    // check the values in quotes array either it is tailgate , residential or both and merge them in the final array
                    foreach ($quotes as $key => $quote) {
                        if (isset($quotes[$key]['tailgate_quotes'])) {
                            $tailgate_quotes = $quotes[$key]['tailgate_quotes'];
                            unset($quotes[$key]['tailgate_quotes']);
                            $quotes[$key] = array_merge($quotes[$key], $tailgate_quotes);
                        }
                        if (isset($quotes[$key]['residential_quotes'])) {
                            $residential_quotes = $quotes[$key]['residential_quotes'];
                            unset($quotes[$key]['residential_quotes']);
                            $quotes[$key] = array_merge($quotes[$key], $residential_quotes);
                        }
                        if (isset($quotes[$key]['tailgate_residential_quotes'])) {
                            $tailgate_residential_quotes = $quotes[$key]['tailgate_residential_quotes'];
                            unset($quotes[$key]['tailgate_residential_quotes']);
                            $quotes[$key] = array_merge($quotes[$key], $tailgate_residential_quotes);
                        }
                    }

                    foreach ($quotes as $key => $quote) {
                        foreach ($quote as $quote_key => $quote_data) {
                            $id = $quote_data['id'];
                            if (isset($multiship_rates[$id])) {
                                $multiship_rates[$id] = $multiship_rates[$id] + $quote_data['cost'];
                            } else {
                                $multiship_rates[$id] = $quote_data['cost'];
                            }
                            if (isset($quote_data['label_suffix']))

                                $multiship_label_suffix[$id] = $quote_data['label_suffix'];
                            $multiship_label[$id] = $quote_data['label'];

                            $this->minPrices[$id . '-' . $key] = $quote_data;
                            $this->en_fdo_meta_data[$id . '-' . $key] = (isset($quote_data['meta_data']['en_fdo_meta_data'])) ? $quote_data['meta_data']['en_fdo_meta_data'] : [];
                        }
                    }

                    foreach ($multiship_rates as $key => $price) {
                        $sappend_label = (isset($multiship_label[$key])) ? $multiship_label[$key] : 'Freight';
                        $sappend_label .= (isset($multiship_label_suffix[$key]) && is_array($multiship_label_suffix[$key])) ? $this->filter_from_label_sufex($multiship_label_suffix[$key]) : '';
                        ($price > 0) ? $this->add_rate($this->arrange_multiship_freight(($price + $smpkgCost), $key, array(), $sappend_label)) : "";
                    }

                    $this->shipment_type = 'multiple';
                    add_filter('woocommerce_package_rates', array($this, 'en_sort_woocommerce_available_shipping_methods'), 10, 2);
                } else {

                    $this->shipment_type = 'single';

                    // Display Local and In-store PickUp Delivery
                    $this->InstorPickupLocalDelivery = $purolator_ltl_res_inst->purolater_ltl_return_local_delivery_store_pickup();
                    (isset($this->InstorPickupLocalDelivery->localDelivery) && ($this->InstorPickupLocalDelivery->localDelivery->status == 1)) ? $this->local_delivery($this->web_service_inst->en_wd_origin_array['fee_local_delivery'], $this->web_service_inst->en_wd_origin_array['checkout_desc_local_delivery']) : "";
                    (isset($this->InstorPickupLocalDelivery->inStorePickup) && ($this->InstorPickupLocalDelivery->inStorePickup->status == 1)) ? $this->pickup_delivery($this->web_service_inst->en_wd_origin_array['checkout_desc_store_pickup']) : "";

                    $quotes = (is_array($quotes) && (!empty($quotes))) ? reset($quotes) : array();

                    // check the values in quotes array either it is tailgate , residential or both and merge them in the final array
                    if (isset($quotes['tailgate_quotes'])) {
                        $tailgate_quotes = $quotes['tailgate_quotes'];
                        unset($quotes['tailgate_quotes']);
                        $quotes = array_merge($quotes, $tailgate_quotes);
                    }
                    if (isset($quotes['residential_quotes'])) {
                        $residential_quotes = $quotes['residential_quotes'];
                        unset($quotes['residential_quotes']);
                        $quotes = array_merge($quotes, $residential_quotes);
                    }
                    if (isset($quotes['tailgate_residential_quotes'])) {
                        $tailgate_residential_quotes = $quotes['tailgate_residential_quotes'];
                        unset($quotes['tailgate_residential_quotes']);
                        $quotes = array_merge($quotes, $tailgate_residential_quotes);
                    }

                    // Images for FDO
                    $image_urls = apply_filters('en_fdo_image_urls_merge', []);

                    foreach ($quotes as $key => $value) {

                        if (isset($value) && !empty($value)) {
                            if (isset($value) && !empty($value['cost'])) {
                                $cost = $value['cost'];
                                $estimtdDays = $value['transit_time'];
                                $show_estimate = get_option('purolator_ltl_delivey_estimate');
                                $label = $value['label'] . $this->filter_from_label_sufex($value['label_suffix']);
                                if ($show_estimate == 'yes' && $estimtdDays != 0) {
                                    $label = $label.' (Intransit days: '.$estimtdDays.')';
                                }

                                if ($handlng_fee != '') {
                                    $grandTotal = $group_package_obj->parse_handeling_fee($handlng_fee, $cost);
                                    (isset($value['meta_data']['en_fdo_meta_data']['rate']['cost'])) ? $value['meta_data']['en_fdo_meta_data']['rate']['cost'] = $grandTotal : "";
                                } else {
                                    $grandTotal = $cost;
                                }
                                if ($grandTotal > 0) {
                                    $rate = $value;
                                    $rate['id'] = $value['id'];
                                    $rate['label'] = $label;
                                    $rate['cost'] = $grandTotal;
                                    $rate['plugin_name'] = 'purolator-ltl';
                                    $rate['plugin_type'] = 'ltl';
                                    $rate['owned_by'] = 'eniture';
                                }

                                // In-store pickup and local delivery
                                $instore_pickup_local_devlivery_action = apply_filters('purolator_ltl_quotes_quotes_plans_suscription_and_features', 'instore_pickup_local_devlivery');

                                // FDO
                                if (isset($rate['meta_data'])) {
                                    $rate['meta_data']['label_sufex'] = (isset($rate['label_sufex'])) ? json_encode($rate['label_sufex']) : array();
                                }

                                $rate['id'] = (isset($rate['id'])) ? $rate['id'] : '';

                                // Micro Warehouse
                                $en_check_action_warehouse_appliance = apply_filters('en_check_action_warehouse_appliance', FALSE);
                                if ($this->shipment_type == 'multiple' && $en_check_action_warehouse_appliance && !empty($this->minPrices)) {
                                    $rate['meta_data']['min_quotes'] = $this->minPrices[$rate['id']];
                                }

                                $en_set_fdo_meta_data['data'] = [$rate['meta_data']['en_fdo_meta_data']];
                                $en_set_fdo_meta_data['shipment'] = 'sinlge';
                                $rate['meta_data']['en_fdo_meta_data'] = wp_json_encode($en_set_fdo_meta_data);
        
                                // Images for FDO
                                $rate['meta_data']['en_fdo_image_urls'] = wp_json_encode($image_urls);
                                $rate['id'] = isset($rate['id']) && is_string($rate['id']) ? $this->id . ':' . $rate['id'] : '';

                                if ($this->web_service_inst->en_wd_origin_array['suppress_local_delivery'] == "1" && (!is_array($instore_pickup_local_devlivery_action)) && $this->shipment_type != 'multiple') {
                                    $rate = apply_filters('suppress_local_delivery', $rate, $this->web_service_inst->en_wd_origin_array, $this->package_plugin, $this->InstorPickupLocalDelivery);

                                    if (!empty($rate)) {
                                        $this->add_rate($rate);
                                        $this->woocommerce_package_rates = 1;
                                    }

                                } else {
                                    $this->add_rate($rate);
                                    $this->woocommerce_package_rates = 1;
                                }
                            }
                            if (isset($this->woocommerce_package_rates) && ($this->woocommerce_package_rates == 1)) {
                                add_filter('woocommerce_package_rates', array($this, 'en_sort_woocommerce_available_shipping_methods'), 10, 2);
                            }
                        }
                    }
                }
            }

            function arrange_multiship_freight($cost, $id, $label_sufex, $append_label)
            {
                $meta_data = [];
                if (!empty($this->en_fdo_meta_data)) {
                    foreach ($this->en_fdo_meta_data as $key => $value) {
                        $_key = explode('-', $key);
                        if ($_key[0] == $id) {
                            $meta_data['en_fdo_meta_data']['data'][] = $value;
                            $meta_data['min_prices'][$key] = $this->minPrices[$key];
                        }
                    }
                }

                // FDO meta data
                !empty($this->en_fdo_meta_data_third_party) ? $meta_data['en_fdo_meta_data']['data'] = array_merge($meta_data['en_fdo_meta_data']['data'], $this->en_fdo_meta_data_third_party) : '';
                $meta_data['min_prices'] = isset($meta_data['min_prices']) ? json_encode($meta_data['min_prices']) : [];
                $meta_data['en_fdo_meta_data']['shipment'] = 'multiple';
                $meta_data['en_fdo_meta_data'] = isset($meta_data['en_fdo_meta_data']) ? wp_json_encode($meta_data['en_fdo_meta_data']) : [];
                $image_urls = apply_filters('en_fdo_image_urls_merge', []);
                $meta_data['en_fdo_image_urls'] = wp_json_encode($image_urls);

                $multiship = array(
                    'id' => $this->id . ':' . $id,
                    'label' => $append_label,
                    'cost' => $cost,
                    'label_sufex' => $label_sufex,
                    'append_label' => $append_label,
                    'plugin_name' => 'purolator-ltl',
                    'plugin_type' => 'ltl',
                    'owned_by' => 'eniture',
                    'meta_data' => $meta_data
                );

                return $multiship;
            }

            /**
             * parameter $label_sufer
             * return Append labels for Quotes
             */

            public function filter_from_label_sufex($label_sufex)
            {
                $append_label = "";
                $label_sufex = isset($label_sufex) && is_array($label_sufex) ? $label_sufex : [];
                switch (TRUE) {
                    case(count($label_sufex) == 1):
                        (in_array('T', $label_sufex)) ? $append_label = " with tailgate delivery " : "";
                        (in_array('R', $label_sufex)) ? $append_label = " with residential delivery " : "";
                        break;
                    case(count($label_sufex) == 2):
                        (in_array('T', $label_sufex)) ? $append_label = " with tailgate delivery " : "";
                        (in_array('R', $label_sufex)) ? $append_label .= (strlen($append_label) > 0) ? " and residential delivery " : " with residential delivery " : "";
                        break;
                }

                return $append_label;
            }

            /**
             * quote settings array
             * @global $wpdb $wpdb
             */
            function purolator_quote_settings()
            {
                $this->web_service_inst->quote_settings['tailgate_delivery'] = get_option('purolator_ltl_always_tailgate');
                $this->web_service_inst->quote_settings['tailgate_delivery_option'] = get_option('purolator_ltl_option_tailgate');
                $this->web_service_inst->quote_settings['residential_delivery'] = get_option('purolator_ltl_always_include_residential');
                $this->web_service_inst->quote_settings['residential_delivery_option'] = get_option('purolator_ltl_option_residential');


                $this->web_service_inst->quote_settings['label_standard'] = get_option('purolator_ltl_label_as_stndrd');
                $this->web_service_inst->quote_settings['label_expedited'] = get_option('purolator_ltl_label_as_expedited');
                $this->web_service_inst->quote_settings['handling_fee'] = get_option('purolator_ltl_handling_fee');
                $this->web_service_inst->quote_settings['transit_time'] = get_option('purolator_ltl_delivey_estimate');

                $this->web_service_inst->quote_settings['handling_weight'] = get_option('handling_weight_purolator_ltl');
                $this->web_service_inst->quote_settings['maximum_handling_weight'] = get_option('maximum_handling_weight_purolator_ltl');
            }

            function en_sort_woocommerce_available_shipping_methods($rates, $package)
            {
                //  if there are no rates don't do anything
                if (!$rates) {
                    return;
                }

                // get an array of prices
                $prices = array();
                foreach ($rates as $rate) {
                    $prices[] = $rate->cost;
                }

                // use the prices to sort the rates
                array_multisort($prices, $rates);

                // return the rates
                return $rates;
            }

            /**
             * Pickup delivery quote
             * @return array type
             */
            function pickup_delivery($label)
            {
                $this->woocommerce_package_rates = 1;
                $this->instore_pickup_and_local_delivery = TRUE;

                $label = (isset($label) && (strlen($label) > 0)) ? $label : 'In-store pick up';

//              check woocommerce version for displying instore pickup cost $0.00
                $woocommerce_version = get_option('woocommerce_version');
                $label = ($woocommerce_version < '3.5.4') ? $label : $label . ': $0.00';

                $pickup_delivery = array(
                    'id' => $this->id . ':' . 'in-store-pick-up',
                    'cost' => 0,
                    'label' => $label,
                    'plugin_name' => 'purolator-ltl',
                    'plugin_type' => 'ltl',
                    'owned_by' => 'eniture'
                );

                add_filter('woocommerce_package_rates', array($this, 'en_sort_woocommerce_available_shipping_methods'), 10, 2);
                $this->add_rate($pickup_delivery);
            }

            /**
             * Local delivery quote
             * @param string type $cost
             * @return array type
             */
            function local_delivery($cost, $label)
            {

                $this->woocommerce_package_rates = 1;
                $this->instore_pickup_and_local_delivery = TRUE;
                $label = (isset($label) && (strlen($label) > 0)) ? $label : 'Local Delivery';
                if ($cost == 0) {
//              check woocommerce version for displying instore pickup cost $0.00
                    $woocommerce_version = get_option('woocommerce_version');
                    $label = ($woocommerce_version < '3.5.4') ? $label : $label . ': $0.00';
                }

                $local_delivery = array(
                    'id' => $this->id . ':' . 'local-delivery',
                    'cost' => $cost,
                    'label' => $label,
                    'plugin_name' => 'purolator-ltl',
                    'plugin_type' => 'ltl',
                    'owned_by' => 'eniture'
                );

                add_filter('woocommerce_package_rates', array($this, 'en_sort_woocommerce_available_shipping_methods'), 10, 2);
                $this->add_rate($local_delivery);
            }

            /**
             * Check is free shipping or not
             * @param $coupon
             * @return string
             */
            function purolator_shipping_coupon_rate($coupon)
            {
                foreach ($coupon as $key => $value) {
                    if ($value->get_free_shipping() == 1) {
                        $rates = array(
                            'id' => $this->id . ':' . 'free',
                            'label' => 'Free Shipping',
                            'cost' => 0
                        );
                        $this->add_rate($rates);
                        return 'y';
                    }
                }
                return 'n';
            }

            /**
             * Virtual Products
             */
            function en_virtual_products()
            {
                global $woocommerce;
                $products = $woocommerce->cart->get_cart();
                $items = $product_name = [];
                foreach ($products as $key => $product_obj) {
                    $product = $product_obj['data'];
                    $is_virtual = $product->get_virtual();

                    if ($is_virtual == 'yes') {
                        $attributes = $product->get_attributes();
                        $product_qty = $product_obj['quantity'];
                        $product_title = str_replace(array("'", '"'), '', $product->get_title());
                        $product_name[] = $product_qty . " x " . $product_title;

                        $meta_data = [];
                        if (!empty($attributes)) {
                            foreach ($attributes as $attr_key => $attr_value) {
                                $meta_data[] = [
                                    'key' => $attr_key,
                                    'value' => $attr_value,
                                ];
                            }
                        }

                        $items[] = [
                            'id' => $product_obj['product_id'],
                            'name' => $product_title,
                            'quantity' => $product_qty,
                            'price' => $product->get_price(),
                            'weight' => 0,
                            'length' => 0,
                            'width' => 0,
                            'height' => 0,
                            'type' => 'virtual',
                            'product' => 'virtual',
                            'sku' => $product->get_sku(),
                            'attributes' => $attributes,
                            'variant_id' => 0,
                            'meta_data' => $meta_data,
                        ];
                    }
                }

                $virtual_rate = [];

                if (!empty($items)) {
                    $virtual_rate = [
                        'id' => 'en_virtual_rate',
                        'label' => 'Virtual Quote',
                        'cost' => 0,
                    ];

                    $virtual_fdo = [
                        'plugin_type' => 'ltl',
                        'plugin_name' => 'wwe_quests',
                        'accessorials' => '',
                        'items' => $items,
                        'address' => '',
                        'handling_unit_details' => '',
                        'rate' => $virtual_rate,
                    ];

                    $meta_data = [
                        'sender_origin' => 'Virtual Product',
                        'product_name' => wp_json_encode($product_name),
                        'en_fdo_meta_data' => $virtual_fdo,
                    ];

                    $virtual_rate['meta_data'] = $meta_data;

                }

                return $virtual_rate;
            }
        }
    }
}
