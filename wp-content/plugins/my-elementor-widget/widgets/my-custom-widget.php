<?php
/**
 * My Custom Widget
 * 
 * Elementor widget that displays products with popup form integration
 * and WooCommerce product creation capabilities.
 * 
 * @package My_Elementor_Widget
 * @since 1.0.0
 */

namespace My_Elementor_Widget;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class My_Custom_Widget
 * 
 * Custom Elementor widget for displaying and managing WooCommerce products
 * with integrated popup form for product creation.
 */
class My_Custom_Widget extends \Elementor\Widget_Base {

	/**
	 * Transient key prefix for this widget
	 */
	const TRANSIENT_PREFIX = 'my_custom_widget_product_queue_';

	/**
	 * Get widget name
	 * 
	 * @return string Widget name
	 */
	public function get_name() {
		return 'my_custom_widget';
	}

	/**
	 * Get widget title
	 * 
	 * @return string Widget title
	 */
	public function get_title() {
		return __( 'My Custom Widget', 'my-elementor-widget' );
	}

	/**
	 * Get widget icon
	 * 
	 * @return string Widget icon class
	 */
	public function get_icon() {
		return 'eicon-button';
	}

	/**
	 * Get widget categories
	 * 
	 * @return array Widget categories
	 */
	public function get_categories() {
		return [ 'woocommerce-elements' ];
	}

	/**
	 * Get script dependencies
	 * 
	 * @return array Script dependencies
	 */
	public function get_script_depends() {
		return [ 'my-custom-widget-editor' ];
	}

	/**
	 * Render widget output on the frontend
	 * 
	 * Displays product created by current user
	 */
	protected function render() {
		$product_id = $this->get_user_product_id();
		$this->render_product_wrapper( $product_id );
	}

	/**
	 * Get product ID for widget instance
	 * 
	 * Priority:
	 * 1. Check database (post meta) - persistent storage
	 * 2. Pull from queue and save to database
	 * 
	 * @return int|false Product ID or false if not found
	 */
	private function get_user_product_id() {
		// Step 1: Check database first
		$product_id = $this->get_product_from_db();
		
		if ( $product_id ) {
			return $product_id;
		}
		
		// Step 2: No product assigned yet, pull from queue
		$current_user_id = get_current_user_id();
		$transient_key = self::TRANSIENT_PREFIX . $current_user_id;
		$queue = get_transient( $transient_key );
		
		// Validate queue
		if ( ! is_array( $queue ) || empty( $queue ) ) {
			return false;
		}
		
		// Get first product from queue
		$product_id = array_shift( $queue );
		
		// Update queue
		set_transient( $transient_key, $queue, DAY_IN_SECONDS );
		
		// Save to database (this is the key fix!)
		$this->save_product_to_db( $product_id );
		
		return (int) $product_id;
	}

	/**
	 * Save product ID to database
	 * 
	 * @param int $product_id Product ID
	 * @return bool Success status
	 */
	private function save_product_to_db( $product_id ) {
		$widget_id = $this->get_id();
		$post_id = get_the_ID();
		
		if ( ! $post_id ) {
			return false;
		}
		
		// Save to post meta
		$meta_key = '_widget_product_' . $widget_id;
		return update_post_meta( $post_id, $meta_key, $product_id );
	}

	/**
	 * Get product ID from database
	 * 
	 * @return int|false Product ID or false
	 */
	private function get_product_from_db() {
		$widget_id = $this->get_id();
		$post_id = get_the_ID();
		
		if ( ! $post_id ) {
			return false;
		}
		
		// Get from post meta
		$meta_key = '_widget_product_' . $widget_id;
		$product_id = get_post_meta( $post_id, $meta_key, true );
		
		return $product_id ? (int) $product_id : false;
	}

	/**
	 * Render product wrapper with product or empty state
	 * 
	 * @param int|false $product_id Product ID
	 */
	private function render_product_wrapper( $product_id ) {
		echo '<div class="my-custom-widget-wrapper">';

		if ( empty( $product_id ) ) {
			$this->render_empty_state();
		} else {
			$product = $this->get_woocommerce_product( $product_id );

			if ( $product ) {
				$this->render_product_display( $product );
			} else {
				$this->render_empty_state();
			}
		}

		echo '</div>';
	}

	/**
	 * Get WooCommerce product object
	 * 
	 * @param int $product_id Product ID
	 * @return \WC_Product|false Product object or false
	 */
	private function get_woocommerce_product( $product_id ) {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return false;
		}

		return wc_get_product( $product_id );
	}

	/**
	 * Render empty state when no product available
	 */
	private function render_empty_state() {
		$is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
		?>
		<div class="empty-state">
			<h3 class="empty-state-title"><?php esc_html_e( 'Chưa có sản phẩm', 'my-elementor-widget' ); ?></h3>
			<p class="empty-state-text">
				<?php if ( $is_editor ) : ?>
					<?php esc_html_e( 'Vui lòng tạo sản phẩm từ popup để hiển thị trong widget này.', 'my-elementor-widget' ); ?>
				<?php else : ?>
					<?php esc_html_e( 'Widget này chưa được liên kết với sản phẩm nào.', 'my-elementor-widget' ); ?>
				<?php endif; ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render product display
	 * 
	 * @param \WC_Product $product WooCommerce product object
	 */
	private function render_product_display( $product ) {
		?>
		<div class="product-display">
			<div class="product-item">
				<div class="product-details">
					<?php $this->render_product_title( $product ); ?>
					<?php $this->render_product_price( $product ); ?>
					<?php $this->render_product_description( $product ); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render product title
	 * 
	 * @param \WC_Product $product WooCommerce product object
	 */
	private function render_product_title( $product ) {
		?>
		<h2 class="product-name">
			<a href="<?php echo esc_url( $product->get_permalink() ); ?>" target="_blank">
				<?php echo esc_html( $product->get_name() ); ?>
			</a>
		</h2>
		<?php
	}

	/**
	 * Render product price
	 * 
	 * @param \WC_Product $product WooCommerce product object
	 */
	private function render_product_price( $product ) {
		?>
		<div class="product-price">
			<?php echo wp_kses_post( $product->get_price_html() ); ?>
		</div>
		<?php
	}

	/**
	 * Render product description
	 * 
	 * @param \WC_Product $product WooCommerce product object
	 */
	private function render_product_description( $product ) {
		$description = $product->get_description();

		if ( ! empty( $description ) ) {
			?>
			<div class="product-description">
				<?php echo wp_kses_post( $description ); ?>
			</div>
			<?php
		}
	}
}
