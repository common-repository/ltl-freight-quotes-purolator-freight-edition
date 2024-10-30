<?php
/**
 * Purolator WooComerce Get ESTES LTL Quotes Rate Class
 * @package     Woocommerce Purolator Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get ESTES LTL Quotes Rate Class
 */
class Purolator_LTL_Get_Shipping_Quotes
{

    public $en_wd_origin_array;
    public $InstorPickupLocalDelivery;
    public $quote_settings;

    function __construct()
    {
        $this->quote_settings = array();
    }

    /**
     * Create Shipping Package
     * @param $packages
     * @return array/string
     */
    function purolator_ltl_shipping_array($packages, $package_plugin = "")
    {
//      set the default values of residential and tailgate delivery flags
        $purolator_ltl_tailgate = 'False';
        $purolator_ltl_residential = 'False';
        // FDO
        $EnPurolatorFdo = new EnPurolatorFdo();
        $en_fdo_meta_data = array();

//      set the value of tailgate delivery flag 
        $wc_always_tailgate = get_option('purolator_ltl_always_tailgate');
        $wc_option_tailgate = get_option('purolator_ltl_option_tailgate');

//      set the value of residential delivery flag 
        $wc_always_residential = get_option('purolator_ltl_always_include_residential');
        $wc_option_residential = get_option('purolator_ltl_option_residential');

//      check any option on quote setting is enable for tailgate delivery
        if (($wc_always_tailgate == 'yes') || ($wc_option_tailgate == 'yes')) $purolator_ltl_tailgate = 'True';

//      check any option on quote setting is enable for residential delivery
        if (($wc_always_residential == 'yes') || ($wc_option_residential == 'yes')) $purolator_ltl_residential = 'True';

        $wc_change_class = new Woo_Update_Changes_purolator_ltl();
        (strlen(WC()->customer->get_shipping_postcode()) > 0) ? $freight_zipcode = WC()->customer->get_shipping_postcode() : $freight_zipcode = $wc_change_class->purolator_ltl_postcode();
        (strlen(WC()->customer->get_shipping_state()) > 0) ? $freight_state = WC()->customer->get_shipping_state() : $freight_state = $wc_change_class->purolator_ltl_state();
        (strlen(WC()->customer->get_shipping_city()) > 0) ? $freight_city = WC()->customer->get_shipping_city() : $freight_city = $wc_change_class->purolator_ltl_city();
        (strlen(WC()->customer->get_shipping_country()) > 0) ? $freight_country = WC()->customer->get_shipping_country() : $freight_country = $wc_change_class->purolator_ltl_country();

        $this->en_wd_origin_array = (isset($packages['origin'])) ? $packages['origin'] : array();

        $access_level = get_option('purolator_ltl_setting_acccess_level');
        $stndrd_quotes = (get_option('service_purolator_ltl_stndrd_quotes') == 'yes') ? 'S' : '';
        $expedited_quotes = (get_option('service_purolator_ltl_expedited_quotes') == 'yes') ? 'I' : '';
        $servic_code = array_filter(array($stndrd_quotes, $expedited_quotes));
        $aPluginVersions = $this->purolator_ltl_wc_version_number();

        $domain = purolator_ltl_quotes_get_domain();
        $product_markup_shipment = $this->purolater_ltl_product_level_markup($packages);
        // FDO
        $en_fdo_meta_data = $EnPurolatorFdo->en_cart_package($packages);

        $post_data = array(
            'platform' => 'wordpress',
            'requestKey' => md5(microtime() . rand()),
            'plugin_version' => $aPluginVersions["purolator_ltl_plugin_version"],
            'wordpress_version' => get_bloginfo('version'),
            'woocommerce_version' => $aPluginVersions["woocommerce_plugin_version"],
            'licence_key' => get_option('purolator_ltl_plugin_licence_key'),
            'sever_name' => $this->purolator_ltl_parse_url($domain),
            'carrierName' => 'purolator-ltl',
            'carrier_mode' => 'pro',
            'productionPass' => get_option('purolator_ltl_production_password'),
            'registeredAccount' => get_option('purolator_ltl_reg_acc_num'),
            'productionKey' => get_option('purolator_ltl_production_key'),
            'accessLevel' => $access_level,
            'senderCity' => $packages['origin']['city'],
            'senderState' => $packages['origin']['state'],
            'senderZip' => $packages['origin']['zip'],
            'senderCountryCode' => $this->getCountryCode($packages['origin']['country']),
            'receiverCity' => $freight_city,
            'receiverState' => $freight_state,
            'receiverZip' => preg_replace('/\s+/', '', $freight_zipcode),
            'receiverCountryCode' => $freight_country,
            'serviceTypeCode' => $servic_code,
            'accessorial' => array(
                'TAILGATE' => $purolator_ltl_tailgate,
                'RESID' => $purolator_ltl_residential,
            ),
            'commdityDetails' => $this->purolator_ltl_lineItem($packages),
            'handlingUnitWeight' => get_option('handling_weight_purolator_ltl'),
            'maxWeightPerHandlingUnit' => get_option('maximum_handling_weight_purolator_ltl'),
            'origin_markup' => (isset($packages['origin']['origin_markup'])) ? $packages['origin']['origin_markup'] : 0,
            'product_level_markup' => $product_markup_shipment,
            'en_fdo_meta_data' => $en_fdo_meta_data
        );

        // Hazardous Material
        $hazardous_material = apply_filters('purolator_ltl_quotes_quotes_plans_suscription_and_features', 'hazardous_material');

        if (!is_array($hazardous_material)) {
            (isset($packages['hazardous_material'])) ? $post_data['accessorial']['DG'] = 'True' : 'False';
        }
        // FDO
        $post_data['en_fdo_meta_data'] = array_merge($post_data['en_fdo_meta_data'], $EnPurolatorFdo->en_package_hazardous($packages, $en_fdo_meta_data));

        // In-store pickup and local delivery
        $instore_pickup_local_devlivery_action = apply_filters('purolator_ltl_quotes_quotes_plans_suscription_and_features', 'instore_pickup_local_devlivery');

        if (!is_array($instore_pickup_local_devlivery_action)) {
            $post_data = apply_filters('en_wd_standard_plans', $post_data, $post_data['receiverZip'], $this->en_wd_origin_array, $package_plugin);
        }

//      Eniture debug mood
        do_action("eniture_debug_mood", "Prolator LTL Features", get_option('eniture_plugin_6'));
        do_action("eniture_debug_mood", "Quotes Request (Prolator LTL)", $post_data);
        do_action("eniture_debug_mood", "Build Query (Prolator LTL)", http_build_query($post_data));

        return $post_data;
    }

    /**
     * Purolator Line Items
     * @param $packages
     * @return string/array
     */
    function purolator_ltl_lineItem($packages)
    {
        $lineItem = array();
        foreach ($packages['items'] as $item) {
            $lineItem[] = array(
                'productName' => $item['productName'],
                'piecesOfLineItem' => $item['productQty'],
                'lineItemClass' => $item['productClass'],
                'lineItemWeight' => $item['productWeight'],
                'lineItemWidth' => $item['productWidth'],
                'lineItemHeight' => $item['productHeight'],
                'lineItemLength' => $item['productLength'],
                'lineItemPackageCode' => 'Pallet',
            );
        }
        return $lineItem;
    }

    /**
     * Check LTL Class For Product
     * @param $slug
     * @param $values
     * @return string
     * @global $woocommerce
     */
    function purolator_ltl_product_with_ltl_class($slug, $values)
    {
        global $woocommerce;
        $product_in_cart = false;
        $_product = $values['data'];
        $terms = get_the_terms($_product->get_id(), 'product_shipping_class');
        if ($terms) {
            foreach ($terms as $term) {
                $_shippingclass = "";
                $_shippingclass = $term->slug;
                if ($slug === $_shippingclass) {
                    $product_in_cart[] = $_shippingclass;
                }
            }
        }
        return $product_in_cart;
    }

    /**
     * Get Nearest Address If Multiple Warehouses
     * @param $warehous_list
     * @param $receiverZipCode
     * @return Warehouse Address
     */
    function purolator_ltl_multi_warehouse($warehous_list, $receiverZipCode)
    {
        if (count($warehous_list) == 1) {
            $warehous_list = reset($warehous_list);
            return $this->purolator_ltl_origin_array($warehous_list);
        }
        foreach ($warehous_list as $key => $value) {

            $origin_zip = preg_replace('/\s+/', '', $value->zip);
            $map_address = array('originZipCode' => $origin_zip, 'destinationZipCode' => preg_replace('/\s+/', '', $receiverZipCode));

            $accessLevel = 'distance';
            $purolator_distance_request = new Get_purolater_ltl_distance();
            $response_json = $purolator_distance_request->purolater_ltl_get_distance($map_address, $accessLevel);
            $json_decode = json_decode($response_json);

            if (isset($json_decode->origin_addresses) && in_array($origin_zip, (array)$json_decode)) {
                $dis = array('address' => $json_decode->origin_addresses[0], 'distance' => $json_decode->rows[0]->elements[0]->distance);
                $distance = $dis['distance'];
                $distance_array['distance_km'][] = $distance->text;
                $distance_array['distance_m'][] = $distance->value;
                $distance_array['origin'][] = $dis['address'];
                $distance_array['origin']['location'] = $value->location;
                $distance_array['id'][] = $value->id;
            }
        }
        if (isset($distance_array)) {
            $array = array_keys($distance_array['distance_m'], min($distance_array['distance_m']));
            $n = $array[0];
            $origin = explode(",", $distance_array['origin'][$n]);

            $zip = explode(" ", trim($origin[1]));
            $state = $zip[0];
            unset($zip[0]);
            $zip = implode('', $zip);

            $origin_address = array();
            $origin_address['city'] = trim($origin[0]);
            $origin_address['state'] = $state;
            $origin_address['zip'] = $zip;
            $origin_address['country'] = trim($origin[2]);

            if (trim($origin[2]) == 'USA') {
                $origin[2] = 'US';
            } elseif (trim($origin[2]) == 'Canada') {
                $origin[2] = 'CA';
            }

            $origin_address['country'] = $origin[2];
            $origin_address['location'] = $distance_array['origin']['location'];
            $origin_address['id'] = $distance_array['id'][$n];
        } else {
            $origin_address = $warehous_list[0];
        }

        return $this->purolator_ltl_origin_array((object)$origin_address);
    }

    /**
     * Create Origin Array
     * @param $origin
     * @return Warehouse Address Array
     */
    function purolator_ltl_origin_array($origin)
    {

//      In-store pickup and local delivery
        if (has_filter("en_wd_origin_array_set")) {
            return apply_filters("en_wd_origin_array_set", $origin);
        }

        $origin = array(
            'locationId' => $origin->id,
            'zip' => $origin->zip,
            'city' => $origin->city,
            'state' => $origin->state,
            'location' => $origin->location,
            'country' => $origin->country
        );
        return $origin;
    }

    /**
     * Refine URL
     * @param $domain
     * @return Domain URL
     */
    function purolator_ltl_parse_url($domain)
    {
        $domain = trim($domain);
        $parsed = parse_url($domain);
        if (empty($parsed['scheme'])) {
            $domain = 'http://' . ltrim($domain, '/');
        }
        $parse = parse_url($domain);
        $refinded_domain_name = $parse['host'];
        $domain_array = explode('.', $refinded_domain_name);
        if (in_array('www', $domain_array)) {
            $key = array_search('www', $domain_array);
            unset($domain_array[$key]);
            $refinded_domain_name = implode($domain_array, '.');
        }
        return $refinded_domain_name;
    }

    /**
     * Curl Request To Get Quotes
     * @param $request_data
     * @return Curl Json response
     */
    function purolator_ltl_get_web_quotes($request_data)
    {
        if (is_array($request_data) && count($request_data) > 0) {
            $purolator_curl_obj = new Purolator_Feight_Curl_Request();
            $output = $purolator_curl_obj->purolator_freight_get_curl_response(PUROLATOR_FREIGHT_DOMAIN_HITTING_URL . '/index.php', $request_data);

            do_action("eniture_debug_mood", "Quotes Response(Purolator LTL)", json_decode($output));

            $response = json_decode($output);
            $this->InstorPickupLocalDelivery = (isset($response->InstorPickupLocalDelivery) ? $response->InstorPickupLocalDelivery : NULL);

//          Eniture debug mood

            do_action("eniture_debug_mood", "Quotes Response (Prolater LTL)", $output);

            return $this->parse_purolator_ltl_output(json_decode($output), $request_data);
        }
    }

    /**
     * Get Shipping Array For Single Shipment
     * @param $output
     * @return Single Quote Array
     */
    function parse_purolator_ltl_output($output, $request_data)
    {
        $accessorials = array();
        $result = $output;
        $en_fdo_meta_data = (isset($request_data['en_fdo_meta_data'])) ? $request_data['en_fdo_meta_data'] : '';
        if (isset($result->debug)) {
            $en_fdo_meta_data['handling_unit_details'] = $result->debug;
        }

        ($this->quote_settings['tailgate_delivery'] == "yes") ? $accessorials[] = "T" : "";
        ($this->quote_settings['tailgate_delivery_option'] == "yes") ? $accessorials[] = "TO" : "";
        ($this->quote_settings['residential_delivery'] == "yes") ? $accessorials[] = "R" : "";
        ($this->quote_settings['residential_delivery_option'] == "yes") ? $accessorials[] = "R0" : "";

        $label_sufex_arr = array();
        $residential_quotes = $tailgate_quotes = $tailgate_residential_quotes = array();
        if (isset($result->q)) {
            $stndrd_label = $this->quote_settings['label_standard'];
            $expited_label = $this->quote_settings['label_expedited'];

            $count = 0;
            foreach ($result->q as $key => $services) {

                ($key == 'S') ? $title = $stndrd_label : '';
                ($key == 'I') ? $title = $expited_label : '';
                ($key == 'S' && empty($title)) ? $title = 'Freight Standard' : '';
                ($key == 'I' && empty($title)) ? $title = 'Freight Expedited' : '';
                ($key == 'S') ? $action = 'standard' : '';
                ($key == 'I') ? $action = 'expedited' : '';


                $price = $services->TotalPrice;
                $transit = $services->TransitDays;
                if ($transit == 0 && !empty($services->totalTransitTimeInDays)) {
                    $transit = $services->totalTransitTimeInDays;
                }

                // Product level markup
                if (!empty($request_data['product_level_markup'])) {
                    $price = $this->add_handling_fee($price, $request_data['product_level_markup']);
                }

                // Origin level markup
                if (!empty($request_data['origin_markup'])) {
                    $price = $this->add_handling_fee($price, $request_data['origin_markup']);
                }

                $surcharges = isset($services->AccessorialDetails->AccessorialItem) ? $services->AccessorialDetails->AccessorialItem : [];

                $quotes[$count] = array(
                    'id' => 'normal_' . $action,
                    'cost' => $price,
                    'label' => $title,
                    'label_suffix' => $label_sufex_arr,
                    'action' => $action,
                    'transit_time' => $transit,
                    'surcharges' => $surcharges,
                    'plugin_name' => 'purolator-ltl',
                    'plugin_type' => 'ltl',
                    'owned_by' => 'eniture',
                );

                //FDO
                $en_fdo_meta_data['rate'] = $quotes[$count];
                if (isset($en_fdo_meta_data['rate']['meta_data'])) {
                    unset($en_fdo_meta_data['rate']['meta_data']);
                }
                $en_fdo_meta_data['quote_settings'] = $this->quote_settings;
                $quotes[$count]['meta_data']['en_fdo_meta_data'] = $en_fdo_meta_data;

                // in case of tailgate delivery and residential delivery are enable
                if (($this->quote_settings['tailgate_delivery_option'] == "yes") && ($this->quote_settings['residential_delivery_option'] == "yes") && (isset($services->AccessorialDetails->AccessorialItem))) {
                    foreach ($services->AccessorialDetails->AccessorialItem as $key => $data) {
                        if ($data->Code == 'TAILGATE') {
                            $tailgate_fee = $data->Charge;
                        }
                        if ($data->Code == 'RESID') {
                            $residential_fee = $data->Charge;
                        }
                    }

                    // calculations for tailgate delivery and residential delivery
                    $normal_fee = $price - $tailgate_fee - $residential_fee;
                    $with_residential_fee = $normal_fee + $residential_fee;
                    $with_tailgate_fee = $normal_fee + $tailgate_fee;
                    $save_counter = $count;

                    // tailgate delivery with residential array for accessorial
                    $tailgate_residential_quotes[$count++] = array(
                        'id' => 'tailgate_residential_delivery_option_' . $action,
                        'label' => $title,
                        'cost' => $normal_fee,
                        'action' => $action,
                        'transit_time' => $transit,
                        'surcharges' => $surcharges,
                        'plugin_name' => 'purolator-ltl',
                        'plugin_type' => 'ltl',
                        'owned_by' => 'eniture',
                    );

                    $quotes[$save_counter]['label_suffix'] = array('T', 'R');
                    // FDO meta data
                    isset($quotes[$save_counter]['meta_data']['en_fdo_meta_data']['accessorials']['residential']) ? $quotes[$save_counter]['meta_data']['en_fdo_meta_data']['accessorials']['residential'] = true : '';
                    isset($quotes[$save_counter]['meta_data']['en_fdo_meta_data']['accessorials']['liftgate']) ? $quotes[$save_counter]['meta_data']['en_fdo_meta_data']['accessorials']['liftgate'] = true : '';

                    $en_fdo_meta_data['rate'] = $tailgate_residential_quotes[$count - 1];
                    isset($en_fdo_meta_data['accessorials']['residential']) ? $en_fdo_meta_data['accessorials']['residential'] = false : '';
                    isset($en_fdo_meta_data['accessorials']['liftgate']) ? $en_fdo_meta_data['accessorials']['liftgate'] = false : '';

                    if (isset($en_fdo_meta_data['rate']['meta_data'])) {
                        unset($en_fdo_meta_data['rate']['meta_data']);
                    }
                    $tailgate_residential_quotes[$count - 1]['meta_data']['en_fdo_meta_data'] = $en_fdo_meta_data;

                    // tailgate delivery array for accessorial
                    $tailgate_residential_quotes[$count++] = array(
                        'id' => 'tailgate_delivery_option_' . $action,
                        'label' => $title,
                        'cost' => $with_tailgate_fee,
                        'label_suffix' => array('T'),
                        'action' => $action,
                        'transit_time' => $transit,
                        'surcharges' => $surcharges,
                        'plugin_name' => 'purolator-ltl',
                        'plugin_type' => 'ltl',
                        'owned_by' => 'eniture'
                    );
                    
                    // FDO
                    $en_fdo_meta_data['rate'] = $tailgate_residential_quotes[$count - 1];
                    isset($en_fdo_meta_data['accessorials']['residential']) ? $en_fdo_meta_data['accessorials']['residential'] = false : '';
                    isset($en_fdo_meta_data['accessorials']['liftgate']) ? $en_fdo_meta_data['accessorials']['liftgate'] = true : '';
                    if (isset($en_fdo_meta_data['rate']['meta_data'])) {
                        unset($en_fdo_meta_data['rate']['meta_data']);
                    }
                    $tailgate_residential_quotes[$count - 1]['meta_data']['en_fdo_meta_data'] = $en_fdo_meta_data;

                    // residential delivery array for accessorial
                    $tailgate_residential_quotes[$count++] = array(
                        'id' => 'residential_delivery_option_' . $action,
                        'label' => $title,
                        'cost' => $with_residential_fee,
                        'label_suffix' => array('R'),
                        'action' => $action,
                        'transit_time' => $transit,
                        'surcharges' => $surcharges,
                        'plugin_name' => 'purolator-ltl',
                        'plugin_type' => 'ltl',
                        'owned_by' => 'eniture'
                    );

                    // FDO
                    $en_fdo_meta_data['rate'] = $tailgate_residential_quotes[$count - 1];
                    isset($en_fdo_meta_data['accessorials']['residential']) ? $en_fdo_meta_data['accessorials']['residential'] = true : '';
                    isset($en_fdo_meta_data['accessorials']['liftgate']) ? $en_fdo_meta_data['accessorials']['liftgate'] = false : '';
                    if (isset($en_fdo_meta_data['rate']['meta_data'])) {
                        unset($en_fdo_meta_data['rate']['meta_data']);
                    }
                    $tailgate_residential_quotes[$count - 1]['meta_data']['en_fdo_meta_data'] = $en_fdo_meta_data;
                } 
                // in case of only tailgate delivery option is enable
                else if (($this->quote_settings['tailgate_delivery_option'] == "yes") && (isset($services->AccessorialDetails->AccessorialItem))) {
                    foreach ($services->AccessorialDetails->AccessorialItem as $key => $data) {
                        if ($data->Code == 'TAILGATE') {
                            $tailgate_fee = $data->Charge;
                        }
                    }
                    $normal_fee = $price - $tailgate_fee;
                    $tailgate_quotes[$count] = array(
                        'id' => 'tailgate_delivery_option_' . $action,
                        'label' => $title,
                        'cost' => $normal_fee,
                        'action' => $action,
                        'transit_time' => $transit,
                        'surcharges' => $surcharges,
                        'plugin_name' => 'purolator-ltl',
                        'plugin_type' => 'ltl',
                        'owned_by' => 'eniture'
                    );

                    $quotes[$count]['label_suffix'] = array('T');
                    // FDO meta data
                    isset($quotes[$count]['meta_data']['en_fdo_meta_data']['accessorials']['liftgate']) ? $quotes[$count]['meta_data']['en_fdo_meta_data']['accessorials']['liftgate'] = true : '';
                    isset($quotes[$count]['meta_data']['en_fdo_meta_data']['accessorials']['residential']) ? $quotes[$count]['meta_data']['en_fdo_meta_data']['accessorials']['residential'] = in_array('R', $accessorials) : '';

                    // FDO
                    $en_fdo_meta_data['rate'] = $tailgate_quotes[$count];
                    isset($en_fdo_meta_data['accessorials']['residential']) ? $en_fdo_meta_data['accessorials']['residential'] = in_array('R', $accessorials) : '';
                    isset($en_fdo_meta_data['accessorials']['liftgate']) ? $en_fdo_meta_data['accessorials']['liftgate'] = false : '';
                    if (isset($en_fdo_meta_data['rate']['meta_data'])) {
                        unset($en_fdo_meta_data['rate']['meta_data']);
                    }
                    $tailgate_quotes[$count]['meta_data']['en_fdo_meta_data'] = $en_fdo_meta_data;
                } 
                // in case of only residential delivery option is enable
                else if (($this->quote_settings['residential_delivery_option'] == "yes") && (isset($services->AccessorialDetails->AccessorialItem))) {
                    foreach ($services->AccessorialDetails->AccessorialItem as $key => $data) {
                        if ($data->Code == 'RESID') {
                            $residential_fee = $data->Charge;
                        }
                    }
                    $normal_fee = $price - $residential_fee;
                    $residential_quotes[$count] = array(
                        'id' => 'residential_delivery_option_' . $action,
                        'label' => $title,
                        'cost' => $normal_fee,
                        'action' => $action,
                        'transit_time' => $transit,
                        'surcharges' => $surcharges,
                        'plugin_name' => 'purolator-ltl',
                        'plugin_type' => 'ltl',
                        'owned_by' => 'eniture'
                    );

                    $quotes[$count]['label_suffix'] = array('R');
                    // FDO meta data
                    isset($quotes[$count]['meta_data']['en_fdo_meta_data']['accessorials']['residential']) ? $quotes[$count]['meta_data']['en_fdo_meta_data']['accessorials']['residential'] = true : '';
                    isset($quotes[$count]['meta_data']['en_fdo_meta_data']['accessorials']['liftgate']) ? $quotes[$count]['meta_data']['en_fdo_meta_data']['accessorials']['liftgate'] = in_array('T', $accessorials) : '';
                    
                    // FDO
                    $en_fdo_meta_data['rate'] = $residential_quotes[$count];
                    isset($en_fdo_meta_data['accessorials']['residential']) ? $en_fdo_meta_data['accessorials']['residential'] = false : '';
                    isset($en_fdo_meta_data['accessorials']['liftgate']) ? $en_fdo_meta_data['accessorials']['liftgate'] = in_array('T', $accessorials) : '';
                    if (isset($en_fdo_meta_data['rate']['meta_data'])) unset($en_fdo_meta_data['rate']['meta_data']);
                    $residential_quotes[$count]['meta_data']['en_fdo_meta_data'] = $en_fdo_meta_data;
                }
                $count++;
            }

            (!empty($residential_quotes)) ? $quotes['residential_quotes'] = $residential_quotes : "";
            (!empty($tailgate_quotes)) ? $quotes['tailgate_quotes'] = $tailgate_quotes : "";
            (!empty($tailgate_residential_quotes)) ? $quotes['tailgate_residential_quotes'] = $tailgate_residential_quotes : "";

            return $quotes;
        }
    }

    public function update_parse_purolator_quotes_output($surcharges)
    {

        $surcharge_amount = array();
        foreach ($surcharges as $key => $surcharge) {
            $surcharge_amount[$key] = $surcharge;
        }

        return $surcharge_amount;
    }

    function add_handling_fee($price, $handling_fee)
    {
        $handelingFee = 0;
        if ($handling_fee != '' && $handling_fee != 0) {
            if (strrchr($handling_fee, "%")) {

                $prcnt = (float)$handling_fee;
                $handelingFee = (float)$price / 100 * $prcnt;
            } else {
                $handelingFee = (float)$handling_fee;
            }
        }

        $handelingFee = $this->smooth_round($handelingFee);

        $price = (float)$price + $handelingFee;
        return $price;
    }

    /**
     * Change Country Code
     * @param $country
     * @return Country Conde
     */
    function getCountryCode($country)
    {
        $countryCode = $country;
        $country = strtolower($country);
        switch ($country) {
            case 'usa':
                $countryCode = 'US';
                break;
            case 'can':
                $countryCode = 'CA';
                break;
            case 'cn':
                $countryCode = 'CA';
                break;
            default:
                $countryCode = strtoupper($country);
                break;
        }
        return $countryCode;
    }

    /**
     * Plugin and WooCommerce version number
     * @return plugin and wooCommerce version number
     */
    function purolator_ltl_wc_version_number()
    {
        if (!function_exists('get_plugins'))
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');

        $plugin_folder = get_plugins('/' . 'woocommerce');
        $plugin_file = 'woocommerce.php';
        $purolator_ltl_plugin_folders = get_plugins('/' . 'ltl-freight-quotes-purolator-freight-edition');
        $purolator_ltl_plugin_files = 'ltl-freight-quotes-purolator-edition.php';
        $wc_plugin = (isset($plugin_folder[$plugin_file]['Version'])) ? $plugin_folder[$plugin_file]['Version'] : "";
        $purolator_ltl_plugin = (isset($purolator_ltl_plugin_folders[$purolator_ltl_plugin_files]['Version'])) ? $purolator_ltl_plugin_folders[$purolator_ltl_plugin_files]['Version'] : "";

        $pluginVersions = array(
            "woocommerce_plugin_version" => $wc_plugin,
            "purolator_ltl_plugin_version" => $purolator_ltl_plugin
        );
        return $pluginVersions;
    }

    /**
     * Return Fedex Freight In-store Pickup Array
     */
    function purolater_ltl_return_local_delivery_store_pickup()
    {
        return $this->InstorPickupLocalDelivery;
    }

    function purolater_ltl_product_level_markup($packages)
    {
        $product_markup_shipment = 0;
        foreach ($packages['items'] as $item) {
            if(!empty($item['markup']) && is_numeric($item['markup'])) {
                $product_markup_shipment += $item['markup'];
            }
        }

        return $product_markup_shipment;
    }

    /**
     *
     * @param float type $val
     * @param int type $min
     * @param int type $max
     * @return float type
     */
    function smooth_round($val, $min = 2, $max = 4)
    {
        $result = round($val, $min);

        if ($result == 0 && $min < $max) {
            return $this->smooth_round($val, ++$min, $max);
        } else {
            return $result;
        }
    }
}
