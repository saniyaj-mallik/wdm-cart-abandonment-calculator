<?php
/**
 * Admin interface functionality
 *
 * @package wdm-cart-abandonment-calculator
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class to handle admin interface
 */
class WDM_Admin_Interface {
    /**
     * Initialize admin interface
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    /**
     * Register the admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Cart Abandonment', 'wdm-cart-abandonment-calculator'),
            __('Cart Abandonment', 'wdm-cart-abandonment-calculator'),
            'manage_options',
            'wdm-cart-abandonment',
            array($this, 'render_main_page'),
            'dashicons-cart',
            25
        );
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if ('toplevel_page_wdm-cart-abandonment' !== $hook) {
            return;
        }

        wp_enqueue_style('wdm-admin-style', plugins_url('assets/css/admin.css', dirname(__FILE__)));
    }

    /**
     * Get statistics for the dashboard
     */
    private function get_statistics() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdm_abandoned_carts';

        // Get total abandoned carts
        $total_abandoned = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_name WHERE status = 'abandoned'"
        );

        // Get total potential revenue lost
        $total_revenue_lost = $wpdb->get_var(
            "SELECT SUM(cart_total) FROM $table_name WHERE status = 'abandoned'"
        );

        // Get average cart value
        $avg_cart_value = $wpdb->get_var(
            "SELECT AVG(cart_total) FROM $table_name WHERE status = 'abandoned'"
        );        return array(
            'total_abandoned' => $total_abandoned,
            'total_revenue_lost' => $total_revenue_lost,
            'avg_cart_value' => $avg_cart_value
        );
    }

    /**
     * Get abandoned carts with filters
     */
    private function get_abandoned_carts() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdm_abandoned_carts';

        // Handle sorting
        $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'created_at';
        $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';

        // Handle filtering
        $where = "WHERE status = 'abandoned'";
        if (!empty($_GET['date_from'])) {
            $date_from = sanitize_text_field($_GET['date_from']);
            $where .= $wpdb->prepare(" AND created_at >= %s", $date_from);
        }
        if (!empty($_GET['date_to'])) {
            $date_to = sanitize_text_field($_GET['date_to']);
            $where .= $wpdb->prepare(" AND created_at <= %s", $date_to);
        }

        $query = "SELECT * FROM $table_name $where ORDER BY $orderby $order";
        return $wpdb->get_results($query);
    }

    /**
     * Render the main admin page
     */
    public function render_main_page() {
        $stats = $this->get_statistics();
        $abandoned_carts = $this->get_abandoned_carts();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Cart Abandonment Calculator', 'wdm-cart-abandonment-calculator'); ?></h1>

            <!-- Statistics Dashboard -->
            <div class="wdm-stats-dashboard">
                <div class="wdm-stat-box">
                    <h3><?php esc_html_e('Total Abandoned Carts', 'wdm-cart-abandonment-calculator'); ?></h3>
                    <p class="stat-number"><?php echo esc_html($stats['total_abandoned']); ?></p>
                </div>
                <div class="wdm-stat-box">
                    <h3><?php esc_html_e('Potential Revenue Lost', 'wdm-cart-abandonment-calculator'); ?></h3>
                    <p class="stat-number"><?php echo wc_price($stats['total_revenue_lost']); ?></p>
                </div>                <div class="wdm-stat-box">
                    <h3><?php esc_html_e('Average Cart Value', 'wdm-cart-abandonment-calculator'); ?></h3>
                    <p class="stat-number"><?php echo wc_price($stats['avg_cart_value']); ?></p>
                </div>
            </div>

            <!-- Filters -->
            <div class="wdm-filters">
                <form method="get">
                    <input type="hidden" name="page" value="wdm-cart-abandonment">
                    <input type="date" name="date_from" value="<?php echo isset($_GET['date_from']) ? esc_attr($_GET['date_from']) : ''; ?>">
                    <input type="date" name="date_to" value="<?php echo isset($_GET['date_to']) ? esc_attr($_GET['date_to']) : ''; ?>">
                    <input type="submit" class="button" value="<?php esc_attr_e('Filter', 'wdm-cart-abandonment-calculator'); ?>">
                </form>
            </div>

            <!-- Abandoned Carts Table -->
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Date', 'wdm-cart-abandonment-calculator'); ?></th>
                        <th><?php esc_html_e('User', 'wdm-cart-abandonment-calculator'); ?></th>
                        <th><?php esc_html_e('Cart Total', 'wdm-cart-abandonment-calculator'); ?></th>
                        <th><?php esc_html_e('Items', 'wdm-cart-abandonment-calculator'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($abandoned_carts as $cart) : ?>
                        <tr>
                            <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($cart->created_at))); ?></td>
                            <td>
                                <?php
                                if ($cart->user_id) {
                                    $user = get_user_by('id', $cart->user_id);
                                    echo esc_html($user ? $user->user_email : __('Unknown User', 'wdm-cart-abandonment-calculator'));
                                } else {
                                    echo esc_html__('Guest', 'wdm-cart-abandonment-calculator');
                                }
                                ?>
                            </td>
                            <td><?php echo wc_price($cart->cart_total); ?></td>
                            <td>
                                <?php
                                $cart_contents = maybe_unserialize($cart->cart_contents);
                                if (is_array($cart_contents)) {
                                    $items = array();
                                    foreach ($cart_contents as $item) {
                                        $product = wc_get_product($item['product_id']);
                                        if ($product) {
                                            $items[] = $product->get_name() . ' × ' . $item['quantity'];
                                        }
                                    }
                                    echo esc_html(implode(', ', $items));
                                }
                                ?>
                            </td>
                        </tr>                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2"><strong><?php esc_html_e('Total (Filtered)', 'wdm-cart-abandonment-calculator'); ?></strong></td>
                        <td>
                            <?php
                            $total = 0;
                            foreach ($abandoned_carts as $cart) {
                                $total += floatval($cart->cart_total);
                            }
                            echo '<strong>' . wc_price($total) . '</strong>';
                            ?>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php
    }
}
