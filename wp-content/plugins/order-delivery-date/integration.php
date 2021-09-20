<?php
/**
 * Order Delivery Date Pro for WooCommerce
 *
 * Integration class to make the Order Delivery Date plugin compatible with various third party plugins.
 *
 * @author      Tyche Softwares
 * @package     Order-Delivery-Date-Pro-for-WooCommerce/Integration
 * @since       2.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once dirname( __FILE__ ) . '/orddd-common.php';

/**
 * orddd_integration Class
 *
 * @class orddd_integration
 */
class orddd_integration {

	public static $product_category_enabled = 'on';
	/**
	 * Constructor. Adds various hooks and filters
	 * based on the settings.
	 */
	public function __construct() {
		// Zapier integration
		add_action( 'plugins_loaded', array( 'orddd_integration', 'orddd_plugins_loaded' ) );
		// WooCommerce PDF Invoices & Packing Slips
		if ( get_option( 'orddd_show_fields_in_pdf_invoice_and_packing_slips' ) == 'on' ) {
			if ( version_compare( get_option( 'wpo_wcpdf_version' ), '2.0.0', '>=' ) ) {
				add_action( 'wpo_wcpdf_after_order_details', array( 'orddd_integration', 'orddd_plugins_packing_slip' ), 10, 2 );
			} else {
				add_action( 'wpo_wcpdf_after_order_details', array( 'orddd_integration', 'orddd_plugins_packing_slip' ) );
			}
		}
		// add custom columns headers to csv when Order/Customer CSV Export Plugin is activate
		if ( get_option( 'orddd_show_fields_in_csv_export_check' ) == 'on' ) {
			add_filter( 'wc_customer_order_csv_export_order_headers', array( 'orddd_integration', 'wc_csv_export_modify_column_headers' ) );
			add_filter( 'wc_customer_order_csv_export_order_row', array( 'orddd_integration', 'wc_csv_export_modify_row_data' ), 10, 3 );
		}
		// WooCommerce Print Invoice & Delivery Note
		if ( get_option( 'orddd_show_fields_in_invoice_and_delivery_note' ) == 'on' ) {
			add_filter( 'wcdn_order_info_fields', array( 'orddd_integration', 'orddd_plugin_print_invoice_delivery_note' ), 10, 2 );
		}
		if ( get_option( 'orddd_show_fields_in_cloud_print_orders' ) == 'on' ) {
			add_action( 'woocommerce_cloudprint_internaloutput_footer', array( 'orddd_integration', 'cloud_print_fields' ) );
		}

		// WooCommerce Print Invoice/Packing list plugin
		add_action( 'wc_pip_after_body', array( 'orddd_integration', 'orddd_plugin_woocommerce_pip' ), 10, 4 );

		// WooCommerce PDF Invoices plugin - Free and Premium
		add_action( 'wpi_after_formatted_shipping_address', array( 'orddd_integration', 'orddd_plugin_wpi' ), 10, 1 );

		// WooCommerce Simply Order Export plugin
		add_filter( 'wpg_order_columns', array( 'orddd_integration', 'wpg_add_columns' ) );
		add_filter( 'wc_settings_tab_order_export', array( 'orddd_integration', 'wpg_add_fields' ) );
		add_action( 'wpg_add_values_to_csv', array( 'orddd_integration', 'csv_write' ), 10, 6 );

		add_action( 'woocommerce_api_order_response', array( 'orddd_integration', 'woocommerce_api_create_order' ), 10, 4 );
		add_action( 'woocommerce_rest_prepare_product_cat', array( 'orddd_integration', 'woocommerce_api_create_category' ), 25, 3 );


		add_filter( 'woocommerce_form_field_select', array( $this, 'orddd_location_field' ), 20, 4 );

		 // WooCommerce One Page Checkout.
		 add_action( 'wcopc_product_selection_fields_after', array( &$this, 'orddd_check_product_enabled' ), 10, 2 );
	}

	/**
	 * Adds the Delivery Details to the WooCommerce API
	 *
	 * @param array    $order_data - Order Data
	 * @param WC_Order $order - Order Details
	 * @param array    $fields - Request fields.
	 * @param object   $server
	 * @return array $order_data - Updated Order Data with Delivery details.
	 *
	 * @hook woocommerce_api_order_response
	 * @since 5.8
	 */
	public static function woocommerce_api_create_order( $order_data, $order, $fields, $server ) {

		if ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', '>=' ) ) {
			$order_id = $order->get_id();
		} else {
			$order_id = $order->id;
		}

		$locations_label = get_option( 'orddd_location_field_label' );
		$address         = get_post_meta( $order_id, $locations_label, true );
		if ( $address != '' ) {
			$order_data[ $locations_label ] = $address;
		}

		$delivery_date_formatted = orddd_common::orddd_get_order_delivery_date( $order_id );
		if ( $delivery_date_formatted != '' ) {
			$order_data[ get_option( 'orddd_delivery_date_field_label' ) ] = $delivery_date_formatted;
		}

		if ( get_post_meta( $order_id, get_option( 'orddd_delivery_timeslot_field_label' ), true ) ) {
			$order_data[ get_option( 'orddd_delivery_timeslot_field_label' ) ] = get_post_meta( $order_id, get_option( 'orddd_delivery_timeslot_field_label' ), true );
		}

		if ( get_post_meta( $order_id, '_orddd_timestamp', true ) ) {
			$order_data['_orddd_timestamp'] = get_post_meta( $order_id, '_orddd_timestamp', true );
		}
		return $order_data;
	}

	/**
	 * Enable the delivery date in category when created through API.
	 *
	 * @param Object $response Reponse object.
	 * @param Object $item The item data.
	 * @param array  $request Request data.
	 * @return void
	 */
	public static function woocommerce_api_create_category( $response, $item, $request ) {
		$delivery_enabled_for_category = get_term_meta( $item->term_id, 'orddd_delivery_date_for_product_category', true );

		if ( '' === $delivery_enabled_for_category || ! isset( $delivery_enabled_for_category ) ) {
			update_term_meta( $item->term_id, 'orddd_delivery_date_for_product_category', 'on' );
		}

		return $response;
	}

	/**
	 * Executed during the 'plugins_loaded' WordPress hook.
	 *
	 * - Load Supported Zapier Triggers
	 *
	 * @hook plugins_loaded
	 * @since 2.7
	 */
	public static function orddd_plugins_loaded() {
		if ( class_exists( 'WC_Zapier' ) ) {
			$trigger_keys = array(
				'wc.new_order', // New Order
				'wc.order_status_change', // New Order Status Change
			);
			foreach ( $trigger_keys as $trigger_key ) {
				add_filter( "wc_zapier_data_{$trigger_key}", array( 'orddd_integration', 'orddd_order_data_override' ), 10, 4 );
			}
		}
	}

	/**
	 * When sending WooCommerce Order data to Zapier, also send order delivery date field
	 * that have been created by the Order Delivery Date plugin.
	 *
	 * @param array             $order_data - Order data that will be overridden.
	 * @param WC_Zapier_Trigger $trigger - Trigger that initiated the data send.
	 * @return array $order_data - Updated Order data with delivery details.
	 *
	 * @since 2.7
	 */
	public static function orddd_order_data_override( $order_data, WC_Zapier_Trigger $trigger ) {
		$field_name      = get_option( 'orddd_delivery_date_field_label' );
		$time_slot       = get_option( 'orddd_delivery_timeslot_field_label' );
		$locations_label = get_option( 'orddd_location_field_label' );

		if ( $trigger->is_sample() ) {
			// We're sending sample data.
			// Send the label of the custom checkout field as the field's value.
			$order_data[ $field_name ]      = $field_name;
			$order_data[ $time_slot ]       = $time_slot;
			$order_data[ $locations_label ] = $locations_label;
		} else {
			// We're sending real data.
			// Send the saved value of this checkout field.
			// If the order doesn't contain this custom field, an empty string will be used as the value.
			$order_data[ $field_name ]      = get_post_meta( $order_data['id'], $field_name, true );
			$order_data[ $time_slot ]       = get_post_meta( $order_data['id'], $time_slot, true );
			$order_data[ $locations_label ] = get_post_meta( $order_data['id'], $locations_label, true );
		}
		return $order_data;
	}

	/**
	 * Adds delivery date and time selected for an order in the PDF invoices
	 * and Packing slips from WooCommerce PDF Invoices & Packing Slips plugin.
	 *
	 * @param string $template_type - Template Type for the Invoice
	 * @param array  $order - Order data
	 *
	 * @hook wpo_wcpdf_after_order_details
	 * @since 2.7
	 */
	public static function orddd_plugins_packing_slip( $template_type = '', $order = array() ) {
		global $orddd_date_formats;
		if ( version_compare( get_option( 'wpo_wcpdf_version' ), '2.0.0', '>=' ) ) {
			$order_id = ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', '>=' ) ) ? $order->get_id() : $order->id;
		} else {
			global $wpo_wcpdf;
			$order_export = $wpo_wcpdf->export;
			$order_obj    = $order_export->order;
			$order_id     = $order_obj->id;
		}

		$locations_label = get_option( 'orddd_location_field_label' );
		$address         = get_post_meta( $order_id, $locations_label, true );

		if ( $address != '' ) {
			echo '<p><strong>' . __( $locations_label, 'order-delivery-date' ) . ': </strong>' . $address;
		}

		$delivery_date_formatted = orddd_common::orddd_get_order_delivery_date( $order_id );
		if ( $delivery_date_formatted != '' ) {
			echo '<p><strong>' . __( get_option( 'orddd_delivery_date_field_label' ), 'order-delivery-date' ) . ': </strong>' . $delivery_date_formatted;
		}

		$order_page_time_slot = orddd_common::orddd_get_order_timeslot( $order_id );
		if ( $order_page_time_slot != '' && $order_page_time_slot != '' ) {
			echo '<p><strong>' . __( get_option( 'orddd_delivery_timeslot_field_label' ), 'order-delivery-date' ) . ': </strong>' . $order_page_time_slot;
		}
	}

	/**
	 * Adds delivery date and time column headings to CSV when order
	 * is exported from Order/Customer CSV Export Plugin.
	 *
	 * @param array $column_headers - List of Column Names
	 * @return array $column_headers - The list of column names
	 *
	 * @hook wc_customer_order_csv_export_order_headers
	 * @since 2.7
	 */
	public static function wc_csv_export_modify_column_headers( $column_headers ) {
		$new_headers = array(
			'column_1' => __( get_option( 'orddd_location_field_label' ), 'order-delivery-date' ),
			'column_2' => __( get_option( 'orddd_delivery_date_field_label' ), 'order-delivery-date' ),
			'column_3' => __( get_option( 'orddd_delivery_timeslot_field_label' ), 'order-delivery-date' ),
		);
		return array_merge( $column_headers, $new_headers );
	}

	/**
	 * Adds delivery date and time column content to CSV when order
	 * is exported from Order/Customer CSV Export Plugin.
	 *
	 * @param array  $order_data -
	 * @param object $order - Order Details
	 * @param object $csv_generator
	 * @return array $new_order_data - Delivery data
	 *
	 * @hook wc_customer_order_csv_export_order_row
	 * @since 2.7
	 */
	public static function wc_csv_export_modify_row_data( $order_data, $order, $csv_generator ) {
		$new_order_data = array();

		if ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', '>=' ) ) {
			$order_id = $order->get_id();
		} else {
			$order_id = $order->id;
		}

		$delivery_date_formatted = orddd_common::orddd_get_order_delivery_date( $order_id );

		$locations_label = get_option( 'orddd_location_field_label' );
		$address         = get_post_meta( $order_id, $locations_label, true );

		$custom_data = array(
			'column_1' => $address,
			'column_2' => $delivery_date_formatted,
			'column_3' => get_post_meta( $order_id, get_option( 'orddd_delivery_timeslot_field_label' ), true ),
		);

		$new_order_data   = array();
		$one_row_per_item = false;

		if ( version_compare( wc_customer_order_csv_export()->get_version(), '4.0.0', '<' ) ) {
			// pre 4.0 compatibility
			$one_row_per_item = ( 'default_one_row_per_item' === $csv_generator->order_format || 'legacy_one_row_per_item' === $csv_generator->order_format );
		} elseif ( isset( $csv_generator->format_definition ) ) {
			// post 4.0 (requires 4.0.3+)
			$one_row_per_item = 'item' === $csv_generator->format_definition['row_type'];
		}
		if ( $one_row_per_item ) {
			foreach ( $order_data as $data ) {
				$new_order_data[] = array_merge( (array) $data, $custom_data );
			}
		} else {
			$new_order_data = array_merge( $order_data, $custom_data );
		}

		return $new_order_data;
	}

	/**
	 * Adds delivery date and time selected for an order in the invoices
	 * and delivery notes from WooCommerce Print Invoice & Delivery Note plugin.
	 *
	 * @param array  $fields - List of fields
	 * @param object $order - Order data
	 * @return array $fields - with the delivery data added
	 *
	 * @hook wcdn_order_info_fields
	 * @since 2.7
	 */
	public static function orddd_plugin_print_invoice_delivery_note( $fields, $order ) {
		$new_fields = array();
		if ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', '>=' ) ) {
			$order_id = $order->get_id();
		} else {
			$order_id = $order->id;
		}

		$locations_label = get_option( 'orddd_location_field_label' );
		$address         = get_post_meta( $order_id, $locations_label, true );
		if ( $address != '' ) {
			$new_fields[ $locations_label ] = array(
				'label' => __( $locations_label, 'order-delivery-date' ),
				'value' => $address,
			);
		}

		$delivery_date_formatted = orddd_common::orddd_get_order_delivery_date( $order_id );
		$delivery_date_label 	 = orddd_custom_delivery_functions::orddd_fetch_delivery_date_field_label( $order_id );
		$time_slot_label 		 = orddd_custom_delivery_functions::orddd_fetch_time_slot_field_label( $order_id );

		if ( $delivery_date_formatted != '' ) {
			$new_fields[ get_option( 'orddd_delivery_date_field_label' ) ] = array(
				'label' => __( $delivery_date_label, 'order-delivery-date' ),
				'value' => $delivery_date_formatted,
			);
		}

		if ( get_post_meta( $order_id, $time_slot_label, true ) ) {
			$new_fields[ $time_slot_label ] = array(
				'label' => __( $time_slot_label, 'order-delivery-date' ),
				'value' => get_post_meta( $order_id, $time_slot_label, true ),
			);
		}
		return array_merge( $fields, $new_fields );
	}

	/**
	 * Adds delivery date and time selected for an order
	 * in the prints from WooCommerce Print Orders plugin.
	 *
	 * @param object $order - Order Details
	 *
	 * @hook woocommerce_cloudprint_internaloutput_footer
	 * @since 2.7
	 */
	public static function cloud_print_fields( $order ) {
		$field_date_label = get_option( 'orddd_delivery_date_field_label' );
		if ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', '>=' ) ) {
			$order_id = $order->get_id();
		} else {
			$order_id = $order->id;
		}

		$locations_label = get_option( 'orddd_location_field_label' );
		$address         = get_post_meta( $order_id, $locations_label, true );
		if ( $address != '' ) {
			echo '<p><strong>' . __( $locations_label, 'order-delivery-date' ) . ': </strong>' . $address;
		}

		$delivery_date_formatted = orddd_common::orddd_get_order_delivery_date( $order_id );
		echo '<p><strong>' . __( $field_date_label, 'order-delivery-date' ) . ': </strong>' . $delivery_date_formatted;

		$order_page_time_slot = orddd_common::orddd_get_order_timeslot( $order_id );
		if ( $order_page_time_slot != '' && $order_page_time_slot != '' ) {
			echo '<p><strong>' . __( get_option( 'orddd_delivery_timeslot_field_label' ), 'order-delivery-date' ) . ': </strong>' . $order_page_time_slot;
		}
	}

	/**
	 * Adds delivery date and time selected for an order in the invoices
	 * and delivery notes from WooCommerce Print Invoice/Packing list plugin.
	 *
	 * @param string   $type
	 * @param string   $action
	 * @param string   $document
	 * @param resource $order Order Details
	 *
	 * @hook wc_pip_after_body
	 * @todo Need to check the type of the parameters.
	 *
	 * @since 2.7
	 */
	public static function orddd_plugin_woocommerce_pip( $type, $action, $document, $order ) {
		global $orddd_date_formats;
		$delivery_date = get_option( 'orddd_delivery_date_field_label' );
		$time_slot     = get_option( 'orddd_delivery_timeslot_field_label' );

		if ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', '>=' ) ) {
			$order_id = $order->get_id();
		} else {
			$order_id = $order->id;
		}

		$locations_label = get_option( 'orddd_location_field_label' );
		$address         = get_post_meta( $order_id, $locations_label, true );
		if ( $address != '' ) {
			echo '<p><strong>' . __( $locations_label, 'order-delivery-date' ) . ': </strong>' . $address;
		}

		$delivery_date_formatted = orddd_common::orddd_get_order_delivery_date( $order_id );
		if ( $delivery_date_formatted != '' ) {
			echo '<p><strong>' . __( $delivery_date, 'order-delivery-date' ) . ': </strong>' . $delivery_date_formatted;
		}

		$order_page_time_slot = orddd_common::orddd_get_order_timeslot( $order_id );
		if ( $order_page_time_slot != '' && $order_page_time_slot != '' ) {
			echo '<p><strong>' . __( $time_slot, 'order-delivery-date' ) . ': </strong>' . $order_page_time_slot;
		}
	}

	 /**
	  * Adds delivery date and time selected for an order in the invoices
	  * from WooCommerce PDF Invoices Premium plugin.
	  *
	  * @param resource $order Invoice Details
	  *
	  * @hook wpi_after_formatted_shipping_address
	  *
	  * @since 9.6
	  */
	public static function orddd_plugin_wpi( $invoice ) {
		$delivery_date   = get_option( 'orddd_delivery_date_field_label' );
		$time_slot       = get_option( 'orddd_delivery_timeslot_field_label' );
		$locations_label = get_option( 'orddd_location_field_label' );

		if ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', '>=' ) ) {
			$order_id = $invoice->order->get_id();
		} else {
			$order_id = $invoice->order->id;
		}

		$address = get_post_meta( $order_id, $locations_label, true );
		if ( $address != '' ) {
			echo '<p><strong>' . __( $locations_label, 'order-delivery-date' ) . ': </strong>' . $address;
		}

		$delivery_date_formatted = orddd_common::orddd_get_order_delivery_date( $order_id );
		if ( $delivery_date_formatted != '' ) {
			echo '<p><strong>' . __( $delivery_date, 'order-delivery-date' ) . ': </strong>' . $delivery_date_formatted;
		}

		$order_page_time_slot = orddd_common::orddd_get_order_timeslot( $order_id );
		if ( $order_page_time_slot != '' && $order_page_time_slot != '' ) {
			echo '<p><strong>' . __( $time_slot, 'order-delivery-date' ) . ': </strong>' . $order_page_time_slot;
		}
	}

	/**
	 * Adds Delivery date and/or time column in CSV export file
	 * for WooCommerce Simply Order Export plugin.
	 *
	 * @param array $cols - Column Names
	 * @return array $cols - Updated Column Names
	 *
	 * @hook wpg_order_columns
	 * @since 4.0
	 */
	public static function wpg_add_columns( $cols ) {
		$locations_label     = get_option( 'orddd_location_field_label' );
		$delivery_date_label = get_option( 'orddd_delivery_date_field_label' );
		$time_slot_label     = get_option( 'orddd_delivery_timeslot_field_label' );

		$cols['orddd_pickup_location'] = $locations_label;
		$cols['orddd_delivery_date']   = $delivery_date_label;
		$cols['orddd_time_slot']       = $time_slot_label;
		return $cols;
	}

	/**
	 * Adds Delivery date and/or time field display settings on the
	 * Settings page for WooCommerce Simply Order Export plugin
	 *
	 * @param array $settings - Settings
	 * @param array $settings - Updated with Delivery field settings
	 *
	 * @hook wc_settings_tab_order_export
	 * @since 4.0
	 */
	public static function wpg_add_fields( $settings ) {
		$delivery_date_label = get_option( 'orddd_delivery_date_field_label' );
		$time_slot_label     = get_option( 'orddd_delivery_timeslot_field_label' );
		$locations_label     = get_option( 'orddd_location_field_label' );

		$settings['Pickup Location'] = array(
			'name' => $locations_label,
			'type' => 'checkbox',
			'desc' => __( 'Pickup Location', 'order-delivery-date' ),
			'id'   => 'orddd_pickup_location',
		);

		$settings['Delivery Date'] = array(
			'name' => $delivery_date_label,
			'type' => 'checkbox',
			'desc' => __( 'Delivery Date', 'order-delivery-date' ),
			'id'   => 'orddd_delivery_date',
		);

		$settings['Time Slot'] = array(
			'name' => $time_slot_label,
			'type' => 'checkbox',
			'desc' => __( 'Time Slot', 'order-delivery-date' ),
			'id'   => 'orddd_time_slot',
		);

		return $settings;

	}

	/**
	 * Adds Delivery date and/or time details in CSV export file
	 * for WooCommerce Simply Order Export plugin.
	 *
	 * @hook wpg_add_values_to_csv
	 *
	 * @param array    $csv Values to export to CSV
	 * @param resoruce $od Order Object
	 * @param string   $field Field slug
	 * @param string   $key
	 * @param string   $item_id
	 * @param array    $current_item
	 *
	 * @since 4.0
	 */
	public static function csv_write( &$csv, $od, $field, $key, $item_id, $current_item ) {
		$order_id = $od->id;
		$address  = get_post_meta( $order_id, $locations_label, true );

		$delivery_date_formatted = orddd_common::orddd_get_order_delivery_date( $order_id );
		$order_page_time_slot    = orddd_common::orddd_get_order_timeslot( $order_id );
		switch ( $field ) {
			case 'orddd_delivery_date':
				array_push( $csv, $delivery_date_formatted );
				break;

			case 'orddd_time_slot':
				array_push( $csv, $order_page_time_slot );
				break;

			case 'orddd_pickup_location':
				array_push( $csv, $address );
				break;

			default:
				break;
		}
	}

	/**
	 * Skip the Pickup Locations field to be modified by CHeckout Manager plugin.
	 *
	 * @param string $field Field html.
	 * @param string $key Field ID.
	 * @param array  $args Extra arguments.
	 * @param string $value Field value.
	 * @return string
	 */
	public function orddd_location_field( $field = '', $key, $args, $value ) {
		if ( is_plugin_active( 'woocommerce-checkout-manager/woocommerce-checkout-manager.php' ) || is_plugin_active( 'add-fields-to-checkout-page-woocommerce/checkout-form-editor.php' ) ) {
			if ( 'select' === $args['type'] && ( 'orddd_locations' === $key || 'orddd_time_slot' === $key ) ) {
				$sort            = $args['priority'] ? $args['priority'] : '';
				$field_container = '<p class="form-row %1$s" id="%2$s" data-priority="' . esc_attr( $sort ) . '">%3$s</p>';

				// Custom attribute handling.
				$custom_attributes         = array();
				$args['custom_attributes'] = array_filter( (array) $args['custom_attributes'], 'strlen' );

				if ( $args['maxlength'] ) {
					$args['custom_attributes']['maxlength'] = absint( $args['maxlength'] );
				}

				if ( ! empty( $args['autocomplete'] ) ) {
					$args['custom_attributes']['autocomplete'] = $args['autocomplete'];
				}

				if ( true === $args['autofocus'] ) {
					$args['custom_attributes']['autofocus'] = 'autofocus';
				}

				if ( $args['description'] ) {
					$args['custom_attributes']['aria-describedby'] = $args['id'] . '-description';
				}

				if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
					foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
						$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
					}
				}
				$field = '';

				if ( ! empty( $args['options'] ) ) {
					$field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="select ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ) . '">';
					if ( ! empty( $args['placeholder'] ) ) {
						$field .= '<option value="" disabled="disabled" selected="selected">' . esc_attr( $args['placeholder'] ) . '</option>';
					}
					foreach ( $args['options'] as $option_key => $option_text ) {
						$field .= '<option value="' . esc_attr( $option_key ) . '" ' . selected( $value, $option_text, false ) . '>' . esc_attr( $option_text ) . '</option>';
					}
					$field .= '</select>';
				}

				if ( ! empty( $field ) ) {
					$field_html = '';
					$label_id   = $args['id'];
					if ( $args['required'] ) {
						$args['class'][] = 'validate-required';
						$required        = '&nbsp;<abbr class="required" title="' . esc_attr__( 'required', 'woocommerce' ) . '">*</abbr>';
					} else {
						$required = '&nbsp;<span class="optional">(' . esc_html__( 'optional', 'woocommerce' ) . ')</span>';
					}

					if ( $args['label'] && 'checkbox' !== $args['type'] ) {
						$field_html .= '<label for="' . esc_attr( $label_id ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>';
					}

					$field_html .= '<span class="woocommerce-input-wrapper">' . $field;

					if ( $args['description'] ) {
						$field_html .= '<span class="description" id="' . esc_attr( $args['id'] ) . '-description" aria-hidden="true">' . wp_kses_post( $args['description'] ) . '</span>';
					}

					$field_html .= '</span>';

					$container_class = esc_attr( implode( ' ', $args['class'] ) );
					$container_id    = esc_attr( $args['id'] ) . '_field';
					$field           = sprintf( $field_container, $container_class, $container_id, $field_html );
				}
			}
		}

		return $field;
	}

	/**
	 * Check if any of the products on one page checkout has delivery enabled or not.
	 *
	 * @param string $template Template to use.
	 * @param array  $atts shortcode attributes.
	 * @return void
	 */
	public static function orddd_check_product_enabled( $template, $atts ) {
		$products                       = $atts['product_ids'];
		$products_arr                   = explode( ',', $products );
		self::$product_category_enabled = 'off';
		foreach ( $products_arr as $key => $product_id ) {
			$terms = get_the_terms( $product_id, 'product_cat' );

			$is_enabled = 'no';
			if ( $terms == '' ) {
				if ( has_filter( 'orddd_remove_delivery_date_if_product_category_no' ) ) {
					$is_enabled = apply_filters( 'orddd_remove_delivery_date_if_product_category_no', $is_enabled );
				}
				if ( $is_enabled == 'yes' ) {
					self::$product_category_enabled = 'on';
				}
			} else {
				foreach ( $terms as $term ) {
					$categoryid    = $term->term_id;
					$delivery_date = get_term_meta( $categoryid, 'orddd_delivery_date_for_product_category', true );

					if ( has_filter( 'orddd_remove_delivery_date_if_product_category_no' ) ) {
						$is_enabled = apply_filters( 'orddd_remove_delivery_date_if_product_category_no', $is_enabled );
					}
					if ( $is_enabled == 'yes' ) {
						if ( $delivery_date === 'on' ) {
							self::$product_category_enabled = 'on';
						} else {
							self::$product_category_enabled = 'off';
							break 2;
						}
					} else {
						if ( $delivery_date === 'on' ) {
							self::$product_category_enabled = 'on';
							break 2;
						} else {
							self::$product_category_enabled = 'off';
						}
					}
				}
			}
		}

	}
}
$orddd_integration = new orddd_integration();

