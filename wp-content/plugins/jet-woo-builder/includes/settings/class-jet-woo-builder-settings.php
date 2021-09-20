<?php
/**
 * Class description
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Woo_Builder_Settings' ) ) {

	/**
	 * Define Jet_Woo_Builder_Settings class
	 */
	class Jet_Woo_Builder_Settings {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    object
		 */
		private static $instance = null;

		/**
		 * [$key description]
		 * @var string
		 */
		public $key = 'jet-woo-builder-settings';

		/**
		 * [$settings description]
		 * @var null
		 */
		public $settings = null;

		/**
		 * Init page
		 */
		public function init() {

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 0 );

			add_action( 'admin_menu', array( $this, 'register_page' ), 99 );
		}

		/**
		 * Initialize page builder module if required
		 *
		 * @return void
		 */
		public function admin_enqueue_scripts() {

			if ( isset( $_REQUEST['page'] ) && $this->key === $_REQUEST['page'] ) {

				$module_data = jet_woo_builder()->module_loader->get_included_module_data( 'cherry-x-vue-ui.php' );
				$ui          = new CX_Vue_UI( $module_data );

				$ui->enqueue_assets();

				wp_enqueue_style(
					'jet-woo-builder-css',
					jet_woo_builder()->plugin_url( 'assets/css/admin.css' ),
					false,
					jet_woo_builder()->get_version()
				);

				wp_enqueue_script(
					'jet-woo-builder-admin-script',
					jet_woo_builder()->plugin_url( 'assets/js/jet-woo-builder-admin.js' ),
					array( 'cx-vue-ui' ),
					jet_woo_builder()->get_version(),
					true
				);

				wp_localize_script(
					'jet-woo-builder-admin-script',
					'JetWooBuilderSettingsPageConfig',
					apply_filters( 'jet-woo-builder/admin/settings-page-config', $this->get_localize_data() )
				);
			}
		}

		/**
		 * [generate_frontend_config_data description]
		 * @return [type] [description]
		 */
		public function get_localize_data() {

			$global_available_widgets = [];
			$default_global_active_widgets = [];

			foreach ( glob( jet_woo_builder()->plugin_path( 'includes/widgets/global/' ) . '*.php' ) as $file ) {
				$data = get_file_data( $file, array( 'class'=>'Class', 'name' => 'Name', 'slug'=>'Slug' ) );

				$slug = basename( $file, '.php' );

				$global_available_widgets[] = array(
					'label' => $data['name'],
					'value' => $slug,
				);

				$default_global_active_widgets[ $slug ] = 'true';
			}

			$single_product_available_widgets = [];
			$default_single_product_active_widgets = [];

			foreach ( glob( jet_woo_builder()->plugin_path( 'includes/widgets/single-product/' ) . '*.php' ) as $file ) {
				$data = get_file_data( $file, array( 'class'=>'Class', 'name' => 'Name', 'slug'=>'Slug' ) );

				$slug = basename( $file, '.php' );

				$single_product_available_widgets[] = array(
					'label' => $data['name'],
					'value' => $slug,
				);

				$default_single_product_active_widgets[ $slug ] = 'true';
			}

			$archive_product_available_widgets = [];
			$default_archive_product_active_widgets = [];

			foreach ( glob( jet_woo_builder()->plugin_path( 'includes/widgets/archive-product/' ) . '*.php' ) as $file ) {
				$data = get_file_data( $file, array( 'class'=>'Class', 'name' => 'Name', 'slug'=>'Slug' ) );

				$slug = basename( $file, '.php' );

				$archive_product_available_widgets[] = array(
					'label' => $data['name'],
					'value' => $slug,
				);

				$default_archive_product_active_widgets[ $slug ] = 'true';
			}

			$archive_category_available_widgets = [];
			$default_archive_category_active_widgets = [];

			foreach ( glob( jet_woo_builder()->plugin_path( 'includes/widgets/archive-category/' ) . '*.php' ) as $file ) {
				$data = get_file_data( $file, array( 'class'=>'Class', 'name' => 'Name', 'slug'=>'Slug' ) );

				$slug = basename( $file, '.php' );

				$archive_category_available_widgets[] = array(
					'label' => $data['name'],
					'value' => $slug,
				);

				$default_archive_category_active_widgets[ $slug ] = 'true';
			}

			$shop_product_available_widgets = [];
			$default_shop_product_active_widgets = [];

			foreach ( glob( jet_woo_builder()->plugin_path( 'includes/widgets/shop/' ) . '*.php' ) as $file ) {
				$data = get_file_data( $file, array( 'class'=>'Class', 'name' => 'Name', 'slug'=>'Slug' ) );

				$slug = basename( $file, '.php' );

				$shop_product_available_widgets[] = array(
					'label' => $data['name'],
					'value' => $slug,
				);

				$default_shop_product_active_widgets[ $slug ] = 'true';
			}

			$product_thumb_effect_options = array(
				array(
					'label' => esc_html__( 'Slide Left', 'jet-woo-builder' ),
					'value' => 'slide-left',
				),
				array(
					'label' => esc_html__( 'Slide Right', 'jet-woo-builder' ),
					'value' => 'slide-right',
				),
				array(
					'label' => esc_html__( 'Slide Top', 'jet-woo-builder' ),
					'value' => 'slide-top',
				),
				array(
					'label' => esc_html__( 'Slide Bottom', 'jet-woo-builder' ),
					'value' => 'slide-bottom',
				),
				array(
					'label' => esc_html__( 'Fade', 'jet-woo-builder' ),
					'value' => 'fade',
				),
				array(
					'label' => esc_html__( 'Fade With Zoom', 'jet-woo-builder' ),
					'value' => 'fade-with-zoom',
				),
			);

			$rest_api_url = apply_filters( 'jet-woo-builder/rest/frontend/url', get_rest_url() );

			return array(
				'messages' => array(
					'saveSuccess' => esc_html__( 'Saved', 'jet-woo-builder' ),
					'saveError'   => esc_html__( 'Error', 'jet-woo-builder' ),
				),
				'settingsApiUrl' => $rest_api_url . 'jet-woo-builder-api/v1/plugin-settings',
				'settingsData' => array(
					'global_available_widgets' => array(
						'value'   => $this->get( 'global_available_widgets', $default_global_active_widgets ),
						'options' => $global_available_widgets,
					),
					'single_product_available_widgets' => array(
						'value'   => $this->get( 'single_product_available_widgets', $default_single_product_active_widgets ),
						'options' => $single_product_available_widgets,
					),
					'archive_product_available_widgets' => array(
						'value'   => $this->get( 'archive_product_available_widgets', $default_archive_product_active_widgets ),
						'options' => $archive_product_available_widgets,
					),
					'archive_category_available_widgets' => array(
						'value'   => $this->get( 'archive_category_available_widgets', $default_archive_category_active_widgets ),
						'options' => $archive_category_available_widgets,
					),
					'shop_product_available_widgets' => array(
						'value'   => $this->get( 'shop_product_available_widgets', $default_shop_product_active_widgets ),
						'options' => $shop_product_available_widgets,
					),
					'enable_product_thumb_effect' => array(
						'value' => $this->get( 'enable_product_thumb_effect' ),
					),
					'product_thumb_effect' => array(
						'value'   => $this->get( 'product_thumb_effect', 'slide-left' ),
						'options' => $product_thumb_effect_options,
					),
				),
			);
		}

		/**
		 * Return settings page URL
		 *
		 * @return string
		 */
		public function get_settings_page_link() {

			return add_query_arg(
				array(
					'page' => $this->key,
				),
				esc_url( admin_url( 'admin.php' ) )
			);
		}

		/**
		 * [get description]
		 * @param  [type]  $setting [description]
		 * @param  boolean $default [description]
		 * @return [type]           [description]
		 */
		public function get( $setting, $default = false ) {

			if ( null === $this->settings ) {
				$this->settings = get_option( $this->key, array() );
			}

			return isset( $this->settings[ $setting ] ) ? $this->settings[ $setting ] : $default;
		}

		/**
		 * Register add/edit page
		 *
		 * @return void
		 */
		public function register_page() {

			add_submenu_page(
				'jet-dashboard',
				esc_html__( 'JetWooBuilder Settings', 'jet-woo-builder' ),
				esc_html__( 'JetWooBuilder Settings', 'jet-woo-builder' ),
				'manage_options',
				$this->key,
				array( $this, 'render_page' )
			);
		}

		/**
		 * Render settings page
		 *
		 * @return void
		 */
		public function render_page() {
			include jet_woo_builder()->get_template( 'admin-templates/settings-page.php' );
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @access public
		 * @return object
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}
}

/**
 * Returns instance of Jet_Woo_Builder_Settings
 *
 * @return object
 */
function jet_woo_builder_settings() {
	return Jet_Woo_Builder_Settings::get_instance();
}
