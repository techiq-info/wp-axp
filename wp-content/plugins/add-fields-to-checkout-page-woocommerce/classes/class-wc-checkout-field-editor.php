<?php
if(!defined( 'ABSPATH' )) exit;

/**
 * WC_Checkout_Field_Editor class.
 */
class WC_Checkout_Field_Editor {
	/**
	 * __construct function.
	 */
	function __construct() {
		// Validation rules are controlled by the local fields and can't be changed
		$this->locale_fields = array(
			'billing_address_1', 'billing_address_2', 'billing_state', 'billing_postcode', 'billing_city',
			'shipping_address_1', 'shipping_address_2', 'shipping_state', 'shipping_postcode', 'shipping_city',
			'order_comments'
		);

		add_action('admin_menu', array($this, 'admin_menu'));
		add_filter('woocommerce_screen_ids', array($this, 'add_screen_id'));
		add_action('woocommerce_checkout_update_order_meta', array($this, 'save_data'), 10, 2);
		add_action( 'wp_enqueue_scripts', array($this, 'wc_checkout_fields_scripts'));
		
		
		add_filter( 'woocommerce_form_field_select', array($this, 'wcfe_checkout_fields_select_field'), 10, 4 );
		
		

	}
	
	/**
	 * menu function.
	 */
	function admin_menu() {
		$this->screen_id = add_submenu_page('woocommerce', esc_html__('WooCommerce Checkout & Register Form Editor', 'wcfe'), esc_html__('Checkout & Register Form', 'wcfe'), 
		'manage_woocommerce', 'checkout_form_editor', array($this, 'the_editor'));

		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
	}
	
	/**
	 * scripts function.
	 */
	function enqueue_admin_scripts() {
		wp_enqueue_style ('wcfe-style', plugins_url('/assets/css/wcfe-style.css', dirname(__FILE__)));
		
		wp_enqueue_script( 'wcfe-admin-script', plugins_url('/assets/js/wcfe-admin.js', dirname(__FILE__)), array('jquery','jquery-ui-dialog', 'jquery-ui-sortable',
		'woocommerce_admin', 'select2', 'jquery-tiptip'), WCFE_VERSION, true );
		
	  		wp_localize_script( 'wcfe-admin-script', 'WcfeAdmin', array(
		    'MSG_INVALID_NAME' => 'NAME contains only following ([a-z,A-Z]), digits ([0-9]) and dashes ("-") underscores ("_")'
		  ));	
	}


	/**
	 * wc_checkout_fields_scripts function.
	 *
	 */
	function wc_checkout_fields_scripts() {
		global $wp_scripts;

		if ( is_checkout() || is_account_page()) {
			wp_enqueue_script( 'wc-checkout-editor-frontend', plugins_url('/assets/js/checkout.js', dirname(__FILE__)), array( 'jquery', 'jquery-ui-datepicker' ), WC()->version, true );

			$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';

			wp_enqueue_style( 'jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $jquery_version . '/themes/smoothness/jquery-ui.css' );



			$pattern = array(
				//day
				'd',		//day of the month
				'j',		//3 letter name of the day
				'l',		//full name of the day
				'z',		//day of the year
				'S',

				//month
				'F',		//Month name full
				'M',		//Month name short
				'n',		//numeric month no leading zeros
				'm',		//numeric month leading zeros

				//year
				'Y', 		//full numeric year
				'y'		//numeric year: 2 digit
			);
			$replace = array(
				'dd','d','DD','o','',
				'MM','M','m','mm',
				'yy','y'
			);
			foreach( $pattern as &$p ) {
				$p = '/' . $p . '/';
			}

			wp_localize_script( 'wc-checkout-editor-frontend', 'wc_checkout_fields', array(
				'date_format' => preg_replace( $pattern, $replace, wc_date_format() )
			) );
		}
	}
	
	
	/**
	 * wcfe_checkout_fields_multiselect_field function.
	 *
	 * @param string $field (default: '')
	 * @param mixed $key
	 * @param mixed $args
	 * @param mixed $value
	 */
	function wcfe_checkout_fields_select_field( $field, $key, $args, $value ) {
		//PHP 8.0 Compatibility
		$field = $field ? $field : '';

		if ( ( ! empty( $args['clear'] ) ) ) $after = '<div class="clear"></div>'; else $after = '';

		if ( $args['required'] ) {
			$args['class'][] = 'validate-required';
			$required = ' <abbr class="required" title="' . esc_attr__( 'required', 'woocommerce-checkout-field-editor' ) . '">*</abbr>';
		} else {
			$required = '';
		}

		$args['maxlength'] = ( $args['maxlength'] ) ? 'maxlength="' . absint( $args['maxlength'] ) . '"' : '';

		$options = '';
		$options .= '<option value=" " selected>' . esc_html__( 'Please Select option', 'wcfe' ) .'</option>';
		if ( ! empty( $args['options'] ) ) {
			foreach ( $args['options'] as $option_key => $option_text ) {
				$option_key = self::translate( $option_key );
				$options .= '<option '. selected( $value, $option_key, false ) . '>' . self::translate( $option_text ) .'</option>';
			}

			$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) .'" id="' . esc_attr( $key ) . '_field">';

			if ( $args['label'] ) {
				$field .= '<label for="' . esc_attr( $key ) . '" class="' . implode( ' ', $args['label_class'] ) .'">' . self::translate($args['label'] ). $required . '</label>';
			}

			$class = '';
			$field .= '<select name="' . esc_attr( $key ) . '[]" id="' . esc_attr( $key ) . '" class="checkout_chosen_select select wc-enhanced-select ' . $class . '">
					' . $options . '
				</select>
			</p>' . $after;
		}

		return $field;
	}
	
	/**
	 * add_screen_id function.
	 */
	function add_screen_id($ids){
		$ids[] = 'woocommerce_page_checkout_form_editor';
		$ids[] = strtolower(esc_html__('WooCommerce', 'wcfe')) .'_page_checkout_form_editor';

		return $ids;
	}

	/**
	 * Reset checkout fields.
	 */
	function reset_checkout_fields() {
		delete_option('wc_fields_billing');
		delete_option('wc_fields_shipping');
		delete_option('wc_fields_additional');
		echo '<div class="updated"><p>'. esc_html__('SUCCESS: Checkout fields successfully reset', 'wcfe') .'</p></div>';
	}
	
	function is_reserved_field_name( $field_name ){
		if($field_name && in_array($field_name, array(
			'billing_first_name', 'billing_last_name', 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 
			'billing_country', 'billing_postcode', 'billing_phone', 'billing_email',
			'shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_state', 
			'shipping_country', 'shipping_postcode', 'customer_note', 'order_comments',
			'account_username','account_password'
		))){
			return true;
		}
		return false;
	}
	
	function is_default_field_name($field_name){
		if($field_name && in_array($field_name, array(
			'billing_first_name', 'billing_last_name', 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 
			'billing_country', 'billing_postcode', 'billing_phone', 'billing_email',
			'shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_state', 
			'shipping_country', 'shipping_postcode', 'customer_note', 'order_comments',
			'account_username','account_password'
		))){
			return true;
		}
		return false;
	}
	
	
	/**
	 * Save Data function.
	 */
	public function save_data($order_id, $posted){
		$types = array('billing', 'shipping', 'additional');

		foreach($types as $type){
			$fields = $this->get_fields($type);
			
			foreach($fields as $name => $field){
				if(isset($field['custom']) && $field['custom'] && isset($posted[$name])){
					$value = wc_clean($posted[$name]);
					if($value){
						update_post_meta($order_id, $name, $value);
					}
				}
			}
		}
	}
	
	public static function get_fields($key){
		// Bug: Array_filter() expects parameter 1 to be array
		$fields = get_option('wc_fields_'. $key, array());
		$fields = is_array($fields) ? array_filter($fields) : array();

		if(empty($fields) || sizeof($fields) == 0){
			if($key === 'billing' || $key === 'shipping'){
				$fields = WC()->countries->get_address_fields(WC()->countries->get_base_country(), $key . '_');

			} else if($key === 'additional'){
				$fields = array(
					'order_comments' => array(
						'type'        => 'textarea',
						'class'       => array('notes'),
						'label'       => esc_html__('Order Notes', 'woocommerce'),
						'placeholder' => _x('Notes about your order, e.g. special notes for delivery.', 'placeholder', 'woocommerce')
					)
				);
			}

			else if($key === 'account'){
				$fields = array(
					'account_username' => array(
						'type' => 'text',
						'label' => esc_html__('Email address', 'woocommerce')
					),
					'account_password' => array(
						'type' => 'password',
						'label' => esc_html__('Password', 'woocommerce')
					)

				);
			}
		}
		return $fields;
	}

	/***********************************
	 ----- i18n functions - START ------
	 ***********************************/
	public static function translate($text){
		if(!empty($text)){	
			$otext = $text;						
			$text = __($text, 'wcfe');	
			if($text === $otext){
				$text = __($text, 'woocommerce');
			}
		}
		return $text;
	}

	public static function echo_translate($text){
		if(!empty($text)){	
			$otext = $text;						
			$text = __($text, 'wcfe');	
			if($text === $otext){
				$text = __($text, 'woocommerce');
			}
		}
		echo $text;
	}
			
	function sort_fields_by_order($a, $b){
	    if(!isset($a['order']) || $a['order'] == $b['order']){
	        return 0;
	    }
	    return ($a['order'] < $b['order']) ? -1 : 1;
	}
	
	function get_field_types(){
		return array(
			'text' => 'Text',
			'textarea' => 'Textarea',
			'select' => 'Select'	
		);
	}

	/*
	 * New field form popup
	 */	
	function wcfe_new_field_form_pp(){
		$field_types = $this->get_field_types(); ?>
        <div id="wcfe_new_field_form_pp" title="New Checkout Field" class="wcfe_popup_wrapper">
          <form>
          	<table>
            	<tr>                
                	<td colspan="2" class="err_msgs"></td>
				</tr>
            	<tr>                    
                	<td width="40%"><?php esc_html_e('Type','wcfe'); ?></td>
                    <td>
                    	<select name="ftype" style="width:250px;" onchange="fieldTypeChangeListner(this)">
                        <?php foreach($field_types as $value=>$label) { ?>
                        	<option value="<?php echo trim($value); ?>"><?php echo $label; ?></option>
                        <?php } ?>
							
						<option disabled><?php esc_html_e('Heading - (Premium Feature)','wcfe'); ?></option>
						<option disabled><?php esc_html_e('Number - (Premium Feature)','wcfe'); ?></option>
						<option disabled><?php esc_html_e('File Upload - (Premium Feature)','wcfe'); ?></option>
						<option disabled><?php esc_html_e('Multi-Select - (Premium Feature)','wcfe'); ?></option>
						<option disabled><?php esc_html_e('Checkbox - (Premium Feature)','wcfe'); ?></option>
						<option disabled><?php esc_html_e('Radio Button - (Premium Feature)','wcfe'); ?></option>
						<option disabled><?php esc_html_e('Date Picker - (Premium Feature)','wcfe'); ?></option>
						<option disabled><?php esc_html_e('password - (Premium Feature)','wcfe'); ?><font color="red"></option>
                        </select>
                    </td>
				</tr>
            	<tr class="rowName">                
                	<td><?php esc_html_e('Name','wcfe'); ?><font color="red"><?php echo esc_html__('*','wcfe'); ?></font></td>
                    <td><input type="text" name="fname" placeholder="eg. new_field" style="width:250px;"/>
					<br><span style="font-size: 12px; color: red;"><?php esc_html_e(' Must be unique of each field', 'wcfe'); ?></span>
					</td>
				</tr>         
                <tr class="rowLabel">
                    <td><?php esc_html_e('Label of Field','wcfe'); ?></td>
                    <td><input type="text" name="flabel" placeholder="eg. New Field" style="width:250px;"/></td>
				</tr>
                <tr class="rowPlaceholder">                    
                    <td><?php esc_html_e('Placeholder','wcfe'); ?></td>
                    <td><input type="text" name="fplaceholder" placeholder="eg. New Field" style="width:250px;"/></td>
				</tr>
				<tr class="rowMaxlength">                    
                    <td><?php esc_html_e('Character limit','wcfe'); ?></td>
                    <td><input type="number" name="fmaxlength" style="width:250px;"/></td>
				</tr>
                <tr class="rowOptions">                    
                    <td><?php esc_html_e('Options','wcfe'); ?><font color="red"><?php echo esc_html__('*','wcfe'); ?></font></td>
                    <td><input type="text" name="foptions" placeholder="eg. Option a, Option b, Option c" style="width:250px;"/>
					<br><span><?php esc_html_e(' add comma separated options', 'wcfe'); ?></span></td>
				</tr>
                <tr class="rowClass">
                    <td><?php esc_html_e('Field Width','wcfe'); ?></td>
                    <td>
                    	<select name="fclass" style="width:250px;">
							<option value="form-row-wide"><?php esc_html_e('Full Width','wcfe'); ?></option>
							<option value="form-row-first"><?php esc_html_e('Half Width left','wcfe'); ?></option>
							<option value="form-row-last"><?php esc_html_e('Half Width Right','wcfe'); ?></option>
						</select>
                    </td>
				</tr>
                                            
                <tr class="rowValidate">                    
                    <td><?php esc_html_e('Validation','wcfe'); ?></td>
                    <td>
                    	<select multiple="multiple" name="fvalidate" placeholder="Select validations" class="wcfe-enhanced-multi-select" 
                        style="width: 250px; height:30px;">
                            <option value="email"><?php esc_html_e('Email','wcfe'); ?></option>
                            <option value="phone"><?php esc_html_e('Phone','wcfe'); ?></option>
							
                        </select>
                    </td>
				</tr>
				
                <tr class="rowRequired">
                	<td>&nbsp;</td>                     
                    <td>                    	
                    	<input type="checkbox" name="frequired" value="yes" checked/>
                        <label><?php esc_html_e('Required','wcfe'); ?></label>
                        <div style="height: 8px; display: block;"></div>
                    	<input type="checkbox" name="fenabled" value="yes" checked/>
                        <label><?php esc_html_e('Enabled','wcfe'); ?></label>
                    </td>
                </tr>
                <tr class="rowShowInEmail"> 
                	<td>&nbsp;</td>                
                    <td>                    	
                    	<input type="checkbox" name="fshowinemail" value="email" checked/>
                        <label><?php esc_html_e('Display in Emails','wcfe'); ?></label>
                    </td>
                </tr>
               
                <tr class="rowShowInOrder"> 
                	<td>&nbsp;</td>                   
                    <td>                    	
                    	<input type="checkbox" name="fshowinorder" value="order-review" checked/>
                        <label><?php esc_html_e('Display in Order Detail Pages','wcfe'); ?></label>
                    </td>
            	</tr>              
            </table>
          </form>
        </div>
        <?php
	}
	
	/*
	 * New field form popup
	 */	
	function wcfe_edit_field_form_pp(){
		$field_types = $this->get_field_types(); ?>
        <div id="wcfe_edit_field_form_pp" title="Edit Checkout Field" class="wcfe_popup_wrapper">
          <form>
          	<table>
            	<tr>                
                	<td colspan="2" class="err_msgs"></td>
				</tr>
            	<tr>                
                	<td width="40%"><?php esc_html_e('Name','wcfe'); ?><font color="red"><?php echo esc_html__('*','wcfe'); ?></font></td>
                    <td>
                    	<input type="hidden" name="rowId"/>
                    	<input type="hidden" name="fname"/>
                    	<input type="text" name="fnameNew" style="width:250px;"/>
						<br><span style="font-size: 12px; color: red;"><?php esc_html_e(' Must be unique of each field', 'wcfe'); ?></span>
                    </td>
				</tr>
                <tr>                   
                    <td><?php esc_html_e('Type','wcfe'); ?></td>
                    <td>
                    	<select name="ftype" style="width:250px;" onchange="fieldTypeChangeListner(this)">
                        <?php foreach($field_types as $value=>$label){ ?>
                        	<option value="<?php echo trim($value); ?>"><?php echo $label; ?></option>
                        <?php } ?>
                        </select>
                    </td>
				</tr>       
       
                <tr class="rowLabel">
                    <td><?php esc_html_e('Label of Field','wcfe'); ?></td>
                    <td><input type="text" name="flabel" placeholder="eg. New Field" style="width:250px;"/></td>
				</tr>
                <tr class="rowPlaceholder">                    
                    <td><?php esc_html_e('Placeholder','wcfe'); ?></td>
                    <td><input type="text" name="fplaceholder" placeholder="eg. New Field" style="width:250px;"/></td>
				</tr>
				<tr class="rowMaxlength">                    
                    <td><?php esc_html_e('Character limit','wcfe'); ?></td>
                    <td><input type="number" name="fmaxlength" style="width:250px;"/></td>
				</tr>
                <tr class="rowOptions">                    
                    <td><?php esc_html_e('Options','wcfe'); ?><font color="red"><?php echo esc_html__('*','wcfe'); ?></font></td>
                    <td><input type="text" name="foptions" placeholder="eg. Option a, Option b, Option c" style="width:250px;"/>
					<br><span><?php esc_html_e(' add comma separated options', 'wcfe'); ?></span></td>
				</tr>
                <tr class="rowClass">
                    <td><?php esc_html_e('Field Width','wcfe'); ?></td>
                    <td>
                    	<select name="fclass" style="width:250px;">
							<option value="form-row-wide"><?php esc_html_e('Full Width','wcfe'); ?></option>
							<option value="form-row-first"><?php esc_html_e('Half Width left','wcfe'); ?></option>
							<option value="form-row-last"><?php esc_html_e('Half Width Right','wcfe'); ?></option>
						</select>
                    </td>
				</tr>				
                                                
                <tr class="rowValidate">                    
                    <td><?php esc_html_e('Validation','wcfe'); ?></td>
                    <td>
                    	<select multiple="multiple" name="fvalidate" placeholder="Select validations" class="wcfe-enhanced-multi-select" 
                        style="width: 250px; height:30px;">
                            <option value="email"><?php esc_html_e('Email','wcfe'); ?></option>
                            <option value="phone"><?php esc_html_e('Phone','wcfe'); ?></option>
							
                        </select>
                    </td>
				</tr>
				
                <tr class="rowRequired">  
                	<td>&nbsp;</td>                     
                    <td>             	
                    	<input type="checkbox" name="frequired" value="yes" checked/>
                        <label><?php esc_html_e('Required','wcfe'); ?></label>
                        <div style="height: 8px; display: block;"></div>
                    	<input type="checkbox" name="fenabled" value="yes" checked/>
                        <label><?php esc_html_e('Enabled','wcfe'); ?></label>
                    </td>                    
                </tr> 

                <tr class="rowShowInEmail"> 
                	<td>&nbsp;</td>                   
                    <td>                    	
                    	<input type="checkbox" name="fshowinemail" value="email" checked/>
                        <label><?php esc_html_e('Display in Emails','wcfe'); ?></label>
                    </td>
                </tr> 
                <tr class="rowShowInOrder"> 
                	<td>&nbsp;</td>                   
                    <td>                    	
                    	<input type="checkbox" name="fshowinorder" value="order-review" checked/>
                        <label><?php esc_html_e('Display in Order Detail Pages','wcfe'); ?></label>
                    </td>
                </tr> 
            </table>
          </form>
        </div>
        <?php
	}
	
	function render_tabs_and_sections(){
		$tabs = array( 'fields' => 'Checkout & Account Fields' );
		$tab  = isset( $_GET['tab'] ) ? esc_attr( $_GET['tab'] ) : 'fields';
		
		$sections = ''; $section  = '';
		if($tab === 'fields') {
			$sections = array( 'billing', 'shipping', 'additional', 'account' );

			$section  = isset( $_GET['section'] ) ? esc_attr( $_GET['section'] ) : 'billing';
		}
		
		echo '<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">';
		foreach( $tabs as $key => $value ) {
			$active = ( $key == $tab ) ? 'nav-tab-active' : '';
			echo '<a class="nav-tab '.$active.'" href="'.admin_url('admin.php?page=checkout_form_editor&tab='.$key).'">'.$value.'</a>';
		}
		echo '</h2>';
		
		if(!empty($sections)){
			echo '<ul class="wcfe-sections">';
			$size = sizeof($sections); $i = 0;
			foreach( $sections as $key ) {
				$i++;
				$active = ( $key == $section ) ? 'current' : '';
				$url = 'admin.php?page=checkout_form_editor&tab=fields&section='.$key;
				echo '<li>';
				echo '<a href="'. admin_url($url) .'" class="'.$active.'" >'.ucwords($key).' '.esc_html__('Fields', 'wcfe').'</a>';
				echo ($size > $i) ? ' ' : '';
				echo '</li>';				
			}

			echo '</ul>';
		}
		if( tl_fields()->is_free_plan() ){
			?>
			<div id="message" style="border-left-color: #00A0D2" class="wc-connect updated wcfe-notice">
            <div class="squeezer">
            	<table>
                	<tr>
                    	<td width="70%">
                        	<p><strong><i><?php esc_html_e('WooCommerce Checkout and Register Field Editor Pro','wcfe'); ?></i></strong> <?php esc_html_e('premium version provides more features to design your checkout and my account page.','wcfe'); ?></p>
                            <ul>
                            	<li><?php esc_html_e('12 field types available,','wcfe'); ?><br/>(<i><?php esc_html_e('Text, Hidden, Password, Textarea, Radio, Checkbox, Select, Multi-select, Date Picker, Heading, Label','wcfe'); ?></i>).</li>
                            	<li><?php esc_html_e('Option to add all of these fields on my account page too.','wcfe'); ?></li>
                                <li><?php esc_html_e('Option to add more sections in addition to the core sections (billing, shipping and additional) in checkout page.','wcfe'); ?></li>
								<li><?php esc_html_e('Integration of My Account With Checkout page','wcfe'); ?></li>
								<li><?php esc_html_e('Conditionally suppress fields','wcfe'); ?></li>
							</ul>
                        </td>
                        <td>
                        	<a target="_blank" href="https://www.themelocation.com/woocommerce-checkout-register-form-editor/" class="">
                            	<img src="<?php echo plugins_url('/assets/css/upgrade-btn.jpg', dirname(__FILE__)); ?>" />
                            </a>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
			<?php
		}
		
	}
	
	function get_current_tab(){
		return isset( $_GET['tab'] ) ? esc_attr( $_GET['tab'] ) : 'fields';
	}
	
	function get_current_section(){
		$tab = $this->get_current_tab();
		$section = '';
		if($tab === 'fields'){
			$section = isset( $_GET['section'] ) ? esc_attr( $_GET['section'] ) : 'billing';
		}
		return $section;
	}

	function render_checkout_fields_heading_row() {
		?>
		<th class="sort"></th>
		<th class="check-column" style="padding-left:0px !important;"><input type="checkbox" style="margin-left:7px;" onclick="wcfeSelectAllCheckoutFields(this)"/></th>
		<th class="name"><?php esc_html_e('Name','wcfe'); ?></th>
		<th class="id"><?php esc_html_e('Type','wcfe'); ?></th>
		<th><?php esc_html_e('Label','wcfe'); ?></th>
		<th><?php esc_html_e('Placeholder','wcfe'); ?></th>
		<th><?php esc_html_e('Validation Rules','wcfe'); ?></th>
        <th class="status"><?php esc_html_e('Required','wcfe'); ?></th>
		
		<th class="status"><?php esc_html_e('Enabled','wcfe'); ?></th>	
        <th class="status"><?php esc_html_e('Edit','wcfe'); ?></th>	
        <?php
	}
	
	function render_actions_row($section){
		?>
        <th colspan="7">
            <button type="button" class="button button-primary" onclick="openNewFieldForm('<?php echo $section; ?>')"><?php _e( '+ Add New field', 'wcfe' ); ?></button>
            <button type="button" class="button" onclick="removeSelectedFields()"><?php _e( 'Remove', 'wcfe' ); ?></button>
            <button type="button" class="button" onclick="enableSelectedFields()"><?php _e( 'Enable', 'wcfe' ); ?></button>
            <button type="button" class="button" onclick="disableSelectedFields()"><?php _e( 'Disable', 'wcfe' ); ?></button>
        </th>
        <th colspan="4">
        	<input type="submit" name="save_fields" class="button-primary" value="<?php _e( 'Save changes', 'wcfe' ) ?>" style="float:right" />
            <input type="submit" name="reset_fields" class="button" value="<?php _e( 'Reset to default fields', 'wcfe' ) ?>" style="float:right; margin-right: 5px;" 
			onclick="return confirm('Are you sure you want to reset to default fields? all your changes will be deleted.');"/>
        </th>  
    	<?php 
	}
	
	function the_editor() {
		$tab = $this->get_current_tab();
		if($tab === 'fields'){
			$this->checkout_form_field_editor();
		}
	}
	
	function checkout_form_field_editor() {
		$section = $this->get_current_section();
						
		echo '<div class="wrap woocommerce wcfe-wrap"><div class="icon32 icon32-attributes" id="icon-woocommerce"><br /></div>';
			$this->render_tabs_and_sections();
			
			if ( isset( $_POST['save_fields'] ) )
				echo $this->save_options( $section );
			
				
			if ( isset( $_POST['reset_fields'] ) )
				echo $this->reset_checkout_fields();		
	
			global $supress_field_modification;
			$supress_field_modification = false;
						
			if( $section != 'account' ) { ?>
			<form method="post" id="wcfe_checkout_fields_form" action="">
            	<table id="wcfe_checkout_fields" class="wc_gateways widefat" cellspacing="0">
					<thead>
                    	<tr><?php $this->render_actions_row($section); ?></tr>
                    	<tr><?php $this->render_checkout_fields_heading_row(); ?></tr>						
					</thead>
                    <tfoot>
                    	<tr><?php $this->render_checkout_fields_heading_row(); ?></tr>
						<tr><?php $this->render_actions_row($section); ?></tr>
					</tfoot>
					<tbody class="ui-sortable">
                    <?php 
					$i=0;
				
					foreach( $this->get_fields( $section ) as $name => $options ) :	
						if ( isset( $options['custom'] ) && $options['custom'] == 1 ) {
							$options['custom'] = '1';
						} else {
							$options['custom'] = '0';
						}
											
						if ( !isset( $options['label'] ) ) {
							$options['label'] = '';
						}
						
						if ( !isset( $options['placeholder'] ) ) {
							$options['placeholder'] = '';
						}
										
															
						if( isset( $options['options'] ) && is_array($options['options']) ) {
							$options['options'] = implode(",", $options['options']);
						}else{
							$options['options'] = '';
						}
					
						
						if( isset( $options['class'] ) && is_array($options['class']) ) {
							$options['class'] = implode(",", $options['class']);
						}else{
							$options['class'] = '';
						}
						
						if( isset( $options['label_class'] ) && is_array($options['label_class']) ) {
							$options['label_class'] = implode(",", $options['label_class']);
						}else{
							$options['label_class'] = '';
						}
						
						if( isset( $options['validate'] ) && is_array($options['validate']) ) {
							$options['validate'] = implode(",", $options['validate']);
						}else{
							$options['validate'] = '';
						}
												
						if ( isset( $options['required'] ) && $options['required'] == 1 ) {
							$options['required'] = '1';
						} else {
							$options['required'] = '0';
						}
										
						if ( !isset( $options['enabled'] ) || $options['enabled'] == 1 ) {
							$options['enabled'] = '1';
						} else {
							$options['enabled'] = '0';
						}

						if ( !isset( $options['type'] ) ) {
							$options['type'] = 'text';
						} 
						
						if ( isset( $options['show_in_email'] ) && $options['show_in_email'] == 1 ) {
							$options['show_in_email'] = '1';
						} else {
							$options['show_in_email'] = '0';
						}
						
						if ( isset( $options['show_in_order'] ) && $options['show_in_order'] == 1 ) {
							$options['show_in_order'] = '1';
						} else {
							$options['show_in_order'] = '0';
						}
					?>
						<?php
						if($name == 'account_username' || $name == 'account_password'){ ?>
						<tr class="row_<?php echo $i; echo ' wcfe-disabled'; ?>">
						<?php } else { ?>
						<tr class="row_<?php echo $i; echo($options['enabled'] == 1 ? '' : ' wcfe-disabled') ?>">
							<?php } ?>
                        	<td width="1%" class="sort ui-sortable-handle">
                            	<input type="hidden" name="f_custom[<?php echo $i; ?>]" class="f_custom" value="<?php echo $options['custom']; ?>" />
                                <input type="hidden" name="f_order[<?php echo $i; ?>]" class="f_order" value="<?php echo $i; ?>" />
                                                                                                
                                <input type="hidden" name="f_name[<?php echo $i; ?>]" class="f_name" value="<?php echo esc_attr( $name ); ?>" />
                                <input type="hidden" name="f_name_new[<?php echo $i; ?>]" class="f_name_new" value="" />
                                <input type="hidden" name="f_type[<?php echo $i; ?>]" class="f_type" value="<?php echo $options['type']; ?>" />                                
                                <input type="hidden" name="f_label[<?php echo $i; ?>]" class="f_label" value="<?php echo $options['label']; ?>" />
								 <?php if(isset($options['maxlength'])){ ?>
                                <input type="hidden" name="f_maxlength[<?php echo $i; ?>]" class="f_maxlength" value="<?php echo $options['maxlength']; ?>" />
								<?php } ?>
                                
                                <input type="hidden" name="f_placeholder[<?php echo $i; ?>]" class="f_placeholder" value="<?php echo $options['placeholder']; ?>" />
                                <input type="hidden" name="f_options[<?php echo $i; ?>]" class="f_options" value="<?php echo($options['options']) ?>" />
								<input type="hidden" name="f_class[<?php echo $i; ?>]" class="f_class" value="<?php echo $options['class']; ?>" />
                                <input type="hidden" name="f_label_class[<?php echo $i; ?>]" class="f_label_class" value="<?php echo $options['label_class']; ?>" />  
								
								<input type="hidden" name="f_required[<?php echo $i; ?>]" class="f_required" value="<?php echo($options['required']) ?>" />
                                                                
                                <input type="hidden" name="f_enabled[<?php echo $i; ?>]" class="f_enabled" value="<?php echo($options['enabled']) ?>" />
                                <input type="hidden" name="f_validation[<?php echo $i; ?>]" class="f_validation" value="<?php echo($options['validate']) ?>" />
                                <input type="hidden" name="f_show_in_email[<?php echo $i; ?>]" class="f_show_in_email" value="<?php echo($options['show_in_email']) ?>" />
                                <input type="hidden" name="f_show_in_order[<?php echo $i; ?>]" class="f_show_in_order" value="<?php echo($options['show_in_order']) ?>" />
                                <input type="hidden" name="f_deleted[<?php echo $i; ?>]" class="f_deleted" value="0" />
                               
                            </td>
                            <td class="td_select"><input type="checkbox" name="select_field"/></td>
                            <td class="td_name"><?php echo esc_attr( $name ); ?></td>
                            <td class="td_type"><?php echo $options['type']; ?></td>
                            <td class="td_label"><?php echo $options['label']; ?></td>
                            
                            <td class="td_placeholder"><?php echo $options['placeholder']; ?></td>
                            <td class="td_validate"><?php echo $options['validate']; ?></td>
                            <td class="td_required status"><?php echo($options['required'] == 1 ? '<span class="status-enabled tips">Yes</span>' : '-' ) ?></td>
                            
                            <td class="td_enabled status"><?php echo($options['enabled'] == 1 ? '<span class="status-enabled tips">Yes</span>' : '-' ) ?></td>
                            <td class="td_edit">
                            	<button type="button" class="f_edit_btn" <?php echo($options['enabled'] == 1 ? '' : 'disabled') ?> 
                                onclick="openEditFieldForm(this,<?php echo $i; ?>)"><?php _e( 'Edit', 'wcfe' ); ?></button>
                            </td>
                    	</tr>
                    <?php $i++; endforeach; ?>
                	</tbody>
				</table> 
            </form>
			
        <?php
        } else {
        ?>

    <div class="premium-message"><a href="https://www.themelocation.com/woocommerce-checkout-register-form-editor/"><img src="<?php echo plugins_url('/assets/css/account_sec.jpg', dirname(__FILE__)); ?>" ></a></div>
    <?php 
    }
    ?>
            <?php
            $this->wcfe_new_field_form_pp();
			$this->wcfe_edit_field_form_pp();
			?>
    	</div>
    <?php 		
	}
	
						
	function save_options( $section ) {
		$o_fields      = $this->get_fields( $section );
		$fields        = $o_fields;
		
		$f_order       = ! empty( $_POST['f_order'] ) ? $_POST['f_order'] : array();
		
		$f_names       = ! empty( $_POST['f_name'] ) ? $_POST['f_name'] : array();
		
		$f_names_new   = ! empty( $_POST['f_name_new'] ) ? $_POST['f_name_new'] : array();
	
		$f_types       = ! empty( $_POST['f_type'] ) ? $_POST['f_type'] : array();
		$f_labels      = ! empty( $_POST['f_label'] ) ? $_POST['f_label'] : array();
		
		
		$f_placeholder = ! empty( $_POST['f_placeholder'] ) ? $_POST['f_placeholder'] : array();
		
		$f_maxlength = ! empty( $_POST['f_maxlength'] ) ? $_POST['f_maxlength'] : array();
		
		if(isset($_POST['f_options'])){
			$f_options     = ! empty( $_POST['f_options'] ) ? $_POST['f_options'] : array();
		}
		
		$f_class       = ! empty( $_POST['f_class'] ) ? $_POST['f_class'] : array();
				
		$f_required    = ! empty( $_POST['f_required'] ) ? $_POST['f_required'] : array();
		
		$f_enabled     = ! empty( $_POST['f_enabled'] ) ? $_POST['f_enabled'] : array();
		
		$f_show_in_email = ! empty( $_POST['f_show_in_email'] ) ? $_POST['f_show_in_email'] : array();

		$f_show_in_order = ! empty( $_POST['f_show_in_order'] ) ? $_POST['f_show_in_order'] : array();
		
		$f_validation  = ! empty( $_POST['f_validation'] ) ? $_POST['f_validation'] : array();

		$f_deleted     = ! empty( $_POST['f_deleted'] ) ? $_POST['f_deleted'] : array();
						
		$f_position        = ! empty( $_POST['f_position'] ) ? $_POST['f_position'] : array();				
		$f_display_options = ! empty( $_POST['f_display_options'] ) ? $_POST['f_display_options'] : array();
		
		$max               = max( array_map( 'absint', array_keys( $f_names ) ) );
			
		for ( $i = 0; $i <= $max; $i ++ ) {
			$name     = empty( $f_names[$i] ) ? '' : urldecode( sanitize_title( wc_clean( stripslashes( $f_names[$i] ) ) ) );
			$new_name = empty( $f_names_new[$i] ) ? '' : urldecode( sanitize_title( wc_clean( stripslashes( $f_names_new[$i] ) ) ) );
			
			if(!empty($f_deleted[$i]) && $f_deleted[$i] == 1){
				unset( $fields[$name] );
				continue;
			}
						
			// Check reserved names
			if($this->is_reserved_field_name( $new_name )){
				continue;
			}
		
			//if update field
			if( $name && $new_name && $new_name !== $name ){
				
				if ( isset( $fields[$name] ) ) {
					$fields[$new_name] = $fields[$name];
				} else {
					$fields[$new_name] = array();
				}

				unset( $fields[$name] );
				$name = $new_name;
			} else {
				$name = $name ? $name : $new_name;

			}

			if(!$name){
				continue;
			}
						
			//if new field
			if ( !isset( $fields[$name] ) ) {
				$fields[$name] = array();
			}

			$o_type  = isset( $o_fields[$name]['type'] ) ? $o_fields[$name]['type'] : 'text';
			
			$fields[$name]['type']    	  = empty( $f_types[$i] ) ? $o_type : wc_clean( $f_types[$i] );
			$fields[$name]['label']   	  = empty( $f_labels[$i] ) ? '' : wp_kses_post( trim( stripslashes( $f_labels[$i] ) ) );
			
			$fields[$name]['placeholder'] = empty( $f_placeholder[$i] ) ? '' : wc_clean( stripslashes( $f_placeholder[$i] ) );

			$fields[$name]['options'] 	  = empty( $f_options[$i] ) ? array() : array_map( 'wc_clean', explode( ',', trim(stripslashes($f_options[$i])) ) );

			$fields[$name]['maxlength'] = empty( $f_maxlength[$i] ) ? '' : wc_clean( stripslashes( $f_maxlength[$i] ) );
			$fields[$name]['class'] 	  = empty( $f_class[$i] ) ? array() : array_map( 'wc_clean', explode( ',', $f_class[$i] ) );
			$fields[$name]['label_class'] = empty( $f_label_class[$i] ) ? array() : array_map( 'wc_clean', explode( ',', $f_label_class[$i] ) );
			
			$fields[$name]['required']    = empty( $f_required[$i] ) ? false : true;
			
			$fields[$name]['enabled']     = empty( $f_enabled[$i] ) ? false : true;
			$fields[$name]['order']       = empty( $f_order[$i] ) ? '' : wc_clean( $f_order[$i] );
				
			if (!empty( $fields[$name]['options'] )) {
				$fields[$name]['options'] = array_combine( $fields[$name]['options'], $fields[$name]['options'] );
			}

			if (!in_array( $name, $this->locale_fields )){
				$fields[$name]['validate'] = empty( $f_validation[$i] ) ? array() : explode( ',', $f_validation[$i] );
			}

			if (!$this->is_default_field_name( $name )){
				$fields[$name]['custom'] = true;
				$fields[$name]['show_in_email'] = empty( $f_show_in_email[$i] ) ? false : true;
				$fields[$name]['show_in_order'] = empty( $f_show_in_order[$i] ) ? false : true;
			} else {
				$fields[$name]['custom'] = false;
			}
			
			$fields[$name]['label']   	  = esc_html__($fields[$name]['label'], 'woocommerce');
			$fields[$name]['placeholder'] = esc_html__($fields[$name]['placeholder'], 'woocommerce');
			
		}
		
		uasort( $fields, array( $this, 'sort_fields_by_order' ) );
		$result = update_option( 'wc_fields_' . $section, $fields );
	
		if ( $result == true ) {
			echo '<div class="updated"><p>' . esc_html__( 'Your changes were saved.', 'wcfe' ) . '</p></div>';
		} else {
			echo '<div class="error"><p> ' . esc_html__( 'Your changes were not saved due to an error (or you made none!).', 'wcfe' ) . '</p></div>';
		}
		
	}
}