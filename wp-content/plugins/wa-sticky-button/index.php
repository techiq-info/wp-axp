<?php defined('ABSPATH') or die('No direct access allowed!');
/*
Plugin Name: WP Sticky Button - Click to Chat
Plugin URI: http://okapitech.in/wordpress-plugin-wa-sticky-button
Description: Display the beautiful WhatsApp Sticky Button on the WordPress frontend.
Version: 1.2
Author: Faraz Ur Rehman Quazi
Author URI: https://profiles.wordpress.org/farazquazi
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wa-sticky-button
*/

define('OKAPI_WASB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('OKAPI_WASB_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('OKAPI_WASB_DEFAULT_IMG', OKAPI_WASB_PLUGIN_URL.'assets/default.png');

if(!class_exists('OkapiWASB_Mobile_Detect')){
    require_once(OKAPI_WASB_PLUGIN_PATH.'classes/OkapiWASB_Mobile_Detect.php');
}

register_activation_hook(__FILE__, function(){
	/* Silence is Golden */
});

register_deactivation_hook(__FILE__, function(){
	/* Silence is Golden */
});

add_action('wp_enqueue_scripts', function(){
	$activate = get_option('okapi_wasb_activate', 2);
	if($activate == 1){
		$on_mobile = get_option('okapi_wasb_display_on_mobile', 'TRUE');
		$on_tablet = get_option('okapi_wasb_display_on_tablet', 'TRUE');
		$on_desktop = get_option('okapi_wasb_display_on_desktop', 'TRUE');
		$obj = new OkapiWASB_Mobile_Detect;
		$is_mobile = $obj->isMobile();
		$is_tablet = $obj->isTablet();
		if(($on_mobile == 'TRUE' && $is_mobile === TRUE && $is_tablet === FALSE) || ($on_tablet == 'TRUE' && $is_tablet === TRUE) || ($on_desktop == 'TRUE')){
			load_template(OKAPI_WASB_PLUGIN_PATH.'views/wasb-button.php');
		}
	}
}); 

add_action('admin_enqueue_scripts', function(){
	wp_enqueue_media();
}); 

add_action('admin_menu', function(){
	add_menu_page(
		'WhatsApp Sticky Button - Settings',
		'WhatsApp',
		'manage_options',
		'okapi-wasb-settings',
		'okapi_wasb_page_settings',
		'dashicons-format-quote',
		75
	);
});

function okapi_wasb_page_settings(){
	load_template(OKAPI_WASB_PLUGIN_PATH.'views/settings.php');
}

add_action('wp_ajax_okapi_wasb_save_settings','okapi_wasb_save_settings');
add_action('wp_ajax_nopriv_okapi_wasb_save_settings','okapi_wasb_save_settings');
function okapi_wasb_save_settings(){
    update_option('okapi_wasb_activate', sanitize_text_field($_POST['activate']), TRUE);
    update_option('okapi_wasb_display_on_mobile', sanitize_text_field($_POST['display_on_mobile']), TRUE);
    update_option('okapi_wasb_display_on_tablet', sanitize_text_field($_POST['display_on_tablet']), TRUE);
    update_option('okapi_wasb_display_on_desktop', sanitize_text_field($_POST['display_on_desktop']), TRUE);
    update_option('okapi_wasb_position', sanitize_text_field($_POST['position']), TRUE);
    update_option('okapi_wasb_number', sanitize_text_field($_POST['number']), TRUE);
    update_option('okapi_wasb_msg', sanitize_text_field($_POST['msg']), TRUE);
    update_option('okapi_wasb_width', sanitize_text_field($_POST['width']), TRUE);
    update_option('okapi_wasb_height', sanitize_text_field($_POST['height']), TRUE);
    update_option('okapi_wasb_margin', sanitize_text_field($_POST['margin']), TRUE);
    update_option('okapi_wasb_icon_type', sanitize_text_field($_POST['icon_type']), TRUE);
    update_option('okapi_wasb_icon_id', sanitize_text_field($_POST['icon_id']), TRUE);
	echo TRUE;
	exit();
}

/* End of Plugin File */