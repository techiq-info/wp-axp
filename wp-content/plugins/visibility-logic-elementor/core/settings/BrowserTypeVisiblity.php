<?php

namespace Stax\VisibilityLogic;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Stax\VisibilityLogic\Singleton;

/**
 * Class BrowserTypeVisiblity
 */
class BrowserTypeVisiblity extends Singleton {

	/**
	 * BrowserTypeVisiblity constructor
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'elementor/element/common/_section_style/after_section_end', [ $this, 'register_section' ] );
		add_action( 'elementor/element/section/section_advanced/after_section_end', [ $this, 'register_section' ] );

		add_action( 'elementor/element/common/' . self::SECTION_PREFIX . 'browser_type_section/before_section_end', [
			$this,
			'register_controls'
		], 10, 2 );
		add_action( 'elementor/element/section/' . self::SECTION_PREFIX . 'browser_type_section/before_section_end', [
			$this,
			'register_controls'
		], 10, 2 );

		add_filter( 'stax/visibility/apply_conditions', [ $this, 'apply_conditions' ], 10, 2 );
	}

	/**
	 * Register section
	 *
	 * @param $element
	 *
	 * @return void
	 */
	public function register_section( $element ) {
		$element->start_controls_section(
			self::SECTION_PREFIX . 'browser_type_section',
			[
				'tab'       => self::VISIBILITY_TAB,
				'label'     => __( 'Browser Type', 'visibility-logic-elementor' ),
				'condition' => [
					self::SECTION_PREFIX . 'enabled' => 'yes',
				],
			]
		);

		$element->end_controls_section();
	}

	/**
	 * @param $element \Elementor\Widget_Base
	 * @param $section_id
	 * @param $args
	 */
	public function register_controls( $element, $args ) {
		$element->add_control(
			self::SECTION_PREFIX . 'browser_type_enabled',
			[
				'label'        => __( 'Enable', 'visibility-logic-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'visibility-logic-elementor' ),
				'label_off'    => __( 'No', 'visibility-logic-elementor' ),
				'return_value' => 'yes',
			]
		);

		$element->add_control(
			self::SECTION_PREFIX . 'browsers',
			[
				'label'       => __( 'Browser', 'visibility-logic-elementor' ),
				'type'        => Controls_Manager::SELECT2,
				'options'     => [
					'is_chrome' => 'Google Chrome',
					'is_gecko'  => 'FireFox',
					'is_safari' => 'Safari',
					'is_ie'     => 'Internet Explorer',
					'is_edge'   => 'Microsoft Edge',
					'is_ns4'    => 'Netscape',
					'is_opera'  => 'Opera',
					'is_lynx'   => 'Lynx',
					'is_iphone' => 'iPhone Safari',
				],
				'description' => __( 'Trigger visibility for a specific browsers.', 'visibility-logic-elementor' ),
				'multiple'    => true,
				'condition'   => [
					self::SECTION_PREFIX . 'browser_type_enabled' => 'yes',
				],
			]
		);
	}

	/**
	 * Apply conditions
	 *
	 * @param array $options
	 * @param array $settings
	 *
	 * @return array
	 */
	public function apply_conditions( $options, $settings ) {
		if ( (bool) $settings[ self::SECTION_PREFIX . 'browser_type_enabled' ] ) {
			$browser_found = false;

			if ( empty( $settings[ self::SECTION_PREFIX . 'browsers' ] ) ) {
				return $options;
			}

			foreach ( $settings[ self::SECTION_PREFIX . 'browsers' ] as $browser_type ) {
				if ( Resources::get_browser_type() === $browser_type ) {
					$browser_found = true;
				}
			}

			$options['browser_type'] = $browser_found;
		}

		return $options;
	}

}

BrowserTypeVisiblity::instance();
