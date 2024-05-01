<?php
/**
 * Plugin Name: WooCommerce Show Order Meta
 * Description: Adds a custom metabox to WooCommerce orders to display meta data for HPOS websites.
 * Author: Saif Hassan
 * Version: 0.0.1
 * Text Domain:       woocommerce-show-order-meta
 * Domain Path:       /languages
 * WC requires at least: 8.0.0
 * WC tested up to: 8.8.3
 *
 * License: GNU General Public License v3.0
 * @package WooCommerce_Show_Order_Meta
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WOOCOMMERCE_SHOW_ORDER_META', '0.0.1' );


add_action( 'plugins_loaded', array( 'WooCommerce_Show_Order_Meta', 'init' ), 9 );

class WooCommerce_Show_Order_Meta {

	public $plugin_name;

	public function __construct() {
		if ( ! $this->should_run() ) {
			return;
		}
		$this->plugin_name = 'woocommerce-show-order-meta';
		add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_admin_order_meta_box' ) );
		$this->load_plugin_textdomain();
	}

	public static function init() {
		$class = __CLASS__;
		new $class();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}
    

	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'woocommerce-show-order-meta',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}


	public function declare_hpos_compatibility() {
		if ( class_exists( AutomatticWooCommerceUtilitiesFeaturesUtil::class ) ) {
			AutomatticWooCommerceUtilitiesFeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}

	public function add_admin_order_meta_box() {
		$screen = 'shop_order';
		if ( class_exists( 'Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) ) {
			$controller = wc_get_container()->get( 'Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' );
			if ( $controller->custom_orders_table_usage_is_enabled() ) {
				$screen = wc_get_page_screen_id( 'shop-order' );
			}
		}

		add_meta_box(
			'order_meta_table',
			'Order Meta Table',
			array( $this, 'display_custom_metabox_content' ),
			$screen,
			'normal',
			'high'
		);
	}

	public function should_run() {

		if ( 'yes' === get_option( 'woocommerce_custom_orders_table_enabled' ) ) {
			return true;
		}
		return false;
	}

	public function display_custom_metabox_content( $the_order ) {
		$order = wc_get_order( $the_order->ID );
	
		if ( ! $order instanceof WC_Order ) {
			return;
		}
	
		$order_meta_data = $order->get_meta_data();
	    $html = '<div class="woocommerce-show-order-meta-wrapper" >';
		$html .= '<table class="woocommerce-show-order-meta-table">';
		$html .= '<tr>';
		$html .= '<th>' . esc_html__( 'ID', 'woocommerce-show-order-meta' ) . '</th>';
		$html .= '<th>' . esc_html__( 'Key', 'woocommerce-show-order-meta' ) . '</th>';
		$html .= '<th>' . esc_html__( 'Value', 'woocommerce-show-order-meta' ) . '</th>';
		$html .= '</tr>';
	
		foreach ( $order_meta_data as $meta ) {
			$meta_data = $meta->get_data();
			$html     .= '<tr>';
			$html     .= '<td>' . esc_html( $meta_data['id'] ) . '</td>';
			$html     .= '<td>' . esc_html( $meta_data['key'] ) . '</td>';
			$html     .= '<td>' . esc_html( $meta_data['value'] ) . '</td>';
			$html     .= '</tr>';
		}
	
		$html .= '</table>';
		$html .= '</div>';
	
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'woocommerce_order_debugger.css', array(), WOOCOMMERCE_SHOW_ORDER_META, 'all' );
	
		echo wp_kses_post( $html );
	}

}
