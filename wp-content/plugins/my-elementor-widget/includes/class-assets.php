<?php
/**
 * Assets Class
 * 
 * Handles enqueueing of scripts and styles.
 * 
 * @package My_Elementor_Widget
 * @since 1.0.0
 */

namespace My_Elementor_Widget;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Assets
 * 
 * Manages registration and enqueuing of plugin assets (CSS/JS).
 */
class Assets {

	/**
	 * Script handle for editor JavaScript
	 */
	const EDITOR_SCRIPT_HANDLE = 'my-custom-widget-editor';

	/**
	 * Style handle for editor CSS
	 */
	const EDITOR_STYLE_HANDLE = 'my-custom-widget-editor';

	/**
	 * Style handle for frontend CSS
	 */
	const FRONTEND_STYLE_HANDLE = 'my-custom-widget-frontend';

	/**
	 * Initialize assets hooks
	 */
	public function init() {
		// Editor scripts and styles
		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'enqueue_editor_scripts' ] );
		add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'localize_editor_script' ] );
		add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'enqueue_editor_styles' ] );

		// Frontend styles
		add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'enqueue_frontend_styles' ] );
	}

	/**
	 * Enqueue editor scripts
	 */
	public function enqueue_editor_scripts() {
		wp_enqueue_script(
			self::EDITOR_SCRIPT_HANDLE,
			MY_ELEMENTOR_WIDGET_URL . 'assets/js/editor.js',
			[ 'jquery', 'elementor-editor' ],
			MY_ELEMENTOR_WIDGET_VERSION,
			true
		);
	}

	/**
	 * Localize editor script with AJAX data
	 */
	public function localize_editor_script() {
		wp_localize_script(
			self::EDITOR_SCRIPT_HANDLE,
			'myElementorWidget',
			$this->get_localize_data()
		);
	}

	/**
	 * Get localization data for JavaScript
	 * 
	 * @return array
	 */
	private function get_localize_data() {
		return [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'my_elementor_widget_nonce' ),
			'i18n'    => [
				'createProduct'      => __( 'Create Product', 'my-elementor-widget' ),
				'productCreated'     => __( 'Product created successfully!', 'my-elementor-widget' ),
				'error'              => __( 'An error occurred', 'my-elementor-widget' ),
				'validationError'    => __( 'Please fill in all required fields correctly!', 'my-elementor-widget' ),
				'connectionError'    => __( 'Connection error', 'my-elementor-widget' ),
			]
		];
	}

	/**
	 * Enqueue editor styles
	 */
	public function enqueue_editor_styles() {
		wp_enqueue_style(
			self::EDITOR_STYLE_HANDLE,
			MY_ELEMENTOR_WIDGET_URL . 'assets/css/editor.css',
			[],
			MY_ELEMENTOR_WIDGET_VERSION
		);
	}

	/**
	 * Enqueue frontend styles
	 */
	public function enqueue_frontend_styles() {
		wp_enqueue_style(
			self::FRONTEND_STYLE_HANDLE,
			MY_ELEMENTOR_WIDGET_URL . 'assets/css/frontend.css',
			[],
			MY_ELEMENTOR_WIDGET_VERSION
		);
	}

	/**
	 * Get asset URL
	 * 
	 * @param string $path Relative path to asset
	 * @return string Full URL to asset
	 */
	public static function get_asset_url( $path ) {
		return MY_ELEMENTOR_WIDGET_URL . ltrim( $path, '/' );
	}
}
