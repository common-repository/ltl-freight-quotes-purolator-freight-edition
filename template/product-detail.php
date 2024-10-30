<?php
/**
 * Purolator WooComerce Product Detail Page
 * @package     Woocommerce Purolator Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!has_filter('En_Plugins_freight_classification_filter')) {
    add_action('woocommerce_product_options_shipping', 'purolator_ltl_freight_class', 20);
    add_filter('En_Plugins_freight_classification_filter', 'purolator_ltl_freight_class_filter', 20, 1);
}
/**
 * LTL Freight Class
 * @param $freight_clasification
 * @return string
 */
function purolator_ltl_freight_class_filter($freight_clasification)
{
    return $freight_clasification;
}

/**
 * LTL Freight Class
 */
function purolator_ltl_freight_class()
{
    $classes = purolator_ltl_freight_class_array();

    $freight_clasification = woocommerce_wp_select(
        array(
            'id' => '_ltl_freight',
            'label' => __('Freight classification', 'woocommerce'),
            'options' => $classes
        )
    );
    apply_filters('En_Plugins_freight_classification_filter', $freight_clasification);
    purolator_ltl_product_markup_field();
}

/**
 * LTL Freight Classes List
 * @return LTL Freight Class array
 */
function purolator_ltl_freight_class_array()
{
    $classification = array(
        '0' => __('No Freight Class', 'woocommerce'),
        '50' => __('50', 'woocommerce'),
        '55' => __('55', 'woocommerce'),
        '60' => __('60', 'woocommerce'),
        '65' => __('65', 'woocommerce'),
        '70' => __('70', 'woocommerce'),
        '77.5' => __('77.5', 'woocommerce'),
        '85' => __('85', 'woocommerce'),
        '92.5' => __('92.5', 'woocommerce'),
        '100' => __('100', 'woocommerce'),
        '110' => __('110', 'woocommerce'),
        '125' => __('125', 'woocommerce'),
        '150' => __('150', 'woocommerce'),
        '175' => __('175', 'woocommerce'),
        '200' => __('200', 'woocommerce'),
        '225' => __('225', 'woocommerce'),
        '250' => __('250', 'woocommerce'),
        '300' => __('300', 'woocommerce'),
        '400' => __('400', 'woocommerce'),
        '500' => __('500', 'woocommerce'),
        'DensityBased' => __('Density Based', 'woocommerce')
    );
    return $classification;
}

if (!has_filter('En_Plugins_variable_freight_classification_filter')) {
    add_action('woocommerce_product_after_variable_attributes', 'purolator_ltl_variable_fields', 20, 3);
    add_filter('En_Plugins_variable_freight_classification_filter', 'purolator_ltl_freight_class_filter', 20, 1);
}
/**
 * Freight Class For Variations
 * @param $freight_clasification
 * @return string
 */
function purolator_ltl_variable_freight_class_filter($freight_clasification)
{
    return $freight_clasification;
}

/**
 * Freight Class For Variations
 * @param $loop
 * @param $variation_data
 * @param $variation
 */
function purolator_ltl_variable_fields($loop, $variation_data, $variation)
{
    $classes = purolator_ltl_freight_class_array();
    $replacement = array(0 => "Same as parent");
    $options = array_replace($classes, $replacement);
    $freight_clasification = woocommerce_wp_select(
        array(
            'id' => '_ltl_freight_variation[' . $variation->ID . ']',
            'label' => __('Freight classification <br>', 'woocommerce'),
            'value' => get_post_meta($variation->ID, '_ltl_freight_variation', true),
            'options' => $options
        )
    );
    apply_filters('En_Plugins_variable_freight_classification_filter', $freight_clasification);
    purolator_ltl_product_markup_field($variation);
}

/*
* 
*/
/**
 * Purolator LTL Dropship Section At Product Detail
 * @param $loop
 * @param $variation_data
 * @param $variation
 * @global $wpdb
 */
function purolator_ltl_dropship($loop, $variation_data = array(), $variation = array())
{
    global $wpdb;
    $dropship_list = $wpdb->get_results("SELECT id, city, state, zip, country, location, nickname FROM " . $wpdb->prefix . "warehouse WHERE location = 'dropship'");
    if (!empty($dropship_list)) {

        (isset($variation->ID) && $variation->ID > 0) ? $variationID = $variation->ID : $variationID = get_the_ID();

        // create enable dropship checkbox.
        woocommerce_wp_checkbox(
            array(
                'id' => '_enable_dropship[' . $variationID . ']',
                'label' => __('Enable drop ship location', 'woocommerce'),
                'value' => get_post_meta($variationID, '_enable_dropship', true),
            )
        );

        $attributes = array(
            'id' => '_dropship_location[' . $variationID . ']',
            'class' => 'p_ds_location',
        );

        $get_loc = maybe_unserialize(get_post_meta($variationID, '_dropship_location', true));


        $valuesArr = array();
        foreach ($dropship_list as $list) {
            (isset($list->nickname) && $list->nickname == '') ? $nickname = '' : $nickname = $list->nickname . ' - ';
            (isset($list->country) && $list->country == '') ? $country = '' : $country = '(' . $list->country . ')';
            $location = $nickname . $list->zip . ', ' . $list->city . ', ' . $list->state . ' ' . $country;
            $finalValue['option_id'] = $list->id;
            $finalValue['option_value'] = $list->id;
            $finalValue['option_label'] = $location;
            $valuesArr[] = $finalValue;
        }

        $aFields[] = array(
            'attributes' => $attributes,
            'label' => 'Drop ship location',
            'value' => $valuesArr,
            'name' => '_dropship_location[' . $variationID . '][]',
            'type' => 'select',
            'selected_value' => $get_loc,
            'variant_id' => $variationID
        );

        $aFields = apply_filters('before_wwe_ltl_product_detail_fields', $aFields);

        $fieldsHtml = '';
        foreach ($aFields as $key => $sField) {
            $sField = apply_filters('wwe_ltl_product_detail_fields', $sField);
            $fieldsHtml = purolator_ltl_dropship_html($sField, $fieldsHtml, $get_loc, $variationID);
        }
        $fieldsHtml = apply_filters('after_wwe_ltl_product_detail_fields', $fieldsHtml);
        echo $fieldsHtml;
    }
}

/*
* 
*/

if (!has_filter('En_Plugins_dropship_filter')) {
    add_action('woocommerce_product_options_shipping', 'purolator_ltl_dropship');
    add_action('woocommerce_product_after_variable_attributes', 'purolator_ltl_dropship', 10, 3);
    add_filter('En_Plugins_dropship_filter', 'purolator_ltl_dropship_filter', 10, 3);
}
/**
 * Dropship Filter
 * @param $aFields
 * @param $get_loc
 * @param $variationID
 */
function purolator_ltl_dropship_filter($aFields, $get_loc, $variationID)
{
    $fieldsHtml = '';
    foreach ($aFields as $key => $sField) {
        $sField = apply_filters('wwe_ltl_product_detail_fields', $sField);
        $fieldsHtml = purolator_ltl_dropship_html($sField, $fieldsHtml, $get_loc, $variationID);
    }
    $fieldsHtml = apply_filters('after_wwe_ltl_product_detail_fields', $fieldsHtml);
    echo $fieldsHtml;
}

/**
 * Custom Product Shipping Fields Save
 * @param $post_id
 */
function purolator_ltl_woo_add_custom_general_fields_save($post_id)
{
    $woocommerce_select = isset($_POST['_ltl_freight']) ? $_POST['_ltl_freight'] : '';
    $woocommerce_checkbox = isset($_POST['_enable_dropship'][$post_id]) ? $_POST['_enable_dropship'][$post_id] : '';
    $ds_locaton = $_POST['_dropship_location'][$post_id];
    $dropship_location = isset($ds_locaton) && is_array($ds_locaton) ? array_map('intval', $ds_locaton) : $ds_locaton;
    $product_markup = isset($_POST['_en_product_markup']) ? $_POST['_en_product_markup'] : '';

    update_post_meta($post_id, '_ltl_freight', esc_attr($woocommerce_select));
    update_post_meta($post_id, '_enable_dropship', esc_attr($woocommerce_checkbox));
    update_post_meta($post_id, '_dropship_location', maybe_serialize($dropship_location));
    update_post_meta($post_id, '_en_product_markup', esc_attr($product_markup));
}

/**
 * Custom Product Variation Fields Save
 * @param $post_id
 */
function purolator_ltl_save_variable_fields($post_id)
{
    if (isset($post_id) && $post_id > 0) {
        $_select = isset($_POST['_ltl_freight_variation'][$post_id]) ? $_POST['_ltl_freight_variation'][$post_id] : '';
        $enable_ds = (isset($_POST['_enable_dropship'][$post_id]) ? $_POST['_enable_dropship'][$post_id] : "");
        $ds_locaton = $_POST['_dropship_location'][$post_id];
        $dropship_location = isset($ds_locaton) && is_array($ds_locaton) ? array_map('intval', $ds_locaton) : $ds_locaton;
        $product_markup = isset($_POST['_en_product_markup_variation'][$post_id]) ? $_POST['_en_product_markup_variation'][$post_id] : '';

        update_post_meta($post_id, '_enable_dropship', esc_attr($enable_ds));

        if (isset($dropship_location)) {
            update_post_meta($post_id, '_dropship_location', maybe_serialize($dropship_location));
        }

        if (isset($_select)) {
            update_post_meta($post_id, '_ltl_freight_variation', esc_attr($_select));
        }

        update_post_meta($post_id, '_en_product_markup_variation', esc_attr($product_markup));
    }
}

/**
 * Attribute For Drop Ship Dropdown
 * @param $attributes
 * @return Attribute String
 */
function purolator_ltl_attributes_string($attributes)
{
    $str = '';
    foreach ($attributes as $key => $sAttribute) {
        $str .= ' ' . $key . ' ="' . $sAttribute . '" ';
    }
    return $str;
}

/**
 * Drop Ship Dropdown Select
 * @param $sField
 * @param $fieldsHtml
 * @param $get_loc
 * @param $variantId
 * @return string Drop Ship Dropdown HTML
 */
function purolator_ltl_dropship_html($sField, $fieldsHtml, $get_loc, $variantId)
{

    $description = "";
    $disable_me = FALSE;
    $dropship_flag = count($sField['value']);
    $dropship_flag = isset($dropship_flag) && ($dropship_flag > 1) ? true : false;

    $plan_notifi = apply_filters('en_woo_plans_notification_action', array());

    if (!empty($plan_notifi) && (isset($plan_notifi['multi_dropship']))) {
        $enable_plugins = (isset($plan_notifi['multi_dropship']['enable_plugins'])) ? $plan_notifi['multi_dropship']['enable_plugins'] : "";
        $disable_plugins = (isset($plan_notifi['multi_dropship']['disable_plugins'])) ? $plan_notifi['multi_dropship']['disable_plugins'] : "";
        if (strlen($disable_plugins) > 0) {
            if (strlen($enable_plugins) > 0) {
                $description = "<br><br>" . apply_filters('en_woo_plans_notification_message_action', $enable_plugins, $disable_plugins);
            } else {

                if ($dropship_flag && get_option('purolater_quotes_store_type') == "1") {
//                  new user and multiple dropship then show msg standard required                
                    $description = apply_filters('purolator_ltl_quotes_plans_notification_link', array(2));
                    $disable_me = TRUE;
                } elseif (get_option('purolater_quotes_store_type') == "0" && get_option('en_old_user_dropship_status') == "1") {
//                  old user and single dropship then show msg standard required
                    $description = apply_filters('purolator_ltl_quotes_plans_notification_link', array(2));
                    $disable_me = TRUE;
                }
            }
        }
    }
    $str = purolator_ltl_attributes_string($sField['attributes']);
    $disable_dropship_flage = true;
    $multi_dropship = apply_filters('purolator_ltl_quotes_quotes_plans_suscription_and_features', 'multi_dropship');

    if (get_option('purolater_quotes_store_type') == "0" && get_option('en_old_user_dropship_status') == "0") {
        $disable_dropship_flage = false;
    }

    $fieldsHtml .= '<p class="form-field _dropship_location">';
    $fieldsHtml .= '<label for="_dropship_location">' . $sField['label'] . '</label>';
    if ($sField['type'] == 'select') {
        $fieldsHtml .= '<select name="' . $sField['name'] . '" ' . $str . '>';
        if ($sField['value']) {
            $count = 0;
            foreach ($sField['value'] as $option) {

                $disabled_option = isset($disable_dropship_flage) && ($disable_dropship_flage == true && $count > 0 && (is_array($multi_dropship))) ? 'disabled' : '';
                $selected_option = purolator_ltl_product_ds_selected_option($sField['selected_value'], $option['option_value']);
                $fieldsHtml .= '<option ' . $disabled_option . ' value="' . esc_attr($option['option_value']) . '" ' . $selected_option . '>' . esc_html($option['option_label']) . ' </option>';

                $count++;
            }
        }
        $fieldsHtml .= '</select>';
        $fieldsHtml .= $description;
    }
    $fieldsHtml .= '</p>';
    return $fieldsHtml;
}

/**
 * Drop Ship Dropdown Selected Options
 * @param $get_loc
 * @param $option_val
 * @return string
 */
function purolator_ltl_product_ds_selected_option($get_loc, $option_val)
{
    $selected = '';
    if (is_array($get_loc)) {
        if (in_array($option_val, $get_loc)) {
            $selected = 'selected="selected"';
        }
    } else {
        $selected = selected($get_loc, $option_val, false);
    }
    return $selected;
}

function purolator_ltl_product_markup_field($variation = null)
{
    $id = '_en_product_markup';
    $value = '';
    if (!empty($variation)) {
        $id = '_en_product_markup_variation[' . $variation->ID . ']';
        $value = get_post_meta($variation->ID, '_en_product_markup_variation', true);
    } 

    $product_markup_field = array(
        'type' => 'text',
        'id' => $id,
        'class' => '_en_product_markup short',
        'label' => __( 'Markup', 'woocommerce' ),
        'placeholder' => 'e.g Currency 1.00 or percentage 5%',
        'description' => "Increases the amount of the returned quote by a specified amount prior to displaying it in the shopping cart. The number entered will be interpreted as dollars and cents unless it is followed by a % sign. For example, entering 5.00 will cause $5.00 to be added to the quotes. Entering 5% will cause 5 percent of the item's price to be added to the shipping quotes.",
        'desc_tip' => true,
    );

    if (!empty($value)) {
        $product_markup_field['value'] = $value;
    }
    
    woocommerce_wp_text_input($product_markup_field);
}