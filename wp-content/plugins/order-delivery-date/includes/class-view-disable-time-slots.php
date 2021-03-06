<?php 

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Display Block a time slot list table in admin.
 *
 * @author Tyche Softwares
 * @package Order-Delivery-Date-Pro-for-WooCommerce/Admin/Settings/General
 * @since 2.8.3
 */

class ORDDD_View_Disable_Time_Slots_Table extends WP_List_Table {

	/**
	 * URL of this page
	 *
	 * @var string
	 * @since 1.4.1
	 */
	public $base_url;
	
	/**
	 * Get things started
	 *
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {

		global $status, $page;
		// Set parent defaults
		parent::__construct( array(
		    'singular' => __( 'block_time_slot', 'order-delivery-date' ), //singular name of the listed records
		    'plural'   => __( 'block_time_slots', 'order-delivery-date' ), //plural name of the listed records
            'ajax'      => false             			// Does this table support ajax?
		) );
		$this->process_bulk_action();
		$this->base_url = admin_url( 'admin.php?page=order_delivery_date&action=general_settings&section=block_time_slot_settings' );
	}
	
	/**
	 * Add delete option in the bulk actions dropdown
	 * 
	 * @since 2.8.3
	 */
	public function get_bulk_actions() {
	    return array(
	        'orddd_delete' => __( 'Unblock', 'order-delivery-date' )
	    );
	}
	
	/**
	 * Add the check box for the items to select 
	 * 
	 * @param string $item 
	 * @return string
	 * @since 2.8.3
	 **/
	function column_cb( $item ){
	    if( isset( $item->disable_dd ) && "" != $item->disable_dd ) {
	        $dd = $item->disable_dd;
	        return sprintf(
	            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
	            'block_time_slot',
	            $dd . ',' . $item->disable_time_slot
	        );
	    }
	}
	
	/**
	 * Prepare items to display in the table
	 * 
	 * @since 2.8.3
	 */
	public function orddd_prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array(); // No hidden columns
		$data     = $this->orddd_disable_time_slot_data();
		$sortable = array();
		$status   = isset( $_GET['status'] ) ? $_GET['status'] : 'any';
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items = $data;
	}
	
	/**
	 * Return columns to add in the table
	 * 
	 * @return array $columns Columns to be displayed in the table
	 * @since 2.8.3
	 */
	public function get_columns() {
		$columns = array(
		    'cb'                 =>  '<input type="checkbox" />',
    		'disable_delivery_days_dates' => __( 'Delivery Days/Dates', 'order-delivery-date' ),
    		'disable_time_slot'           => __( 'Time Slot', 'order-delivery-date' ),
		);
		return apply_filters( 'orddd_disable_time_slot_table_columns', $columns );
	}
	
	/**
	 * Displays the data in the table
	 * 
	 * @return array $return_disable_time_slot All disabled time slots
	 * @since 2.8.3
	 */
	
	public function orddd_disable_time_slot_data() { 
		global $wpdb, $woocommerce, $orddd_weekdays;	
		$existing_timeslots_str = get_option( 'orddd_disable_time_slot_log' );
		$existing_timeslots_arr = $return_disable_time_slot = array();
		if ( $existing_timeslots_str == 'null' || $existing_timeslots_str == '' || $existing_timeslots_str == '{}' || $existing_timeslots_str == '[]' ) {
		    $existing_timeslots_arr = array();
		} else {
		    $existing_timeslots_arr = json_decode( $existing_timeslots_str );
		}
		if ( is_array( $existing_timeslots_arr ) && count( $existing_timeslots_arr ) > 0 ) {
		    if( $existing_timeslots_arr == 'null' ) {
		        $existing_timeslots_arr = array();
		    }
		    $i = 0;
		    foreach ( $existing_timeslots_arr as $k => $v ) {
		        $time_format = get_option( 'orddd_delivery_time_format' );
		        if ( $time_format == '1' ) {
		            $time_format_to_show = 'h:i A';
		        } else {
		            $time_format_to_show = 'H:i';
		        }
		        
                $time_slots = json_decode( $v->ts );
	            foreach( $time_slots as $time_key => $time_value ) {
                    $return_disable_time_slot[ $i ] = new stdClass();
	                if ( isset( $v->dtv ) && $v->dtv == 'dates' ) {
	                    $disable_date = explode( "-", $v->dd );
	                    $delivery_disable_date = date( "m-d-Y", gmmktime( 0, 0, 0, $disable_date[0], $disable_date[1], $disable_date[2] ) );
                        $return_disable_time_slot[ $i ]->disable_delivery_days_dates = $delivery_disable_date;
                        $return_disable_time_slot[ $i ]->disable_dd = $v->dd;
                        $return_disable_time_slot[ $i ]->disable_time_slot = $time_value;
                        $return_disable_time_slot[ $i ]->date_type = $v->dtv;
                    } else {
                        if( isset( $orddd_weekdays[ $v->dd ] ) ) {
                            $return_disable_time_slot[ $i ]->disable_delivery_days_dates = $orddd_weekdays[ $v->dd ];
                        } else if ( $v->dd == 'all' ) {
                            $return_disable_time_slot[ $i ]->disable_delivery_days_dates = "All";
                        } else {
                            $return_disable_time_slot[ $i ]->disable_delivery_days_dates = "";
                        }
                        $return_disable_time_slot[ $i ]->disable_dd = $v->dd;
                        $return_disable_time_slot[ $i ]->disable_time_slot = $time_value;
                        $return_disable_time_slot[ $i ]->date_type = $v->dtv;
                    }
                    $i++;
	            }
            }
        }
		return apply_filters( 'orddd_disable_time_slot_table_data', $return_disable_time_slot );
	}
	
	/**
	 * Add Edit and Delete link in each row of the table data
	 * 
	 * @param resource $shipping_settings
	 * @param string $column_name
	 * @return array 
	 * @since 2.8.3
	 */
	public function column_default( $disable_time_slot_settings, $column_name ) {
	    $value = isset( $disable_time_slot_settings->$column_name ) ? $disable_time_slot_settings->$column_name : '';
	    
		return apply_filters( 'orddd_disable_time_slot_table_column_default', $value, $disable_time_slot_settings, $column_name );
	}	
}
?>