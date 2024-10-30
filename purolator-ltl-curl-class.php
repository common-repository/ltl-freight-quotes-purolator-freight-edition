<?php
/**
 * Purolator WooComerce Get ESTES LTL Quotes Rate Class
 * @package     Woocommerce Purolator Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

/**
 * Purolator Curl Response Class
 */
class Purolator_Feight_Curl_Request 
{
    /**
     * Get Curl Response 
     * @param $url
     * @param $postData
     * @return Request Url, Post data
     */
    function purolator_freight_get_curl_response($url, $postData) 
    {
        if ( !empty( $url ) && !empty( $postData ) )
        {
            $field_string = http_build_query($postData);
            
            $response = wp_remote_post($url,
                array(
                    'method' => 'POST',
                    'timeout' => 60,
                    'redirection' => 5,
                    'blocking' => true,
                    'body' => $field_string,
                )
            );

            $response = wp_remote_retrieve_body($response);
            
            $output_decoded = json_decode($response);
            if (empty($output_decoded)) {
                return $response = json_encode(array('error' => 'Unable to get response from API')) ;
            }
            return $response;
        }    
    }
}