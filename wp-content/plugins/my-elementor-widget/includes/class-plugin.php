<?php
/**
 * Main Plugin Class
 * 
 * Core plugin class that initializes all components.
 * 
 * @package My_Elementor_Widget
 * @since 1.0.0
 */

namespace My_Elementor_Widget;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Plugin
 * 
 * Main plugin orchestrator that coordinates all plugin components.
 */
class Plugin {

	/**
	 * Plugin instance
	 * 
	 * @var Plugin
	 */
	private static $instance = null;

	/**
	 * Admin handler instance
	 * 
	 * @var Admin
	 */
	private $admin;

	/**
	 * Assets handler instance
	 * 
	 * @var Assets
	 */
	private $assets;

	/**
	 * Ajax handler instance
	 * 
	 * @var Ajax_Handler
	 */
	private $ajax;

	/**
	 * Get singleton instance
	 * 
	 * @return Plugin
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 * 
	 * Initializes the plugin.
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		add_action( 'plugins_loaded', [ $this, 'init' ] );
	}

	/**
	 * Initialize plugin components
	 */
	public function init() {
		// Check dependencies
		if ( ! $this->check_dependencies() ) {
			return;
		}

		// Initialize components
		$this->init_components();

		// Register Elementor widgets
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
	}

	/**
	 * Check if all required dependencies are active
	 * 
	 * @return bool True if all dependencies are met
	 */
	private function check_dependencies() {
		$this->admin = new Admin();
		
		// Check Elementor
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this->admin, 'elementor_missing_notice' ] );
			return false;
		}

		// Check WooCommerce
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', [ $this->admin, 'woocommerce_missing_notice' ] );
			return false;
		}

		return true;
	}

	/**
	 * Initialize plugin components
	 */
	private function init_components() {
		// Initialize assets manager
		$this->assets = new Assets();
		$this->assets->init();

		// Initialize AJAX handler
		$this->ajax = new Ajax_Handler();
		$this->ajax->init();
	}

	/**
	 * Register Elementor widgets
	 * 
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager
	 */
	public function register_widgets( $widgets_manager ) {
		require_once MY_ELEMENTOR_WIDGET_PATH . 'widgets/my-custom-widget.php';
		$widgets_manager->register( new \My_Elementor_Widget\My_Custom_Widget() );
	}

	/**
	 * Get plugin version
	 * 
	 * @return string
	 */
	public function get_version() {
		return MY_ELEMENTOR_WIDGET_VERSION;
	}
}

