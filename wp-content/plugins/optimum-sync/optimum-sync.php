<?php 
defined( 'ABSPATH' ) or die( 'Nope, not accessing this' );
/**
 * Plugin Name: Optimum Products Sync
 * Plugin URI:  http://myoctopus.co.il/
 * Description: Syncronising the data like Products, Ordres, Customers from api of http://myoctopus.co.il/ to woocommrece
 * Version:     1.0.0
 * Author:      Octopus Web
 * Author URI:  http://myoctopus.co.il/
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
class OptimumOctopus_WoocommerceSync {
	private $optimum_api_table = 'optimum_api_table';
	private $templatePath = 'templates/';
	private $customer_Optimum = 2200;
	private $user_Optimum     = "benis1";
	private $pass_Optimum     = "benis1";
	private $type_Optimum     = "full";
	private $offset     = 30;
	function __construct(){
		/*sync products /category  STOPed  if like to sync remove comment line 28-35*/
		if(get_option('optimum_sync_api_products') == 'yes') {
			add_action('wp_ajax_getOptimumProducts', array($this, 'getProducts'));
			add_action('wp_ajax_nopriv_getOptimumProducts', array($this, 'getProducts'));
			add_action('wp_ajax_getOptimumProductsCategory', array($this, 'getProductsCategory'));
			add_action('wp_ajax_nopriv_getOptimumProductsCategory', array($this, 'getProductsCategory'));
			add_action('wp_ajax_getOptimumProductsSubCategory', array($this, 'getProductsSubCategory'));
			add_action('wp_ajax_nopriv_getOptimumProductsSubCategory', array($this, 'getProductsSubCategory'));
		}

		/* add_action('woocommerce_thankyou', array($this, 'getProductUpdateOrderShort'), 10, 1); */
		
		add_action('wp_ajax_nopriv_attach_product_thumbnail', array($this, 'attach_product_thumbnail'));
		add_action( 'admin_init', array($this, 'optimum_api_settings_init') );
		add_action('wp_ajax_update_product', array($this, 'update_product_post')); 
		add_action('wp_ajax_nopriv_update_product', array($this, 'update_product_post'));

		add_action('wp_ajax_getProductUpdateOrderShort', array( $this, 'getProductUpdateOrderShort' ) );
		add_action('wp_ajax_nopriv_getProductUpdateOrderShort', array( $this, 'getProductUpdateOrderShort' ) );

		add_action('wp_ajax_getorder_ids', array( $this, 'getorder_ids' ) );
		add_action('wp_ajax_nopriv_getorder_ids', array( $this, 'getorder_ids' ) );

		if ( is_admin() ) {
            register_activation_hook(__FILE__, array($this,'activate'));
            register_deactivation_hook(__FILE__, array($this,'remove_database'));
        }
	}
	/*public function getProducts(){
		$data = array(
			'id_Customer_Optimum' => $this->customer_Optimum,
			'enter_user_Optimum'  => $this->user_Optimum,
			'enter_pass_Optimum'  => $this->pass_Optimum,
			'type' => $this->type_Optimum
		);
		$dataXml = $this->generateRequestContext('items_ordrs_export', $data);
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  	CURLOPT_URL => "http://myoctopus.co.il/octopus_web/Web_service/WebService_Order.asmx/items_ordrs_export?id_Customer_Optimum=".$this->customer_Optimum."&enter_user_Optimum=".$this->user_Optimum."&enter_pass_Optimum=".$this->pass_Optimum."&type=".$this->type_Optimum,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING       => "",
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => "GET",
			//CURLOPT_POSTFIELDS     => $dataXml,
			CURLOPT_HTTPHEADER => array("Content-Type: text/xml"),
		));
		$response = curl_exec($curl);
		curl_close($curl);
		$arrData = simplexml_load_string($response) or die("Error: Cannot create object");
		$con     = json_encode($arrData); 
		$newArr  = json_decode($con, true);
		if(isset($newArr) && !empty($newArr)) {
			$productsAdded   = 0;
			$productsUpdated = 0;
			$total_products  = count($newArr['items_export']);
			//$cnt = 0;
			$startpoint = $this->checkStartpointIdExists();
			//All products sync then truncate table
			if($total_products == $startpoint) { //echo "stop";
				$del = $this->dropStartpointtable();
			}
			$startpo = $this->insertStartpointId($startpoint);
			$start = $startpoint;
			$elementsPerPage = 13;
			$elements = array_slice($newArr['items_export'], $start, $elementsPerPage);
			if(isset($elements) && !empty($elements)) 
			{
				foreach ($elements as $key => $product) 
				{	
					$productcheck = $this->checkProductByMetaData('_id_code', $product['id_code']);
					if($productcheck == false) {
						$post_id = $this->create_products($product);
						$productsAdded++;
					} else {
						$post_id = $productcheck;
						$productsUpdated++;
					}
					$this->UpdateProductMetas($post_id, $product);				
					$terms = array();
					if( isset($product['id_Department']) && !empty($product['id_Department']) ) {
						$parent_term_id = $this->checkIftermExistsByMetaValue('id_Departments', $product['id_Department'] );
						if(!empty($parent_term_id)) {
							$terms[] = $parent_term_id;
						}
					}
					if( isset($product['id_Group']) && !empty($product['id_Group']) ) {
						$child_term_id = $this->checkIftermExistsByMetaValue('id_Group', $product['id_Group'] );
						if(!empty($child_term_id)) {
							$terms[] = $child_term_id;
						}
					}
					if( is_array($terms) && !empty($terms) ) {
						wp_set_post_terms( $post_id, $terms, 'product_cat' );	
					}
					//map with image
					if(isset($product['name_picture']) && !empty($product['name_picture'])) {
						//$url = "http://ok-rsoft.com/Proxy/Optimum_Orders/Content/ProductsImages/1000/".$product['name_picture'];
						$url = "http://62.219.68.121/Optimum_Orders/Content/ProductsImages/2900/".$product['name_picture'];
						if( !get_the_post_thumbnail_url($post_id) ) {
							$this->Generate_Featured_Image($url, $post_id);	
						}
					}
				}
				die(json_encode( array('result' => true, 'msg' => $productsAdded.' products created and '.$productsUpdated.' products updated') ));
			} else {
				die(json_encode( array('result' => false, 'msg' => 'No any product found.All products Sync,please check it.') ));		
			}
		}
		exit(json_encode( array('result' => false, 'msg' => 'Oops. Something went wrong. Please try again later.') ));
	}*/
	public function getProducts(){
		global $wpdb;
		$table_name = $wpdb->prefix . $this->optimum_api_table;
		$data = array(
			'id_Customer_Optimum' => $this->customer_Optimum,
			'enter_user_Optimum'  => $this->user_Optimum,
			'enter_pass_Optimum'  => $this->pass_Optimum,
			'type' => $this->type_Optimum
		);
		$dataXml = $this->generateRequestContext('items_ordrs_export', $data);
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  	CURLOPT_URL => "http://myoctopus.co.il/octopus_web/Web_service/WebService_Order.asmx/items_ordrs_export?id_Customer_Optimum=".$this->customer_Optimum."&enter_user_Optimum=".$this->user_Optimum."&enter_pass_Optimum=".$this->pass_Optimum."&type=".$this->type_Optimum,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING       => "",
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => "GET",
			//CURLOPT_POSTFIELDS     => $dataXml,
			CURLOPT_HTTPHEADER => array("Content-Type: text/xml"),
		));
		$response = curl_exec($curl);
		curl_close($curl);
		$arrData = simplexml_load_string($response) or die("Error: Cannot create object");
		$con     = json_encode($arrData);
		$newArr  = json_decode($con, true);
		/*print "<pre>";
		print_r($newArr['items_export']);
		print "</pre>";
		echo "================";*/
		//exit("dfgg");
		if(isset($newArr) && !empty($newArr)) {
			$productsAdded   = 0;
			$productsUpdated = 0;
			$total_products  = count($newArr['items_export']);
			//$cnt = 0;
			/*echo "<br/>";*/
			$startpoint = $this->checkStartpointIdExists();
			$isTrunkated = false;
			$sqlCheck = "select * from ".$table_name." where id in (select MAX(id) as id from " . $table_name." ) ";
        	$resCheck = $wpdb->get_results($sqlCheck);
			/*echo "<pre>";
			print_r($resCheck);*/
			if($resCheck[0]->startpoint){
				if( intval($resCheck[0]->startpoint) >= intval($total_products) ) {
					$this->dropStartpointtable();
					$isTrunkated = true;
				}
			}
			//All products sync then truncate table
			/*if($total_products == $startpoint) { //echo "stop";
				$del = $this->dropStartpointtable();
			}*/	
			if($isTrunkated == false) {
				$startpo = $this->insertStartpointId($startpoint);	
			}
			$start = $startpoint;
			$elementsPerPage = 10;
			$elements = array_slice($newArr['items_export'], $start, $elementsPerPage);
			/*echo "<pre>";
			print_r($elements);
			exit("dsdsada");*/
			if(isset($elements) && !empty($elements))
			{
				foreach ($elements as $key => $product)
				{	
					$productcheck = $this->checkProductByMetaData('_id_code', $product['id_code']);
					if($product['Locked_item'] == 'False')
						{
							$show_product = 'Active';
						}
						else
						{
							$show_product = 'Inactive';
						}
					if($productcheck == false) {
						$post_id = $this->create_products($product);
						$productsAdded++;
						$message = array(
							'POSTID' => $post_id,
							'SKU' => $product['id_code'],
							'Quantity' => $product['balances'],
							'Price' => $product['Sale_Price'],
							'Locked Iteam' => $show_product,
							'Status' => 'Product Inserted',
						);
					} else {
						$message = array(
							'POSTID' => $post_id,
							'SKU' => $product['id_code'],
							'Quantity' => $product['balances'],
							'Price' => $product['Sale_Price'],
							'Locked Iteam' => $show_product,
							'Status' => 'Product Updated',
						);
						$post_id = $productcheck;
						$productsUpdated++;
					}
					$this->UpdateProductMetas($post_id, $product);				
					$this->custom_logs($message);
					/*if(!$this->checkProductByMetaData('_id_Barcod', $val1['id_Barcod'])){
						$post_id = $this->create_products($val1);
						$productsAdded++;
					}else{
						$post_id = $this->checkProductByMetaData('_id_Barcod', $val1['id_Barcod']);
						$productsUpdated++;
					}
					$this->UpdateProductMetas($post_id, $val1);*/
					/**
					 * map with category
					 * if we create category and subcategory through api then below code used
					 *
					 **/
					$terms = array();
					if( isset($product['id_Department']) && !empty($product['id_Department']) ) {
						$parent_term_id = $this->checkIftermExistsByMetaValue('id_Departments', $product['id_Department'] );
						if(!empty($parent_term_id)) {
							$terms[] = $parent_term_id;
						}
					}
					if( isset($product['id_Group']) && !empty($product['id_Group']) ) {
						$child_term_id = $this->checkIftermExistsByMetaValue('id_Group', $product['id_Group'] );
						if(!empty($child_term_id)) {
							$terms[] = $child_term_id;
						}
					}
					if( is_array($terms) && !empty($terms) ) {
						wp_set_post_terms( $post_id, $terms, 'product_cat' );	
					}
					//map with image
					if(get_option('optimum_sync_api_images') == 'yes') { 
						if(isset($product['name_picture']) && !empty($product['name_picture'])) {
							//$url = "http://ok-rsoft.com/Proxy/Optimum_Orders/Content/ProductsImages/1000/".$product['name_picture'];
							$url = "http://62.219.68.121/Optimum_Orders/Content/ProductsImages/2200/".$product['name_picture'];
							if( !get_the_post_thumbnail_url($post_id) ) {
								$this->Generate_Featured_Image($url, $post_id);	
							}
						}
					}
				}
				/*$startpoint = $this->insertStartpointId( $start_slice, $end_slice );*/
				die(json_encode( array('result' => true, 'msg' => $productsAdded.' products created and '.$productsUpdated.' products updated') ));
			} else {
				die(json_encode( array('result' => false, 'msg' => 'No any product found.All products Sync,please check it.') ));		
			}
		}
		exit(json_encode( array('result' => false, 'msg' => 'Oops. Something went wrong. Please try again later.') ));
	}
	public function custom_logs($message) {
		if(is_array($message)) {
	        $text = json_encode($message);
	        $text .= PHP_EOL;
	    }
		$path = plugin_dir_path(__FILE__) . 'log/log_'.date('Y-m-d').'.txt';
        if( !file_exists($path) ) {
        	fopen($path, 'w') or die("Can't create file");
        }
        $current = file_get_contents($path);
        $current .= $text;
		/*$file = fopen($path, "w");*/
		file_put_contents($path, $current) or die('failed to put');
		/*fclose($file); */
	}
	public function getProductsCategory() {
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "http://myoctopus.co.il/octopus_web/Web_service/WebService_Order.asmx/Departments_ordrs_export?id_Customer_Optimum=".$this->customer_Optimum."&enter_user_Optimum=".$this->user_Optimum."&enter_pass_Optimum=".$this->pass_Optimum,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING       => "",
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => "GET",
		//  CURLOPT_POSTFIELDS => $dataXml,
		  CURLOPT_HTTPHEADER => array(
		    "Content-Type: text/xml"
		  ),
		));
		$response = curl_exec($curl);
		curl_close($curl);
		$arrData = simplexml_load_string($response) or die("Error: Cannot create object");
		$con = json_encode($arrData); 
		$newArr = json_decode($con, true);
		/*echo "<pre>";
		print_r($newArr['Departments_export']);
		exit("productcate");*/
		if( isset($newArr) && !empty($newArr) ) {
			foreach ($newArr['Departments_export'] as $key => $value) {
				// insert product_cat
				$tax_insert_id = wp_insert_term( $value['name_Departments'],'product_cat' );
				# add term meta field
				if( isset($tax_insert_id->error_data['term_exists']) && !empty($tax_insert_id->error_data['term_exists']) ) {
					$term_id = $tax_insert_id->error_data['term_exists'];
					update_term_meta( $term_id, "id_Departments", $value['id_Departments'] );
				} else {
					add_term_meta( $tax_insert_id['term_taxonomy_id'], "id_Departments", $value['id_Departments'], false );	
				}
			}
			echo json_encode( array('result' => true, 'msg' => 'products category created.') );
			exit();
		}
	}
	public function getProductsSubCategory() { 
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  	CURLOPT_URL => "http://myoctopus.co.il/octopus_web/Web_service/WebService_Order.asmx/Grups_ordrs_export?id_Customer_Optimum=".$this->customer_Optimum."&enter_user_Optimum=".$this->user_Optimum."&enter_pass_Optimum=".$this->pass_Optimum,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING       => "",
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => "GET",
		    //CURLOPT_POSTFIELDS => $dataXml,
		    CURLOPT_HTTPHEADER => array(
		       "Content-Type: text/xml"
		    ),
		));
		$response = curl_exec($curl);
		curl_close($curl);
		$arrData = simplexml_load_string($response) or die("Error: Cannot create object");
		$con = json_encode($arrData); 
		$newArr = json_decode($con, true);
		/*echo "<pre>";
		print_r($newArr);
		exit("productsubcate");*/
		if( isset($newArr) && !empty($newArr) ) {
			foreach ($newArr['Groups_export'] as $key => $value) {
				$parent_term_id = $this->checkIftermExistsByMetaValue('id_Departments', $value['Attribution_Department']);
				if( isset($parent_term_id) && !empty($parent_term_id) ) {
					$term_id = wp_insert_term( $value['name_Group'], 'product_cat', array('parent' => $parent_term_id) );
					if( isset($term_id->error_data['term_exists']) && !empty($term_id->error_data['term_exists']) ) {
						$uterm_id = $term_id->error_data['term_exists'];
						update_term_meta( $uterm_id, "id_Group", $value['id_Group'] );
					} else {
						add_term_meta( $term_id['term_taxonomy_id'], "id_Group", $value['id_Group'], false );	
					}
				}
			}
		}
		echo json_encode( array('result' => true, 'msg' => 'products Sub category created.') );
		exit();
	}
	public function checkIftermExistsByMetaValue($meta_key = '', $meta_value = '') {
		if($meta_key != '' && $meta_value != '') {
			$args = array(
			    'taxonomy'   => 'product_cat',
			    'hide_empty' => false,
			    'meta_query' => array(
			         array(
			            'key'       => $meta_key,
			            'value'     => $meta_value,
			            'compare'   => '='
			         )
			    )
			);
			$terms = get_terms( $args );
			/*echo "<pre>";
			print_r($terms);
			exit("test11");*/
			if(!empty($terms)){
				return $terms[0]->term_id;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	public function activate(){
		global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        if ($wpdb->get_var("show tables like '" . $wpdb->prefix . $this->optimum_api_table . "'") != $wpdb->prefix . $this->optimum_api_table)
        {
            $sql = "CREATE TABLE " . $wpdb->prefix . $this->optimum_api_table . " (
				`id` mediumint(9) NOT NULL AUTO_INCREMENT,
				`startpoint` mediumint(9) NOT NULL,
				PRIMARY KEY (id)
				)$charset_collate;";
            require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
	}
	public function remove_database(){
		global $wpdb;
        $table_name = $wpdb->prefix . $this->optimum_api_table;
        $sql = "DROP TABLE IF EXISTS $table_name";
        $wpdb->query($sql);
	}
	public function insertStartpointId($startpoint) {
		global $wpdb;
		$table_name = $wpdb->prefix . $this->optimum_api_table;
        $sql = $wpdb->prepare("INSERT INTO  `$table_name` VALUES (NULL, %s)", $startpoint);
        $sqlres = $wpdb->query($sql);
        /*echo "<pre>";
        print_r($sqlres);
        exit("insert11");*/
    }
    public function checkStartpointIdExists() {
    	global $wpdb;
    	$table_name = $wpdb->prefix . $this->optimum_api_table;
		//$sqlCheck = "select MAX(id) as id,MAX(page) as page from " . $table_name;
		//$sqlCheck = "select count(id) as total_row, MAX(startpoint) startpoint from " . $table_name;
        $sqlCheck = "select MAX(id) as id from " . $table_name;
        $resCheck = $wpdb->get_results($sqlCheck);
        /*$resss = $wpdb->get_results($sqlCheck);
        $finalArry = array("total_row" => $resss[0]->total_row , "startpoint" => $resss[0]->startpoint);
        return $resss ? $finalArry : array(); */
        /*echo "<pre>";
        print_r($resCheck);
        echo $rowcount = $resCheck->num_rows;
    	exit("test");*/
        if( empty($resCheck[0]->id) ) {
			//$page = 30;
			return $startpoint = 0;
			//return ( array('startpoint' => 0, 'page' => $page) );
		} else {
			return $resCheck[0]->id * 13;
			/*$startPoints = $resCheck[0]->startpoint + $this->offset;
			$page = $resCheck[0]->page + $this->offset;
			return ( array( 'startpoint' => $startPoints , 'page' => $page) );*/
		}
	}
	public function dropStartpointtable() {
		global $wpdb;
		$table_name = $wpdb->prefix . $this->optimum_api_table;
		$sqlquery = "TRUNCATE TABLE " . $table_name;
		$delres = $wpdb->query($sqlquery);
	}
	public function create_products($product) {
		$post_id = wp_insert_post( array(
			'post_title'  => $product['name_item'],
			'post_status' => 'publish',
			'post_type'   => "product",
		) );
		wp_set_object_terms( $post_id, 'simple', 'product_type' );
		return $post_id;
	}
	public function checkProductByMetaData($meta_key = '', $meta_value = ''){
		if($meta_key != '' && $meta_value != '') {
			$args = array(
				'post_type' => 'product',
				'post_status' => array('publish', 'trash', 'private'),
			    'meta_query' => array(
			        array(
			            'key' => $meta_key,
			            'value' => $meta_value,
			            'compare' => '=',
			        )
			    )
			);
			$query = new WP_Query($args);
			$results = $query->posts;
			if(!empty($results)){
				return $results[0]->ID;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	public function UpdateProductMetas($post_id, $product) {
		update_post_meta( $post_id, '_visibility', 'visible' );
		update_post_meta( $post_id, '_stock_status', 'instock');
		update_post_meta( $post_id, 'total_sales', '0' );
		update_post_meta( $post_id, '_downloadable', 'no' );
		update_post_meta( $post_id, '_virtual', 'no' );
		update_post_meta( $post_id, '_regular_price', $product['Sale_Price'] );
		update_post_meta( $post_id, '_sale_price', '' );
		update_post_meta( $post_id, '_purchase_note', '' );
		update_post_meta( $post_id, '_featured', 'no' );
		update_post_meta( $post_id, '_weight', '' );
		update_post_meta( $post_id, '_length', '' );
		update_post_meta( $post_id, '_width', '' );
		update_post_meta( $post_id, '_height', '' );
		/*if( is_array($product['id_Barcod']) && empty($product['id_Barcod']) ) {
			update_post_meta( $post_id, '_sku', $post_id );	
		} else {
			update_post_meta( $post_id, '_sku', $product['id_Barcod'] );	
		}*/
		if( !empty($product['id_code']) ) {
			update_post_meta( $post_id, '_sku', $product['id_code'] );	
		}
		update_post_meta( $post_id, '_product_attributes', array() );
		update_post_meta( $post_id, '_sale_price_dates_from', '' );
		update_post_meta( $post_id, '_sale_price_dates_to', '' );
		update_post_meta( $post_id, '_price', $product['Sale_Price'] );
		update_post_meta( $post_id, '_sold_individually', '' );
		update_post_meta( $post_id, '_manage_stock', 'yes' );
		update_post_meta( $post_id, '_backorders', 'no' );
		
		if(get_option('optimum_sync_api_qty') == 'yes') {
			update_post_meta( $post_id, '_stock', $product['balances'] );	
		}
		
		update_post_meta( $post_id, '_id_code', $product['id_code'] );						
		update_post_meta( $post_id, '_id_Department', $product['id_Department'] );						
		update_post_meta( $post_id, '_id_Group', $product['id_Group'] );						
		update_post_meta( $post_id, '_Limited_quantity', $product['Limited_quantity'] );						
		update_post_meta( $post_id, '_Packing_quantity', $product['Packing_quantity'] );						
		update_post_meta( $post_id, '_measure', $product['measure'] );
		update_post_meta( $post_id, '_memo', $product['memo'] );
		update_post_meta( $post_id, '_English_name', $product['English_name'] );
		update_post_meta( $post_id, '_Creation', $product['Creation'] );
		update_post_meta( $post_id, '_update_date', $product['update_date'] );
		update_post_meta( $post_id, '_Locked_item', $product['Locked_item'] );
		if($product['Locked_item'] == 'True')
		{
			wp_update_post( array( 'ID' => $post_id, 'post_status' => 'private' ) );
		}
		update_post_meta( $post_id, '_alut1', $product['alut1'] );
		update_post_meta( $post_id, '_no_tax', $product['no_tax'] );
		update_post_meta( $post_id, '_mchir_neto', $product['mchir_neto'] );
		update_post_meta( $post_id, '_anchat_mchira', $product['anchat_mchira'] );
		update_post_meta( $post_id, '_balances', $product['balances'] );
		update_post_meta( $post_id, '_id_Sub_Group', $product['id_Sub_Group'] );
		update_post_meta( $post_id, '_id_Barcod', $product['id_Barcod'] );
		update_post_meta( $post_id, '_id_kod_nosaf', $product['id_kod_nosaf'] );
		update_post_meta( $post_id, '_id_kod_aviv', $product['id_kod_aviv'] );
	}
	function mwe_get_formatted_shipping_name_and_address($user_id) {
	    $address['fname'] = get_user_meta( $user_id, 'shipping_first_name', true );
	    $address['lname'] = get_user_meta( $user_id, 'shipping_last_name', true );
	    $address['company'] = get_user_meta( $user_id, 'shipping_company', true );
	    $address['address1'] = get_user_meta( $user_id, 'shipping_address_1', true );
	    $address['address2'] = get_user_meta( $user_id, 'shipping_address_2', true );
	    $address['city'] = get_user_meta( $user_id, 'shipping_city', true );
	    $address['state'] = get_user_meta( $user_id, 'shipping_state', true );
	    $address['postcode'] = get_user_meta( $user_id, 'shipping_postcode', true );
	    $address['country'] = get_user_meta( $user_id, 'shipping_country', true );
	    $address['phone'] = get_user_meta( $user_id, 'billing_phone', true );
	    $address['email'] = get_user_meta( $user_id, 'billing_email', true );
	    return $address;
	}

	public function getorder_ids() {
		/*error_reporting(E_ALL);
		ini_set('display_errors', 1);*/
		global $wpdb;
		$args = array(
		    'limit'        => -1, // Query all orders
		    'return' 	   => 'ids',
		    'meta_key'     => 'has_entry_in_optimum',
		    'meta_value'   => '',
		    'meta_compare' => 'NOT EXISTS',
		    'status' 	   => array('wc-processing')
		);
		$orders_ids_arr = wc_get_orders($args);
		/*echo "<pre>";
		print_r($orders_ids_arr);
		exit("dev");*/
		foreach ($orders_ids_arr as $or_id) {
			$this->getProductUpdateOrderShort($or_id);
		}
	}

	public function getProductUpdateOrderShort($order_id) {
		// $this->getorder_ids();
		if(isset($_GET['order_id']) && $_GET['order_id'] != ''){
			$order_id = $_GET['order_id'];
		}else{	
			if(get_post_meta($order_id, 'has_entry_in_optimum', true) == 'yes'){ //echo "teststst123";
				return;
			}
		}
		/*echo $order_id;
		exit("Fdfsdfs");*/
		$cardToken = $cardMask = $cardExp = '';
		if(get_option('optimum_sync_token_enabled') == 'yes'){
			$order_note = wc_get_order_notes([
			   'order_id' => $order_id,
			   'type' => '',
			]);
			if ( $order_note ) {
				if(count($order_note) != 0) {
					$count = count($order_note);
					$lastInd = $count - 1;
					//$bendaData = reset($order_note);
					$bendaData = $order_note[$lastInd];
					$bdArr = explode(': ',$bendaData->content);
					if(isset($bdArr[1])) {
						$data = $bdArr[1];
						$new = str_replace(PHP_EOL, '', $data);
						$new = str_replace(' ', '', $new);
						$new = htmlspecialchars_decode($new);
						/*$userData['cardToken'] = $this->get_string_between($new, '[cardToken]=>', '[cardExp]');*/
						$cardToken = $this->get_string_between($new, '[cardToken]=>', '[cardExp]');
						/*$userData['cardExp'] = $this->get_string_between($new, '[cardExp]=>', '[personalId]');*/
						$cardExp = $this->get_string_between($new, '[cardExp]=>', '[personalId]');
						/*$userData['cardMask'] = $this->get_string_between($new, '[cardMask]=>', '[txId]');	
						$userData['cardMask'] = substr($userData['cardMask'], -4);*/
						$cardMask = substr($this->get_string_between($new, '[cardMask]=>', '[txId]'), -4);
 					}	
				}
			} else {
				return;
			}
		}
		
		$data = array();
		$order = wc_get_order( $order_id );
		/*echo "<pre>";
		print_r($order);*/

		// get delivery time slot
		$d_time_slot = get_post_meta($order_id, '_orddd_time_slot', true);
		//get delivery location
		$d_location = get_post_meta($order_id, 'עיר , שכונה', true);
		// get delivery date
		$d_date = get_post_meta($order_id, 'תאריך רצוי למשלוח', true);
		// get delivery price
		$delivery_charge = get_post_meta($order_id, '_total_delivery_charges', true);

		$phoneno = trim($order->get_billing_phone());
		if($phoneno != '' && intval($phoneno) > 0) { 
			$userdata = $this->find_customer($phoneno);
			if(!empty($userdata['find_Users_Customers_class']['id_user'])) { 
				$customername = $userdata['find_Users_Customers_class']['name_User'];
				$customer = $userdata['find_Users_Customers_class']['id_User'];
			} else {
				$customer = $order_id;	
			}
		} 
		
		$w_customer = $order->get_user_id();
		if($w_customer != '' && intval($w_customer) > 0){
			$custInfo = $this->mwe_get_formatted_shipping_name_and_address($w_customer);
		}else{
			//$customer = $order_id;
			$custInfo['fname'] = $order->get_billing_first_name();
			$custInfo['lname'] = $order->get_billing_last_name();
			$custInfo['company'] = $order->get_billing_company();
			$custInfo['address1'] = $order->get_billing_address_1();
			$custInfo['address2'] = $order->get_billing_address_2();
			$custInfo['city'] = $order->get_billing_city();
			$custInfo['state'] = $order->get_billing_state();
			$custInfo['postcode'] = $order->get_billing_postcode();
			$custInfo['country'] = $order->get_billing_country();
			$custInfo['email'] = $order->get_billing_email();
			$custInfo['phone'] = $order->get_billing_phone();
		}
		$customername = $custInfo['fname'].' '.$custInfo['lname'];

		//echo $customer;exit;
		$dataCust['id_Customer_Optimum'] = $this->customer_Optimum;
		$dataCust['enter_user_Optimum'] = $this->user_Optimum;
		$dataCust['enter_pass_Optimum'] = $this->pass_Optimum;
		$dataCust['id_Customer'] = "123".$customer;
		$dataCust['name_Customer'] = $customername;
		$dataCust['Authorized_dealer'] = '';
		$dataCust['Street'] = $custInfo['address1'];
		/*$dataCust['City'] = $custInfo['city'];*/
		$dataCust['City'] = !empty($d_location) ? $d_location : '';
		$dataCust['mikud'] = '';
		$dataCust['Phone1'] = $custInfo['phone'];
		$dataCust['Phone2'] = !empty(get_post_meta($order_id, 'addphone', true)) ? get_post_meta($order_id, 'addphone', true) : '';
		$dataCust['fax'] = '';
		$dataCust['free_tax'] = '';
		$dataCust['Contact'] = '';
		$dataCust['memo1'] = '';
		$dataCust['memo2'] = '';
		$dataCust['memo3'] = '';
		$dataCust['Email'] = $custInfo['email'];
		$dataCust['Type'] = '';
		$dataCust['nm_token_card'] = $cardToken;
		$dataCust['nm_card'] = $cardMask;
		$dataCust['tokef_card'] = $cardExp;
		$dataCust['enter_user_Customer'] = '';
		$dataCust['enter_pass_Customer'] = '';
		$dataCust['cvv_card'] = '';
		$dataCust['nm_apartment'] = !empty(get_post_meta($order_id, 'appartment', true)) ? get_post_meta($order_id, 'appartment', true) : '';
		$dataCust['nm_intercom'] = !empty(get_post_meta($order_id, 'intercom', true)) ? get_post_meta($order_id, 'intercom', true) : '';
		$dataCust['floor'] = !empty(get_post_meta($order_id, 'flor', true)) ? get_post_meta($order_id, 'flor', true) : '';
		$dataCust['tranId'] = !empty(get_post_meta($order_id, 'CG Transaction Id', true)) ? get_post_meta($order_id, 'CG Transaction Id', true) : '';
		$dataCust['Address_Number'] = '';
		
		$this->add_log_mine(json_encode($dataCust).PHP_EOL.'Update_Customers_orders_input'.PHP_EOL);
		$req = $this->Update_Customers_orders_input($dataCust);
		
		$data = array();
		$order      = wc_get_order( $order_id );
		$order_data = $order->get_data();
		$order      = new WC_Order( $order_id );
		$items      = $order->get_items();
		//$line       = 0;
		$line       = 1;
		$orderTotal = 0;
		
		foreach ($order->get_items() as $item_id => $item_data) { 
			$line++;
			$data = [];
			$product                     = $item_data->get_product();
			$pr_id                       = $item_data->get_product_id();
			$data['id_Customer_Optimum'] = $this->customer_Optimum;
			$data['enter_user_Optimum']  = $this->user_Optimum;
			$data['enter_pass_Optimum']  = $this->pass_Optimum;
			$data['store']               = '';
			$data['ID_order']            = $order_id;
			//$data['date_order']          = $order_data['date_created']->date('Y-m-d');
			$data['date_order']          = $d_date;
			$data['id_Customer']         = "123".$customer;
			$data['line']                = $line;
			$data['id_code']             = get_post_meta( $pr_id, '_sku', true);
			//$data['id_code_2'] = $pr_id;
			//$data['order_total'] = $order->get_total();
			$data['name_item']         = $product->get_name();
			$data['quantity']          = $item_data->get_quantity();
			$data['memo']              = '';
			$data['measure']           = (!is_array(get_post_meta( $pr_id, '_measure', true))) ? get_post_meta( $pr_id, '_measure', true) : '';
			$data['Sale_Price']        = $product->get_price();
			$data['Discount_Percent']  = '';
			$data['total_Payment']     = $item_data->get_total();
			$orderTotal                += floatval($item_data->get_total());
			$data['situation']         = '';
			$data['quantity_Provided'] = '';
			$this->add_log_mine(json_encode($data).PHP_EOL.'Update_order_SHOROT_input'.PHP_EOL);
			$this->Update_order_SHOROT_input($data);
		}
		
		$product                     = $item_data->get_product();
		$pr_id                       = $item_data->get_product_id();
		$sdata['id_Customer_Optimum'] = $this->customer_Optimum;
		$sdata['enter_user_Optimum']  = $this->user_Optimum;
		$sdata['enter_pass_Optimum']  = $this->pass_Optimum;
		$sdata['store']               = '';
		$sdata['ID_order']            = $order_id;
		//$sdata['date_order']          = $order_data['date_created']->date('Y-m-d');
		$sdata['date_order']          = $d_date;
		$sdata['id_Customer']         = "123".$customer;
		$sdata['line']                = 1;
		$sdata['id_code']             = 1;
		//$data['id_code_2'] = $pr_id;
		//$data['order_total'] = $order->get_total();
		$sdata['name_item']         = "Shipping";
		$sdata['quantity']          = 1;
		$sdata['memo']              = '';
		$sdata['measure']           = '';
		$sdata['Sale_Price']        = number_format($delivery_charge, 2, '.', '');
		$sdata['Discount_Percent']  = '';
		$sdata['total_Payment']     = '';
		//$orderTotal                += floatval($item_data->get_total());
		$sdata['situation']         = '';
		$sdata['quantity_Provided'] = '';
		$this->add_log_mine(json_encode($sdata).PHP_EOL.'Update_order_SHOROT_input'.PHP_EOL);
		$this->Update_order_SHOROT_input($sdata);

		//$memoNote = "[CardToken] =>" .$userData['cardToken'].", [CardExp] =>". $userData['cardExp'] .", [cardMask] =>".$userData['cardMask'] ;
		$Kdata['id_Customer_Optimum'] = $this->customer_Optimum;
		$Kdata['enter_user_Optimum']  = $this->user_Optimum;
		$Kdata['enter_pass_Optimum']  = $this->pass_Optimum;
		$Kdata['Number_order']        = $order_id;
		//$Kdata['date_order']          = $order_data['date_created']->date('Y-m-d');
		$Kdata['date_order']          = $d_date;
		$Kdata['Round']               = 1;
		$Kdata['day']                 = '';
		$Kdata['name_User']           = $customername;
		$Kdata['number_line']         = $line;
		$Kdata['total_quantity']      = $order->get_item_count();
		$Kdata['total_Account']       = $orderTotal;
		$Kdata['memo']                = "";
		$Kdata['id_Customer']         = "123".$customer;
		$this->add_log_mine(json_encode($Kdata).PHP_EOL.'Update_order_KOTERT_input'.PHP_EOL);
		$this->add_log_mine('================================'.PHP_EOL);
		if($this->Update_order_KOTERT_input($Kdata)){
			update_post_meta($order_id, 'has_entry_in_optimum', 'yes');
		}
	}

	public function get_string_between($string, $start, $end){
		$string = ' ' . $string;
	    $ini = strpos($string, $start);
	    if ($ini == 0) return '';
	    $ini += strlen($start);
	    $len = strpos($string, $end, $ini) - $ini;
	    return substr($string, $ini, $len);
	}
	/*public function get_string_between($str, $starting_word, $ending_word) {
		$arr = explode($starting_word, $str);
		if (isset($arr[1])){ 
		    $arr = explode($ending_word, $arr[1]);
		    return $arr[0];
		}
		return ''; 
	} */

	public function find_customer($phoneno) {
		if($phoneno != NULL && !empty($phoneno)) {
			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => "http://myoctopus.co.il/octopus_web/Web_service/WebService_Order.asmx/find_Users_Customers?id_Customer_Optimum=".$this->customer_Optimum."&enter_user_Optimum=".$this->user_Optimum."&enter_pass_Optimum=".$this->pass_Optimum."&telefone=".$phoneno."&Authorized_dealer=",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_HTTPHEADER => array(
			    "cache-control: no-cache",
			    "postman-token: 29422b28-b6fd-993c-e812-ec8320c6eb9d"
			  ),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
				echo "cURL Error #:" . $err;
			} else {
				$arrData = simplexml_load_string($response) or die("Error: Cannot create object");
				$con     = json_encode($arrData);
				$newArr  = json_decode($con, true);
				return $newArr;
			}
		}
	}

	public function Update_Customers_orders_input($custData = NULL){
		if($custData != NULL && !empty($custData)){
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => "http://myoctopus.co.il/octopus_web/Web_service/WebService_Order.asmx/Update_Customers_orders_input",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => http_build_query($custData),
			  CURLOPT_HTTPHEADER => array(
			    "Content-Type: application/x-www-form-urlencoded"
			  ),
			));
			$response = curl_exec($curl);
			echo "Customer Update : </br>";
			echo $response.'</br>';
			$err = curl_error($curl);
			curl_close($curl);
			if(trim(strip_tags($response)) == 'Ok'){
				return true;
			}else{
				return false;				
			}
		}
	}
	public function Update_order_SHOROT_input($productData = NULL){
		if($productData != NULL && !empty($productData)){
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => "http://myoctopus.co.il/octopus_web/Web_service/WebService_Order.asmx/Update_order_SHOROT_input",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => http_build_query($productData),
			  CURLOPT_HTTPHEADER => array(
			    "Content-Type: application/x-www-form-urlencoded"
			  ),
			));
			$response = curl_exec($curl);
			echo "Shorot Order : <br/>";
			echo $response.'<br/>';
			$err = curl_error($curl);
			curl_close($curl);
			if(trim(strip_tags($response)) == 'Ok'){
				return true;
			}else{
				return false;				
			}
		}
	}
	public function Update_order_KOTERT_input($productData = NULL){
		if($productData != NULL && !empty($productData)){
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => "http://myoctopus.co.il/octopus_web/Web_service/WebService_Order.asmx/Update_order_KOTERT_input",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => http_build_query($productData),
			  CURLOPT_HTTPHEADER => array(
			    "Content-Type: application/x-www-form-urlencoded"
			  ),
			));
			$response = curl_exec($curl);
			echo "Kotert Order : <br/>";
			echo $response."</br>";
			$err = curl_error($curl);
			if(trim(strip_tags($response)) == 'Ok'){
				return true;
			}else{
				return false;				
			}
		}
	}

	public function add_log_mine($text) {
        $path = plugin_dir_path(__FILE__) . 'log/order_log_' . date('Ymd') . '.txt';
        if (!file_exists($path)) {
            $file = fopen($path, 'w') or die("Can't create file");
        }
        file_put_contents($path, $text, FILE_APPEND) or die('failed to put');
    }

	public function generateRequestContext($reqFor, $data){
		$contentdata = '';
		$contentstart = '<?xml version="1.0" encoding="utf-8"?>
					<soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
						<soap12:Body>';
		$contentstart .= '<'.$reqFor.' xmlns="http://microsoft.com/webservices/">';
		foreach ($data as $key => $value) {
			$contentdata .='<'.$key.'>'.$value.'</'.$key.'>';
		}
		$contentend = '</'.$reqFor.'></soap12:Body></soap12:Envelope>';
		return $contentstart . $contentdata . $contentend;
	}
	public function Generate_Featured_Image( $image_url, $post_id  ){
	    $upload_dir = wp_upload_dir();
	    $image_data = file_get_contents($image_url);
	    $filename = basename($image_url);
	    if(wp_mkdir_p($upload_dir['path']))
	      $file = $upload_dir['path'] . '/' . $filename;
	    else
	      $file = $upload_dir['basedir'] . '/' . $filename;
	    file_put_contents($file, $image_data);
	    $wp_filetype = wp_check_filetype($filename, null );
	    $attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title'     => sanitize_file_name($filename),
			'post_content'   => '',
			'post_status'    => 'inherit'
	    );
	    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
	    require_once(ABSPATH . 'wp-admin/includes/image.php');
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		$res1        = wp_update_attachment_metadata( $attach_id, $attach_data );
		$res2        = set_post_thumbnail( $post_id, $attach_id );
	}
	public function InitPlugin(){
		add_action('admin_menu', array($this, 'PluginMenu'));
	}
	public function PluginMenu(){
		add_menu_page('Optimum Api', 'Optimum Api', 'manage_options', 'optimum-api-ref', array($this, 'RenderPage'));
   		add_submenu_page('optimum-api-ref', 'Settings', 'Settings', 'manage_options', 'optimum_api_settings', array($this, 'settings'));
	}
	public function RenderPage(){
		$this->checkWooCommerce(); ?>
		<div class='wrap'>
			<h2>Optimum API - Dashboard</h2>
		</div>
		<?php
	}
	public function settings(){
		$this->checkWooCommerce();
		$this->loadTemplate('settings');
	}
	public function checkWooCommerce(){
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if (! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			echo '<div class="error"><p><strong>Optimum API requires WooCommerce to be installed and active. You can download <a href="https://woocommerce.com/" target="_blank">WooCommerce</a> here.</strong></p></div>';
			die();
		}
	}
	public function loadTemplate($templateName = ''){
		if($templateName != ''){
			require_once($this->templatePath.$templateName.'.php');
		}
	}
	public function optimum_api_settings_init() {
		register_setting('optimum_api_settings_fg', 'optimum_api_product_quantity');
		register_setting('optimum_api_settings_fg', 'optimum_api_product_status');

		register_setting('optimum_sync_api_settings_fg', 'optimum_sync_api_enabled');
		register_setting('optimum_sync_api_settings_fg', 'optimum_sync_token_enabled');
		register_setting('optimum_sync_api_settings_fg', 'optimum_sync_api_qty');
		register_setting('optimum_sync_api_settings_fg', 'optimum_sync_api_products');
		register_setting('optimum_sync_api_settings_fg', 'optimum_sync_api_images');
	}
	public function update_product_post() {
		$qty = intval($_POST['qty']);
		$radioValue = $_POST['radioValue'];
	    $prod_id = $_POST['prod_id'];
	   	wp_update_post( array( 'ID' => $prod_id, 'post_status' => $radioValue ) );
		update_post_meta( $prod_id, '_manage_stock', 'yes' );
		if($qty !='' && $qty !='0') {
			wc_update_product_stock($prod_id,$qty);
		} else {
			wc_update_product_stock($prod_id,$qty);
			update_post_meta( $prod_id, '_manage_stock', 'no' );
		}
	}
}
$OptimumOctopus = new OptimumOctopus_WoocommerceSync;
$OptimumOctopus->InitPlugin();