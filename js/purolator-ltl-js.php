<?php
/**
 * Purolator WooComerce Connection Section Fields Validation
 * @package     Woocommerce Purolator Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_footer', 'purolator_ltl_freight_footer_scipts');
/**
 * Connection Section Fields Validation
 * Quote Setting Section Fields Validation
 * Test Connection AJAX
 */
function purolator_ltl_freight_footer_scipts()
{
    ?>
    <script>

        // Update plan
        if (typeof en_update_plan != 'function') {
            function en_update_plan(input) {
                let action = jQuery(input).attr('data-action');
                jQuery.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: {action: action},
                    success: function (data_response) {
                        window.location.reload(true);
                    }
                });
            }
        }

        jQuery(document).ready(function () {

//          set the check/uncheck values of residential delivery options
            residential_delivery_options();
//          set the check/uncheck values of tailgate delivery options
            tailgate_delivery_options();

            var url = getUrlVarsPuroFreight()["tab"];
            if (url === 'purolator_ltl_quotes') {
                jQuery('#footer-left').attr('id', 'wc-footer-left');
            }


            /*
             * Add Title To Quote Setting Fields
             */
            jQuery('#purolator_ltl_label_as_stndrd').attr('title', 'Label As (Standard)');
            jQuery('#purolator_ltl_label_as_expedited').attr('title', 'Label As (Expedited)');
            jQuery('#purolator_ltl_handling_fee').attr('title', 'Handling Fee / Markup');

            /*
             * Restrict Handling Fee with 8 digits limit
             */

            jQuery("#purolator_ltl_handling_fee").attr('maxlength', '8');

            jQuery('.connection_section_class_purolator_ltl input[type="text"]').each(function () {
                if (jQuery(this).parent().find('.err').length < 1) {
                    jQuery(this).after('<span class="err"></span>');
                }
            });

            jQuery('.connection_section_class_purolator_ltl .form-table').before('<div class="purolator_ltl_warning_msg"><p><b>Note!</b> You must have a Purolator account to use this application. If you do not have one contact Purolator at 1-888-744-7123 or <a href="https://eshiponline.purolator.com/ShipOnline/SecurePages/Public/Register.aspx" target="_blank" >register online</a>.</p>');


            jQuery('.purolator_ltl_quotes_services').closest('tr').addClass('purolator_ltl_quotes_services_tr');
            jQuery('.purolator_ltl_quotes_services').closest('td').addClass('purolator_ltl_quotes_services_td');

            jQuery('.purolator_ltl_residential').closest('tr').addClass('purolator_ltl_residential_tr');
            jQuery('.purolator_ltl_residential').closest('td').addClass('purolator_ltl_residential_td');

            jQuery('.purolator_ltl_tailgate').closest('tr').addClass('purolator_ltl_tailgate_tr');
            jQuery('.purolator_ltl_tailgate').closest('td').addClass('purolator_ltl_tailgate_td');


            jQuery('._en_hazardous_material').closest('p').addClass('_en_hazardous_material');


            var fdx_freight_all_checkboxes = jQuery('.purolator_ltl_quotes_services');
            if (fdx_freight_all_checkboxes.length === fdx_freight_all_checkboxes.filter(":checked").length) {
                jQuery('.purolator_ltl_all_services').prop('checked', true);
            }

            /*
             * Check All Checkbox
             */

            jQuery(".purolator_ltl_all_services").change(function () {
                if (this.checked) {
                    jQuery(".purolator_ltl_quotes_services").each(function () {
                        this.checked = true;
                    })
                } else {
                    jQuery(".purolator_ltl_quotes_services").each(function () {
                        this.checked = false;
                    })
                }
            });


            /*
             * Uncheck Select All Checkbox
             */

            jQuery(".purolator_ltl_quotes_services").on('change load', function () {
                var checkboxes = jQuery('.purolator_ltl_quotes_services:checked').size();
                var un_checkboxes = jQuery('.purolator_ltl_quotes_services').size();
                if (checkboxes === un_checkboxes) {
                    jQuery('.purolator_ltl_all_services').attr('checked', true);
                } else {
                    jQuery('.purolator_ltl_all_services').attr('checked', false);
                }
            });


            /*
             * Add Title To Connection Setting Fields
             */
            jQuery('#purolator_ltl_production_key').attr('title', 'Production Key');
            jQuery('#purolator_ltl_production_password').attr('title', 'Production Password');
            jQuery('#purolator_ltl_reg_acc_num').attr('title', 'Registered Account Number');
            jQuery('#purolator_ltl_plugin_licence_key').attr('title', 'Plugin License Key');
            jQuery('#purolator_ltl_setting_acccess_level').attr('title', 'Aceess Level');
            jQuery('#wc_settings_purolator_ltl_handling_fee').attr('title', 'Handling Fee / Markup');
            jQuery('#wc_settings_purolator_ltl_label_as').attr('title', 'Label As');
        })

        //      set the check/uncheck values of residential delivery options
        function residential_delivery_options() {
            jQuery('#purolator_ltl_always_include_residential').on('change load', function () {
                if (this.checked) {
                    jQuery("#purolator_ltl_option_residential").prop("checked", false);
                }
            });

            jQuery('#purolator_ltl_option_residential').on('change load', function () {
                if (this.checked) {
                    jQuery("#purolator_ltl_always_include_residential").prop("checked", false);
                }
            });
        }

        //      set the check/uncheck values of tailgate delivery options
        function tailgate_delivery_options() {
            jQuery('#purolator_ltl_always_tailgate').on('change load', function () {
                if (this.checked) {
                    jQuery("#purolator_ltl_option_tailgate").prop("checked", false);
                }
            });

            jQuery('#purolator_ltl_option_tailgate').on('change load', function () {
                if (this.checked) {
                    jQuery("#purolator_ltl_always_tailgate").prop("checked", false);
                }
            });
        }

        /*
         * Save Changes At Connection Section Action
         */

        jQuery(".connection_section_class_purolator_ltl .woocommerce-save-button").click(function () {
            var input = validateInput('.connection_section_class_purolator_ltl');
            if (input === false) {
                return false;
            }
        });

        /*
         * Test Connection
         */

        jQuery(".connection_section_class_purolator_ltl .woocommerce-save-button").before('<a href="javascript:void(0)" class="button-primary purolator_ltl_test_connection">Test Connection</a>');
        jQuery('.purolator_ltl_test_connection').click(function (e) {
            var input = validateInput('.connection_section_class_purolator_ltl');
            if (input === false) {
                return false;
            }
            var postForm = {
                'action': 'purolator_ltl_test_conn',
                'production_key': jQuery('#purolator_ltl_production_key').val(),
                'production_pass': jQuery('#purolator_ltl_production_password').val(),
                'registered_account_number': jQuery('#purolator_ltl_reg_acc_num').val(),
                'plugin_license_key': jQuery('#purolator_ltl_plugin_licence_key').val(),
                'access_level': jQuery("input[name='purolator_ltl_setting_acccess_level']:checked").val(),
            };

            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: postForm,
                dataType: 'json',
                beforeSend: function () {
                    jQuery(".connection_save_button").remove();
                    jQuery('#purolator_ltl_production_key, #purolator_ltl_production_password, #purolator_ltl_reg_acc_num, #purolator_ltl_plugin_licence_key').addClass('purolator_ltl_test_conn_prosessing');

                },
                success: function (data) {
                    jQuery(".updated").remove();
                    if (data['Error']) {
                        jQuery('#purolator_ltl_production_key, #purolator_ltl_production_password, #purolator_ltl_reg_acc_num, #purolator_ltl_plugin_licence_key').removeClass('purolator_ltl_test_conn_prosessing');
                        jQuery(".purolator_ltl_success_message").remove();
                        jQuery(".purolator_ltl_error_message").remove();
                        jQuery('.purolator_ltl_warning_msg').before('<div class="notice notice-error purolator_ltl_error_message"><p>Error! ' + data['Error'] + '</p></div>');
                    } else {
                        jQuery('#purolator_ltl_production_key, #purolator_ltl_production_password, #purolator_ltl_reg_acc_num, #purolator_ltl_plugin_licence_key').removeClass('purolator_ltl_test_conn_prosessing');
                        jQuery(".purolator_ltl_success_message").remove();
                        jQuery(".purolator_ltl_error_message").remove();
                        jQuery('.purolator_ltl_warning_msg').before('<div class="notice notice-success purolator_ltl_success_message"><p><strong>Success! ' + data['Success'] + '</strong></p></div>');
                    }
                }
            });
            e.preventDefault();
        })


        /*
         * Save Changes At Quote Section Action
         */

        jQuery('.quote_section_class_purolator_ltl .woocommerce-save-button').on('click', function () {
            jQuery(".updated").hide();
            jQuery('.error').remove();

            var num_of_checkboxes = jQuery('.purolator_ltl_quotes_services:checked').size();
            if (num_of_checkboxes < 1) {
                no_service_selected(num_of_checkboxes);
                return false;
            }


            var handling_fee = jQuery('#wc_settings_purolator_ltl_handling_fee').val();
            if (handling_fee.slice(handling_fee.length - 1) == '%') {
                handling_fee = handling_fee.slice(0, handling_fee.length - 1)
            }
            if (handling_fee === "") {
                return true;
            } else {
                if (isValidNumber(handling_fee) === false) {
                    jQuery("#mainform .quote_section_class_purolator_ltl").prepend('<div id="message" class="error inline purolator_ltl_handlng_fee_error"><p><strong>Handling fee format should be 100.2000 or 10%.</strong></p></div>');
                    jQuery('html, body').animate({
                        'scrollTop': jQuery('.purolator_ltl_handlng_fee_error').position().top
                    });
                    return false;
                } else if (isValidNumber(handling_fee) === 'decimal_point_err') {
                    jQuery("#mainform .quote_section_class_purolator_ltl").prepend('<div id="message" class="error inline purolator_ltl_handlng_fee_error"><p><strong>Handling fee format should be 100.2000 or 10% and only 4 digits are allowed after decimal.</strong></p></div>');
                    jQuery('html, body').animate({
                        'scrollTop': jQuery('.purolator_ltl_handlng_fee_error').position().top
                    });
                    return false;
                } else {
                    return true;
                }
            }
        });

        /*
         * Validate Input If Empty or Invalid
         */

        function validateInput(form_id) {
            var has_err = true;
            jQuery(form_id + " input[type='text']").each(function () {
                var input = jQuery(this).val();
                var response = validateString(input);

                var errorElement = jQuery(this).parent().find('.err');
                jQuery(errorElement).html('');
                var errorText = jQuery(this).attr('title');
                var optional = jQuery(this).data('optional');
                optional = (optional === undefined) ? 0 : 1;
                errorText = (errorText != undefined) ? errorText : '';
                if ((optional == 0) && (response == false || response == 'empty')) {
                    errorText = (response == 'empty') ? errorText + ' is required.' : 'Invalid input.';
                    jQuery(errorElement).html(errorText);
                }
                has_err = (response != true && optional == 0) ? false : has_err;
            });
            return has_err;
        }

        function isValidNumber(value, noNegative) {
            if (typeof (noNegative) === 'undefined') noNegative = false;
            var isValidNumber = false;
            var validNumber = (noNegative == true) ? parseFloat(value) >= 0 : true;
            if ((value == parseInt(value) || value == parseFloat(value)) && (validNumber)) {
                if (value.indexOf(".") >= 0) {
                    var n = value.split(".");
                    if (n[n.length - 1].length <= 4) {
                        isValidNumber = true;
                    } else {
                        isValidNumber = 'decimal_point_err';
                    }
                } else {
                    isValidNumber = true;
                }
            }
            return isValidNumber;
        }

        /*
         * Check Input Value Is Not String
         */
        function validateString(string) {
            if (string == '') {
                return 'empty';
            } else {
                return true;
            }
        }


        /*
         * Check Input Value Is Not String
         */

        function no_service_selected(num_of_checkboxes) {
            jQuery(".updated").hide();
            jQuery(".quote_section_class_purolator_ltl h2:first-child").after('<div id="message" class="error inline no_srvc_select"><p><strong>Please select at least one quote service.</strong></p></div>');
            jQuery('html, body').animate({
                'scrollTop': jQuery('.no_srvc_select').position().top
            });
            return false;
        }

        /**
         * Read a page's GET URL variables and return them as an associative array.
         */
        function getUrlVarsPuroFreight() {
            var vars = [], hash;
            var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
            for (var i = 0; i < hashes.length; i++) {
                hash = hashes[i].split('=');
                vars.push(hash[0]);
                vars[hash[0]] = hash[1];
            }
            return vars;
        }
    </script>
    <?php
}
