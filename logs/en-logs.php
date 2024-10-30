<script type="text/javascript">
    jQuery(function() {
        jQuery('a').on('click', function(e) {
            const class_name = this.className;
            const show_class_name = class_name.includes('show') ? class_name.replace('show', 'hide') : class_name.replace('hide', 'show');

            if (class_name.includes('show') || class_name.includes('hide')) {
                e.preventDefault();
            }

            jQuery('.' + class_name).hide();
            jQuery('.' + show_class_name).show();
        })
    });
</script>

<style>
    .hide {
        display: none;
    }

    .dims_space {
        padding-top: 0 !important;
    }

    .items_space {
        padding-top: 1.5em !important;
    }
</style>

<?php

if (!class_exists('EnPurolatorLfqLogs')) {
    class EnPurolatorLfqLogs
    {
        public function __construct()
        {
            $this->enLogs();
        }

        // Logs request
        public function enLogs()
        {
            $obj_classs = new \Purolator_Feight_Curl_Request();
            $data = array(
                'serverName' => purolator_ltl_quotes_get_domain(),
                'licenseKey' => get_option('purolator_ltl_plugin_licence_key'),
                'lastLogs' => '25',
                'carrierName' => 'purolator-ltl',
            );

            require_once 'en-json-tree-view/en-jtv.php';
            
            $url = PUROLATOR_FREIGHT_DOMAIN_HITTING_URL . '/request-log/index.php';
            $logs = $obj_classs->purolator_freight_get_curl_response($url, $data);
            $logs = (isset($logs) && is_string($logs) && strlen($logs) > 0) ? json_decode($logs, true) : [];

            echo '<table class="en_logs">';

            if (isset($logs['severity'], $logs['data']) && $logs['severity'] == 'SUCCESS') {
                echo '<tr>';
                echo '<th>Log ID</th>';
                echo '<th>Request Time</th>';
                echo '<th>Response Time</th>';
                echo '<th>Latency</th>';
                echo '<th>Items</th>';
                echo '<th>DIMs (L x W x H)</th>';
                echo '<th>Qty</th>';
                echo '<th>Sender Address</th>';
                echo '<th>Receiver Address</th>';
                echo '<th>Response</th>';
                echo '</tr>';

                foreach ($logs['data'] as $key => $shipment) {
                    if (empty($shipment) || !is_array($shipment)) continue;
                    echo '<tr>';

                    $request = $response = $carrier = $status = '';
                    $address = $senderCity = $senderState = $senderZip = $senderCountryCode = '';
                    $receiverLineAddress = $receiverCity = $receiverState = $receiverZip = $receiverCountryCode = '';
                    
                    extract($shipment);
                    $request = is_string($request) && strlen($request) > 0 ? json_decode($request, true) : [];
                    extract($request);
                    if (empty($request) || !is_array($request)) continue;

                    $en_fdo_meta_data = isset($en_fdo_meta_data) ? $en_fdo_meta_data: [];

                    if (isset($carrier_mode) && $carrier_mode == 'test') {
                        continue;
                    }

                    $log_id = isset($shipment['id']) ? $shipment['id'] : '';
                    $items = [];
                    $en_address = [];
                    $en_qty = $en_items = $en_dim = '';
                    $class_name = 'purolator-ltl-log-' . $key . rand(1, 100);

                    $items = empty($items) ? $commdityDetails : $items;

                    foreach ($items as $key => $item) {
                        $productName = $piecesOfLineItem = $lineItemLength = $lineItemWidth = $lineItemHeight = '';
                        extract($item);
                        $en_qty .= strlen($en_qty) > 0 ? "<br> $piecesOfLineItem" : $piecesOfLineItem;
                        $en_items .= strlen($en_items) > 0 ? "<br> $productName" : $productName;
                        $en_dim .= strlen($en_dim) > 0 ? "<br> $lineItemLength X $lineItemWidth X $lineItemHeight" : "$lineItemLength X $lineItemWidth X $lineItemHeight";
                    }

                    $en_updated_qty = $en_updated_items = $en_updated_dim = '';
                    $updated_items = count($items) > 5 ? array_slice($items, 0, 5) : $items;
                    if (!empty($updated_items)) {
                        foreach ($updated_items as $key => $item) {
                            $productName = $piecesOfLineItem = $lineItemLength = $lineItemWidth = $lineItemHeight = '';
                            extract($item);
                            $en_updated_qty .= strlen($en_updated_qty) > 0 ? "<br> $piecesOfLineItem" : $piecesOfLineItem;
                            $en_updated_items .= strlen($en_updated_items) > 0 ? "<br> $productName" : $productName;
                            $en_updated_dim .= strlen($en_dim) > 0 ? "<br> $lineItemLength X $lineItemWidth X $lineItemHeight" : "$lineItemLength X $lineItemWidth X $lineItemHeight";
                        }
                    }

                    // Sender address
                    extract($en_address);
                    $en_sender = strlen(trim($address) > 0) ? "$address, " : '';
                    $en_sender .= "$senderCity, $senderState $senderZip $senderCountryCode";

                    // Receiver address
                    $en_receiver = strlen(trim($receiverLineAddress) > 0) ? "$receiverLineAddress, " : '';
                    $en_receiver .= "$receiverCity, $receiverState $receiverZip $receiverCountryCode";
                    $carrier = ucfirst($carrier);
                    $status = ucfirst($status);
                    $request_time = $this->setTimeZone($request_time);
                    $response_time = $this->setTimeZone($response_time);
                    $latency = strtotime($response_time) - strtotime($request_time);
                    $response = json_decode($response);

                    if (!empty($response) && isset($response->fdo_handling_unit)) {
                        $response->handling_unit_details = $response->fdo_handling_unit;
                        unset($response->fdo_handling_unit);
                    }

                    $response = json_encode($response);
                    $response = str_replace(array("\r", "\n"), '', $response);

                    echo "<td>$log_id</td>";
                    echo "<td>$request_time</td>";
                    echo "<td>$response_time</td>";
                    echo "<td>$latency</td>";

                    $name = 'show-' . $class_name;
                    if (count($items) > 5) {
                        echo "<td class='items_space $name'>$en_updated_items <br /> <a href='#' class='$name'>Show more items</a> </td>";
                    } else {
                        echo "<td class='items_space $name'>$en_updated_items</td>";
                    }

                    echo "<td class='dims_space $name'>$en_updated_dim</td>";
                    echo "<td class='$name'>$en_updated_qty</td>";

                    $name = 'hide-' . $class_name;
                    echo "<td class='items_space hide $name'>$en_items <br /> <a href='#' class='$name'>Hide more items</a> </td>";
                    echo "<td class='dims_space hide $name'>$en_dim</td>";
                    echo "<td class='hide $name'>$en_qty</td>";

                    echo "<td>$en_sender</td>";
                    echo "<td>$en_receiver</td>";
                    echo '<td><a href = "#en_jtv_showing_res" class="response" onclick=\'en_jtv_res_detail(' . $response . ')\'>' . $status . '</a></td>';
                    echo '</tr>';
                }
            } else {
                echo '<div class="user_guide">';
                echo '<p>Logs are not available.</p>';
                echo '</div>';
            }

            echo '<table>';
        }

        public function setTimeZone($date_time)
        {
            $time_zone = wp_timezone_string();
            if (empty($time_zone)) {
                return $date_time;
            }

            $converted_date_time = new DateTime($date_time, new DateTimeZone($time_zone));

            return $converted_date_time->format('m/d/Y h:i:s');
        }
    }

    new EnPurolatorLfqLogs();
}
