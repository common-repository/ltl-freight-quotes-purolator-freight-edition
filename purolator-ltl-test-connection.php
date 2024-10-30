<?php
/**
 * Purolator Test Connection AJAX Request
 * @package     Woocommerce Purolator Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_nopriv_purolator_ltl_test_conn', 'purolator_ltl_test_submit');
add_action('wp_ajax_purolator_ltl_test_conn', 'purolator_ltl_test_submit');
/**
 * Purolator Test Connection AJAX Request
 */
function purolator_ltl_test_submit()
{
    if (isset($_POST)) {
        foreach ($_POST as $key => $post) {
            $data[$key] = sanitize_text_field($post);
        }
        $production_key = $data['production_key'];
        $production_pass = $data['production_pass'];
        $registered_account_number = $data['registered_account_number'];
        $plugin_license_key = $data['plugin_license_key'];
        $access_level = $data['access_level'];
    }

    $domain = purolator_ltl_quotes_get_domain();

    $data = array(
        'platform' => 'wordpress',
        'licence_key' => $plugin_license_key,
        'sever_name' => $domain,
        'carrierName' => 'purolator-ltl',
        'carrier_mode' => 'test',
        'productionPass' => $production_pass,
        'registeredAccount' => $registered_account_number,
        'productionKey' => $production_key,
        'accessLevel' => $access_level,
        'senderCity' => 'Langley',
        'senderState' => 'BC',
        'senderZip' => 'V4W4A9',
        'senderCountryCode' => 'CA',
    );
    if (is_array($data) && count($data) > 0) {
        $purolator_curl_obj = new Purolator_Feight_Curl_Request();
        $output = $purolator_curl_obj->purolator_freight_get_curl_response(PUROLATOR_FREIGHT_DOMAIN_HITTING_URL . '/index.php', $data);
    }
    $result = json_decode($output);
    if (isset($result->error)) {
        $response = array('Error' => $result->error);
    } elseif (isset($result->Error, $result->Error->Description) && $result->Error->Description != "") {
        $response = array('Error' => $result->Error->Description);
    } elseif (isset($result->Error)) {
        $response = array('Error' => 'Please verify credentials and try again.');
    } elseif (isset($result->q) && !empty ($result->q)) {
        $response = array('Success' => 'The test resulted in a successful connection.');
    } else {
        $response = array('Error' => 'Please verify credentials and try again.');
    }
    echo json_encode($response);
    exit();
}