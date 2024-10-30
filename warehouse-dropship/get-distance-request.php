<?php
/**
 * WWE LTL Distance Get
 *
 * @package     WWE LTL Quotes
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Get_purolater_ltl_distance
 */
class Get_purolater_ltl_distance
{
    /**
     * Get Distance Function
     * @param $map_address
     * @param $accessLevel
     * @return json
     */
    function purolater_ltl_get_distance($map_address, $accessLevel, $destinationZip = array())
    {

        $domain = purolator_ltl_quotes_get_domain();
        $post = array(
            'acessLevel' => $accessLevel,
            'address' => $map_address,
            'originAddresses' => (isset($map_address)) ? $map_address : "",
            'destinationAddress' => (isset($destinationZip)) ? $destinationZip : "",
            'eniureLicenceKey' => get_option('purolator_ltl_plugin_licence_key'),
            'ServerName' => $domain,
        );


        if (is_array($post) && count($post) > 0) {

            $purolater_ltl_curl_obj = new Purolator_Feight_Curl_Request();
            $output = $purolater_ltl_curl_obj->purolator_freight_get_curl_response(PUROLATOR_FREIGHT_DOMAIN_HITTING_URL . '/addon/google-location.php', $post);
            return $output;
        }
    }
}
