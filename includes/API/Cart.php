<?php

namespace WebToApp\API;


class Cart extends \WebToApp\Abstracts\WTA_WC_REST_Controller
{

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'web-to-app/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'cart';

	/**
	 * Register the routes for products.
	 */
	public function register_routes()
	{
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_cart_items'),
					'permission_callback' => array($this, 'get_cart_items_permissions_check'),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'add_to_cart'),
					'permission_callback' => array($this, 'add_to_cart_permissions_check'),
				),
			)
		);
	}

	public function add_to_cart_permissions_check($request)
	{
		return true;
	}

	public function add_to_cart($request)
	{

		if (is_null(WC()->cart)) {
			wc_load_cart();
		}

		$items = $request->get_params("items");

		$items= $items['items'];

		$response = array();

		$coupon = $request->get_param("coupon");

		if ($coupon) {
			$coupon = new \WC_Coupon($coupon);
			if ($coupon->is_valid()) {
				WC()->cart->add_discount($coupon->get_code());
			}
		}

		foreach ($items as $item) {
			$product_id = $item['product_id'];
			$quantity = $item['quantity'];
			$variation_id = $item['variation_id'];
			$variation = $item['variation'];

			WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation);
		}

		$response = $this->get_cart_items($request);

		return rest_ensure_response($response);

	}


	public function get_cart_items()
	{

		if (is_null(WC()->cart)) {
			wc_load_cart();
		}
		
		$cart = WC()->cart->get_cart();

		$cart_items = array();

		$cart_items['coupons_applied'] = WC()->cart->get_applied_coupons();
		$cart_items['coupon_discounted'] = WC()->cart->get_coupon_discount_totals();
		$cart_items['count'] = WC()->cart->get_cart_contents_count();
		$cart_items['shipping_fee'] = WC()->cart->get_shipping_total();
		$cart_items['shipping_fee_display'] = WC()->cart->get_shipping_total();
		$cart_items['tax'] = WC()->cart->get_cart_contents_tax();
		$cart_items['tax_display'] = WC()->cart->get_cart_contents_tax();
		$cart_items['fees'] = WC()->cart->get_fees();
		$cart_items['currency'] = get_woocommerce_currency();
		$cart_items['currency_symbol'] = get_woocommerce_currency_symbol();
		$cart_items['total'] = WC()->cart->get_total();
		$cart_items['cart_total'] = WC()->cart->get_cart_total();
		$cart_items['order_total'] = WC()->cart->get_total();
		$cart_items['total_display'] = WC()->cart->get_total();
		$cart_items['cart_total_display'] = WC()->cart->get_cart_total();
		$cart_items['order_total_display'] = WC()->cart->get_total();
		$cart_items['product_subtotal_display'] = WC()->cart->get_cart_subtotal();
		$cart_items['header_message'] = WC()->cart->get_cart_subtotal();
		$cart_items['price_format'] = get_woocommerce_price_format();
		$cart_items['tax_included'] = WC()->cart->tax_display_cart === 'incl';
		$cart_items['weight_unit'] = get_option( 'woocommerce_weight_unit' );
		$cart_items['dimension_unit'] = get_option( 'woocommerce_dimension_unit' );
		$cart_items['can_proceed'] = WC()->cart->needs_payment();
		$cart_items['error_message'] = WC()->cart->get_cart_contents_count() ? '' : __( 'Cart is empty', 'woocommerce' );
		$cart_items['show_tax'] = wc_tax_enabled() && ! WC()->cart->display_prices_including_tax();
		$cart_items['product_count'] = WC()->cart->get_cart_contents_count();
		$cart_items['total_quantity_in_cart'] = WC()->cart->get_cart_contents_count();
		$cart_items['products'] = array();

		foreach ( $cart as $cart_item_key => $cart_item ) {
			$_product = $cart_item['data'];

			$product = array();

			$product['key'] = $cart_item_key;
			$product['product_id'] = $_product->get_id();
			$product['variation_id'] = $cart_item['variation_id'];
			$product['variation'] = $cart_item['variation'];
			$product['quantity'] = $cart_item['quantity'];
			$product['data_hash'] = $cart_item['data_hash'];
			$product['line_tax_data'] = $cart_item['line_tax_data'];
			$product['line_subtotal'] = $cart_item['line_subtotal'];
			$product['line_subtotal_tax'] = $cart_item['line_subtotal_tax'];
			$product['line_total'] = $cart_item['line_total'];
			$product['line_tax'] = $cart_item['line_tax'];
			$product['product_title'] = $_product->get_title();
			$product['short_description'] = $_product->get_short_description();
			$product['featured_src'] = wp_get_attachment_image_src( $_product->get_image_id(), 'woocommerce_thumbnail' )[0];
			$product['product_type'] = $_product->get_type();
			$product['product_price'] = $_product->get_price();
			$product['product_regular_price'] = $_product->get_regular_price();
			$product['product_sale_price'] = $_product->get_sale_price();
			$product['product_regular_price_display'] = $_product->get_regular_price();
			$product['product_sale_price_display'] = $_product->get_sale_price();
			$product['on_sale'] = $_product->is_on_sale();
			$product['product_in_webview'] = false;
			$product['qty_config'] = array(
				'type' => 'normal',
				'label' => 'Quantity',
				'display' => true,
				'input_value' => '1',
				'max_value' => '1000',
				'min_value' => 1,
				'step' => '1',
			);
			$product['regular_price_display'] = $_product->get_regular_price();
			$product['line_total_display'] = $cart_item['line_total'];
			$product['product_price_display'] = $_product->get_price();

			$cart_items['products'][] = $product;
		}

		return $cart_items;
	}
}
