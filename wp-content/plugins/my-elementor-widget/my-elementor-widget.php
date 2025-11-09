<?php
/**
 * Plugin Name: My Elementor Widget
 * Description: A custom Elementor widget plugin with WooCommerce integration and popup form.
 * Version: 1.0.0
 * Author: Kevin
 * Text Domain: my-elementor-widget
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Requires Plugins: elementor, woocommerce
 */

namespace My_Elementor_Widget;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'MY_ELEMENTOR_WIDGET_VERSION', '1.0.0' );
define( 'MY_ELEMENTOR_WIDGET_PATH', plugin_dir_path( __FILE__ ) );
define( 'MY_ELEMENTOR_WIDGET_URL', plugin_dir_url( __FILE__ ) );
define( 'MY_ELEMENTOR_WIDGET_FILE', __FILE__ );

/**
 * Autoloader for plugin classes
 * 
 * Automatically loads classes from the includes directory.
 * 
 * @param string $class Class name to load
 */
function autoload( $class ) {
	// Check if class belongs to this plugin
	$prefix = __NAMESPACE__ . '\\';
	$len = strlen( $prefix );

	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	// Get the relative class name
	$relative_class = substr( $class, $len );

	// Convert class name to file name
	$file_name = 'class-' . strtolower( str_replace( '_', '-', $relative_class ) ) . '.php';

	// Build the full file path
	$file = MY_ELEMENTOR_WIDGET_PATH . 'includes/' . $file_name;

	// Load the file if it exists
	if ( file_exists( $file ) ) {
		require_once $file;
	}
}

// Register autoloader
spl_autoload_register( __NAMESPACE__ . '\autoload' );

/**
 * Initialize the plugin
 * 
 * Returns the main instance of the plugin.
 * 
 * @return Plugin
 */
function plugin() {
	return Plugin::instance();
}

// Initialize plugin
plugin();
