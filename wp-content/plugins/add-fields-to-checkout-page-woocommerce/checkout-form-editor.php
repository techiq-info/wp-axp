<?php
/**
* Plugin Name: WooCommerce Checkout & Account Field Editor
* Description: Customize WooCommerce checkout and my account page (Add, Edit, Delete and re-arrange fields)
* Author:      ThemeLocation
* Version: 	1.2.5
* Author URI:  https://www.themelocation.com
* Plugin URI:  https://wordpress.org/plugins/add-fields-to-checkout-page-woocommerce/
* Text Domain: wcfe
* Domain Path: /languages
* WC requires at least: 3.0.0
* WC tested up to: 3.9.3
*/

// Create a helper function for easy SDK access.
function tl_fields() {
    global $tl_fields;

    if ( ! isset( $tl_fields ) ) {
        // Include Freemius SDK.
        require_once dirname(__FILE__) . '/freemius/start.php';

        $tl_fields = fs_dynamic_init( array(
            'id'                  => '1707',
            'slug'                => 'add-fields-to-checkout-page-woocommerce',
            'type'                => 'plugin',
            'public_key'          => 'pk_3abcc29391266d676ac7996328bce',
            'is_premium'          => false,
            // If your plugin is a serviceware, set this option to false.
            'has_premium_version' => true,
            'has_addons'          => false,
            'has_paid_plans'      => true,
            'menu'                => array(
                'slug'           => 'checkout_form_editor',
                'support'        => false,
                'parent'         => array(
                    'slug' => 'woocommerce',
                ),
            ),
        ) );
    }

    return $tl_fields;
}

// Init Freemius.
tl_fields();

// Signal that SDK was initiated.
do_action( 'tl_fields_loaded' );

if(!defined( 'ABSPATH' )) exit;

if (!function_exists('is_woocommerce_active')){
	function is_woocommerce_active(){
	    $active_plugins = (array) get_option('active_plugins', array());
	    if(is_multisite()){
		   $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
	    }
	    return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins);
	}
}


if(is_woocommerce_active()) {
	load_plugin_textdomain( 'wcfe', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	/**
	 * woocommerce_init_checkout_field_editor function.
	 */
	function wcfe_init_checkout_field_editor_lite() {
		global $supress_field_modification;
		$supress_field_modification = false;
		
		 define('WCFE_VERSION', '1.2');
		!defined('WCFE_URL') && define('WCFE_URL', plugins_url( '/', __FILE__ ));
		!defined('WCFE_ASSETS_URL') && define('WCFE_ASSETS_URL', WCFE_URL . 'assets/');

		if(!class_exists('WC_Checkout_Field_Editor')){
			require_once('classes/class-wc-checkout-field-editor.php');
		}

		if (!class_exists('WC_Checkout_Field_Editor_Export_Handler')){
			require_once('classes/class-wc-checkout-field-editor-export-handler.php');
		}
		new WC_Checkout_Field_Editor_Export_Handler();

		$GLOBALS['WC_Checkout_Field_Editor'] = new WC_Checkout_Field_Editor();
	}
	add_action('init', 'wcfe_init_checkout_field_editor_lite');
	
	function wcfe_is_locale_field( $field_name ){
		if(!empty($field_name) && in_array($field_name, array(
			'billing_address_1', 'billing_address_2', 'billing_state', 'billing_postcode', 'billing_city',
			'shipping_address_1', 'shipping_address_2', 'shipping_state', 'shipping_postcode', 'shipping_city',
		))){
			return true;
		}
		return false;
	}
	 
	function wcfe_woocommerce_version_check( $version = '3.0' ) {
	  	if(function_exists( 'is_woocommerce_active' ) && is_woocommerce_active() ) {
			global $woocommerce;
			if( version_compare( $woocommerce->version, $version, ">=" ) ) {
		  		return true;
			}
	  	}
	  	return false;
	}
	
	function wcfe_enqueue_scripts(){	
		global $wp_scripts;

		if(is_checkout()){
			$in_footer = apply_filters( 'wcfe_enqueue_script_in_footer', true );

			wp_register_script('wcfe-field-editor-script', WCFE_ASSETS_URL.'js/wcfe-checkout-field-editor-frontend.js', 
			array('jquery', 'select2'), WCFE_VERSION, $in_footer);
			
			wp_enqueue_script('wcfe-field-editor-script');	
		}
	}
	add_action('wp_enqueue_scripts', 'wcfe_enqueue_scripts');
	
	
	function wcfe_admin_scripts( $hook ) {


    if ( $hook == 'user-edit.php' ) {
       wp_enqueue_script( 'wc-checkout-editor', plugin_dir_url( __FILE__ ) . 'assets/js/checkout.js', array( 'jquery', 'jquery-ui-datepicker' ), '', true );
    }
}

add_action( 'admin_enqueue_scripts', 'wcfe_admin_scripts', 10, 1 );

	
	/**
	 * Hide Additional Fields title if no fields available.
	 *
	 * @param mixed $old
	 */
	function wcfe_enable_order_notes_field() {
		global $supress_field_modification;

		if($supress_field_modification){
			return $fields;
		}

		$additional_fields = get_option('wc_fields_additional');
		if(is_array($additional_fields)){
			$enabled = 0;
			foreach($additional_fields as $field){
				if($field['enabled']){
					$enabled++;
				}
			}
			return $enabled > 0 ? true : false;
		}
		return true;
	}
	add_filter('woocommerce_enable_order_notes_field', 'wcfe_enable_order_notes_field', 1000);
		
	function wcfe_woo_default_address_fields( $fields ) {
		$sname = apply_filters('wcfe_address_field_override_with', 'billing');
		
		if($sname === 'billing' || $sname === 'shipping'){
			$address_fields = get_option('wc_fields_'.$sname);
			
			if(is_array($address_fields) && !empty($address_fields) && !empty($fields)){
				$override_required = apply_filters( 'wcfe_address_field_override_required', true );
				
				foreach($fields as $name => $field) {
					$fname = $sname.'_'.$name;
					
					if(wcfe_is_locale_field($fname) && $override_required){
						$custom_field = isset($address_fields[$fname]) ? $address_fields[$fname] : false;
						
						if($custom_field && !( isset($custom_field['enabled']) && $custom_field['enabled'] == false )){
							$fields[$name]['required'] = isset($custom_field['required']) && $custom_field['required'] ? true : false;
						}
					}
				}
			}
		}
		
		return $fields;
	}	
	add_filter('woocommerce_default_address_fields' , 'wcfe_woo_default_address_fields' );
	
	function wcfe_prepare_country_locale($fields) {
		if(is_array($fields)){
			foreach($fields as $key => $props){
				$override_ph = apply_filters('wcfe_address_field_override_placeholder', true);
				$override_label = apply_filters('wcfe_address_field_override_label', true);
				$override_required = apply_filters('wcfe_address_field_override_required', false);
				$override_priority = apply_filters('wcfe_address_field_override_priority', true);
				
				if($override_ph && isset($props['placeholder'])){
					unset($fields[$key]['placeholder']);
				}
				if($override_label && isset($props['label'])){
					unset($fields[$key]['label']);
				}

				if($override_required && isset($props['required'])){
					$fkey = $sname.'_'.$key;
					if(is_array($address_fields) && isset($address_fields[$fkey])){
						$cf_props = $address_fields[$fkey];
						if(is_array($cf_props) && isset($cf_props['required'])){
							$fields[$key]['required'] = $cf_props['required'] ? true : false;
						}
					}
				}
				
				if($override_priority && isset($props['priority'])){
					unset($fields[$key]['priority']);
				}
			}
		}
		return $fields;
	} 
	add_filter('woocommerce_get_country_locale_default', 'wcfe_prepare_country_locale');
	add_filter('woocommerce_get_country_locale_base', 'wcfe_prepare_country_locale');
	
	function wcfe_woo_get_country_locale($locale) {
		if(is_array($locale)){
			foreach($locale as $country => $fields){
				$locale[$country] = wcfe_prepare_country_locale($fields);
			}
		}
		return $locale;
	}
	add_filter('woocommerce_get_country_locale', 'wcfe_woo_get_country_locale');
	
	
	/**
	 * wc_checkout_fields_modify_billing_fields function.
	 *
	 * @param mixed $fields
	 */
	function wcfe_billing_fields_lite($fields, $country){
		global $supress_field_modification;

		if($supress_field_modification){
			return $fields;
		}
		if(is_wc_endpoint_url('edit-address')){
			return $fields;
		} else{
			
			return wcfe_prepare_address_fields(get_option('wc_fields_billing'), $fields, 'billing', $country);
			
		}
	}
	add_filter('woocommerce_billing_fields', 'wcfe_billing_fields_lite', 1000, 2);
	

	/**
	 * wc_checkout_fields_modify_shipping_fields function.
	 *
	 * @param mixed $old
	 */
	function wcfe_shipping_fields_lite($fields, $country){
		global $supress_field_modification;

		if ($supress_field_modification){
			return $fields;
		}
		if(is_wc_endpoint_url('edit-address')){
			return $fields;
		} else {
			return wcfe_prepare_address_fields(get_option('wc_fields_shipping'), $fields, 'shipping', $country);
		}
	}
	add_filter('woocommerce_shipping_fields', 'wcfe_shipping_fields_lite', 1000, 2);

	/**
	 * wc_checkout_fields_modify_shipping_fields function.
	 *
	 * @param mixed $old
	 */
	function wcfe_checkout_fields_lite( $fields ) {
		
		global $supress_field_modification;

		if($supress_field_modification){
			return $fields;
		}

		if($additional_fields = get_option('wc_fields_additional')){
			if( isset($fields['order']) && is_array($fields['order']) ){
				$fields['order'] = $additional_fields + $fields['order'];
			}

			// check if order_comments is enabled/disabled
			if(isset($additional_fields) && !$additional_fields['order_comments']['enabled']){
				unset($fields['order']['order_comments']);
			}
		}
				
		if(isset($fields['order']) && is_array($fields['order'])){
			$fields['order'] = wcfe_prepare_checkout_fields_lite($fields['order'], false);
		}
		
		return $fields;
	}
	add_filter('woocommerce_checkout_fields', 'wcfe_checkout_fields_lite', apply_filters('wcfe_checkout_fields_priority', 1000));
	
	/**
	 *
	 */
	function wcfe_prepare_address_fields($fieldset, $original_fieldset, $sname, $country){
		//PHP 8.0 Compatibility
		$original_fieldset = $original_fieldset ? $original_fieldset : false;
		$sname = $sname ? $sname : 'billing';


		if(is_array($fieldset) && !empty($fieldset)) {
			$locale = WC()->countries->get_country_locale();
			if(isset($locale[ $country ]) && is_array($locale[ $country ])) {
				foreach($locale[ $country ] as $key => $value){
					if(is_array($value) && isset($fieldset[$sname.'_'.$key])){
						if(isset($value['required'])){
							$fieldset[$sname.'_'.$key]['required'] = $value['required'];
						}
					}
				}
			}
		
			$fieldset = wcfe_prepare_checkout_fields_lite($fieldset, $original_fieldset, $sname);
			return $fieldset;
		} else {
			return $original_fieldset;
		}
	}
	

	/**
	 * checkout_fields_modify_fields function.
	 *
	 * @param mixed $data
	 * @param mixed $old
	 */
	 function wcfe_prepare_checkout_fields_lite($fields, $original_fields, $sname = "") {
		if(is_array($fields) && !empty($fields)) {
			foreach($fields as $name => $field) {
				if(isset($field['enabled']) && $field['enabled'] == false ) {
					unset($fields[$name]);
				}else{
					$new_field = false;

					$allow_override = apply_filters('wcfe_allow_default_field_override_'.$name, false);

					if( $original_fields && isset($original_fields[$name]) && !$allow_override ) {
						$new_field = $original_fields[$name];
						
						$new_field['label'] = isset($field['label']) ? $field['label'] : '';
						$new_field['placeholder'] = isset($field['placeholder']) ? $field['placeholder'] : '';
						
						$new_field['class'] = isset($field['class']) && is_array($field['class']) ? $field['class'] : array();
						$new_field['label_class'] = isset($field['label_class']) && is_array($field['label_class']) ? $field['label_class'] : array();
						$new_field['validate'] = isset($field['validate']) && is_array($field['validate']) ? $field['validate'] : array();
						
						$new_field['required'] = isset($field['required']) ? $field['required'] : 0;
						$new_field['clear'] = isset($field['clear']) ? $field['clear'] : 0;
					}else{
						$new_field = $field;
					}
					
					if(isset($new_field['type']) && $new_field['type'] === 'select'){
						if(apply_filters('wcfe_enable_select2_for_select_fields', true)){
							$new_field['input_class'][] = 'wcfe-enhanced-select';
						}
					}
										
					$new_field['order'] = isset($field['order']) && is_numeric($field['order']) ? $field['order'] : 0;
					if(isset($new_field['order']) && is_numeric($new_field['order'])){
						$new_field['priority'] = $new_field['order'];
					}
					
					if(isset($new_field['label'])){
						$new_field['label'] = __($new_field['label'], 'woocommerce');
					}
					if(isset($new_field['placeholder'])){
						$new_field['placeholder'] = __($new_field['placeholder'], 'woocommerce');
					}
					
					$fields[$name] = $new_field;
				}
			}								
			return $fields;
		}else {
			return $original_fields;
		}
	}
	
	/*****************************************
	 ----- Display Field Values - START ------
	 *****************************************/
	/**
	 * Display custom fields in emails
	 *
	 * @param array $keys
	 * @return array
	 */
	function wcfe_display_custom_fields_in_emails_lite($order, $sent_to_admin, $plain_text){
		$fields_html = '';
		
		if(get_option( 'wcfe_account_sync_fields') && get_option( 'wcfe_account_sync_fields') == "on"){
				$fields = array_merge(WC_Checkout_Field_Editor::get_fields('account'), WC_Checkout_Field_Editor::get_fields('billing'), WC_Checkout_Field_Editor::get_fields('shipping'), WC_Checkout_Field_Editor::get_fields('additional'));
			} else {
				$fields = array_merge(WC_Checkout_Field_Editor::get_fields('billing'), WC_Checkout_Field_Editor::get_fields('shipping'), WC_Checkout_Field_Editor::get_fields('additional'));
			}
			
		if($plain_text === false){
			$fields_html .=  '<h2>'.esc_html('Custom Checkout Fields','wcfe').'</h2>';
			$fields_html .= '<ul>';
		}
		
		// Loop through all custom fields to see if it should be added
		foreach( $fields as $key => $options ) {
			if(isset($options['show_in_email']) && $options['show_in_email']){
				$value = '';
				if(wcfe_woo_version_check()){
				if($options['type'] == 'select'){
						$value = get_post_meta( $order->get_id(), $key, true );
						if(is_array($value)){
							$value = implode(",",$value);
						} else {
							$value = get_post_meta( $order->get_id(), $key, true );
						}
					} else{
						$value = get_post_meta( $order->get_id(), $key, true );
					}
				} else {
					if($options['type'] == 'select'){
						$value = get_post_meta( $order->id, $key, true );
						if(is_array($value)){
							$value = implode(",",$value);
						} else {
							$value = get_post_meta( $order->id, $key, true );
						}
						
					} else{
						$value = get_post_meta( $order->id, $key, true );
					}
				}
							
				if(!empty($value)){
					$label = isset($options['label']) && $options['label'] ? $options['label'] : $key;
					$label = esc_attr($label);
					if($plain_text === false){
						$fields_html .= '<li><strong>'.$label.': </strong>'.$value.'</li>';
					} else{
						$fields_html .= $label .':'.$value;
					}
				}
			}
		}

		if($plain_text === false){
			$fields_html .= '</ul>';
		}
					
		echo $fields_html;
	}	
	//add_filter('woocommerce_email_order_meta_fields', 'wcfe_display_custom_fields_in_emails_lite', 10, 3);
	add_action( 'woocommerce_email_order_meta', 'wcfe_display_custom_fields_in_emails_lite', 10, 3 );
	/**
	 * Display custom checkout fields on view order pages
	 *
	 * @param  object $order
	 */
	function wcfe_order_details_after_customer_details_lite($order){
		
		if( wcfe_woocommerce_version_check() ){
			$order_id = $order->get_id();	
		}else{
			$order_id = $order->id;
		}
		
		$fields = array();		
		if(!wc_ship_to_billing_address_only() && $order->needs_shipping_address()){
			
			if(get_option( 'wcfe_account_sync_fields') && get_option( 'wcfe_account_sync_fields') == "on"){
			$fields = array_merge(WC_Checkout_Field_Editor::get_fields('account'), WC_Checkout_Field_Editor::get_fields('billing'), WC_Checkout_Field_Editor::get_fields('shipping'), WC_Checkout_Field_Editor::get_fields('additional'));
			}
			else{
				$fields = array_merge(WC_Checkout_Field_Editor::get_fields('billing'), WC_Checkout_Field_Editor::get_fields('shipping'), WC_Checkout_Field_Editor::get_fields('additional'));
			}
		}else{
			if(get_option( 'wcfe_account_sync_fields') && get_option( 'wcfe_account_sync_fields') == "on") {
				
				$fields = array_merge(WC_Checkout_Field_Editor::get_fields('account'), WC_Checkout_Field_Editor::get_fields('billing'), WC_Checkout_Field_Editor::get_fields('additional'));
			} else {
				$fields = array_merge(WC_Checkout_Field_Editor::get_fields('billing'), WC_Checkout_Field_Editor::get_fields('additional'));
			}
			
		}
		
		if(is_array($fields) && !empty($fields)){
			$fields_html = '';
			// Loop through all custom fields to see if it should be added
			foreach($fields as $name => $options){
				
				$enabled = (isset($options['enabled']) && $options['enabled'] == false) ? false : true;
				$is_custom_field = (isset($options['custom']) && $options['custom'] == true) ? true : false;
			     
				if(isset($options['show_in_order']) && $options['show_in_order'] && $enabled && $is_custom_field){
					if($options['type'] == 'select'){
						$value = get_post_meta($order_id, $name, true);
						if(is_array($value)){
							$value = implode(",",$value);
						}
						else{
							$value = get_post_meta($order_id, $name, true);
						}
					} else{
						$value = get_post_meta($order_id, $name, true);
					}
					
					if(!empty($value)){
						$label = isset($options['label']) && !empty($options['label']) ? __( $options['label'], 'wcfe' ) : $name;
						
						if(is_account_page()){
							if(apply_filters( 'wcfe_view_order_customer_details_table_view', true )){
								
								$fields_html .= '<tr><th>'. esc_attr($label) .':</th><td>'. wptexturize($value) .'</td></tr>';
							}else{
								
								$fields_html .= '<br/><dt>'. esc_attr($label) .':</dt><dd>'. wptexturize($value) .'</dd>';
							}
						}else{
							
							if(apply_filters( 'wcfe_thankyou_customer_details_table_view', true )){
								$fields_html .= '<tr><th>'. esc_attr($label) .':</th><td>'. wptexturize($value) .'</td></tr>';
							} else{
								$fields_html .= '<br/><dt>'. esc_attr($label) .':</dt><dd>'. wptexturize($value) .'</dd>';
							}
						}
					}
				}
			}
			
			if($fields_html){
				do_action( 'wcfe_order_details_before_custom_fields_table', $order ); 
				?>
				<h2 class="woocommerce-order-details__title"><?php esc_html_e('Checkout Fields','wcfe'); ?></h2>
				<table class="woocommerce-table woocommerce-table--custom-fields shop_table custom-fields">
					<?php
						echo $fields_html;
					?>
				</table>
				<?php
				do_action( 'wcfe_order_details_after_custom_fields_table', $order ); 
			}
		}
	}
	
	/**
	 * Register meta box(es).
	 */
	function wcfe_register_order_meta_boxes() {
		add_meta_box( 'wcfe-custom-order-box', __( 'Custom Checkout Fields', 'wcfe' ), 'wcfe_orderbox_display_callback', 'shop_order', 'normal', 'high' );
	}
	add_action( 'add_meta_boxes', 'wcfe_register_order_meta_boxes' );
	 
	/**
	 * Meta box display callback.
	 *
	 * @param WP_Post $post Current post object.
	 */
	function wcfe_orderbox_display_callback( $post ) {
		
		// Display code/markup goes here. Don't forget to include nonces!
		 $order = new WC_Order( $post->ID );
		
		if(wcfe_woocommerce_version_check()){
			$order_id = $order->get_id();	
		}else{
			$order_id = $order->id;
		}
		
		
		$fields = array();		
		if(!wc_ship_to_billing_address_only() && $order->needs_shipping_address()){
			
			if(get_option( 'wcfe_account_sync_fields') && get_option( 'wcfe_account_sync_fields') == "on"){
			$fields = array_merge(WC_Checkout_Field_Editor::get_fields('account'), WC_Checkout_Field_Editor::get_fields('billing'), WC_Checkout_Field_Editor::get_fields('shipping'), WC_Checkout_Field_Editor::get_fields('additional'));
			}
			else{
				$fields = array_merge(WC_Checkout_Field_Editor::get_fields('billing'), WC_Checkout_Field_Editor::get_fields('shipping'), WC_Checkout_Field_Editor::get_fields('additional'));
			}
		}else{
			
			if(get_option( 'wcfe_account_sync_fields') && get_option( 'wcfe_account_sync_fields') == "on"){
				
				$fields = array_merge(WC_Checkout_Field_Editor::get_fields('account'), WC_Checkout_Field_Editor::get_fields('billing'), WC_Checkout_Field_Editor::get_fields('additional'));
			}
			else{
				$fields = array_merge(WC_Checkout_Field_Editor::get_fields('billing'), WC_Checkout_Field_Editor::get_fields('additional'));
			}
		}
		
		
		if(is_array($fields) && !empty($fields)){
			$fields_html = '';
			// Loop through all custom fields to see if it should be added
			foreach($fields as $name => $options){
				$enabled = (isset($options['enabled']) && $options['enabled'] == false) ? false : true;
				$is_custom_field = (isset($options['custom']) && $options['custom'] == true) ? true : false;
			     
				if(isset($options['show_in_order']) && $options['show_in_order'] && $enabled && $is_custom_field){
					
					if($options['type'] == 'select'){
						$value = get_post_meta($order_id, $name, true);
						if(is_array($value)){
							$value = implode(",",$value);
						} else {
							$value = get_post_meta($order_id, $name, true);
						
						}
						
					} else {
						$value = get_post_meta($order_id, $name, true);
					}
					
					if(!empty($value)){
						$label = isset($options['label']) && !empty($options['label']) ? __( $options['label'], 'wcfe' ) : $name;
						
						if(is_account_page()){
								$fields_html .= '<tr><th style="text-align:left; width:50%">'. esc_attr($label) .':</th><td style="text-align:left; width:50%">'. wptexturize($value) .'</td></tr>';
						}else {
							$fields_html .= '<tr><th style="text-align:left; width:50%">'. esc_attr($label) .':</th><td style="text-align:left; width:50%">'. wptexturize($value) .'</td></tr>';
							
						}
					}
				}
			}
			
			if($fields_html){
				?>
				<table width="100%" class="woocommerce-table woocommerce-table--custom-fields shop_table custom-fields">
					<?php
						echo $fields_html;
					?>
				</table>
				<?php
			}
		}
	}
	 
	/**
	 * Save meta box content.
	 *
	 * @param int $post_id Post ID
	 */
	function wcfe_save_order_meta_box( $post_id ) {
		// Save logic goes here. Don't forget to include nonce checks!
	}
	add_action( 'save_post', 'wcfe_save_order_meta_box' );

	
	add_action('woocommerce_order_details_after_order_table', 'wcfe_order_details_after_customer_details_lite', 20, 1);
	
	/*****************************************
	 ----- Display Field Values - END --------
	 *****************************************/
	function wcfe_woo_version_check( $version = '3.0' ) {
	  	if(function_exists( 'is_woocommerce_active' ) && is_woocommerce_active() ) {
			global $woocommerce;
			if( version_compare( $woocommerce->version, $version, ">=" ) ) {
		  		return true;
			}
	  	}
	  	return false;
	}

	/**
	 * Field Validation Required.
	 *
	 * @since 1.2.4
	 */
	add_action('woocommerce_checkout_process', 'wcfe_checkout_field_process');
	function wcfe_checkout_field_process( $posted ) {
		// Show an error message if the field is not set.
		//$posted =  $_POST;
		$fields = get_option( 'wc_fields_billing' );
		if ( is_array( $fields ) && !empty($fields) ) {
			foreach ( $fields as $name => $field ) {
				if ( isset( $field['enabled'] ) && $field['enabled'] == false ) {
					unset( $fields[$name] );
				} else {
					$label = '';
					if(isset($field['label'])){
						$label = $field['label'];
					} else{
						$label = $name;
					}
					
					if ( $field['required'] ) {
						if( $field["type"] == 'select' ) {
							if( $_POST[$name][0] == '' ) {
								wc_add_notice( __( '<strong>'.$label.'</strong> is a required field.', 'wcfe' ), 'error' );
							}
						} else {
							if( !empty($_POST[$name]) ) {
								continue;
							} else {
								wc_add_notice( __( '<strong>'.$label.'</strong> is a required field.', 'wcfe' ), 'error' );
							}
						}
								
					}
				}
			}
		}
	}
	 
}