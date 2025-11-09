<?php
/**
 * Ajax Handler Class
 * 
 * Handles AJAX requests for the plugin.
 * 
 * @package My_Elementor_Widget
 * @since 1.0.0
 */

namespace My_Elementor_Widget;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Ajax_Handler
 * 
 * Manages all AJAX endpoints and request handling.
 */
class Ajax_Handler {

	/**
	 * Nonce action name
	 */
	const NONCE_ACTION = 'my_elementor_widget_nonce';

	/**
	 * AJAX action for creating products
	 */
	const ACTION_CREATE_PRODUCT = 'my_elementor_widget_create_product';

	/**
	 * Initialize AJAX hooks
	 */
	public function init() {
		add_action( 'wp_ajax_' . self::ACTION_CREATE_PRODUCT, [ $this, 'create_product' ] );
	}

	/**
	 * AJAX handler to create WooCommerce product
	 * 
	 * Creates a WooCommerce product using the REST API internally.
	 * 
	 * @since 1.0.0
	 */
	public function create_product() {
		try {
			// Verify nonce for security
			$this->verify_nonce();

			// Check user capabilities
			$this->check_capabilities();

			// Get and validate input data
			$product_data = $this->get_validated_product_data();

			// Create the product
			$result = $this->create_woocommerce_product( $product_data );

			// Store product ID in transient for current user
			$this->store_product_reference( $result['product_id'] );

			// Send success response
			wp_send_json_success( [
				'message'       => __( 'Product created successfully!', 'my-elementor-widget' ),
				'product_id'    => $result['product_id'],
				'product_name'  => $result['product_name'],
				'product_price' => $result['product_price'],
				'product_url'   => $result['product_url'],
				'edit_url'      => $result['edit_url'],
			] );

		} catch ( \Exception $e ) {
			wp_send_json_error( [
				'message' => $e->getMessage()
			] );
		}
	}

	/**
	 * Verify AJAX request nonce
	 * 
	 * @throws \Exception If nonce verification fails
	 */
	private function verify_nonce() {
		if ( ! check_ajax_referer( self::NONCE_ACTION, 'nonce', false ) ) {
			throw new \Exception(
				__( 'Security check failed. Please refresh the page and try again.', 'my-elementor-widget' )
			);
		}
	}

	/**
	 * Check user capabilities
	 * 
	 * @throws \Exception If user doesn't have required capabilities
	 */
	private function check_capabilities() {
		if ( ! current_user_can( 'edit_products' ) ) {
			throw new \Exception(
				__( 'You do not have permission to create products.', 'my-elementor-widget' )
			);
		}
	}

	/**
	 * Get and validate product data from POST request
	 * 
	 * @return array Sanitized product data
	 * @throws \Exception If validation fails
	 */
	private function get_validated_product_data() {
		// Sanitize input
		$product_name = isset( $_POST['product_name'] ) 
			? sanitize_text_field( wp_unslash( $_POST['product_name'] ) ) 
			: '';

		$product_price = isset( $_POST['product_price'] ) 
			? floatval( $_POST['product_price'] ) 
			: 0;

		$product_description = isset( $_POST['product_description'] ) 
			? sanitize_textarea_field( wp_unslash( $_POST['product_description'] ) ) 
			: '';

		// Validate required fields
		if ( empty( $product_name ) ) {
			throw new \Exception(
				__( 'Product name is required.', 'my-elementor-widget' )
			);
		}

		if ( $product_price <= 0 ) {
			throw new \Exception(
				__( 'Product price must be greater than 0.', 'my-elementor-widget' )
			);
		}

		return [
			'name'        => $product_name,
			'price'       => $product_price,
			'description' => $product_description,
		];
	}

	/**
	 * Create WooCommerce product using REST API
	 * 
	 * @param array $product_data Product data
	 * @return array Product information
	 * @throws \Exception If product creation fails
	 */
	private function create_woocommerce_product( $product_data ) {
		// Prepare product data for REST API
		$api_data = [
			'name'               => $product_data['name'],
			'type'               => 'simple',
			'regular_price'      => (string) $product_data['price'],
			'status'             => 'publish',
			'catalog_visibility' => 'visible',
			'manage_stock'       => false,
		];

		// Add description if provided
		if ( ! empty( $product_data['description'] ) ) {
			$api_data['description'] = $product_data['description'];
		}

		// Create internal REST API request
		$request = new \WP_REST_Request( 'POST', '/wc/v3/products' );
		$request->set_body_params( $api_data );

		// Execute the request
		$server = rest_get_server();
		$response = $server->dispatch( $request );

		// Check response
		if ( $response->is_error() ) {
			$error_data = $response->get_data();
			$error_message = isset( $error_data['message'] ) 
				? $error_data['message'] 
				: __( 'Failed to create product. Please try again.', 'my-elementor-widget' );

			throw new \Exception( $error_message );
		}

		$response_data = $response->get_data();

		if ( empty( $response_data['id'] ) ) {
			throw new \Exception(
				__( 'Product was created but ID is missing. Please check WooCommerce.', 'my-elementor-widget' )
			);
		}

		return [
			'product_id'    => $response_data['id'],
			'product_name'  => $response_data['name'],
			'product_price' => $response_data['price'],
			'product_url'   => $response_data['permalink'],
			'edit_url'      => admin_url( 'post.php?post=' . $response_data['id'] . '&action=edit' ),
		];
	}

	/**
	 * Store product reference in queue for current user
	 * 
	 * Uses a queue system to allow multiple widgets to retrieve different products.
	 * Each widget will pull the next product from the queue when rendered.
	 * 
	 * @param int $product_id Product ID
	 */
	private function store_product_reference( $product_id ) {
		$current_user_id = get_current_user_id();
		$transient_key = 'my_custom_widget_product_queue_' . $current_user_id;
		
		// Get current queue
		$queue = get_transient( $transient_key );
		if ( ! is_array( $queue ) ) {
			$queue = [];
		}
		
		// Add new product to queue
		$queue[] = $product_id;
		
		// Save updated queue
		set_transient( $transient_key, $queue, DAY_IN_SECONDS );
	}
}

