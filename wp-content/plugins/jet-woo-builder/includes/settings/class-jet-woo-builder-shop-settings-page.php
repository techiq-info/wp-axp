<?php
/**
 * WooCommerce Product Settings
 *
 * @author   WooThemes
 * @category Admin
 * @package  WooCommerce/Admin
 * @version  2.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Jet_Woo_Builder_Shop_Settings_Page.
 */
class Jet_Woo_Builder_Shop_Settings_Page extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = jet_woo_builder_shop_settings()->key;
		$this->label = __( 'Jet Woo Builder', 'jet-woo-builder' );

		parent::__construct();
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {
		$sections = array(
			'' => __( 'General', 'jet-woo-builder' ),
		);

		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		global $current_section;
		$settings = $this->get_settings( $current_section );

		WC_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings( $current_section );

		WC_Admin_Settings::save_fields( $settings );

	}

	/**
	 * Get settings array.
	 *
	 * @param string $current_section Current section name.
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {
		global $current_section;

		$settings = array(
			array(
				'title' => __( 'General', 'jet-woo-builder' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'general_options',
			),

			array(
				'title'    => __( 'Widgets Render Method', 'jet-woo-builder' ),
				'desc'     => __( 'Select widgets render method for archive product and archive category templates', 'jet-woo-builder' ),
				'id'       => jet_woo_builder_shop_settings()->options_key . '[widgets_render_method]',
				'default'  => 'macros',
				'type'     => 'jet_woo_select_render_method_field',
				'class'    => 'wc-enhanced-select-nostd',
				'css'      => 'min-width:300px;',
			),

			array(
				'type' => 'sectionend',
				'id'   => 'general_options',
			),

			array(
				'title' => __( 'Shop Page', 'jet-woo-builder' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'shop_options',
			),

			array(
				'title'   => __( 'Custom Shop Page', 'jet-woo-builder' ),
				'desc'    => __( 'Enable custom shop page', 'jet-woo-builder' ),
				'id'      => jet_woo_builder_shop_settings()->options_key . '[custom_shop_page]',
				'default' => '',
				'type'    => 'checkbox',
			),

			array(
				'title'    => __( 'Shop Template', 'jet-woo-builder' ),
				'desc'     => __( 'Select template to use it as global shop template', 'jet-woo-builder' ),
				'id'       => jet_woo_builder_shop_settings()->options_key . '[shop_template]',
				'doc_type' => 'shop',
				'default'  => '',
				'type'     => 'jet_woo_select_template',
				'class'    => 'wc-enhanced-select-nostd',
				'css'      => 'min-width:300px;',
			),

			array(
				'title'   => __( 'Custom Taxonomy Template', 'jet-woo-builder' ),
				'desc'    => __( 'Enable custom taxonomy template. Read more about custom template <a href="https://crocoblock.com/knowledge-base/articles/jetwoobuilder-how-to-set-up-a-custom-product-taxonomy-template/" target="_blank" rel="nofollow">here</a>', 'jet-woo-builder' ),
				'id'      => jet_woo_builder_shop_settings()->options_key . '[custom_taxonomy_template]',
				'default' => '',
				'type'    => 'checkbox',
			),

			array(
				'type' => 'sectionend',
				'id'   => 'shop_options',
			),

			array(
				'title' => __( 'Single Product', 'jet-woo-builder' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'single_options',
			),

			array(
				'title'   => __( 'Custom Single Product', 'jet-woo-builder' ),
				'desc'    => __( 'Enable custom single product page', 'jet-woo-builder' ),
				'id'      => jet_woo_builder_shop_settings()->options_key . '[custom_single_page]',
				'default' => '',
				'type'    => 'checkbox',
			),

			array(
				'title'    => __( 'Single Product Template', 'jet-woo-builder' ),
				'desc'     => __( 'Select template to use it as global single product template', 'jet-woo-builder' ),
				'id'       => jet_woo_builder_shop_settings()->options_key . '[single_template]',
				'doc_type' => 'single',
				'default'  => '',
				'type'     => 'jet_woo_select_template',
				'class'    => 'wc-enhanced-select-nostd',
				'css'      => 'min-width:300px;',
			),

			array(
				'type' => 'sectionend',
				'id'   => 'single_options',
			),

			array(
				'title' => __( 'Archive Product', 'jet-woo-builder' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'archive_options',
			),

			array(
				'title'   => __( 'Custom Archive Product', 'jet-woo-builder' ),
				'desc'    => __( 'Enable custom archive product', 'jet-woo-builder' ),
				'id'      => jet_woo_builder_shop_settings()->options_key . '[custom_archive_page]',
				'default' => '',
				'type'    => 'checkbox',
			),

			array(
				'title'    => __( 'Archive Product Template', 'jet-woo-builder' ),
				'desc'     => __( 'Select template to use it as global archive product template', 'jet-woo-builder' ),
				'id'       => jet_woo_builder_shop_settings()->options_key . '[archive_template]',
				'doc_type' => 'archive',
				'default'  => '',
				'type'     => 'jet_woo_select_template',
				'class'    => 'wc-enhanced-select-nostd',
				'css'      => 'min-width:300px;',
			),

			array(
				'title'    => __( 'Search Page Template', 'jet-woo-builder' ),
				'desc'     => __( 'Select template to use it as global cross search page template', 'jet-woo-builder' ),
				'id'       => jet_woo_builder_shop_settings()->options_key . '[search_template]',
				'doc_type' => 'archive',
				'default'  => '',
				'type'     => 'jet_woo_select_template',
				'class'    => 'wc-enhanced-select-nostd',
				'css'      => 'min-width:300px;',
			),

			array(
				'title'    => __( 'Product Shortcode Template', 'jet-woo-builder' ),
				'desc'     => __( 'Select template to use it as global product shortcode template', 'jet-woo-builder' ),
				'id'       => jet_woo_builder_shop_settings()->options_key . '[shortcode_template]',
				'doc_type' => 'archive',
				'default'  => '',
				'type'     => 'jet_woo_select_template',
				'class'    => 'wc-enhanced-select-nostd',
				'css'      => 'min-width:300px;',
			),

			array(
				'title'    => __( 'Related and Up Sells Products Template', 'jet-woo-builder' ),
				'desc'     => __( 'Select template to use it as global related products template', 'jet-woo-builder' ),
				'id'       => jet_woo_builder_shop_settings()->options_key . '[related_template]',
				'doc_type' => 'archive',
				'default'  => '',
				'type'     => 'jet_woo_select_template',
				'class'    => 'wc-enhanced-select-nostd',
				'css'      => 'min-width:300px;',
			),

			array(
				'title'    => __( 'Cross Sells Product Template', 'jet-woo-builder' ),
				'desc'     => __( 'Select template to use it as global cross sells product template', 'jet-woo-builder' ),
				'id'       => jet_woo_builder_shop_settings()->options_key . '[cross_sells_template]',
				'doc_type' => 'archive',
				'default'  => '',
				'type'     => 'jet_woo_select_template',
				'class'    => 'wc-enhanced-select-nostd',
				'css'      => 'min-width:300px;',
			),

			array(
				'type' => 'sectionend',
				'id'   => 'archive_options',
			),

			array(
				'title' => __( 'Archive Category', 'jet-woo-builder' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'categories_options',
			),

			array(
				'title'   => __( 'Custom Archive Category', 'jet-woo-builder' ),
				'desc'    => __( 'Enable custom archive category', 'jet-woo-builder' ),
				'id'      => jet_woo_builder_shop_settings()->options_key . '[custom_archive_category_page]',
				'default' => '',
				'type'    => 'checkbox',
			),

			array(
				'title'    => __( 'Archive Category Template', 'jet-woo-builder' ),
				'desc'     => __( 'Select template to use it as global archive category template', 'jet-woo-builder' ),
				'id'       => jet_woo_builder_shop_settings()->options_key . '[category_template]',
				'doc_type' => 'category',
				'default'  => '',
				'type'     => 'jet_woo_select_template',
				'class'    => 'wc-enhanced-select-nostd',
				'css'      => 'min-width:300px;',
			),

			array(
				'type' => 'sectionend',
				'id'   => 'categories_options',
			),

			array(
				'title' => __( 'Other Options', 'jet-woo-builder' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'other_options',
			),

			array(
				'title'   => __( 'Use Native Templates', 'jet-woo-builder' ),
				'desc'    => __( 'Force use native WooCommerce templates instead of rewritten in theme', 'jet-woo-builder' ),
				'id'      => jet_woo_builder_shop_settings()->options_key . '[use_native_templates]',
				'default' => '',
				'type'    => 'checkbox',
			),

			array(
				'title'   => __( 'Number related products to show', 'jet-woo-builder' ),
				'id'      => jet_woo_builder_shop_settings()->options_key . '[related_products_per_page]',
				'type'    => 'number',
				'default' => 4,
				'step'    => 1,
				'min'     => 1,
				'max'     => '',
				'std'     => 10,
			),

			array(
				'title'   => __( 'Number up sells products to show', 'jet-woo-builder' ),
				'id'      => jet_woo_builder_shop_settings()->options_key . '[up_sells_products_per_page]',
				'type'    => 'number',
				'default' => 4,
				'step'    => 1,
				'min'     => 1,
				'max'     => '',
				'std'     => 10,
			),

			array(
				'title'   => __( 'Number cross sells products to show', 'jet-woo-builder' ),
				'id'      => jet_woo_builder_shop_settings()->options_key . '[cross_sells_products_per_page]',
				'type'    => 'number',
				'default' => 4,
				'step'    => 1,
				'min'     => 1,
				'max'     => '',
				'std'     => 10,
			),

			array(
				'type' => 'sectionend',
				'id'   => 'other_options',
			),
		);

		return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );
	}
}
