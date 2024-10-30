jQuery(document).ready(function () {
    // set the check/uncheck values of residential delivery options
    residential_delivery_options();
    // set the check/uncheck values of tailgate delivery options
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
    
    // Handling unit
    jQuery("#handling_weight_purolator_ltl").attr('maxlength', '7');
    jQuery("#maximum_handling_weight_purolator_ltl").attr('maxlength','7');
    jQuery('#handling_weight_purolator_ltl').closest('tr').addClass('purolator_ltl_residential_tr');
    jQuery('#handling_weight_purolator_ltl').closest('td').addClass('purolator_ltl_handling_unit_td');
    jQuery('#maximum_handling_weight_purolator_ltl').closest('tr').addClass('purolator_ltl_residential_tr');
    jQuery('#maximum_handling_weight_purolator_ltl').closest('td').addClass('purolator_ltl_handling_unit_td');

    jQuery("#handling_weight_purolator_ltl, #maximum_handling_weight_purolator_ltl").keydown(function (e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            // Allow: Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)|| e.keyCode == 109) {
            // let it happen, don't do anything
            return;
        }
        
        // Ensure that it is a number and stop the keypress
        if ((e.keyCode === 190 || e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    
        if ((jQuery(this).val().indexOf('.') != -1) && (jQuery(this).val().substring(jQuery(this).val().indexOf('.'), jQuery(this).val().indexOf('.').length).length > 2)) {
            if (event.keyCode !== 8 && event.keyCode !== 46) { //exception
                event.preventDefault();
            }
        }
    });
        
    jQuery("#handling_weight_purolator_ltl, #maximum_handling_weight_purolator_ltl").keyup(function (e) {
    
        var val = jQuery(this).val();
    
        if (val.split('.').length - 1 > 1) {
            var newval = val.substring(0, val.length - 1);
            var countDots = newval.substring(newval.indexOf('.') + 1).length;
            newval = newval.substring(0, val.length - countDots - 1);
            jQuery(this).val(newval);
        }
    
        if (val.split('%').length - 1 > 1) {
            var newval = val.substring(0, val.length - 1);
            var countPercentages = newval.substring(newval.indexOf('%') + 1).length;
            newval = newval.substring(0, val.length - countPercentages - 1);
            jQuery(this).val(newval);
        }
    });

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
        var checkboxes = jQuery('.purolator_ltl_quotes_services:checked').length;
        var un_checkboxes = jQuery('.purolator_ltl_quotes_services').length;
        if (checkboxes === un_checkboxes) {
            jQuery('.purolator_ltl_all_services').prop('checked', true);
        } else {
            jQuery('.purolator_ltl_all_services').prop('checked', false);
        }
    });


    /*
     * Add Title To Connection Setting Fields
     */
    jQuery('#purolator_ltl_production_key').attr('title', 'Production Key');
    jQuery('#purolator_ltl_production_password').attr('title', 'Production Password');
    jQuery('#purolator_ltl_reg_acc_num').attr('title', 'Registered Account Number');
    jQuery('#purolator_ltl_plugin_licence_key').attr('title', 'Eniture API Key');
    jQuery('#purolator_ltl_setting_acccess_level').attr('title', 'Aceess Level');
    jQuery('#wc_settings_purolator_ltl_handling_fee').attr('title', 'Handling Fee / Markup');
    jQuery('#wc_settings_purolator_ltl_label_as').attr('title', 'Label As');

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

        var num_of_checkboxes = jQuery('.purolator_ltl_quotes_services:checked').length;
        if (num_of_checkboxes < 1) {
            no_service_selected(num_of_checkboxes);
            return false;
        }

        // Handling unit validations
        if (!purolator_ltl_handling_unit_validation('handling_weight_purolator_ltl')) {
            return false;
        }
        if (!purolator_ltl_handling_unit_validation('maximum_handling_weight_purolator_ltl')) {
            return false;
        }

        var handling_fee = jQuery('#purolator_ltl_handling_fee').val();
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
    
    // Product variants settings
    jQuery(document).on("click", '._nestedMaterials', function(e) {
        const checkbox_class = jQuery(e.target).attr("class");
        const name = jQuery(e.target).attr("name");
        const checked = jQuery(e.target).prop('checked');

        if (checkbox_class?.includes('_nestedMaterials')) {
            const id = name?.split('_nestedMaterials')[1];
            setNestMatDisplay(id, checked);
        }
    });

    // Callback function to execute when mutations are observed
    const handleMutations = (mutationList) => {
        let childs = [];
        for (const mutation of mutationList) {
            childs = mutation?.target?.children;
            if (childs?.length) setNestedMaterialsUI();
          }
    };
    const observer = new MutationObserver(handleMutations),
        targetNode = document.querySelector('.woocommerce_variations.wc-metaboxes'),
        config = { attributes: true, childList: true, subtree: true };
    if (targetNode) observer.observe(targetNode, config);

    en_purolator_ltl_weight_threshold_limit();
});

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

function purolator_ltl_handling_unit_validation(field) {
    var handling_unit = jQuery('#' + field).val();
    var handling_unit_regex = /^([0-9]{1,4})*(\.[0-9]{0,2})?$/;
    const title = field == 'handling_weight_purolator_ltl' ? 'Weight of Handling Unit' : 'Maximum Weight per Handling Unit';
    
    if (handling_unit != '' && !handling_unit_regex.test(handling_unit)) {
        jQuery("#mainform .quote_section_class_purolator_ltl").prepend('<div id="message" class="error inline purolator_ltl_handlng_fee_error"><p><strong>Error! </strong>' + title + ' format should be 100.20 or 10 and only 2 digits are allowed after decimal.</p></div>');
        jQuery('html, body').animate({
            'scrollTop': jQuery('.purolator_ltl_handlng_fee_error').position().top
        });

        return false;
    } else {
        return true;
    }
}

if (typeof setNestedMaterialsUI != 'function') {
    function setNestedMaterialsUI() {
        const nestedMaterials = jQuery('._nestedMaterials');
        const productMarkups = jQuery('._en_product_markup');
        
        if (productMarkups?.length) {
            for (const markup of productMarkups) {
                jQuery(markup).attr('maxlength', '7');

                jQuery(markup).keypress(function (e) {
                    if (!String.fromCharCode(e.keyCode).match(/^[0-9.%-]+$/))
                        return false;
                });
            }
        }

        if (nestedMaterials?.length) {
            for (let elem of nestedMaterials) {
                const className = elem.className;

                if (className?.includes('_nestedMaterials')) {
                    const checked = jQuery(elem).prop('checked'),
                        name = jQuery(elem).attr('name'),
                        id = name?.split('_nestedMaterials')[1];
                    setNestMatDisplay(id, checked);
                }
            }
        }
    }
}

if (typeof setNestMatDisplay != 'function') {
    function setNestMatDisplay (id, checked) {
        
        jQuery(`input[name="_nestedPercentage${id}"]`).attr('min', '0');
        jQuery(`input[name="_nestedPercentage${id}"]`).attr('max', '100');
        jQuery(`input[name="_nestedPercentage${id}"]`).attr('maxlength', '3');
        jQuery(`input[name="_maxNestedItems${id}"]`).attr('min', '0');
        jQuery(`input[name="_maxNestedItems${id}"]`).attr('max', '100');
        jQuery(`input[name="_maxNestedItems${id}"]`).attr('maxlength', '3');

        jQuery(`input[name="_nestedPercentage${id}"], input[name="_maxNestedItems${id}"]`).keypress(function (e) {
            if (!String.fromCharCode(e.keyCode).match(/^[0-9]+$/))
                return false;
        });

        jQuery(`input[name="_nestedPercentage${id}"]`).closest('p').css('display', checked ? '' : 'none');
        jQuery(`select[name="_nestedDimension${id}"]`).closest('p').css('display', checked ? '' : 'none');
        jQuery(`input[name="_maxNestedItems${id}"]`).closest('p').css('display', checked ? '' : 'none');
        jQuery(`select[name="_nestedStakingProperty${id}"]`).closest('p').css('display', checked ? '' : 'none');
    }
}

// Weight threshold for LTL freight
if (typeof en_purolator_ltl_weight_threshold_limit != 'function') {
    function en_purolator_ltl_weight_threshold_limit() {
        // Weight threshold for LTL freight
        jQuery("#en_weight_threshold_lfq").keypress(function (e) {
            if (String.fromCharCode(e.keyCode).match(/[^0-9]/g) || !jQuery("#en_weight_threshold_lfq").val().match(/^\d{0,3}$/)) return false;
        });

        jQuery('#en_plugins_return_LTL_quotes').on('change', function () {
            if (jQuery('#en_plugins_return_LTL_quotes').prop("checked")) {
                jQuery('tr.en_weight_threshold_lfq').css('display', 'contents');
                jQuery('tr.en_suppress_parcel_rates').css('display', '');
            } else {
                jQuery('tr.en_weight_threshold_lfq').css('display', 'none');
                jQuery('tr.en_suppress_parcel_rates').css('display', 'none');
            }
        });

        jQuery("#en_plugins_return_LTL_quotes").closest('tr').addClass("en_plugins_return_LTL_quotes_tr");
        // Weight threshold for LTL freight
        const weight_threshold_class = jQuery("#en_weight_threshold_lfq").attr("class");
        jQuery("#en_weight_threshold_lfq").closest('tr').addClass("en_weight_threshold_lfq " + weight_threshold_class);

        // Suppress parcel rates when thresold is met
        jQuery(".en_suppress_parcel_rates").closest('tr').addClass("en_suppress_parcel_rates");
        if (!jQuery("#en_plugins_return_LTL_quotes").is(":checked")) {
            jQuery('tr.en_suppress_parcel_rates').css('display', 'none');
        }

        // Weight threshold for LTL freight is empty
        if (jQuery('#en_weight_threshold_lfq').length && !jQuery('#en_weight_threshold_lfq').val().length > 0) {
            jQuery('#en_weight_threshold_lfq').val(150);
        }
    }
}
