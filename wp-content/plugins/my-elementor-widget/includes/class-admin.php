<?php
/**
 * Admin Class
 * 
 * Handles admin notices and dependency checks.
 * 
 * @package My_Elementor_Widget
 * @since 1.0.0
 */

namespace My_Elementor_Widget;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Admin
 * 
 * Manages admin-related functionality including notices.
 */
class Admin {

	/**
	 * Display admin notice for missing Elementor
	 */
	public function elementor_missing_notice() {
		$this->render_notice(
			__( 'My Elementor Widget', 'my-elementor-widget' ),
			__( 'Elementor', 'my-elementor-widget' ),
			__( '"%1$s" requires "%2$s" to be installed and activated.', 'my-elementor-widget' ),
			'error'
		);
	}

	/**
	 * Display admin notice for missing WooCommerce
	 */
	public function woocommerce_missing_notice() {
		$this->render_notice(
			__( 'My Elementor Widget', 'my-elementor-widget' ),
			__( 'WooCommerce', 'my-elementor-widget' ),
			__( '"%1$s" requires "%2$s" to be installed and activated for product creation features.', 'my-elementor-widget' ),
			'warning'
		);
	}

	/**
	 * Render admin notice
	 * 
	 * @param string $plugin_name Plugin name
	 * @param string $dependency Dependency name
	 * @param string $message_template Message template with placeholders
	 * @param string $type Notice type (error, warning, success, info)
	 */
	private function render_notice( $plugin_name, $dependency, $message_template, $type = 'error' ) {
		$message = sprintf(
			/* translators: 1: Plugin name 2: Dependency name */
			esc_html( $message_template ),
			'<strong>' . esc_html( $plugin_name ) . '</strong>',
			'<strong>' . esc_html( $dependency ) . '</strong>'
		);

		printf(
			'<div class="notice notice-%s"><p>%s</p></div>',
			esc_attr( $type ),
			$message // Already escaped above
		);
	}
}

