<?php
/**
 * Plugin Name: WDM Cart Abandonment Calculator
 * Plugin URI: https://wisdmlabs.com/
 * Description: A basic plugin scaffold for WDM Cart Abandonment Calculator. No functionality yet.
 * Version: 1.0.0
 * Author: WisdmLabs
 * Author URI: https://wisdmlabs.com/
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wdm-cart-abandonment-calculator
 * Domain Path: /languages
 *
 * @package wdm-cart-abandonment-calculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main plugin class for WDM Cart Abandonment Calculator.
 */
class WDM_Cart_Abandonment_Calculator {
	/**
	 * Singleton instance.
	 *
	 * @var WDM_Cart_Abandonment_Calculator
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return WDM_Cart_Abandonment_Calculator
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor. Hooks into WordPress.
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
	}

	/**
	 * Register the WDM CAC settings page in the admin menu.
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'WDM CAC Settings', 'wdm-cart-abandonment-calculator' ), // Page title.
			__( 'WDM CAC', 'wdm-cart-abandonment-calculator' ),        // Menu title.
			'manage_options',                                          // Capability.
			'wdm-cac-settings',                                        // Menu slug.
			array( $this, 'settings_page' ),                            // Callback function.
			'dashicons-admin-generic',                                 // Icon.
			25                                                        // Position (after Comments).
		);
	}

	/**
	 * Display content on the settings page.
	 */
	public function settings_page() {
		echo '<div class="wrap"><h1>' . esc_html__( 'WDM CAC Settings', 'wdm-cart-abandonment-calculator' ) . '</h1><p>Saniyaj</p></div>';
	}
}

// Initialize the plugin.
WDM_Cart_Abandonment_Calculator::get_instance();

// ... existing code ... 
