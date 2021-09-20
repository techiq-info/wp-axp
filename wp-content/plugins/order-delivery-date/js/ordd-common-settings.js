/**
 * Events added to perform UI changes in the admin
 * 
 * @namespace orddd_admin_js
 * @since 7.5
 */

jQuery( function( $ ) {	
	$('#orddd_business_opening_time, #orddd_business_closing_time').timepicker({ 
		'scrollDefault': 'now', 
		'timeFormat' : 'h:i A',
		'step'	: 15,
		'listWidth' : 1,
	});

	$(document).on( 'change', '#orddd_delivery_date_1, #orddd_delivery_date_2, #orddd_delivery_date_3, #orddd_delivery_date', function() {
		var ordd_id    = $(this).attr( "id" );
		var ordd_value = this.value.length; 

		if ( "orddd_delivery_date_1" == ordd_id && ordd_value === 0 ) {
			$( "#additional_charges_1" ).prop( "disabled", true );
			$( "#specific_charges_label_1" ).prop( "disabled", true );
		} else if ( "orddd_delivery_date_2" == ordd_id && ordd_value === 0 ) {
			$( "#additional_charges_2" ).prop( "disabled", true );
			$( "#specific_charges_label_2" ).prop( "disabled", true );
		} else if ( "orddd_delivery_date_3" == ordd_id && ordd_value === 0 ) {
			$( "#additional_charges_3" ).prop( "disabled", true );
			$( "#specific_charges_label_3" ).prop( "disabled", true );
		} else if( "orddd_delivery_date" == ordd_id && ordd_value === 0 ) {
			$( "#additional_charges" ).prop( "disabled", true );
			$( "#specific_charges_label" ).prop( "disabled", true );
		} else if ( "orddd_delivery_date_1" == ordd_id && ordd_value > 0 ) {
			$( "#additional_charges_1" ).prop( "disabled", false );
			$( "#specific_charges_label_1" ).prop( "disabled", false );
		} else if ( "orddd_delivery_date_2" == ordd_id && ordd_value > 0 ) {
			$( "#additional_charges_2" ).prop( "disabled", false );
			$( "#specific_charges_label_2" ).prop( "disabled", false );
		} else if( "orddd_delivery_date_3" == ordd_id && ordd_value > 0 ) {
			$( "#additional_charges_3" ).prop( "disabled", false );
			$( "#specific_charges_label_3" ).prop( "disabled", false );
		} else if( "orddd_delivery_date" == ordd_id && ordd_value > 0 ) {
			$( "#additional_charges" ).prop( "disabled", false );
			$( "#specific_charges_label" ).prop( "disabled", false );
		}
	});

	$(document).on( 'change', '#orddd_delivery_dates_in_dropdown', function() {
		if( 'yes' === $(this).val() ) {
			$('#start_of_week').closest('tr').hide();
			$('#orddd_number_of_months').closest('tr').hide();
			$('#switcher').closest('tr').hide();
			$('#orddd_calendar_display_mode').closest('tr').hide();
		} else {
			$('#start_of_week').closest('tr').show();
			$('#orddd_number_of_months').closest('tr').show();
			$('#switcher').closest('tr').show();
			$('#orddd_calendar_display_mode').closest('tr').show();
		}
	});

	jQuery( document ).ready( function() {
		// Add Color Picker to all inputs that have 'color-field' class
		jQuery( '.cpa-color-picker' ).wpColorPicker();

		if( 'yes' === $('#orddd_delivery_dates_in_dropdown').val() ) {
			$('#start_of_week').closest('tr').hide();
			$('#orddd_number_of_months').closest('tr').hide();
			$('#switcher').closest('tr').hide();
			$('#orddd_calendar_display_mode').closest('tr').hide();
		} else {
			$('#start_of_week').closest('tr').show();
			$('#orddd_number_of_months').closest('tr').show();
			$('#switcher').closest('tr').show();
			$('#orddd_calendar_display_mode').closest('tr').show();
		}

		if( typeof jQuery( "#is_shipping_based_page" ).val() != "undefined" && jQuery( "#is_shipping_based_page" ).val() != '' ) {
			if ( jQuery( "input[type=radio][id=\"orddd_delivery_settings_type\"][value=\"product_categories\"]" ).is(":checked") ) {
				jQuery( '.delivery_type_options' ).slideUp();
				jQuery( '.delivery_type_product_categories' ).slideDown();
		        i = 0;
		        var isChecked = jQuery( "#orddd_enable_shipping_based_delivery_date" ).is( ":checked" );
		        jQuery( ".form-table" ).each( function() {
		            if( i == 1 ) {
		                k = 0;
		                var row = jQuery( this ).find( "tr" );
		                jQuery.each( row , function() {
		                    if( k == 0 ) {
		                    	if( isChecked == 'true' ) {
		                    		jQuery( this ).fadeIn();            	
		                    	}
		                    }
		                    k++ 
		                });
		            } 
		            i++;
		        }); 
			} else if ( jQuery( "input[type=radio][id=\"orddd_delivery_settings_type\"][value=\"orddd_locations\"]" ).is(":checked") ) { 
				jQuery( '.delivery_type_options' ).slideUp();
				jQuery( '.delivery_type_orddd_locations' ).slideDown();
		        i = 0;
		        jQuery( ".form-table" ).each( function() {
		            if( i == 1 ) {
		                k = 0;
		                var row = jQuery( this ).find( "tr" );
		                jQuery.each( row , function() {
		                    if( k == 0 ) {
		                    	jQuery( this ).fadeOut();
		                    }
		                    k++ 
		                });
		            } 
		            i++;
		        }); 
			} else {
				jQuery( '.delivery_type_options' ).slideDown();
				jQuery( '.delivery_type_product_categories' ).slideUp();
     		    jQuery( '.delivery_type_orddd_locations' ).slideUp();
		        i = 0;
		        jQuery( ".form-table" ).each( function() {
		            if( i == 1 ) {
		                k = 0;
		                var row = jQuery( this ).find( "tr" );
		                jQuery.each( row , function() {
		                    if( k == 0 ) {
		                        jQuery( this ).fadeOut();            
		                    }
		                    k++ 
		                });
		            } 
		            i++;
		        } ); 
			}
		}

		var month_short_names =  ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
		var formats = [ "mm-dd-yy", "d.m.y", "d M, yy","MM d, yy" ];

        jQuery.datepicker.setDefaults( jQuery.datepicker.regional[ "en-GB" ] );
        jQuery( "#orddd_shipping_based_holiday_from_date" ).width( "160px" );
        jQuery( "#orddd_shipping_based_holiday_to_date" ).width( "160px" );

        jQuery( "#orddd_shipping_based_holiday_from_date" ).val("").datepicker( {
            constrainInput: true,
            dateFormat: formats[0],
            onSelect: function( selectedDate,inst ) {
                var monthValue = inst.selectedMonth+1;
				var dayValue = inst.selectedDay;
				var yearValue = inst.selectedYear;
                var current_dt = dayValue + "-" + monthValue + "-" + yearValue;
                var to_date = jQuery( "#orddd_shipping_based_holiday_to_date" ).val();
                if ( to_date == "" ) {
                    var split = current_dt.split( "-" );
					split[1] = split[1] - 1;
					var minDate = new Date( split[2], split[1], split[0] );
                    jQuery( "#orddd_shipping_based_holiday_to_date" ).datepicker( "setDate",minDate );
                }
			},
			firstDay: jQuery("input[name='orddd_holiday_start_day']").val()
		} );
            
		jQuery( "#orddd_shipping_based_holiday_to_date" ).val("").datepicker( {
		    constrainInput: true,
			dateFormat: formats[0],
			firstDay: jQuery("input[name='orddd_holiday_start_day']").val()
		} );

        jQuery( "table#orddd_holidays_list" ).on( "click", "a.confirmation_holidays", function() {
            var holidays_hidden = jQuery( "#orddd_holiday_hidden" ).val();
            var holiday_name = jQuery( "table#orddd_holidays_list tr#"+ this.id + " td#orddd_holiday_name" ).html();
            var holiday_date = jQuery( "table#orddd_holidays_list tr#"+ this.id + " td#orddd_holiday_date" ).html();
            var recurring_type_text = jQuery( "table#orddd_holidays_list tr#"+ this.id + " td#orddd_allow_recurring_type" ).html();
            if( recurring_type_text == localizeStrings.holidayrecurringText ) {
            	var recurring_type = 'on';
            } else {
            	var recurring_type = '';
            }
            var split_date = holiday_date.split( "-" );            
            var dt = new Date ( split_date[ 0 ] + "/" + split_date[ 1 ] + "/" + split_date[ 2 ] );
            var date = ( dt.getMonth() + 1 ) + "-" + dt.getDate() + "-" + dt.getFullYear();    
            var substring = "{" + holiday_name + ":" + date + ":" + recurring_type + "},";
            var updatedString = holidays_hidden.replace( substring, "" );
            jQuery( "#orddd_holiday_hidden" ).val( updatedString );
            jQuery( "table#orddd_holidays_list tr#"+ this.id ).remove();
        });
        
        jQuery( "#save_holidays" ).click(function() {
            var holidays_row_arr = [];
            var holidays = [];
            
            var row = jQuery( "#orddd_holiday_hidden" ).val();
            if( row != "" ) {
                holidays_row_arr = row.split(",");
                for( i = 0; i < holidays_row_arr.length; i++ ) {
                    if( holidays_row_arr[ i ] != "" ) {
                        var string = holidays_row_arr[ i ].replace( "{", "" );
                        string = string.replace( "}", "" );
                        var string_arr = string.split( ":" );
                        holidays.push( string_arr[ 1 ] );
                    }
                }
            }
                    
	        var split_from_date = jQuery( "#orddd_shipping_based_holiday_from_date" ).val().split( "-" );
	        split_from_date[0] = split_from_date[0] - 1;
	        var from_date = new Date( split_from_date[2], split_from_date[0], split_from_date[1] );
	        
	        var split_to_date = jQuery( "#orddd_shipping_based_holiday_to_date" ).val().split( "-" );
	        split_to_date[0] = split_to_date[0] - 1;
	        var to_date = new Date( split_to_date[2], split_to_date[0], split_to_date[1] );
                    
            var timediff = ( ( to_date.getTime() - from_date.getTime() ) / ( 1000 * 60 * 60 * 24 ) ) + 1;
            var date = jQuery( "#orddd_shipping_based_holiday_from_date" ).val();
            for ( i = 1; i <= timediff; i++ ) {
                if( from_date <= to_date ) {
                    hidden_date = ( from_date.getMonth() + 1 ) + "-" + from_date.getDate() + "-" + from_date.getFullYear();
                    if( jQuery.inArray( hidden_date, holidays ) == -1 ) {  
                        var rowCount = jQuery( "#orddd_holidays_list tr" ).length;
                        if( rowCount == 0 ) {
                            jQuery( "#orddd_holidays_list" ).append( "<tr class=\"orddd_common_list_tr\"><th class=\"orddd_holidays_list\"> " + localizeStrings.holidaynameText + "</th><th class=\"orddd_holidays_list\">" + localizeStrings.holidaydateText + "</th><th class=\"orddd_holidays_list\">" + localizeStrings.holidaytypeText + "</th><th class=\"orddd_holidays_list\">" + localizeStrings.holidayactionText + "</th></tr>" );
                            var rowCount = 1;
                        }

                        rowCount = rowCount - 1;
                        if( from_date.getDate() < 10 ){ 
                            dd = "0" + from_date.getDate();
                        } else {
                            dd = from_date.getDate();
                        }

                        if( ( from_date.getMonth() + 1 ) < 10 ){ 
                            mm = "0" + ( from_date.getMonth() + 1 );
                        } else {
                            mm = ( from_date.getMonth() + 1 );
                        }

                        date =  mm + "-" + dd + "-" + from_date.getFullYear();

                        var recurring_type_text = localizeStrings.holidaycurrentText;
                        var recurring_type = '';
                        var isChecked = jQuery( "#orddd_shipping_based_allow_recurring_holiday" ).is( ":checked" );
                        
                		if( isChecked == true ) {
                        	recurring_type_text = localizeStrings.holidayrecurringText;
                        	recurring_type = 'on';
                        }

                        jQuery( "#orddd_holidays_list tr:last" ).after( "<tr class=\"orddd_common_list_tr\" id=\"orddd_delete_holidays_" + rowCount + "\"><td class=\"orddd_holidays_list\" id=\"orddd_holiday_name\">" + jQuery("#orddd_shipping_based_holiday_name").val() + "</td><td class=\"orddd_holidays_list\" id=\"orddd_holiday_date\">" + date +"</td><td class=\"orddd_holidays_list\" id=\"orddd_allow_recurring_type\">" + recurring_type_text +"</td><td class=\"orddd_holidays_list\"><a href=\"javascript:void(0)\" class=\"confirmation_holidays\" id=\"orddd_delete_holidays_" + rowCount + "\">" + localizeStrings.holidaydeleteText + "</a></td></tr>" );

                        row += "{" + jQuery( "#orddd_shipping_based_holiday_name" ).val() + ":" + hidden_date + ":" + recurring_type + "},";
                    }

                    from_date.setDate( from_date.getDate() + 1 );
                }
            }

            jQuery( "#orddd_holiday_hidden" ).val( row );
            jQuery( "#orddd_shipping_based_holiday_from_date" ).datepicker( "setDate", "" );
            jQuery( "#orddd_shipping_based_holiday_to_date" ).datepicker( "setDate", "" );
            jQuery( "#orddd_shipping_based_holiday_name" ).val( "" );
            jQuery( "#orddd_shipping_based_allow_recurring_holiday" ).prop( "checked", false );
        });
	});

	if( typeof jQuery( "#is_shipping_based_page" ).val() != "undefined" && jQuery( "#is_shipping_based_page" ).val() != '' ) {
	    jQuery( '.orddd_shipping_methods' ).select2();
	    jQuery( '.orddd_shipping_methods' ).css({'width': '300px' });
	    jQuery( "input[type=radio][id=\"orddd_delivery_settings_type\"]" ).on( 'change', function() {
			if ( jQuery( this ).is(':checked') ) {
				var value = jQuery( this ).val();
				jQuery( '.delivery_type_options' ).slideUp();
				jQuery( '.delivery_type_' + value ).slideDown();
				var isChecked = jQuery( "#orddd_enable_shipping_based_delivery_date" ).is( ":checked" );
	            if( value == 'product_categories' ) {
	                i = 0;
	                jQuery( ".form-table" ).each( function() {
	                    if( i == 1 ) {
	                        k = 0;
	                        var row = jQuery( this ).find( "tr" );
	                        jQuery.each( row , function() {
	                            if( k == 0 ) {
	                            	if( isChecked == true ) {
	                                	jQuery( this ).fadeIn();            
	                                }
	                            }
	                            k++ 
	                        });
	                    } 
	                    i++;
	                } ); 
	            } else {
	                i = 0;
	                jQuery( ".form-table" ).each( function() {
	                    if( i == 1 ) {
	                        k = 0;
	                        var row = jQuery( this ).find( "tr" );
	                        jQuery.each( row , function() {
	                            if( k == 0 ) {
	                                jQuery( this ).fadeOut();            
	                            }
	                            k++ 
	                        });
	                    } 
	                    i++;
	                } ); 
	            }
			}
		});
	}

	from_value = $( '#orddd_delivery_from_hours' ).val();
	to_value = $( '#orddd_delivery_to_hours' ).val();
	for( i = from_value - 1; i > 0; i-- ) {
		$( '#orddd_delivery_to_hours option[value="'+i+'"]' ).attr( 'disabled', true );		
	}
	
	$( '#orddd_delivery_from_hours' ).on( 'select change', function() {
		from_value = $( '#orddd_delivery_from_hours' ).val();
		to_value = $( '#orddd_delivery_to_hours' ).val();
		for( i = from_value - 1; i >= 0; i-- ) {
			if( i != 0 ) {
				$( '#orddd_delivery_to_hours option[value="'+i+'"]' ).attr( 'disabled', true );		
			}
			$( '#orddd_delivery_to_hours' ).val( from_value );
		}

		for( j = from_value ; j < 24 ; j++ ) {
			$( '#orddd_delivery_to_hours option[value="'+j+'"]' ).attr( 'disabled', false );		
		}
	});

	$( '#clone_setting' ).on( 'click', function(e) {
		e.preventDefault();
		var setting_id = $(this).data('id');
		var data = {
			setting_id : setting_id,
			action: 'orddd_clone_custom_settings'
		}

		jQuery.post( localizeStrings.ajax_url, data, function( response ) {
			if( 'success' == response ) {
				location.reload();
			}
		});
	});

	jQuery( '#save_timeslots' ).click( function() {
		var time_slot_hidden = jQuery( '#orddd_time_slot_hidden' ).val();
		if( time_slot_hidden != '' ) {
			var hidden_arr = time_slot_hidden.split( '},' );	
		} else {
			var hidden_arr = [];
		}

		let from_time_array = [];
		let to_time_array 	= [];
		var timeslot_array  = [];
		var delivery_days   = '';
		var duration		= '';
		var frequency		= '';
		var is_bulk 		= jQuery( '#orddd_individual_or_bulk' ).val();
		var selected_days	= '';
		var lockout     	= '';
		var charges 		= '';
		var charges_label   = '';
		if( 'bulk' === is_bulk ) {
			delivery_days  = jQuery('input[name=orddd_shipping_based_time_slot_for_delivery_days_bulk]:checked' ).val();
			duration 	   = jQuery('#orddd_shipping_based_time_slot_duration').val();
			frequency 	   = jQuery('#orddd_shipping_based_time_slot_interval').val();
			selected_days  = jQuery( '#orddd_shipping_based_time_slot_for_weekdays_bulk' ).val();
			selected_dates = jQuery( '#orddd_shipping_based_select_delivery_dates_bulk' ).val();
			lockout 	   = jQuery( '#orddd_shipping_based_time_slot_lockout_bulk' ).val();
			charges 	   = jQuery( '#orddd_shipping_based_time_slot_additional_charges_bulk' ).val();
			charges_label  = jQuery( '#orddd_shipping_based_time_slot_additional_charges_label_bulk' ).val();

			var duration_in_secs  = duration * 60;
			var frequency_in_secs = frequency * 60;
			var time_starts_from  = jQuery("#orddd_shipping_based_time_slot_starts_from").val();
			var time_ends_at 	  = jQuery("#orddd_shipping_based_time_slot_ends_at").val();

			if( 0 == duration ) {
				jQuery( '#orddd_time_slot_list' ).before( '<div id=\'cdts-days-error-msg\' class=\'error settings-error notice is-dismissible\' style=\' width:50%;position:absolute;margin-left:50px;\'>Please Set the Time Slot Duration to be Greater than 0.</div>' );
				setTimeout( function() {
					jQuery( '#cdts-days-error-msg' ).fadeOut();
				}, 3000 );
			} else if( '' !== time_starts_from ) {
				var data = {
					time_starts_from: time_starts_from,
					time_ends_at: time_ends_at,
					duration_in_secs: duration_in_secs,
					frequency_in_secs: frequency_in_secs,
					action: 'orddd_get_time_slots_between_interval'
				}
			
				jQuery.post( localizeStrings.ajax_url, data, function( response ) {
					response.forEach( from_time => {
						timeslot_array.push( from_time );
					});
		
					orddd_add_timeslots( timeslot_array, delivery_days, hidden_arr, selected_days, selected_dates, lockout, charges, charges_label );
				});
			}
		} else {
			delivery_days   = jQuery('input[name=orddd_shipping_based_time_slot_for_delivery_days]:checked' ).val();
			selected_days   = jQuery( '#orddd_shipping_based_time_slot_for_weekdays' ).val();
			selected_dates  = jQuery( '#orddd_shipping_based_select_delivery_dates' ).val();
			lockout 	    = jQuery( '#orddd_shipping_based_time_slot_lockout' ).val();
			charges 	    = jQuery( '#orddd_shipping_based_time_slot_additional_charges' ).val();
			charges_label   = jQuery( '#orddd_shipping_based_time_slot_additional_charges_label' ).val();
			from_time_array = jQuery( 'input[name="orddd_shipping_based_time_from_hours[]"]' ).map( function(){
								return $(this).val();
							}).get();

			to_time_array = jQuery( 'input[name="orddd_shipping_based_time_to_hours[]"]' ).map( function(){
									return $(this).val();
								})
							.get();

			from_time_array.forEach( ( from_time, index ) => {
				if( '' === to_time_array[index] ) {
					timeslot_array.push( from_time );
				} else {
					timeslot_array.push( from_time + " - " + to_time_array[index] );
				}
			});

			orddd_add_timeslots( timeslot_array, delivery_days, hidden_arr, selected_days, selected_dates, lockout, charges, charges_label );
		}
	});

	// Delete time slots

	jQuery( 'table#orddd_time_slot_list' ).on( 'click', 'a.confirmation_time_slot', function() {
		var orddd_weekdays = jQuery.parseJSON( localizeStrings.orddd_weekdays );

		var time_slot_hidden = jQuery( '#orddd_time_slot_hidden' ).val();
		var time_slot = jQuery( 'table#orddd_time_slot_list tr#'+ this.id + ' td#orddd_time_slot' ).html();
		var date_str = jQuery( 'table#orddd_time_slot_list tr#'+ this.id + ' td#orddd_delivery_day' ).html();
		if( date_str.indexOf( '-' ) !== -1 ) {
			var delivery_day_type = 'specific_dates';
		} else {
			var delivery_day_type = 'weekdays'; 
		}
		if( delivery_day_type == 'weekdays' ) { 
			var orddd_weekdays_js = {};
			jQuery.each( orddd_weekdays, function( key, value ) {
				orddd_weekdays_js[ `${key}_custom_setting` ] = value;
			});
			jQuery.each( orddd_weekdays_js, function( key, name ) {
				if ( name == date_str ) {
					date_str = key;
				} else if( 'All' == date_str ) {
					date_str = 'all';
				}
			});
		} else if( delivery_day_type == 'specific_dates' ) {
			var specific_date = date_str.split( '-' );
			var new_specific_date = new Date( specific_date[ 0 ] + '/' + specific_date[ 1 ] + '/' + specific_date[ 2 ] );
			date_str = ( new_specific_date.getMonth()+1 ) + '-' + new_specific_date.getDate() + '-' + new_specific_date.getFullYear(); 
		} 
	
		var hidden_arr = time_slot_hidden.split( '},' );
		var substring = '';
		for( i = 0; i < hidden_arr.length; i++ ) {
			if( hidden_arr[ i ] != '' ) {
				var date_hidden_arr = hidden_arr[ i ].split( ':' );
				if( date_hidden_arr.length == '7' ) {
					var time_slot_str = date_hidden_arr[ 2 ] + ':' + date_hidden_arr[ 3 ];
				} else {
					var time_slot_str = date_hidden_arr[ 2 ] + ':' + date_hidden_arr[ 3 ] + ':' + date_hidden_arr[ 4 ];
				}    
				var array = date_hidden_arr[ 1 ].split( ',' );
				for( j = 0; j < array.length; j++ ) {
					if( date_str == array[ j ].trim() && time_slot == time_slot_str ) {
						array.splice( j, 1 );
					}
				}
				var array_str = array.join( ',' );
				if( array_str != '' ) {
					if( date_hidden_arr.length == '7' ) {
						hidden_arr[ i ] = date_hidden_arr[ 0 ] + ':' + array_str + ':' + time_slot_str + ':' + date_hidden_arr[ 4 ] + ':' + date_hidden_arr[ 5 ] + ':' + date_hidden_arr[ 6 ];
					} else {
						hidden_arr[ i ] = date_hidden_arr[ 0 ] + ':' + array_str + ':' + time_slot_str + ':' + date_hidden_arr[ 5 ] + ':' + date_hidden_arr[ 6 ] + ':' + date_hidden_arr[ 7 ];
					}
				} else {
					hidden_arr.splice( i, 1 );                           
				}
			}
		}
		jQuery( '#orddd_time_slot_hidden' ).val(  hidden_arr.join( '},' ) );
		jQuery( 'table#orddd_time_slot_list tr#'+ this.id ).remove();
	});


	jQuery( 'table#orddd_time_slot_list' ).on( 'click', 'a.edit_time_slot', function(e) {
		e.preventDefault();
		var orddd_weekdays = jQuery.parseJSON( localizeStrings.orddd_weekdays );

		var currentTD = $(this).closest('tr').find('td');
		var rowIndex  = $(this).parents("tr").index();
		rowIndex	  = rowIndex - 1;

		$.each(currentTD, function( index, value ) {
			var html = $(this).html();
			$(this).data( 'oldValue', html );
			if( 0 !== index && 5 !== index ) {
				var input = '';
				if( 2 == index ) {
					input = $(`<input name="orddd_edit_max_deliveries_${rowIndex}" id="orddd_edit_max_deliveries_${rowIndex}" type="number" value="${html}" />`);
					input.val(html);
				} else if( 3 == index ) {
					var charges_value = html.length > 0 ? parseFloat( html.substring(1) ) : '';
					input = $(`<input name="orddd_edit_charges_${rowIndex}" id="orddd_edit_charges_${rowIndex}" type="text"  value="${charges_value}"/>`);
					input.val(charges_value);
				} else if( 4 == index ) {
					input = $(`<input name="orddd_edit_charges_label_${rowIndex}" id="orddd_edit_charges_label_${rowIndex}" type="text" value="${html}"/>`);
					input.val(html);
				} else if( 1 == index ) {
					var time_array = html.split( ' - ' );
					var from_time  = time_array[0];
					var to_time    = '';
					if( undefined !== time_array[1] ) {
						to_time = time_array[1];
					}
					input = $( `
						<input type="text" name="orddd_edit_time_from_hours_${rowIndex}" id="orddd_edit_time_from_hours_${rowIndex}" class="orddd_time_slot" value="${from_time}" data-value="${from_time}"/>
						To
						<input type="text" name="orddd_edit_time_to_hours_${rowIndex}" id="orddd_edit_time_to_hours_${rowIndex}" class="orddd_time_slot" value="${to_time}" data-value="${to_time}"/>
					`);
				}
				$(this).html(input);
			}
			
			if( 5 == index ) {
				input = $( `
					<a href="#" class="orddd_custom_update_time">Update</a>
					<a href="#" class="orddd_custom_cancel">Cancel</a>
				`);

				$(this).html(input);
			}

			if( 0 == index ) {
				var weekday = $(this)
								.clone()	//clone the element
								.children()	//select all the children
								.remove()	//remove all the children
								.end()	//again go back to selected element
								.text();
				var selected_weekday = '';
				var weekday_or_date = '';
				if( weekday.indexOf( '-' ) !== -1 ) {
					var specific_date = weekday.split( '-' );
					var new_specific_date = new Date( specific_date[ 0 ] + '/' + specific_date[ 1 ] + '/' + specific_date[ 2 ] );
					weekday = ( new_specific_date.getMonth()+1 ) + '-' + new_specific_date.getDate() + '-' + new_specific_date.getFullYear(); 
					selected_weekday = weekday;
					weekday_or_date = 'specific_dates';
				}
				jQuery.each( orddd_weekdays, function( key, day ) {
					if( day == weekday ) {
						selected_weekday = `${key}_custom_setting`;
						weekday_or_date = 'weekdays';
					}
				});
				input = $( `
					<input type="hidden" name="orddd_edit_weekday" id="orddd_edit_weekday_${rowIndex}" value="${selected_weekday}">
					<input type="hidden" name="orddd_edit_weekday_or_date" id="orddd_edit_weekday_or_date${rowIndex}" value="${weekday_or_date}">
				`);

				$(this).append(input);
			}
		});
	});

	jQuery( 'table#orddd_time_slot_list' ).on( 'click', '.orddd_custom_cancel', function(e) {
		e.preventDefault();
		var currentTD = $(this).closest('tr').find('td');
		var rowIndex  = $(this).parents("tr").index();
		rowIndex	  = rowIndex - 1;
		//enable the current row
		$.each(currentTD, function( index, value ) {
			var html = $(this).data('oldValue');
		
			if ( 5 === index ) {
				input = $(`<a href="javascript:void(0)" id="orddd_edit_time_slot_${rowIndex}" class="edit_time_slot">Edit</a> | 
				<a href="javascript:void(0)"  class="confirmation_time_slot" id="orddd_delete_time_slot_${rowIndex}">Delete</a>`);
				$(this).html(input);
			} else {
				$(this).html(html);
			}
		});
	});

	$( 'table#orddd_time_slot_list' ).on( 'click', '.orddd_custom_update_time', function(e) {
		e.preventDefault();
		var orddd_weekdays = jQuery.parseJSON( localizeStrings.orddd_weekdays );
		var currentTD = jQuery(this).closest('tr').find('td');
		var rowIndex  = jQuery(this).parents("tr").index();
		rowIndex	  = rowIndex - 1;

		var time_slot_hidden = jQuery( '#orddd_time_slot_hidden' ).val();

		var date_str 								 = jQuery( `#orddd_edit_weekday_${rowIndex}` ).val();
		var orddd_time_from_hours 					 = jQuery(`#orddd_edit_time_from_hours_${rowIndex}`).val();
		var	from_time_old 							 = jQuery(`#orddd_edit_time_from_hours_${rowIndex}`).data('value');
		var	orddd_time_to_hours 					 = jQuery(`#orddd_edit_time_to_hours_${rowIndex}`).val();
		var	to_time_old 						 	 = jQuery(`#orddd_edit_time_to_hours_${rowIndex}`).data('value');
		var	orddd_time_slot_lockout 				 = jQuery(`#orddd_edit_max_deliveries_${rowIndex}`).val();
		var	orddd_time_slot_additional_charges 		 = jQuery(`#orddd_edit_charges_${rowIndex}`).val();
		var	orddd_time_slot_additional_charges_label = jQuery(`#orddd_edit_charges_label_${rowIndex}`).val();
		var time_slot 								 = from_time_old + ' - ' + to_time_old;

		if( '' == to_time_old ) {
			time_slot = from_time_old;
		}

		if( date_str.indexOf( '-' ) !== -1 ) {
			var delivery_day_type = 'specific_dates';
		} else {
			var delivery_day_type = 'weekdays'; 
		}
		if( delivery_day_type == 'weekdays' ) { 
			var orddd_weekdays_js = {};
			jQuery.each( orddd_weekdays, function( key, value ) {
				orddd_weekdays_js[ `${key}_custom_setting` ] = value;
			});
			jQuery.each( orddd_weekdays_js, function( key, name ) {
				if ( name == date_str ) {
					date_str = key;
				} else if( 'All' == date_str ) {
					date_str = 'all';
				}
			});
		} else if( delivery_day_type == 'specific_dates' ) {
			var specific_date = date_str.split( '-' );
			var new_specific_date = new Date( specific_date[ 0 ] + '/' + specific_date[ 1 ] + '/' + specific_date[ 2 ] );
			date_str = ( new_specific_date.getMonth()+1 ) + '-' + new_specific_date.getDate() + '-' + new_specific_date.getFullYear(); 
		} 

		var hidden_arr = time_slot_hidden.split( '},' );
		hidden_arr = orddd_get_separate_values( hidden_arr );
		for( i = 0; i < hidden_arr.length; i++ ) {
			if( hidden_arr[ i ] != '' ) {
				var date_hidden_arr = hidden_arr[ i ].split( ':' );

				if( date_hidden_arr.length == '7' ) {
					var time_slot_str = date_hidden_arr[ 2 ] + ':' + date_hidden_arr[ 3 ];
				} else {
					var time_slot_str = date_hidden_arr[ 2 ] + ':' + date_hidden_arr[ 3 ] + ':' + date_hidden_arr[ 4 ];
				}    
				var array = date_hidden_arr[ 1 ].split( ',' );
				for( j = 0; j < array.length; j++ ) {
					if( date_str == array[ j ].trim() && time_slot == time_slot_str ) {
						time_slot_str = orddd_time_from_hours + ' - ' + orddd_time_to_hours;
						if( '' == orddd_time_to_hours ) {
							time_slot_str = orddd_time_from_hours;
						}
						if( date_hidden_arr.length == '7' ) {
							date_hidden_arr[ 4 ] = orddd_time_slot_lockout;
							date_hidden_arr[ 5 ] = orddd_time_slot_additional_charges;
							date_hidden_arr[ 6 ] = orddd_time_slot_additional_charges_label;
						} else {
							date_hidden_arr[ 5 ] = orddd_time_slot_lockout;
							date_hidden_arr[ 6 ] = orddd_time_slot_additional_charges;
							date_hidden_arr[ 7 ] = orddd_time_slot_additional_charges_label;
						}
					}
				}
				var array_str = array.join( ',' );
				if( array_str != '' ) {
					if( date_hidden_arr.length == '7' ) {
						hidden_arr[ i ] = date_hidden_arr[ 0 ] + ':' + array_str + ':' + time_slot_str + ':' + date_hidden_arr[ 4 ] + ':' + date_hidden_arr[ 5 ] + ':' + date_hidden_arr[ 6 ];
					} else {
						hidden_arr[ i ] = date_hidden_arr[ 0 ] + ':' + array_str + ':' + time_slot_str + ':' + date_hidden_arr[ 5 ] + ':' + date_hidden_arr[ 6 ] + ':' + date_hidden_arr[ 7 ];
					}
				}
			}
		}
		jQuery( '#orddd_time_slot_hidden' ).val(  hidden_arr.join( '},' ) );

		$.each(currentTD, function( index, value ) {
			var html = $(this).data('oldValue');
			switch( index ) {
				case 1:
					var from_time = $(`#orddd_edit_time_from_hours_${rowIndex}`).val();
					var to_time = $(`#orddd_edit_time_to_hours_${rowIndex}`).val();
					var time = from_time + " - " + to_time;
					if( '' == to_time ) {
						time = from_time;
					}
					$(this).html(time);
					break;
				case 2:
					$(this).html( $(`#orddd_edit_max_deliveries_${rowIndex}`).val() );
					break;
				case 3:
					var charges_value = $(`#orddd_edit_charges_${rowIndex}`).val();
					var charges_str = charges_value.length > 0 ? timeslotStrings.currency + charges_value : charges_value;
					$(this).html( charges_str );
					break;
				case 4:
					$(this).html( $(`#orddd_edit_charges_label_${rowIndex}`).val() );
					break;
				case 5:
					input = $(`<a href="#" id="orddd_edit_time_slot_${rowIndex}" class="edit_time_slot">Edit</a> | 
					<a href="javascript:void(0)"  class="confirmation_time_slot" id="orddd_delete_time_slot_${rowIndex}">Delete</a>`);
					$(this).html(input);
					break;
			}
		});

	});

});

function orddd_add_timeslots( timeslot_array, delivery_days, hidden_arr, selected_days, selected_dates, lockout, charges, charges_label ) {
	var orddd_weekdays = jQuery.parseJSON( localizeStrings.orddd_weekdays );

	if( timeslot_array.length > 0 ) {	
		timeslot_array.forEach( ( timeslot, index ) => {
			var time_slot = timeslot;
			var added_timeslots = [];
			var weekdays = [];
			for( i = 0; i < hidden_arr.length; i++ ) {
				if( hidden_arr[ i ] != '' ) {
					var date_hidden_arr = hidden_arr[ i ].split( ':' );
					if( date_hidden_arr.length == '7' ) {
						var time_slot_str = date_hidden_arr[ 2 ] + ':' + date_hidden_arr[ 3 ];
					} else {
						var time_slot_str = date_hidden_arr[ 2 ] + ':' + date_hidden_arr[ 3 ] + ':' + date_hidden_arr[ 4 ];
					}    
					added_timeslots.push( time_slot_str );
					weekdays[time_slot_str] = date_hidden_arr[ 1 ];
				}
			}


			var timeslot_present = 'no';
			var rowCount 	   	 = jQuery( '#orddd_time_slot_list tr').length;

			if( delivery_days == 'weekdays' ) {
				var dd = selected_days;
				if( dd != '' && dd != null ) {
					var orddd_weekdays_js = {};

					jQuery.each( orddd_weekdays, function( key, value ) {
						orddd_weekdays_js[ `${key}_custom_setting` ] = value;
					});
					if( rowCount == 0 ) {
						jQuery( '#orddd_time_slot_list' ).append( `
						<tr class=\'orddd_common_list_tr\'>
							<th class=\'orddd_holiday_list\'>${timeslotStrings.timeslotDayText}</th>
							<th class=\'orddd_holiday_list\'>${timeslotStrings.timeslotText}</th>
							<th class=\'orddd_holiday_list\'>${timeslotStrings.timeslotLockoutText}</th>
							<th class=\'orddd_holiday_list\'>${timeslotStrings.timeslotChargesText}</th>
							<th class=\'orddd_holiday_list\'>${timeslotStrings.timeslotChargesLabelText}</th>
							<th class=\'orddd_holiday_list\'>${timeslotStrings.timeslotActionsText}</th>
						</tr>` );
						var rowCount = 1;
					}

					rowCount = rowCount - 1;
					for( i = 0; i < dd.length; i++ ) {
						if( dd[ i ] == 'all' ) {
							for( k = 0; k < hidden_arr.length; k++ ) {
								if( hidden_arr[ k ] != '' ) {
									var date_hidden_arr = hidden_arr[ k ].split( ':' );
									if( date_hidden_arr.length == '7' ) {
										var time_slot_str = date_hidden_arr[ 2 ] + ':' + date_hidden_arr[ 3 ];
									} else {
										var time_slot_str = date_hidden_arr[ 2 ] + ':' + date_hidden_arr[ 3 ] + ':' + date_hidden_arr[ 4 ];
									}    
									if( time_slot == time_slot_str && date_hidden_arr[ 1 ] == 'all' ) {
										timeslot_present = 'yes';
									}
								}
							}

							if( timeslot_present == 'no' ) {
								if( jQuery( '#orddd_shipping_based_time_slot_additional_charges' ).val() != '' ) {

									//Add all the individual enabled weekdays if 'all' is selected.
									jQuery.each( orddd_weekdays_js, function(key, value ) {
										var isChecked = jQuery( '#'+key).is(':checked');
										
										if( isChecked == true ) {
											jQuery( '#orddd_time_slot_list tr:last').after(`
											<tr class=\'orddd_common_list_tr\' id=\'orddd_delete_time_slot_${rowCount}\'>
												<td class=\'orddd_holiday_list\' id=\'orddd_delivery_day\'>${value}</td>
												<td class=\'orddd_holiday_list\' id=\'orddd_time_slot\'>${time_slot}</td>
												<td class=\'orddd_holiday_list\' id=\'orddd_time_slot_lockout\'>${lockout}</td>
												<td class=\'orddd_holiday_list\' id=\'orddd_time_slot_additional_charges\' >${ charges}</td>
												<td class=\'orddd_holiday_list\' id=\'orddd_time_slot_additional_charges_label\'>${charges_label}</td>
												<td class=\'orddd_holiday_list\'>
													<a href=\'javascript:void(0)\' class=\'edit_time_slot\' id=\'orddd_edit_time_slot_${rowCount}\'>Edit</a> | 
													<a href=\'javascript:void(0)\' class=\'confirmation_time_slot\' id=\'orddd_delete_time_slot_${rowCount}\'>Delete</a></td>
											</tr>` );
											rowCount = rowCount + 1;
										}
									}); 
									
								} else {
									jQuery.each( orddd_weekdays_js, function(key, value ) {
										var isChecked = jQuery( '#'+key).is(':checked');

										if( isChecked == true ) {
											jQuery( '#orddd_time_slot_list tr:last').after(`
											<tr class=\'orddd_common_list_tr\' id=\'orddd_delete_time_slot_${rowCount}'\'><td class=\'orddd_holiday_list\' id=\'orddd_delivery_day\'>${value}</td>
												<td class=\'orddd_holiday_list\' id=\'orddd_time_slot\'>${time_slot}</td>
												<td class=\'orddd_holiday_list\' id=\'orddd_time_slot_lockout\'>${lockout}</td>
												<td class=\'orddd_holiday_list\' id=\'orddd_time_slot_additional_charges\' ></td>
												<td class=\'orddd_holiday_list\' id=\'orddd_time_slot_additional_charges_label\' >${charges_label}</td>
												<td class=\'orddd_holiday_list\'>
												<a href=\'javascript:void(0)\' class=\'edit_time_slot\' id=\'orddd_edit_time_slot_${rowCount}\'>Edit</a> |
												<a href=\'javascript:void(0)\' class=\'confirmation_time_slot\' id=\'orddd_delete_time_slot_${rowCount}\'>Delete</a></td>
											</tr>` );
											rowCount = rowCount + 1;
										}
									});
									
								}
							}
						} else {    
							var weekday_value = dd[ i ];
							for( j = 0; j < hidden_arr.length; j++ ) {
								if( hidden_arr[ j ] != '' ) {
									var date_hidden_arr = hidden_arr[ j ].split( ':' );
									if( date_hidden_arr.length == '7' ) {
										var time_slot_str = date_hidden_arr[ 2 ] + ':' + date_hidden_arr[ 3 ];
									} else {
										var time_slot_str = date_hidden_arr[ 2 ] + ':' + date_hidden_arr[ 3 ] + ':' + date_hidden_arr[ 4 ];
									}    
									
									if( time_slot == time_slot_str && date_hidden_arr[ 1 ].indexOf( weekday_value ) != -1 ) { 
										timeslot_present = 'yes';
									}
								}
							}	
							
							if( timeslot_present == 'no' ) {
								if( charges != '' ) {
									jQuery( '#orddd_time_slot_list tr:last').after(`
									<tr class=\'orddd_common_list_tr\' id=\'orddd_delete_time_slot_${rowCount}\'>
										<td class=\'orddd_holiday_list\' id=\'orddd_delivery_day\'>${orddd_weekdays_js[ weekday_value ]}</td>
										<td class=\'orddd_holiday_list\' id=\'orddd_time_slot\'>${time_slot}</td>
										<td class=\'orddd_holiday_list\' id=\'orddd_time_slot_lockout\'>${lockout}</td>
										<td class=\'orddd_holiday_list\' id=\'orddd_time_slot_additional_charges\' >${timeslotStrings.currency}${charges}</td>
										<td class=\'orddd_holiday_list\' id=\'orddd_time_slot_additional_charges_label\' >${charges_label}</td>
										<td class=\'orddd_holiday_list\'>
											<a href=\'javascript:void(0)\' class=\'edit_time_slot\' id=\'orddd_edit_time_slot_${rowCount}\'>Edit</a> |
											<a href=\'javascript:void(0)\' class=\'confirmation_time_slot\' id=\'orddd_delete_time_slot_${rowCount}\'>Delete</a>
										</td>
									</tr>` );
								} else {
									jQuery( '#orddd_time_slot_list tr:last').after(`
									<tr class=\'orddd_common_list_tr\' id=\'orddd_delete_time_slot_${rowCount}\'>
										<td class=\'orddd_holiday_list\' id=\'orddd_delivery_day\'>${orddd_weekdays_js[ weekday_value ]}</td>
										<td class=\'orddd_holiday_list\' id=\'orddd_time_slot\'>${time_slot}</td>
										<td class=\'orddd_holiday_list\' id=\'orddd_time_slot_lockout\'>${lockout}</td>
										<td class=\'orddd_holiday_list\' id=\'orddd_time_slot_additional_charges\' ></td>
										<td class=\'orddd_holiday_list\' id=\'orddd_time_slot_additional_charges_label\' > ${charges_label} </td>
										<td class=\'orddd_holiday_list\'>
											<a href=\'javascript:void(0)\' class=\'edit_time_slot\' id=\'orddd_edit_time_slot_${rowCount}\'>Edit</a> |
											<a href=\'javascript:void(0)\' class=\'confirmation_time_slot\' id=\'orddd_delete_time_slot_${rowCount}\'>Delete</a>
										</td>
									</tr>` );
								}
							}
							rowCount = rowCount + 1;
							
						}
					}
					jQuery( '#cdts-days-error-msg' ).hide();
				} else { 
					jQuery( '#orddd_time_slot_list' ).before( '<div id=\'cdts-duration-error-msg\' class=\'error settings-error notice is-dismissible\' style=\' width:50%;position:absolute;margin-left:50px;\'>Please Select Delivery Days/Dates for the Time slot </div>' );
					setTimeout( function() {
						jQuery( '#cdts-duration-error-msg' ).fadeOut();
					}, 3000 );
				}      
			} else if( delivery_days == 'specific_dates' ) {
				var dd = selected_dates;
				if( dd != '' && dd != null ) {
					
					if( rowCount == 0 ) {
						jQuery( '#orddd_time_slot_list' ).append( `
						<tr class=\'orddd_common_list_tr\'>
							<th class=\'orddd_holiday_list\'>${timeslotStrings.timeslotDayText}</th>
							<th class=\'orddd_holiday_list\'>${timeslotStrings.timeslotText}</th>
							<th class=\'orddd_holiday_list\'>${timeslotStrings.timeslotLockoutText}</th>
							<th class=\'orddd_holiday_list\'>${timeslotStrings.timeslotChargesText}</th>
							<th class=\'orddd_holiday_list\'>${timeslotStrings.timeslotChargesLabelText}</th>
							<th class=\'orddd_holiday_list\'>${timeslotStrings.timeslotActionsText}</th>
						</tr>` );
						var rowCount = 1;
					}

					rowCount = rowCount - 1;
					for( i = 0; i < dd.length; i++ ) {
						var split_to_date = dd[ i ].split( '-' );
						split_to_date[0] = split_to_date[0] - 1;
						var date = new Date( split_to_date[2], split_to_date[0], split_to_date[1] );
						if( date.getDate() < 10 ) {
						   var dd_str = '0' + date.getDate();
						} else {
						   var dd_str = date.getDate();
						}
			
						if( ( date.getMonth() + 1 ) < 10 ) {
						   var mm = '0' + ( date.getMonth() + 1 );
						} else {
						   var mm = ( date.getMonth() + 1 );
						}

						var date_str = mm + '-' + dd_str + '-' + date.getFullYear();
						for( k = 0; k < hidden_arr.length; k++ ) {
							if( hidden_arr[ k ] != '' ) {
								var date_hidden_arr = hidden_arr[ k ].split( ':' );
								if( date_hidden_arr.length == '7' ) {
									var time_slot_str = date_hidden_arr[ 2 ] + ':' + date_hidden_arr[ 3 ];
								} else {
									var time_slot_str = date_hidden_arr[ 2 ] + ':' + date_hidden_arr[ 3 ] + ':' + date_hidden_arr[ 4 ];
								}    
								
								if( time_slot == time_slot_str && date_str == date_hidden_arr[ 1 ] ) {
									timeslot_present = 'yes';
								}
							}
						}

						if( timeslot_present == 'no' ) {
							if( charges != '' ) {
								jQuery( '#orddd_time_slot_list tr:last').after(`
								<tr class=\'orddd_common_list_tr\' id=\'orddd_delete_time_slot_${rowCount}\'>
									<td class=\'orddd_holiday_list\' id=\'orddd_delivery_day\'>${date_str}</td>
									<td class=\'orddd_holiday_list\' id=\'orddd_time_slot\'>${time_slot}</td>
									<td class=\'orddd_holiday_list\' id=\'orddd_time_slot_lockout\'>${lockout}</td>
									<td class=\'orddd_holiday_list\' id=\'orddd_time_slot_additional_charges\' >${timeslotStrings.currency}${charges}</td>
									<td class=\'orddd_holiday_list\' id=\'orddd_time_slot_additional_charges_label\' >${charges_label}</td>
									<td class=\'orddd_holiday_list\'>
										<a href=\'javascript:void(0)\' class=\'edit_time_slot\' id=\'orddd_edit_time_slot_${rowCount}\'>Edit</a> |
										<a href=\'javascript:void(0)\' class=\'confirmation_time_slot\' id=\'orddd_delete_time_slot_${rowCount}\'>Delete</a>
									</td>
								</tr>` );
							} else {
								jQuery( '#orddd_time_slot_list tr:last').after(`
								<tr class=\'orddd_common_list_tr\' id=\'orddd_delete_time_slot_${rowCount}\'>
									<td class=\'orddd_holiday_list\' id=\'orddd_delivery_day\'>${date_str}</td>
									<td class=\'orddd_holiday_list\' id=\'orddd_time_slot\'>${time_slot}</td>
									<td class=\'orddd_holiday_list\' id=\'orddd_time_slot_lockout\'>${lockout}</td>
									<td class=\'orddd_holiday_list\' id=\'orddd_time_slot_additional_charges\' ></td>
									<td class=\'orddd_holiday_list\' id=\'orddd_time_slot_additional_charges_label\' >${charges_label}</td>
									<td class=\'orddd_holiday_list\'>
										<a href=\'javascript:void(0)\' class=\'edit_time_slot\' id=\'orddd_edit_time_slot_${rowCount}\'>Edit</a> |
										<a href=\'javascript:void(0)\' class=\'confirmation_time_slot\' id=\'orddd_delete_time_slot_${rowCount} \'>Delete</a>
									</td>
								</tr>` );
							}
						}
						rowCount = rowCount + 1;
					}
					jQuery( '#cdts-dates-error-msg' ).hide();
				} else { 
					jQuery( '#orddd_time_slot_list' ).before( '<div id=\'cdts-dates-error-msg\' class=\'error settings-error notice is-dismissible\' style=\' width:50%;position:absolute;margin-left:50px; \'>Please Select Delivery Days/Dates for the Time slot </div>' );
					setTimeout( function() {
						jQuery( '#cdts-dates-error-msg' ).fadeOut();
					},3000 );
				} 

			} else {
				var dd = [];
			}

			if( dd != '' && dd != null && timeslot_present == 'no' ) {
				var row = jQuery( '#orddd_time_slot_hidden' ).val();

				if( jQuery.inArray( 'all', dd ) != -1 ) {
					jQuery.each( orddd_weekdays_js, function(key, value ) {
						var isChecked = jQuery( '#'+key).is(':checked');
						if( isChecked == true ) {
							row += '{' + delivery_days + ':' + key + ':' + time_slot + ':' + lockout + ':' + charges + ':' + charges_label + '},';
							jQuery( '#orddd_time_slot_hidden' ).val( row );
						}
					});
				}else{
					row += '{' + delivery_days + ':' + dd + ':' + time_slot + ':' + lockout + ':' + charges + ':' + charges_label + '},';

				}
				jQuery( '#orddd_time_slot_hidden' ).val( row );
				
			}
			jQuery( '#orddd_shipping_based_time_from_hours').val('');
			jQuery( `#orddd_shipping_based_time_from_hours_${index + 1}`).val('');
			jQuery( '#orddd_shipping_based_time_to_hours').val('');
			jQuery( `#orddd_shipping_based_time_to_hours_${index + 1}`).val('');

		});
		
		jQuery( '#orddd_shipping_based_time_slot_lockout' ).val( '' );
		jQuery( '#orddd_shipping_based_time_slot_lockout_bulk' ).val( '' );
		jQuery( '#orddd_shipping_based_time_slot_for_weekdays' ).val('').trigger('change');
		jQuery( '#orddd_shipping_based_time_slot_for_weekdays_bulk' ).val('').trigger('change');
		jQuery( '#orddd_shipping_based_select_delivery_dates' ).val('').trigger('change');
		jQuery( '#orddd_shipping_based_select_delivery_dates_bulk' ).val('').trigger('change');
		jQuery( '#orddd_shipping_based_time_slot_additional_charges_label' ).val( '' );
		jQuery( '#orddd_shipping_based_time_slot_additional_charges' ).val( '' ); 
		jQuery( '#orddd_shipping_based_time_slot_additional_charges_bulk' ).val( '' );
		jQuery( '#orddd_shipping_based_time_slot_additional_charges_label_bulk' ).val( '' );
		jQuery( '#orddd_shipping_based_time_slot_duration' ).val( '' );
		jQuery( '#orddd_shipping_based_time_slot_interval' ).val( '' );
		jQuery( '#orddd_shipping_based_time_slot_starts_from' ).val( '' );
		jQuery( '#orddd_shipping_based_time_slot_ends_at' ).val( '' );
	}
}

function orddd_get_separate_values( hidden_arr ) {
	var hidden_arr_updated = [];
	for( i = 0; i < hidden_arr.length; i++ ) {
		if( hidden_arr[ i ] != '' ) {
			var date_hidden_arr = hidden_arr[ i ].split( ':' );
			if( date_hidden_arr.length == '7' ) {
				var time_slot_str = date_hidden_arr[ 2 ] + ':' + date_hidden_arr[ 3 ];
			} else {
				var time_slot_str = date_hidden_arr[ 2 ] + ':' + date_hidden_arr[ 3 ] + ':' + date_hidden_arr[ 4 ];
			}    
			var array = date_hidden_arr[ 1 ].split( ',' );
			if( array.length > 1 ) {
				for( j = 0; j < array.length; j++ ) {
					if( date_hidden_arr.length == '7' ) {
						var str = date_hidden_arr[ 0 ] + ':' + array[ j ] + ':' + time_slot_str + ':' + date_hidden_arr[ 4 ] + ':' + date_hidden_arr[ 5 ] + ':' + date_hidden_arr[ 6 ];
					} else {
						var str = date_hidden_arr[ 0 ] + ':' + array[ j ] + ':' + time_slot_str + ':' + date_hidden_arr[ 5 ] + ':' + date_hidden_arr[ 6 ] + ':' + date_hidden_arr[ 7 ];
					}
					hidden_arr_updated.push( str );
				}
				hidden_arr.splice( i,1 );
				hidden_arr.splice(i, 0, ...hidden_arr_updated);
			}				
		}
	}

	return hidden_arr;
}