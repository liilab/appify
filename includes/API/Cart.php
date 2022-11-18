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

	public function add_to_cart($request)
	{

		if (is_null(WC()->cart)) {
			wc_load_cart();
		}

		$items = $request->get_params();

		$response = array();

		foreach ($items as $item) {
			$product_id = $item['product_id'];
			$quantity = $item['quantity'];
			$variation_id = $item['variation_id'];
			$variation = $item['variation'];

			WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation);

			$response[] = array(
				'product_id' => $product_id,
				'quantity' => $quantity,
				'variation_id' => $variation_id,
				'variation' => $variation,
			);
		}

		return rest_ensure_response($response);

	}


	public function get_cart_items()
	{

		if (is_null(WC()->cart)) {
			wc_load_cart();
		}

		$cart = WC()->cart->get_cart();

		$data = [];

		foreach ($cart as $key => $item) {
			$data[] = [
				'key' => $key,
				'product_id' => $item['product_id'],
				'quantity' => $item['quantity'],
			];
		}


		$response = rest_ensure_response($data);
		return $response;
	}
}
