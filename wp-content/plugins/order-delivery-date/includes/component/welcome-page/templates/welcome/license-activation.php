<?php
/**
 * Welcome page on activate or updation of the plugin
 */
?>
<form method="post">
<div class="wrap about-wrap">
    <?php echo $get_welcome_header; ?>

<ul class='<?php echo $plugin_prefix;?>_sub'>
	<li style='display:inline-block; font-size:13pt; padding:0 20px 0 20px;'><?php _e( 'License Key', $plugin_context );?></li> | 
	<li style='display:inline-block; font-size:13pt; padding:0 20px 0 20px; color:#b3b1b1;'><?php _e( "About $plugin_name", $plugin_context );?></li>
</ul>
<hr>
    <div>
        <p style="font-size:28pt; text-align:center;"><?php _e( 'License Key', $plugin_context );?></p>
        
        <p><?php _e( "Enter your $plugin_name License Key below. Your key unlocks access to automatic updates and support. You can find your key on the $purchase_history page on the $site_name site.", $plugin_context );?></p>
        
        <p>
            <input id='license_key' name='license_key' type='text' class='regular-text' style='font-size:14pt;' placeholder='Enter Your License Key' />
            <input type='hidden' id='<?php echo $plugin_prefix;?>_license_display' name='<?php echo $plugin_prefix;?>_license_display' value='1' />
            <input type='hidden' id='orddd_license_activate' name='orddd_license_activate' value="<?php _e( 'Activate License' ); ?>" />
            <?php wp_nonce_field( 'edd_sample_nonce', 'edd_sample_nonce' ); ?>
            </p>
         
        <p><button type='submit' class='button-primary'><?php _e( 'Next', $plugin_context );?></button></p>
    </div>
</div>
</form>