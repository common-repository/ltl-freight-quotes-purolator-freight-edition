<?php
/**
 * Purolator WooComerce Get Shipping Package Class
 * @package     Woocommerce Purolator Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

/**
 * Purolator Get Shipping Package Class
 */
class Purolator_LTL_Shipping_Get_Package 
{
    /**
     * hasLTLShipment
     * @var int 
     */
    public $hasLTLShipment  = 0;
    /**
     * Errors
     * @var varchar 
     */
    public $errors          = array();
    public $en_fdo_image_urls = array();
    /**
     * Grouping For Shipments
     * @global $wpdb
     * @param $package
     * @param $purolator_ltl_res_inst
     * @return boolean|int
     */
    function group_purolator_ltl_shipment( $package, $purolator_ltl_res_inst ) 
    {
        $wc_change_class        = new Woo_Update_Changes_Purolator_LTL();
        global $wpdb;
        $weight                 = 0;
        $dimensions             = 0;
        $purolator_ltl_enable   = false;
        $smallPluginExist       = 0;
        $calledMethod           = array();
        $purolator_ltl_zipcode  = (strlen(WC()->customer->get_shipping_postcode()) > 0) ? WC()->customer->get_shipping_postcode() : $wc_change_class->purolator_ltl_postcode();
        if( empty( $purolator_ltl_zipcode ) ){
            return FALSE;
        }
        
        $flat_rate_shipping_addon = apply_filters('en_add_flat_rate_shipping_addon', false);
        
        foreach ( $package['contents'] as $item_id => $values ) 
        {
            $_product                       = $values['data'];
            // Images for FDO
            $this->en_fdo_image_urls($values, $_product);

            // Flat rate pricing
            $en_flat_rate_price = $this->en_get_flat_rate_price($values, $_product);
            if ($flat_rate_shipping_addon && isset($en_flat_rate_price) && strlen($en_flat_rate_price) > 0) {
                continue;
            }

            $height                         = ceil(wc_get_dimension( $_product->get_height(), 'in' ) );
            $width                          = ceil( wc_get_dimension( $_product->get_width(), 'in' ) );
            $length                         = ceil( wc_get_dimension( $_product->get_length(), 'in' ) );
            $product_weight                 = round( wc_get_weight( $_product->get_weight(), 'lbs' ), 2);
            $weight                         = $product_weight * $values['quantity'];
            $dimensions                     = (($length * $values['quantity']) * $width * $height);            
            $locationId                     = 0;
            $origin_address                 = $this->purolator_ltl_get_origin ( $_product, $values, $purolator_ltl_res_inst, $purolator_ltl_zipcode );
            $locationId                     = $origin_address['locationId'];
            $purolator_ltl_package[$locationId]['origin']   = $origin_address;
            $get_freight                    = $this->purolator_ltl_get_freight_class( $values, $_product );
            $freightClass_ltl_gross         = ($get_freight['freight_class'] == 0) ? $get_freight['freight_class'] = "" : $get_freight['freight_class'];          
            $product_level_markup = $this->purolator_ltl_get_product_level_markup($_product, $values['variation_id'], $values['product_id'], $values['quantity']);  
            $eniturePluigns                 = json_decode(get_option('EN_Plugins'));
            if( !empty($eniturePluigns) ) {
                foreach ($eniturePluigns as $enIndex    => $enPlugin) {
                    $freightSmallClassName              = 'WC_' . $enPlugin;
                    if (!in_array($freightSmallClassName, $calledMethod)) {
                        if (class_exists($freightSmallClassName)) {
                            $smallPluginExist           = 1;
                        }
                        $calledMethod[]                 = $freightSmallClassName;
                    }
                }
            }            
            if( ( (  !empty($dimensions) ||  !empty($freightClass_ltl_gross) ) &&  !empty($product_weight) && isset($purolator_ltl_package[$locationId]['purolator_ltl']) && $purolator_ltl_package[$locationId]['purolator_ltl'] == 1 ) || $smallPluginExist == 1 ) {
                if($smallPluginExist != 1) {
                    if( !empty( $dimensions ) &&  !empty( $product_weight ) && $purolator_ltl_enable == 1  ) {
                        $freightClass_ltl_gross =  ( isset($freightClass_ltl_gross) && !empty($freightClass_ltl_gross) ) ? $freightClass_ltl_gross : 'DensityBased';
                    }
                }
            }
            $parent_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
            if(isset($values['variation_id']) && $values['variation_id'] > 0){
                $variation = wc_get_product($values['variation_id']);
                $parent_id = $variation->get_parent_id();
            }
            if( !empty( $origin_address ) ) {
                if (!$_product->is_virtual()) {                     
                    $purolator_ltl_package[$locationId]['items'][] = array(
                        'productId'             => $parent_id,
                        'productName'           => str_replace(array("'", '"'), '', $_product->get_name()),
                        'productQty'            => $values['quantity'],
                        'productPrice'          => $_product->get_price(),
                        'productWeight'         => $product_weight,
                        'productLength'         => $length,
                        'productWidth'          => $width,
                        'productHeight'         => $height,
                        'productClass'          => $freightClass_ltl_gross,
                        'markup'                => $product_level_markup
                    );
                }
                
            }
            // Hazardous Material
            $hazardous_material = $this->en_hazardous_material($values, $_product);

            if($hazardous_material == "yes" && !isset($purolator_ltl_package[$locationId]['hazardous_material']))
            {
                $purolator_ltl_package[$locationId]['hazardous_material'] = TRUE;
            }
            $purolator_ltl_enable           = $this->purolator_ltl_enable_shipping_class( $_product );
            $exceedWeight                   = get_option( 'en_plugins_return_LTL_quotes' );
            $weight_threshold               = get_option('en_weight_threshold_lfq');
            $weight_threshold               = isset($weight_threshold) && $weight_threshold > 0 ? $weight_threshold : 150;
            $purolator_ltl_package[$locationId]['shipment_weight'] = isset($purolator_ltl_package[$locationId]['shipment_weight']) ? $purolator_ltl_package[$locationId]['shipment_weight'] + $weight : $weight;
            if ($purolator_ltl_enable == true || ($purolator_ltl_package[$locationId]['shipment_weight'] > $weight_threshold && $exceedWeight == 'yes' ) ) {
                $purolator_ltl_package[$locationId]['purolator_ltl']    = 1;
                $this->hasLTLShipment       = 1;
            }elseif (isset($purolator_ltl_package[$locationId]['purolator_ltl'])) {
                $purolator_ltl_package[$locationId]['purolator_ltl'] = 1;
                $this->hasLTLShipment       = 1;
            } elseif($smallPluginExist == 1) {
                $purolator_ltl_package[$locationId]['small'] = 1;
            }
            if(empty($purolator_ltl_package[$locationId]['items'])){
                unset($purolator_ltl_package[$locationId]);
                $purolator_ltl_package[$locationId]["NOPARAM"] = 1;
            } 
        }
        return $purolator_ltl_package;
    } 
    function en_hazardous_material($values, $_product)
    {
        $post_id = ( isset($values['variation_id']) && $values['variation_id'] > 0 ) ? $values['variation_id'] : $_product->get_id();
        return get_post_meta($post_id, '_hazardousmaterials', true);
    }
    /**
     * Grouping For Shipment Quotes
     * @param $quotes
     * @param $smallQuotes
     * @param $handlng_fee
     * @return Total Cost
     */
    function purolator_ltl_grouped_quotes( $quotes, $smallQuotes, $handlng_fee )
    {
        $grandTotal     = 0;
        foreach ( $quotes as $multiValues ){
            if( isset( $multiValues ) && !empty( $multiValues ) ){
                $price_sorted_key = array();
                foreach ($multiValues as $key => $cost_carrier) {
                    $price_sorted_key[$key] = $cost_carrier['cost'];
                }
                array_multisort($price_sorted_key, SORT_ASC, $multiValues);
                $multiValues    = reset($multiValues);                
                if($handlng_fee != ''){
                    $grandTotal += $this->parse_handeling_fee($handlng_fee, $multiValues['cost']);
                }else{
                    $grandTotal += $multiValues['cost'];
                }         
            }else{
                $this->errors   = 'no quotes return'; 
                continue;
            }
        }
        if( count( $this->errors ) < 1 ) {
            $freight = array(
                'total'     => $grandTotal
            );
            return $freight;
        }
    }

    /**
     * Calculate Handeling Fee
     * @param $handlng_fee
     * @param $cost
     * @return Handling Fee
     */
    function parse_handeling_fee( $handlng_fee, $cost )
    {   
        $pos = strpos( $handlng_fee, '%' );
        if ($pos > 0) {
            $rest       = substr( $handlng_fee, $pos );
            $exp        = explode( $rest, $handlng_fee );
            $get        = $exp[0];
            $percnt     = $get / 100 * $cost;
            $grandTotal = $cost + $percnt;
        }else{
            $grandTotal = $cost + $handlng_fee;
        }  
        return $grandTotal;
    }

    /**
     * Calculate Small Package Rates
     * @param $smallQuotes
     * @return int
     */
    function getSmallPackagesCostpurolatorltl( $smallQuotes ) {
        $result = array();
        $minCostArr = array();
        if ( isset( $smallQuotes ) && count( $smallQuotes ) > 0 ) {
            foreach ( $smallQuotes as $smQuotes ) {
                $CostArr = array();
                if( !isset( $smQuotes['error'] ) ){
                    foreach ( $smQuotes as $smQuote ) {
                        $CostArr[] = $smQuote['cost'];
                        $result['error'] = false;
                    }
                    $minCostArr[] = min($CostArr);
                }
                else{
                    $result['error'] = !isset( $result['error'] ) ? true : $result['error'];
                }
            }
            $result['price'] = (isset($minCostArr) && count($minCostArr) > 0 )? min($minCostArr): "";
        }else{
            $result['error'] = false;
            $result['price'] = 0;
        }
        return $result;
    }

    /**
     * Get Shipment Origin
     * @global $wpdb
     * @param $_product
     * @param $values
     * @param $ltl_res_inst
     * @param $ltl_zipcode
     * @return Origin Address
     */
    function purolator_ltl_get_origin ( $_product, $values, $ltl_res_inst, $ltl_zipcode )
    {
        global $wpdb;

//      UPDATE QUERY In-store pick up                           
        $en_wd_update_query_string = apply_filters("en_wd_update_query_string", "");

        ( isset($values['variation_id']) && $values['variation_id'] > 0 ) ? $post_id = $values['variation_id'] : $post_id = $_product->get_id();
        $enable_dropship = get_post_meta($post_id, '_enable_dropship', true);
        if ($enable_dropship == 'yes') 
        {
            $get_loc = get_post_meta($post_id, '_dropship_location', true);
            if ($get_loc == '') {
                return array('error' => 'Purolator ltl dp location not found!');
            }

//          Multi Dropship
            $multi_dropship = apply_filters('purolator_ltl_quotes_quotes_plans_suscription_and_features' , 'multi_dropship');

            if(is_array($multi_dropship))
            {
                $locations_list = $wpdb->get_results(
                        "SELECT id, city, state, zip, country, location, origin_markup ".$en_wd_update_query_string."FROM " . $wpdb->prefix . "warehouse WHERE location = 'dropship' LIMIT 1"
                );
            }
            else
            {
                $get_loc = ( $get_loc !== '' ) ? maybe_unserialize($get_loc) : $get_loc;
                $get_loc = is_array($get_loc) ? implode(" ', '", $get_loc) : $get_loc;
                $locations_list = $wpdb->get_results(
                        "SELECT id, city, state, zip, country, location, origin_markup, nickname ".$en_wd_update_query_string."FROM " . $wpdb->prefix . "warehouse WHERE id IN ('" . $get_loc . "')"
                );
            }

            $eniture_debug_name = "Dropships";
        } 
        else 
        {

//          Multi Warehouse
            $multi_warehouse = apply_filters('purolator_ltl_quotes_quotes_plans_suscription_and_features' , 'multi_warehouse');
            if(is_array($multi_warehouse))
            {
                $locations_list = $wpdb->get_results(
                        "SELECT id, city, state, zip, country, location, origin_markup ".$en_wd_update_query_string."FROM " . $wpdb->prefix . "warehouse WHERE location = 'warehouse' LIMIT 1"
                );
            }
            else
            {
                $locations_list = $wpdb->get_results(
                        "SELECT id, city, state, zip, country, location, origin_markup ".$en_wd_update_query_string."FROM " . $wpdb->prefix . "warehouse WHERE location = 'warehouse'"
                );
            }

            $eniture_debug_name = "Warehouses";

        }

        do_action("eniture_debug_mood" , "Quotes $eniture_debug_name (s)" , $locations_list);

        $origin_address = $ltl_res_inst->purolator_ltl_multi_warehouse( $locations_list, $ltl_zipcode );
        return $origin_address;
    }

    /**
     * Check Product Freight Class
     * @param $values
     * @param $_product
     * @return Freight Class
     */
    function purolator_ltl_get_freight_class( $values, $_product )
    {
        if ( $_product->get_type() == 'variation' ) {
            $variation_class = get_post_meta( $values['variation_id'], '_ltl_freight_variation', true );
            if( $variation_class == 0 ){
                $variation_class = get_post_meta( $values['product_id'], '_ltl_freight', true );
                $freightClass_ltl_gross = $variation_class;
            }else{
                if ( $variation_class > 0 ) {
                    $freightClass_ltl_gross = get_post_meta( $values['variation_id'], '_ltl_freight_variation', true );
                } 
                else {
                    $freightClass_ltl_gross = get_post_meta( $_product->get_id(), '_ltl_freight', true );
                }
            }
        } 
        else {
            $freightClass_ltl_gross = get_post_meta( $_product->get_id(), '_ltl_freight', true );
        }
        return array('freight_class'=>$freightClass_ltl_gross);
    }

    /**
     * Check Product Enable Against LTL Freight
     * @param $_product
     * @return Shipping Class
     */
    function purolator_ltl_enable_shipping_class( $_product )
    {
        if( $_product->get_type() == 'variation' ) {
            $ship_class_id = $_product->get_shipping_class_id();
            if($ship_class_id == 0) {
                $parent_data          = $_product->get_parent_data();
                $get_parent_term      = get_term_by( 'id', $parent_data['shipping_class_id'], 'product_shipping_class' );
                $get_shipping_result  =  ( isset( $get_parent_term->slug ) ) ? $get_parent_term->slug : '';

            } else{
                $get_shipping_result = $_product->get_shipping_class();
            }
            $ltl_enable  = ($get_shipping_result && $get_shipping_result == 'ltl_freight') ?  true : false;
        } else  {
            $get_shipping_result  = $_product->get_shipping_class();
            $ltl_enable           = ($get_shipping_result  == 'ltl_freight') ?  true : false;
        }
        return $ltl_enable;
    }

    /**
    * Returns flat rate price and quantity
    */
    public function en_get_flat_rate_price($values, $_product)
    {
        if ($_product->get_type() == 'variation') {
            $flat_rate_price = get_post_meta($values['variation_id'], 'en_flat_rate_price', true);
            if (strlen($flat_rate_price) < 1) {
                $flat_rate_price = get_post_meta($values['product_id'], 'en_flat_rate_price', true);
            }
        } else {
            $flat_rate_price = get_post_meta($_product->get_id(), 'en_flat_rate_price', true);
        }

        return $flat_rate_price;
    }

    /**
    * Returns product level markup
    */
    public function purolator_ltl_get_product_level_markup($_product, $variation_id, $product_id, $quantity)
    {
        $product_level_markup = 0;
        if ($_product->get_type() == 'variation') {
            $product_level_markup = get_post_meta($variation_id, '_en_product_markup_variation', true);
            if(empty($product_level_markup) || $product_level_markup == 'get_parent'){
                $product_level_markup = get_post_meta($_product->get_id(), '_en_product_markup', true);
            }
        } else {
            $product_level_markup = get_post_meta($_product->get_id(), '_en_product_markup', true);
        }
        if(empty($product_level_markup)) {
            $product_level_markup = get_post_meta($product_id, '_en_product_markup', true);
        }
        if(!empty($product_level_markup) && strpos($product_level_markup, '%') === false 
        && is_numeric($product_level_markup) && is_numeric($quantity))
        {
            $product_level_markup *= $quantity;
        } else if(!empty($product_level_markup) && strpos($product_level_markup, '%') > 0 && is_numeric($quantity)){
            $position = strpos($product_level_markup, '%');
            $first_str = substr($product_level_markup, $position);
            $arr = explode($first_str, $product_level_markup);
            $percentage_value = $arr[0];
            $product_price = $_product->get_price();
 
            if (!empty($product_price)) {
                $product_level_markup = $percentage_value / 100 * ($product_price * $quantity);
            } else {
                $product_level_markup = 0;
            }
         }
 
        return $product_level_markup;
    }

    /**
     * Get images urls | Images for FDO
     * @param array type $values
     * @param array type $_product
     * @return array type
     */
    public function en_fdo_image_urls($values, $_product)
    {
        $product_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
        $gallery_image_ids = $_product->get_gallery_image_ids();
        foreach ($gallery_image_ids as $key => $image_id) {
            $gallery_image_ids[$key] = $image_id > 0 ? wp_get_attachment_url($image_id) : '';
        }

        $image_id = $_product->get_image_id();
        $this->en_fdo_image_urls[$product_id] = [
            'product_id' => $product_id,
            'image_id' => $image_id > 0 ? wp_get_attachment_url($image_id) : '',
            'gallery_image_ids' => $gallery_image_ids
        ];

        add_filter('en_fdo_image_urls_merge', [$this, 'en_fdo_image_urls_merge'], 10, 1);
    }

    /**
     * Set images urls | Images for FDO
     * @param array type $en_fdo_image_urls
     * @return array type
     */
    public function en_fdo_image_urls_merge($en_fdo_image_urls)
    {
        return array_merge($this->en_fdo_image_urls, $en_fdo_image_urls);
    }
}