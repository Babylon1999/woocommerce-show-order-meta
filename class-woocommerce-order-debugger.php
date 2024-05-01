<?php
/**
 * Plugin Name: WooCommerce Order Debugger
 * Description: Adds a custom metabox to WooCommerce orders to display meta data for HPOS websites.
 * Author: Your Name
 * Version: 0.0.1
 *
 * @package WooCommerce_Order_Debugger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'plugins_loaded', array( 'WooCommerce_Order_Debugger', 'init' ), 9 );

class WooCommerce_Order_Debugger {

	public function __construct() {
		if ( ! $this->should_run() ) {
			return;
		}
		add_action( 'add_meta_boxes', array( $this, 'add_admin_order_meta_box' ) );
	}

	public static function init() {
		$class = __CLASS__;
		new $class();
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
		return 'yes' === get_option( 'woocommerce_custom_orders_table_enabled' );
	}

	public function display_custom_metabox_content( $the_order ) {
		$order = wc_get_order( $the_order->ID );

		if ( ! $order instanceof WC_Order ) {
			return;
		}

		$order_meta_data = $order->get_meta_data();

		$html  = '<table style="width:100%;border:1px solid #ddd;border-collapse:collapse;">';
		$html .= '<tr style="background-color:#f7f7f7;">';
		$html .= '<th style="border:1px solid #ddd;padding:5px;text-align:left;">ID</th>';
		$html .= '<th style="border:1px solid #ddd;padding:5px;text-align:left;">Key</th>';
		$html .= '<th style="border:1px solid #ddd;padding:5px;text-align:left;">Value</th>';
		$html .= '</tr>';

		foreach ( $order_meta_data as $meta ) {
			$meta_data = $meta->get_data();
			$html     .= '<tr>';
			$html     .= '<td style="border:1px solid #ddd;padding:5px;">' . esc_html( $meta_data['id'] ) . '</td>';
			$html     .= '<td style="border:1px solid #ddd;padding:5px;">' . esc_html( $meta_data['key'] ) . '</td>';
			$html     .= '<td style="border:1px solid #ddd;padding:5px;">' . esc_html( $meta_data['value'] ) . '</td>';
			$html     .= '</tr>';
		}

		$html .= '</table>';

		echo wp_kses_post( $html );
	}
}
