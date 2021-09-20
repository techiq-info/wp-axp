<?php
/**
 *
 * Plugin Name: Dynamic Visibility for Elementor
 * Description: Visibility rules for widgets, rows, columns or sections with advanced conditions.
 * Plugin URI: https://www.dynamic.ooo/widget/dynamic-visibility/?utm_source=wp-plugins&utm_campaign=plugin-uri&utm_medium=wp-dash
 * Version: 4.0.1
 * Author: Dynamic.ooo
 * Author URI: https://www.dynamic.ooo/
 * Text Domain: dynamic-visibility-for-elementor
 * Requires at least: 5.2
 * Requires PHP: 5.6
 * License: GPL-3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Elementor tested up to: 3.2.0
 * Elementor Pro tested up to: 3.2.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'DVE_VERSION', '4.0.1' );
define( 'DVE_TEXTDOMAIN', 'dynamic-visibility-for-elementor' );
define( 'DVE_ELEMENTOR_VERSION_REQUIRED', '2.6.0' );
define( 'DVE_URL', plugins_url( '/', __FILE__ ) );
define( 'DVE_PATH', plugin_dir_path( __FILE__ ) );
define( 'DVE_PLUGIN_BASE', plugin_basename( __FILE__ ) );
define( 'DVE__FILE__', __FILE__ );

/**
 * Load DVE
 *
 * Load the plugin after Elementor (and other plugins) are loaded.
 *
 * @since 1.0.0
 */
function dynamic_visibility_for_elementor_load() {
	// Load localization file
	load_plugin_textdomain( DVE_TEXTDOMAIN );

	// Notice if the Elementor is not active
	if ( ! did_action( 'elementor/loaded' ) ) {
		add_action( 'admin_notices', 'dynamic_visibility_for_elementor_fail_load' );
		return;
	}

	// Check required version
	if ( ! version_compare( ELEMENTOR_VERSION, DVE_ELEMENTOR_VERSION_REQUIRED, '>=' ) ) {
		add_action( 'admin_notices', 'dynamic_visibility_for_elementor_fail_load_out_of_date' );
		return;
	}

	// Don't load it if Dynamic Content for Elementor is installed
	if ( defined( 'DCE_VERSION' ) ) {
		return;
	}

	// Require the main plugin file
	require_once __DIR__ . '/plugin.php';
	$plugin = new \DynamicVisibilityForElementor\Plugin();

	if ( ! get_transient( 'dve_upgrade_notice' ) && is_admin() ) {
		set_transient( 'dve_upgrade_notice', true, 60 * 24 * 30 );
		add_action( 'admin_notices', 'dynamic_content_for_elementor_promo' );
	}
}

add_action( 'plugins_loaded', 'dynamic_visibility_for_elementor_load' );

function dynamic_visibility_for_elementor_fail_load_out_of_date() {
	if ( ! current_user_can( 'update_plugins' ) ) {
		return;
	}

	$file_path = 'elementor/elementor.php';

	$upgrade_link = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $file_path, 'upgrade-plugin_' . $file_path );
	$message = '<p>' . __( 'Dynamic Visibility for Elementor is not working because you are using an old version of Elementor.', 'dynamic-visibility-for-elementor' ) . '</p>';
	$message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $upgrade_link, __( 'Update Elementor Now', 'dynamic-visibility-for-elementor' ) ) . '</p>';

	echo '<div class="error">' . $message . '</div>';
}

function dynamic_visibility_for_elementor_fail_load() {
	$class = 'notice notice-error';
	$message = sprintf( __( 'You need %1$s"Elementor"%2$s for the %1$s"Dynamic Visibility for Elementor"%2$s plugin to work and updated.', 'dynamic-visibility-for-elementor' ), '<strong>', '</strong>' );
	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );
}

function dynamic_content_for_elementor_promo() { ?>
	<div class="error notice-error notice dce-generic-notice is-dismissible">
		<div class="img-responsive pull-left" style="float: left; margin-top: 10px; margin-right: 20px;"><img src="<?php echo DVE_URL; ?>/assets/media/dce.png" title="Dynamic Content for Elementor" height="36" width="36"></div>
		<p><strong><?php _e( 'Upgrade to Dynamic Content for Elementor', 'dynamic-visibility-for-elementor' ); ?></strong><br />
			<?php printf( __( '%1$sBuy now Dynamic Content for Elementor%2$s and save 10&#37; using promo code %3$sVISIBILITY%4$s', 'dynamic-visibility-for-elementor' ), '<a href="https://www.dynamic.ooo/upgrade/visibility-to-premium?utm_source=wp-plugins&utm_campaign=plugin-uri&utm_medium=wp-dash-promo">', '</a>', '<strong>', '</strong>' ); ?></p>
	</div>
	<?php
}
