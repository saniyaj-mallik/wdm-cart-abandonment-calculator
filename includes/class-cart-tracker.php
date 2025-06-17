<?php
/**
 * Cart tracking functionality
 *
 * @package wdm-cart-abandonment-calculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to handle cart tracking operations
 */
class WDM_Cart_Tracker {
	/**
	 * Initialize the tracker
	 */
	public function __construct() {
		add_action( 'woocommerce_add_to_cart', array( $this, 'track_cart_updated' ), 10, 0 );
		add_action( 'woocommerce_cart_item_removed', array( $this, 'track_cart_updated' ), 10, 0 );
		add_action( 'woocommerce_cart_item_restored', array( $this, 'track_cart_updated' ), 10, 0 );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'mark_cart_completed' ), 10, 1 );
	}

	/**
	 * Track cart updates
	 */
	public function track_cart_updated() {
		if ( ! is_admin() && WC()->cart ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'wdm_abandoned_carts';
			
			$user_id    = get_current_user_id();
			$session_id = WC()->session->get_customer_id();
			
			// Ensure cart totals are up to date before saving.
			WC()->cart->calculate_totals();
			
			$cart_contents = WC()->cart->get_cart();
			$cart_total    = WC()->cart->total;
			$existing_cart = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT id FROM {$wpdb->prefix}wdm_abandoned_carts WHERE session_id = %s AND status = %s",
					$session_id,
					'abandoned'
				)
			);

			if ( $existing_cart ) {
				$wpdb->update(
					$table_name,
					array(
						'cart_contents' => maybe_serialize( $cart_contents ),
						'cart_total'    => $cart_total,
						'modified_at'   => current_time( 'mysql' ),
						'user_id'       => $user_id,
					),
					array( 'session_id' => $session_id )
				);
			} else {
				$wpdb->insert(
					$table_name,
					array(
						'session_id'    => $session_id,
						'user_id'       => $user_id,
						'cart_contents' => maybe_serialize( $cart_contents ),
						'cart_total'    => $cart_total,
						'created_at'    => current_time( 'mysql' ),
						'modified_at'   => current_time( 'mysql' ),
						'status'        => 'abandoned',
					)
				);
			}
		}
	}

	/**
	 * Mark cart as completed when order is processed
	 *
	 * @param int $order_id Order ID.
	 */
	public function mark_cart_completed( $order_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'wdm_abandoned_carts';
		
		$session_id = WC()->session->get_customer_id();
		
		$wpdb->update(
			$table_name,
			array(
				'status'      => 'completed',
				'modified_at' => current_time( 'mysql' ),
				'order_id'    => $order_id,
			),
			array( 'session_id' => $session_id )
		);
	}
}
