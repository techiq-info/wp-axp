jQuery( document ).ready( function( $ ) {
    $( '#e_deliverydate' ).select2();
    $( '#orddd_time_slot' ).select2();
    $( '#orddd_locations' ).select2();

    window.onload = load_functions();
    
    if( 'on' === jsL10n.is_timeslot_list_view ) {
        jQuery( '#orddd_time_slot' ).hide();
        orddd_load_time_slots_list();
    }

    if ( jQuery( "#orddd_enable_autofill_of_delivery_date" ).val() == "on" ) {
        orddd_autofil_date_time();
    } else { 
        orddd_set_date_dropdown_from_session();
    }

    $( document ).on( "change", "#e_deliverydate", function() {
        var e_deliverydate = $( this ).val();
        if( 'select' == e_deliverydate ) {
            e_deliverydate = '';
        }
        $( this ).find('option[value="'+ e_deliverydate + '"]').prop( 'selected', true );

        $( "#h_deliverydate" ).val( e_deliverydate );
        localStorage.setItem( "h_deliverydate_session", jQuery( "#h_deliverydate" ).val() );

        show_times_for_dropdown();
    });

    jQuery( '#edit_delivery_date' ).on( 'click', function() {
        jQuery( '#orddd_edit_div' ).toggle();
    });
    jQuery( '#cancel_delivery_date' ).on( 'click', function() {
        jQuery( '#orddd_edit_div' ).fadeOut();
    });
    
    jQuery( '#update_date' ).on( 'click', function() {
        var ordd_date_and_time_validation = "allow";

        var ordd_is_delivery_date_mandatory = jQuery( '#orddd_date_field_mandatory' ).val();
        var ordd_is_delivery_time_mandatory = jQuery( '#orddd_timeslot_field_mandatory' ).val();
        
        var ordd_get_delivery_date = jQuery( '#' + jQuery( "#orddd_field_name" ).val() ).val();
        var ordd_get_delivery_time = jQuery( '#orddd_time_slot' ).val();

        var ordd_date_label        = jQuery( '#orddd_field_label' ).val();
        var ordd_time_label        = jQuery( '#orddd_timeslot_field_label' ).val();

        var ordd_validation_message = "";
        if ( "checked" == ordd_is_delivery_date_mandatory && "checked" == ordd_is_delivery_time_mandatory ) {
            ordd_validation_message =  ordd_date_label + " is a required field." + ordd_time_label + " is a required field.";
            if ( ordd_get_delivery_date.length == 0 ||  "select" == ordd_get_delivery_time ) {
                ordd_date_and_time_validation = "no";
            }
        }else if ( "checked" == ordd_is_delivery_date_mandatory ) {
            ordd_validation_message = ordd_date_label +" is a required field.";
            if ( ordd_get_delivery_date.length == 0 ) {
                ordd_date_and_time_validation = "no";
            }
        } else if ( "checked" == ordd_is_delivery_time_mandatory ) {
            ordd_validation_message = ordd_time_label + " is a required field.";
            if ( "select" == ordd_get_delivery_time ) {
                ordd_date_and_time_validation = "no";
            }
        }

        if ( "no" == ordd_date_and_time_validation ) {
            jQuery( "#display_update_message" ).css( "color","red" );
            jQuery( "#display_update_message" ).html( ordd_validation_message );
            jQuery( "#display_update_message" ).fadeIn();
            var delay = 2000; 
            setTimeout(function() {
                jQuery( "#display_update_message" ).fadeOut();
            }, delay );
        }

        if ( "allow" == ordd_date_and_time_validation ) {
            var data = {
                order_id: jQuery( "#orddd_my_account_order_id" ).val(),
                e_deliverydate: jQuery( '#' + jQuery( "#orddd_field_name" ).val() ).val(),
                h_deliverydate: jQuery( '#h_deliverydate' ).val(),
                shipping_method: jQuery( '#shipping_method' ).val(),
                orddd_category_settings_to_load: jQuery( '#orddd_category_settings_to_load' ).val(),
                time_setting_enable_for_shipping_method: jQuery( '#time_setting_enable_for_shipping_method' ).val(),
                orddd_time_settings_selected: jQuery( '#orddd_time_settings_selected' ).val(),
                orddd_time_slot: jQuery( '#orddd_time_slot' ).val(),
                is_my_account: jQuery( '#orddd_is_account_page' ).val(),
                action: 'orddd_update_delivery_date'
            };
            jQuery( '#display_update_message' ).html( '<b>Saving...</b>' );
            jQuery.post( jQuery( '#orddd_admin_url' ).val() + 'admin-ajax.php', data, function( response, status ) {
                jQuery( '#display_update_message' ).html( jsL10n.success_delivery_date_message );
                var delay = 500; //10 second
                setTimeout(function() {
                      location.reload();
                }, delay);
            });
        }
    });
    
    var local_storage_postcode = localStorage.getItem( "orddd_availability_postcode" );
    if( local_storage_postcode != '' && local_storage_postcode != 'undefined' && local_storage_postcode != null ) {
        jQuery( '#billing_postcode' ).val( local_storage_postcode );    
    }

    //Hide pickup location field if the shipping method is not selected. 
    var shipping_method = orddd_get_selected_shipping_method();
    if( shipping_method.indexOf( 'local_pickup' ) === -1 ) {
        jQuery( "#orddd_locations_field" ).hide();
        jQuery( "#orddd_locations" ).val( "select_location" ).trigger( "change" );    
    }

    //Validate the time field if set to mandatory
    var parent =  jQuery( '#orddd_time_slot' ).closest( '.form-row' );
    validated = true;
    jQuery( 'form.checkout' ).on( 'input validate change','#orddd_time_slot', function( e ){
        if ( 'validate' === e.type || 'change' === e.type ) {
            if( jQuery('#orddd_time_slot').val() == 'select' && jQuery( '#orddd_timeslot_field_mandatory' ).val() == 'checked' ) {
                jQuery(parent).removeClass( 'woocommerce-validated' ).addClass( 'woocommerce-invalid woocommerce-invalid-required-field' );
                validated = false;
            }
        }
        
        if( validated ) {
            jQuery(parent).removeClass( 'woocommerce-invalid woocommerce-invalid-required-field' ).addClass( 'woocommerce-validated' );
        }
    });

    //Clear local storage for the selected delivery date in next 2 hours. 
    var orddd_last_check_date = localStorage.getItem( "orddd_storage_next_time" );
    var current_date = jQuery( "#orddd_current_day" ).val();
    
    if( current_date != '' && typeof( current_date ) != 'undefined' ) {
        var split_current_date = current_date.split( '-' );
        var ordd_next_date = new Date( split_current_date[ 2 ], ( split_current_date[ 1 ] - 1 ), split_current_date[ 0 ], jQuery( "#orddd_current_hour" ).val(), jQuery( "#orddd_current_minute" ).val() );
    } else {
        var ordd_next_date = new Date();
    }

    if ( null != orddd_last_check_date ) {
        if ( ordd_next_date.getTime() > orddd_last_check_date ) {
            localStorage.removeItem( "orddd_storage_next_time" );
            localStorage.removeItem( "e_deliverydate_session" );
            localStorage.removeItem( "h_deliverydate_session" );
            localStorage.removeItem( "orddd_time_slot" );
            localStorage.removeItem( "orddd_availability_postcode" );
        }
    }

    jQuery( document ).on( "change", "#orddd_time_slot", function() {
        var shipping_method = orddd_get_selected_shipping_method();
        jQuery( "#hidden_e_deliverydate" ).val( jQuery( '#e_deliverydate option:selected' ).text() );
        jQuery( "#hidden_h_deliverydate" ).val( jQuery( "#h_deliverydate" ).val() );
        jQuery( "#hidden_timeslot" ).val( jQuery(this).find(":selected").val() );
        jQuery( "#hidden_shipping_method" ).val( shipping_method );
        jQuery( "#hidden_shipping_class" ).val( jQuery( "#orddd_shipping_class_settings_to_load" ).val() );

        var selected_val = jQuery(this).val();
        jQuery(this).find('option[value="'+ selected_val + '"]').prop( 'selected', true );
        if ( "1" !== jQuery( "#orddd_is_admin" ).val() ) {
            if( 'select' == jQuery( '#e_deliverydate' ).val() ) {
                localStorage.setItem( "e_deliverydate_session", '' );
            } else {
                localStorage.setItem( "e_deliverydate_session", jQuery( '#e_deliverydate option:selected' ).text() );
            }
            localStorage.setItem( "h_deliverydate_session", jQuery( "#h_deliverydate" ).val() );
            localStorage.setItem( "orddd_time_slot", selected_val );
        }

        var current_date = jQuery( "#orddd_current_day" ).val();
        var split_current_date = current_date.split( '-' );
        var ordd_next_date = new Date( split_current_date[ 2 ], ( split_current_date[ 1 ] - 1 ), split_current_date[ 0 ],jQuery( "#orddd_current_hour" ).val(), jQuery( "#orddd_current_minute" ).val() );

        ordd_next_date.setHours( ordd_next_date.getHours() + 2 );
        localStorage.setItem( "orddd_storage_next_time", ordd_next_date.getTime() );

        jQuery( "body" ).trigger( "update_checkout" );
        if ( 'on' == jQuery( '#orddd_delivery_date_on_cart_page' ).val() && jQuery( '#orddd_is_cart' ).val() == '1' ) {
            jQuery( "body" ).trigger( "wc_update_cart" );
        }
        jQuery( "body" ).trigger( "change_orddd_time_slot", [ jQuery( this ) ] );
    });

    if ( jQuery( "#orddd_field_note_text" ).val() != '' ) {
        jQuery( "#e_deliverydate_field" ).append( "<br><small class='orddd_field_note'>" + jQuery( "#orddd_field_note_text" ).val() + "</small>" );
    }
    
    jQuery(document).on( "change", "select[name=\"orddd_locations\"]", function() {
        if ( jQuery( "#orddd_enable_shipping_based_delivery" ).val() == 'on' ) {
            // var update_settings = load_delivery_date();
            // if( update_settings == 'yes' && jQuery( "#orddd_enable_autofill_of_delivery_date" ).val() == 'on' ) {
            //     orddd_autofil_date_time();
            // }
        }
        localStorage.setItem( "orddd_location_session", jQuery(this).val() );
    });
    
       
    jQuery(document).on( "change", "input[name=\"shipping_method[0]\"]", function() {
        if( jQuery( "#orddd_enable_shipping_based_delivery" ).val() == "on" ) {
            localStorage.removeItem( "orddd_storage_next_time" );
            localStorage.removeItem( "e_deliverydate_session" );
            localStorage.removeItem( "h_deliverydate_session" );
            localStorage.removeItem( "orddd_time_slot" );  
        }
        orddd_update_delivery_session();
    });
        
    jQuery(document).on( "change", "select[name=\"shipping_method[0]\"]", function() {
        orddd_update_delivery_session();
    });

    jQuery(document).on( "change", "input[name=\"shipping_method_[0]\"]", function() {
        orddd_update_delivery_session();
    });

    jQuery(document).on( "change", '#ship-to-different-address input', function() {
        orddd_update_delivery_session();
    });

    if( '1' == jQuery( "#orddd_is_admin" ).val() ) {
        jQuery( '#' + jQuery( "#orddd_field_name" ).val() ).width( "150px" );
        jQuery( '#' + jQuery( "#orddd_field_name" ).val() ).attr( "readonly", true );
    }

    jQuery(document).on( 'change', '.address-field input.input-text, .update_totals_on_change input.input-text, .address-field select', function( e ) {
        // if( jQuery( "#orddd_enable_shipping_based_delivery" ).val() == "on" &&  jQuery( '#orddd_disable_delivery_fields' ).val() == 'yes' ) {
        //     jQuery( '#' + jQuery( "#orddd_field_name" ).val()).datepicker( "option", "disabled", true );    
        //     jQuery( "#orddd_time_slot" ).attr( "disabled", "disabled" );
        // }
    } );

    var old_zone_id = "";
    var old_shipping_method = "";
    jQuery(document).on( "ajaxComplete", function( event, xhr, options ) {
        if ( options.url.indexOf( "wc-ajax=get_refreshed_fragments" ) !== -1 ) {
            return;
        }
        var update_settings = 'no';
        
        var new_shipping_postcode = "";
        var new_shipping_country = "";
        var new_shipping_state = "";

        var is_shipping_checked = true;
        if( options.url.indexOf( "wc-ajax=update_order_review" ) !== -1 ) {
            var new_billing_postcode = jQuery( "#billing_postcode" ).val();
            var new_billing_country = jQuery( "#billing_country" ).val();
            var new_billing_state = jQuery( "#billing_state" ).val();

            var new_shipping_postcode = jQuery( "#shipping_postcode" ).val();
            var new_shipping_country = jQuery( "#shipping_country" ).val();
            var new_shipping_state = jQuery( "#shipping_state" ).val();
            if( xhr.statusText != "abort" ) {
                update_settings = 'yes';
            }
            var is_shipping_checked = jQuery( '#ship-to-different-address input' ).is( ":checked" );
        } else if ( options.url.indexOf( "cart/?remove_item=" ) !== -1 ||
            options.url.indexOf( "cart/?undo_item=" ) !== -1 ) {
            if( xhr.statusText != "abort" ) {
                //Sent cart_delete as a parameter in this function, as we want to update orddd_hidden_vars_str variable only on 
                //Cart item deletion or added again using Undo option on the cart page.
                orddd_update_delivery_session( 'cart_delete' );
            }
        } else if( options.url.indexOf( "wc-ajax=update_shipping_method" ) !== -1 ) {

            var new_billing_postcode = jQuery( "#calc_shipping_postcode" ).val();
            var new_billing_country = jQuery( "#calc_shipping_country" ).val();
            var new_billing_state = jQuery( "#calc_shipping_state" ).val();
            if( xhr.statusText != "abort" ) {
                update_settings = 'yes';
            }
            var is_shipping_checked = false;
        }

        if( update_settings == 'yes' ) {
            var shipping_method = orddd_get_selected_shipping_method();
            if( shipping_method.indexOf( 'local_pickup' ) === -1 ) {
                jQuery( "#orddd_locations_field" ).hide();
                jQuery( "#orddd_locations" ).val( "select_location" ).trigger( "change" );    
            } else {
                jQuery( "#orddd_locations_field" ).show();    
            }

            if( jQuery( "#orddd_enable_shipping_based_delivery" ).val() == "on" ) {
                
                var data = {
                    action: 'orddd_get_zone_id',
                    billing_postcode: new_billing_postcode,
                    billing_country: new_billing_country,
                    billing_state: new_billing_state,
                    shipping_postcode: new_shipping_postcode,
                    shipping_country: new_shipping_country,
                    shipping_state: new_shipping_state,
                    shipping_checkbox: is_shipping_checked
                };

                if( ( new_billing_postcode != '' && new_billing_country != '' ) && 
                    ( false == is_shipping_checked || 
                    ( true == is_shipping_checked && '' != new_shipping_country && '' != new_shipping_postcode ) ) 
                    ) {
                   
                    jQuery("#e_deliverydate_field").block({
                        message: null,
                        overlayCSS: {
                            background: '#fff',
                            opacity: 0.6
                        }
                    });
        
                    jQuery.post( jQuery( '#orddd_admin_url' ).val() + "admin-ajax.php", data, function( response ) {
                        var zone_id = 0;
                        if( "" != response ) {
                            var zone_shipping_details = response.split('-');
                            var zone_id = zone_shipping_details[ 0 ];
                            var orddd_shipping_id = zone_shipping_details[ 1 ];
                        }
                        jQuery( "#orddd_zone_id" ).val( zone_id );
                        jQuery( "#orddd_shipping_id" ).val( orddd_shipping_id );

                        if ( old_zone_id != zone_id || old_shipping_method != orddd_shipping_id ) {

                            jQuery( "#orddd_time_slot" ).removeAttr( "disabled", "disabled" );
                            load_delivery_date();

                            // running the session related code only if auto-populate is not set to ON
                            // because orddd_autofil_date_time() already runs the session code too
                            if ( jQuery( "#orddd_enable_autofill_of_delivery_date" ).val() == "on" ) {
                                orddd_autofil_date_time();
                            } else { 
                                orddd_set_date_dropdown_from_session();
                            }
                            old_zone_id = zone_id;
                            old_shipping_method = orddd_shipping_id; 
                        } else {
                            jQuery( "#orddd_time_slot" ).removeAttr( "disabled", "disabled" );
                        }

                        jQuery("#e_deliverydate_field").unblock();

                    });
                } 
            }
        }
    });

    // Update the delivery calendar on change on address on cart page.
    jQuery( document ).on( 'updated_cart_totals', function() {
        orddd_update_delivery_session();
    });
   
    if( '1' == jQuery( "#orddd_is_admin" ).val() ) {
        jQuery( "#save_delivery_date" ).click(function() {
        	save_delivery_dates( 'no' );
        }); 

        jQuery( "#save_delivery_date_and_notify" ).click(function() {
        	save_delivery_dates( 'yes' );
        });        
    }

});

function show_times_for_dropdown() {
    //jQuery( "#h_deliverydate" ).val( jQuery('#e_deliverydate').val() );
    var h_deliverydate_session = localStorage.getItem( 'h_deliverydate_session' );
    let h_deliverydate = jQuery( "#h_deliverydate" ).val();

    if( h_deliverydate_session ) {
        h_deliverydate = h_deliverydate_session;
        jQuery( "#h_deliverydate" ).val(h_deliverydate_session);
       // jQuery( "#e_deliverydate" ).val(h_deliverydate_session);
    }

    if( 'select' == jQuery( "#h_deliverydate" ).val() ) {
        h_deliverydate = '';
        jQuery( "#h_deliverydate" ).val('');
    }
    var location = jQuery( "select[name=\"orddd_locations\"]" ).find(":selected").val();
    if( typeof location === "undefined" ) {
        var location = "";
    }

    var shipping_method = orddd_get_selected_shipping_method();
    if( typeof( shipping_method ) != 'undefined' && shipping_method != '' && shipping_method.indexOf( 'usps' ) !== -1 && (shipping_method.split(":").length ) < 3 ) {
        shipping_method = jQuery( "#orddd_zone_id" ).val() + ":" + shipping_method;
    }

    if( typeof( shipping_method ) != 'undefined' && shipping_method != '' && shipping_method.indexOf( 'wf_fedex_woocommerce_shipping' ) === -1 && shipping_method.indexOf( 'fedex' ) !== -1 && ( shipping_method.split( ":" ).length ) < 3 ) {
        shipping_method = jQuery( "#orddd_zone_id" ).val() + ":" + shipping_method;
    }

    var shipping_class = jQuery( "#orddd_shipping_class_settings_to_load" ).val();
    
    var product_category = jQuery( "#orddd_category_settings_to_load" ).val();

    // TODO: Below code can be removed as the Pickup Locations addon is no longer available now
    var pickup_location = '';
    if( typeof orddd_lpp_method_func == 'function' ) {
        pickup_location = orddd_lpp_method_func( shipping_method );    
    }
    if( jQuery( "#time_slot_enable_for_shipping_method" ).val() == "on" ) {
        var data = {
            current_date: h_deliverydate,
            shipping_method: shipping_method,
            pickup_location: pickup_location,
            shipping_class: shipping_class, 
            product_category: product_category,
            orddd_location: location,
            time_slot_session: localStorage.getItem( "orddd_time_slot" ),
            min_date: jQuery( "#orddd_min_date_set" ).val(),
            current_date_to_check: jQuery( "#orddd_current_date_set" ).val(),
            holidays_str: jQuery( "#orddd_delivery_date_holidays" ).val(),
            lockout_str: jQuery( "#orddd_lockout_days" ).val(),
            action: "check_for_time_slot_orddd",
            admin: jsL10n.is_admin,
        };
        var option_selected = jQuery( '#orddd_auto_populate_first_available_time_slot' ).val();
        jQuery( "#orddd_time_slot" ).attr("disabled", "disabled");
        jQuery( "#orddd_time_slot_field" ).attr( "style", "opacity: 0.5" );
        if( jQuery( '#orddd_admin_url' ).val() != '' && typeof( jQuery( '#orddd_admin_url' ).val() ) != 'undefined' ) {
            jQuery.post( jQuery( '#orddd_admin_url' ).val() + "admin-ajax.php", data, function( response ) {
                jQuery( "#orddd_time_slot_field" ).attr( "style" ,"opacity:1" );
                if( jQuery( "#orddd_is_cart" ).val() == 1 ) {
                    jQuery( "#orddd_time_slot" ).attr( "style", "cursor: pointer !important;max-width:300px" );
                } else {
                    jQuery( "#orddd_time_slot" ).attr( "style", "cursor: pointer !important" );
                }
                jQuery( "#orddd_time_slot" ).removeAttr( "disabled" ); 
                
                orddd_load_time_slots( response );

                if( option_selected == "on" || localStorage.getItem( "orddd_time_slot" ) != '' ) {
                    jQuery( "body" ).trigger( "update_checkout" );
                    if ( 'on' == jQuery( '#orddd_delivery_date_on_cart_page' ).val() && jQuery( '#orddd_is_cart' ).val() == '1' ) {
                        jQuery( "#hidden_e_deliverydate" ).val( jQuery( '#e_deliverydate option:selected' ).text()  );
                        jQuery( "#hidden_h_deliverydate" ).val( h_deliverydate );
                        jQuery( "#hidden_timeslot" ).val( jQuery( "#orddd_time_slot" ).val() );
                        jQuery( "body" ).trigger( "wc_update_cart" );
                    }
                } 
            });
        }
    } else if( jQuery( "#orddd_enable_time_slot" ).val() == "on" && ( typeof( jQuery( "#time_slot_enable_for_shipping_method" ).val() ) == 'undefined' ) ) {
        var data = {
            current_date: h_deliverydate,
            order_id: jQuery( "#orddd_my_account_order_id" ).val(),
            min_date: jQuery( "#orddd_min_date_set" ).val(),
            current_date_to_check: jQuery( "#orddd_current_date_set" ).val(),
            time_slot_session: localStorage.getItem( "orddd_time_slot" ),
            holidays_str: jQuery( "#orddd_delivery_date_holidays" ).val(),
            lockout_str: jQuery( "#orddd_lockout_days" ).val(),
            action: "check_for_time_slot_orddd"
        };

        var option_selected = jQuery( '#orddd_auto_populate_first_available_time_slot' ).val();
        jQuery( "#orddd_time_slot" ).attr( "disabled", "disabled" );
        jQuery( "#orddd_time_slot_field" ).attr( "style", "opacity: 0.5" );
        if( jQuery( '#orddd_admin_url' ).val() != '' && typeof( jQuery( '#orddd_admin_url' ).val() ) != 'undefined' ) {
            jQuery.post( jQuery( '#orddd_admin_url' ).val() + "admin-ajax.php", data, function( response ) {
                jQuery( "#orddd_time_slot_field" ).attr( "style", "opacity: 1" );
                if( jQuery( "#orddd_is_cart" ).val() == 1 ) {
                    jQuery( "#orddd_time_slot" ).attr( "style", "cursor: pointer !important;max-width:300px" );
                } else {
                    jQuery( "#orddd_time_slot" ).attr( "style", "cursor: pointer !important" );
                }
                jQuery( "#orddd_time_slot" ).removeAttr( "disabled" ); 

                orddd_load_time_slots( response );

                if( option_selected == "on" ||  localStorage.getItem( "orddd_time_slot" ) != '' ) {
                    jQuery( "body" ).trigger( "update_checkout" );
                    if ( 'on' == jQuery( '#orddd_delivery_date_on_cart_page' ).val() && jQuery( '#orddd_is_cart' ).val() == '1' ) {
                        jQuery( "#hidden_e_deliverydate" ).val( jQuery( '#e_deliverydate option:selected' ).text() );
                        jQuery( "#hidden_h_deliverydate" ).val( h_deliverydate );
                        jQuery( "#hidden_timeslot" ).val( jQuery( "#orddd_time_slot" ).val() );
                        jQuery( "body" ).trigger( "wc_update_cart" );
                    }
                }  
            });
        }
    } else {
        jQuery( "#hidden_e_deliverydate" ).val( jQuery( '#e_deliverydate option:selected' ).text() );
        jQuery( "#hidden_h_deliverydate" ).val( h_deliverydate );
        jQuery( "#hidden_timeslot" ).val( jQuery( "#orddd_time_slot" ).val() );
        jQuery( "body" ).trigger( "update_checkout" );
        if ( 'on' == jQuery( '#orddd_delivery_date_on_cart_page' ).val() && jQuery( '#orddd_is_cart' ).val() == '1') {
            jQuery( "body" ).trigger( "wc_update_cart" );
        }
    }

    //localStorage.setItem( "e_deliverydate_session",jQuery('#e_deliverydate option:selected').text() );
    if( 'select' == jQuery( '#e_deliverydate' ).val() ) {
        localStorage.setItem( "e_deliverydate_session", '' );
    } else {
        localStorage.setItem( "e_deliverydate_session", jQuery( '#e_deliverydate option:selected' ).text() );
    }
    localStorage.setItem( "h_deliverydate_session",  h_deliverydate );
    if( localStorage.getItem( "orddd_time_slot" ) == null ) {
        localStorage.setItem( "orddd_time_slot", jQuery( "#orddd_time_slot" ).find( ":selected" ).val() );
    } 

    var current_date = jQuery( "#orddd_current_day" ).val();
    if( typeof( current_date ) != 'undefined' && current_date != '' ) {
        var split_current_date = current_date.split( '-' );
        var ordd_next_date = new Date( split_current_date[ 2 ], ( split_current_date[ 1 ] - 1 ), split_current_date[ 0 ], jQuery( "#orddd_current_hour" ).val(), jQuery( "#orddd_current_minute" ).val() );
    } else {
        var ordd_next_date = new Date();
    }            

    ordd_next_date.setHours( ordd_next_date.getHours() + 2 );
    localStorage.setItem( "orddd_storage_next_time", ordd_next_date.getTime() );
}

function load_dropdown_dates() {
    jQuery( "#h_deliverydate" ).val( jQuery( "e_deliverydate option:selected" ).val() );
   
    if( 'on' === jQuery( '#orddd_enable_shipping_based_delivery' ).val() ) {
        var custom_settings = jQuery('#orddd_unique_custom_settings').val();
        var custom_setting_id = '';
        if( 'global_settings' == custom_settings || '' == custom_settings ) {
            custom_setting_id = 0;
        } else {
            var custom_settings_arr = custom_settings.split('_');
            custom_setting_id = custom_settings_arr[2];
        }
      
        var data = {
            custom_setting_id: custom_setting_id,
            action: "check_for_dates_orddd"
        };

        if( jQuery( '#orddd_admin_url' ).val() != '' && typeof( jQuery( '#orddd_admin_url' ).val() ) != 'undefined' ) {
            jQuery.post( jQuery( '#orddd_admin_url' ).val() + "admin-ajax.php", data, function( response ) {
                jQuery( "#e_deliverydate_field" ).attr( "style", "opacity: 1" );
                if( jQuery( "#orddd_is_cart" ).val() == 1 ) {
                    jQuery( "#e_deliverydate" ).attr( "style", "cursor: pointer !important;max-width:300px" );
                } else {
                    jQuery( "#e_deliverydate" ).attr( "style", "cursor: pointer !important" );
                }
                jQuery( "#e_deliverydate" ).removeAttr( "disabled" );

                orddd_load_dates( response );
            });
        }
    }
    show_times_for_dropdown();
}

function orddd_load_dates( response ) {
    jQuery( "#e_deliverydate" ).empty(); 
    for( key in response ) {
        jQuery( "#e_deliverydate" ).append( jQuery( "<option></option>" ).attr( "value", key ).text( response[key] ) );
    }

    var h_deliverydate_session = localStorage.getItem( 'h_deliverydate_session' );

    if( h_deliverydate_session ) {
        jQuery( "#h_deliverydate" ).val(h_deliverydate_session);
        jQuery( "#e_deliverydate" ).val(h_deliverydate_session);
    }
}

/**
 * Update the date field based on session.
 */
function orddd_set_date_dropdown_from_session() {
    var e_deliverydate_session = localStorage.getItem( 'e_deliverydate_session' ),
        h_deliverydate_session = localStorage.getItem( 'h_deliverydate_session' );
    
    var shipping_method = orddd_get_selected_shipping_method();
    if ( ! e_deliverydate_session ) {
        e_deliverydate_session = jQuery( `#e_deliverydate option[value="${jQuery( "#h_deliverydate" ).val()}"]` ).text();

        localStorage.setItem( "e_deliverydate_session", e_deliverydate_session );
    }

    if ( ! h_deliverydate_session ) {
        localStorage.setItem( "h_deliverydate_session", jQuery( "#h_deliverydate" ).val() );
        h_deliverydate_session = jQuery( "#h_deliverydate" ).val();
    }
    if( typeof( e_deliverydate_session ) != 'undefined' && e_deliverydate_session != '' ) {
        if ( h_deliverydate_session ) {
            var default_date_arr = h_deliverydate_session.split( '-' );
            var default_date = new Date( default_date_arr[ 1 ] + '/' + default_date_arr[ 0 ] + '/' + default_date_arr[ 2 ] );
            
            var delay_weekday = default_date.getDay();
            var day           = 'orddd_weekday_' + delay_weekday;
            var enabled       = dwd( default_date );

            var delay_date = jQuery( "#orddd_minimumOrderDays" ).val();

            if( delay_date != "" && typeof( delay_date ) != 'undefined' ) {
                 var split_date = delay_date.split( "-" );
                 var delay_days = new Date ( split_date[ 1 ] + "/" + split_date[ 0 ] + "/" + split_date[ 2 ] );
            } else {
                 var delay_days = new Date();
            }

            var date_to_set = orddd_get_first_available_date( delay_date, delay_days );
            var session_date = '';
            if( undefined !== date_to_set && '' !== date_to_set ) {
                var session_date = date_to_set.getDate() + "-" + ( date_to_set.getMonth()+1 ) + "-" + date_to_set.getFullYear();
            }

            if( delay_days < default_date && enabled[0] == true ) {
                date_to_set = default_date;
            }else {
                h_deliverydate_session = session_date;
                localStorage.setItem( 'h_deliverydate_session', h_deliverydate_session );
            }

            jQuery( "#h_deliverydate" ).val( h_deliverydate_session );
            jQuery( '#e_deliverydate' ).val( h_deliverydate_session ).trigger('change');

            jQuery( "body" ).trigger( "update_checkout" );
            if ( 'on' == jQuery( '#orddd_delivery_date_on_cart_page' ).val() && jQuery( '#orddd_is_cart' ).val() == '1') {
                jQuery( "#hidden_e_deliverydate" ).val( jQuery( '#e_deliverydate option:selected').text() );
                jQuery( "#hidden_h_deliverydate" ).val( h_deliverydate_session );
                jQuery( "#hidden_timeslot" ).val( jQuery( "#orddd_time_slot" ).find( ":selected" ).val() );
                jQuery( "#hidden_shipping_method" ).val( shipping_method );
                jQuery( "#hidden_shipping_class" ).val( jQuery( "#orddd_shipping_class_settings_to_load" ).val() );
                jQuery( "body" ).trigger( "wc_update_cart" );
            }

            show_times_for_dropdown();
        }
    }
}