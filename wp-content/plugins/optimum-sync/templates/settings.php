<?php
$OptimumOctopus = new OptimumOctopus_WoocommerceSync;
?>
<link rel="stylesheet" href="<?php echo plugin_dir_url(__DIR__).'css/bootstrap.min.css'; ?>">
<link rel="stylesheet" href="<?php echo plugin_dir_url(__DIR__).'css/dataTables.bootstrap.min.css'; ?>">
<div>
	<h2>Optimum API - Settings</h2>
	<div class="wrap">
	<?php if( isset($_GET['settings-updated']) ) { ?>
		<div id="message" class="updated">
			<p><strong><?php _e('Congratulations settings are saved.') ?></strong></p>
		</div>
	<?php } ?>
	<form method="post" action="options.php">
		<?php settings_fields( 'optimum_sync_api_settings_fg' ); ?>
		<table>
			<tr>
				<th scope="row" align="left" ><label for="optimum_sync_token_enabled">Sync Token : </label></th>
				<td >
					<div style="display: inline-block; margin-right: 15px;" class="radio">
						<label for="optimum_sync_token_enabled_yes" style="vertical-align: top;"><input type="radio" id="optimum_sync_token_enabled_yes" name="optimum_sync_token_enabled" value="yes" <?php checked( 'yes', get_option('optimum_sync_token_enabled'), true ); ?> />Yes</label>
					</div>
					<div style="display: inline-block;" class="radio">
						<label for="optimum_sync_token_enabled_no" style="vertical-align: top;"><input type="radio" id="optimum_sync_token_enabled_no" name="optimum_sync_token_enabled" value="no" <?php checked( 'no', get_option('optimum_sync_token_enabled'), true ); ?> />No</label>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row" align="left" ><label for="optimum_sync_api_qty">Sync Qty : </label></th>
				<td >
					<div style="display: inline-block; margin-right: 15px;" class="radio">
						<label for="optimum_sync_api_qty_yes" style="vertical-align: top;"><input type="radio" id="optimum_sync_api_qty_yes" name="optimum_sync_api_qty" value="yes" <?php checked( 'yes', get_option('optimum_sync_api_qty'), true ); ?> />Yes</label>
					</div>
					<div style="display: inline-block;" class="radio">
						<label for="optimum_sync_api_qty_no" style="vertical-align: top;"><input type="radio" id="optimum_sync_api_qty_no" name="optimum_sync_api_qty" value="no" <?php checked( 'no', get_option('optimum_sync_api_qty'), true ); ?> />No</label>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row" align="left" ><label for="optimum_sync_api_products">Sync Products : </label></th>
				<td >
					<div style="display: inline-block; margin-right: 15px;" class="radio">
						<label for="optimum_sync_api_products_yes" style="vertical-align: top;"><input type="radio" id="optimum_sync_api_products_yes" name="optimum_sync_api_products" value="yes" <?php checked( 'yes', get_option('optimum_sync_api_products'), true ); ?> />Yes</label>
					</div>
					<div style="display: inline-block;" class="radio">
						<label for="optimum_sync_api_products_no" style="vertical-align: top;"><input type="radio" id="optimum_sync_api_products_no" name="optimum_sync_api_products" value="no" <?php checked( 'no', get_option('optimum_sync_api_products'), true ); ?> />No</label>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row" align="left" ><label for="optimum_sync_api_images">Sync Images : </label></th>
				<td >
					<div style="display: inline-block; margin-right: 15px;" class="radio">
						<label for="optimum_sync_api_images_yes" style="vertical-align: top;"><input type="radio" id="optimum_sync_api_images_yes" name="optimum_sync_api_images" value="yes" <?php checked( 'yes', get_option('optimum_sync_api_images'), true ); ?> />Yes</label>
					</div>
					<div style="display: inline-block;" class="radio">
						<label for="optimum_sync_api_images_no" style="vertical-align: top;"><input type="radio" id="optimum_sync_api_images_no" name="optimum_sync_api_images" value="no" <?php checked( 'no', get_option('optimum_sync_api_images'), true ); ?> />No</label>
					</div>
				</td>
			</tr>
			
		</table>
			<?php wp_nonce_field( 'optimumwoo-settings-save', 'optimum_sync_api_settings_fg' ); ?>
			<?php submit_button(); ?>	
	</form>


	
		<?php /*if( isset($_GET['settings-updated']) ) { ?>
			<div id="message" class="updated">
				<p><strong><?php _e('Congratulations settings are saved.') ?></strong></p>
			</div>
		<?php }*/ ?>
		<form method="post" id="productlist" action="#">
			<?php settings_fields( 'optimum_api_settings_fg' ); ?>
			<table id="optimumTable" class="table table-striped table-bordered" width="100%">
  				<thead>
    				<tr>
      					<th scope="col" >#</th>
      					<th scope="col" class="col-sm-5">Product Name</th>
      					<th scope="col" class="col-sm-2">Product Quantity</th>
     					<th scope="col" class="col-sm-2">Product Publish</th>
     					<th scope="col" class="col-sm-3">Save</th>
    				</tr>
				</thead>
				<tbody>
					<?php
						$args = array(
							'post_type' => 'product',
							'posts_per_page' => -1,
						);
						$loop = new WP_Query( $args );
						$i = 1;
						while ( $loop->have_posts() ) : $loop->the_post();
        					global $product;?>
        					<tr>
						    	<td><?php echo $i;?></td>
						    	<td><?php echo get_the_title();?></td>
						    	<td>
						    		<div>
						    			<?php $optimum_product_quantity = get_post_meta( get_the_ID(), '_stock', true ); ?>
										<input type="number" min='0' id="optimum_api_product_quantity_<?php echo  get_the_ID(); ?>" name="optimum_api_product_quantity_<?php echo  get_the_ID(); ?>" value="<?php echo $optimum_product_quantity != '' ? intval($optimum_product_quantity) : '' ?>" oninput="validity.valid||(value='');" />
									</div>
						    	</td>
						    	<?php $product_status  = get_post( get_the_ID(), 'post_status', true ); ?>
						    	<td>
						    		<div style="display: inline-block; margin-right: 15px;" class="radio">
										<label for="optimum_api_product_yes<?php echo  get_the_ID(); ?>" style="vertical-align: top;"><input type="radio" id="optimum_api_product_yes<?php echo  get_the_ID(); ?>" name="optimum_api_product_status_<?php echo  get_the_ID(); ?>" value="publish" <?php checked( 'publish', $product_status->post_status, true ); ?> />Yes</label>
									</div>
									<div style="display: inline-block;" class="radio">
										<label for="optimum_api_product_no<?php echo  get_the_ID(); ?>" style="vertical-align: top;"><input type="radio" id="optimum_api_product_no<?php echo  get_the_ID(); ?>" name="optimum_api_product_status_<?php echo  get_the_ID(); ?>" value="private" <?php checked( 'private', $product_status->post_status, true ); ?> />No</label>
									</div>
								</td>
						    	<td><input type="button" name="submit" id="product_submit_<?php echo get_the_ID(); ?>" onclick="product_submit(<?php echo get_the_ID(); ?>);" class="button button-primary"  value="Save Changes">
						    		<div style="display:none;" id="my_title<?php echo  get_the_ID(); ?>"></div>
						    	</td>
						    </tr>
						<?php
						$i++;	
    					endwhile;
    					wp_reset_query();
					?>
    			</tbody>
			</table>
			<?php wp_nonce_field( 'noptowoo-settings-save', 'optimum_api_settings_fg' ); ?>
		</form>
	</div>
</div>
<script src="<?php echo plugin_dir_url(__DIR__).'js/jquery.dataTables.min.js'; ?>"></script>
<script src="<?php echo plugin_dir_url(__DIR__).'js/dataTables.bootstrap4.min.js'; ?>"></script>
<script>
jQuery(document).ready(function() {
   jQuery('#optimumTable').DataTable({
   	"bSort" : false,
   	"pagingType": "full_numbers",
   	"lengthMenu" : [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
   	"bAutoWidth": false
   });
});

function product_submit(prod_id) {
	var qty = parseInt(jQuery('#optimum_api_product_quantity_'+prod_id).val());
	var radioValue = jQuery("input[name='optimum_api_product_status_"+prod_id+"']:checked").val();
	var formData = {
    	action: 'update_product',
    	qty: qty,
    	radioValue: radioValue,
    	prod_id: prod_id,
    };

    request = jQuery.ajax({
   		type: 'POST',
    	url: '<?php echo admin_url('admin-ajax.php'); ?>',
    	data: formData,
        success : function( response ) {
        	var  message = "<span style='color:green;'>Success! Product Updated.</span>";
        	jQuery("#my_title"+prod_id).show();
			jQuery("#my_title"+prod_id).html(message);
			setTimeout(function() { jQuery("#my_title"+prod_id).hide(); }, 5000);
		},
		error: function(jqXHR, textStatus, errorThrown){
            jQuery("#my_title"+prod_id).show();
			jQuery("#my_title"+prod_id).html('<span style="color:red;">Error! Please try again.</span>');
			setTimeout(function() { jQuery("#my_title"+prod_id).hide(); }, 5000);
        }
	});
}
</script>