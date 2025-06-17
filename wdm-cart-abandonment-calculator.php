<?php
/**
 * Plugin Name: WDM Cart Abandonment Calculator
 * Plugin URI: https://wisdmlabs.com/
 * Description: Track and analyze abandoned carts in WooCommerce
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

// Define plugin constants
define( 'WDM_CAC_VERSION', '1.0.0' );
define( 'WDM_CAC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WDM_CAC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include required files
require_once WDM_CAC_PLUGIN_DIR . 'includes/class-database-setup.php';
require_once WDM_CAC_PLUGIN_DIR . 'includes/class-cart-tracker.php';
require_once WDM_CAC_PLUGIN_DIR . 'includes/class-admin-interface.php';

/**
 * Main plugin class
 */
class WDM_Cart_Abandonment_Calculator {
	/**
	 * Singleton instance
	 *
	 * @var WDM_Cart_Abandonment_Calculator
	 */
	private static $instance = null;

	/**
	 * Cart tracker instance
	 *
	 * @var WDM_Cart_Tracker
	 */
	private $cart_tracker;

	/**
	 * Admin interface instance
	 *
	 * @var WDM_Admin_Interface
	 */
	private $admin_interface;

	/**
	 * Get singleton instance
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
	 * Constructor
	 */
	private function __construct() {
		register_activation_hook( __FILE__, array( $this, 'activate_plugin' ) );
		
		// Initialize components
		add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
	}

	/**
	 * Plugin activation
	 */
	public function activate_plugin() {
		// Create database tables
		WDM_Database_Setup::create_tables();
	}

	/**
	 * Initialize plugin components
	 */
	public function init_plugin() {
		// Check if WooCommerce is active
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_not_found_notice' ) );
			return;
		}

		// Initialize components
		$this->cart_tracker = new WDM_Cart_Tracker();
		$this->admin_interface = new WDM_Admin_Interface();
	}

	/**
	 * Display WooCommerce not found notice
	 */
	public function woocommerce_not_found_notice() {
		?>
        <div class="error">
            <p>
                <?php
                echo esc_html__(
                    'WDM Cart Abandonment Calculator requires WooCommerce to be installed and active.',
                    'wdm-cart-abandonment-calculator'
                );
                ?>
            </p>
        </div>
        <?php
	}
}

// Initialize the plugin
WDM_Cart_Abandonment_Calculator::get_instance();
