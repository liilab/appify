<?php

namespace WebToApp\API;


class Cart extends \WP_REST_Controller
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
				),
			)
		);
	}

	public function add_to_cart( $request ) {
		$product_id = $request->get_param( 'product_id' );
		$quantity = $request->get_param( 'quantity' );
	}


	public function get_cart_items()
	{

		$session = new \WC_Session_Handler();
		$session->init();

		$cart = $session->get('cart');

		$cart = maybe_unserialize($cart);

		$data = array();

		//$session_data = $session->get_session_data();

		foreach ($cart as $key => $value) {
			$product = wc_get_product($value['product_id']);
			$data[] = array(
				'key' => $key,
				'product_id' => $value['product_id'],
				'variation_id' => $value['variation_id'],
				'variation' => $value['variation'],
				'quantity' => $value['quantity'],
				'data_hash' => $value['data_hash'],
				'line_tax_data' => $value['line_tax_data'],
				'line_subtotal' => $value['line_subtotal'],
				'line_subtotal_tax' => $value['line_subtotal_tax'],
				'line_total' => $value['line_total'],
				'line_tax' => $value['line_tax'],
				'product' => array(
					'id' => $product->get_id(),
					'name' => $product->get_name(),
					'price' => $product->get_price(),
					'images' => $product->get_image(),
					'permalink' => $product->get_permalink(),
				)
			);
		}
		return $data;
	}
}
