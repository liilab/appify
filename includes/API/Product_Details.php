<?php

namespace WebToApp\API;

class Product_Details extends \WP_REST_Controller {
	public $user_token;
	public $user;

	public function __construct() {
		$this->namespace = 'web-to-app/v1';
		$this->rest_base = 'products';

		$this->user = wp_get_current_user();
		$this->user_token = \WebToApp\User\Token::get_user_access_token($this->user->ID);
	}

	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'api_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'api_permissions_check' ),
				'args'                => array(
					'context' => $this->get_context_param( array( 'default' => 'view' ) ),
					'id'      => [
						'description' => __( 'Unique identifier for the object.' ),
						'type'        => 'integer',
					],
				),
			),
		) );
	}

	public function get_items( $request ) {

		$products = wc_get_products( array(
			'limit'  => - 1,
			'page'   => 1,
			'status' => 'publish',
		) );

		$data = array_map( function ( $product ) {
			return $this->format_wc_product( $product );
		}, $products );


		$response = new \WP_REST_Response( $data );
		$response->set_status( 200 );

		return $response;
	}

	public function get_item( $request ) {
		$id   = (int) $request['id'];
		$post = get_post( $id );

		$product = wc_get_product( $post->ID );

		$products = wc_get_products( array(
			'limit'  => - 1,
			'status' => 'publish',
		) );


		$thumbnail_id = get_post_thumbnail_id( $product->is_type( 'variation' ) ? $product->variation_id : $product->get_id() );
		$image        = wp_get_attachment_image_src( $thumbnail_id, 'full' );


		return $this->format_wc_product( $product );
	}

	public function format_wc_product( \WC_Product $product ): array {

		

		$data = [
			'id'                          => $product->get_id(),
			'name'                        => $product->get_name(),
			'slug'                        => $product->get_slug(),
			'permalink'                   => $product->get_permalink(),
			'type'                        => $product->get_type(),
			'featured'                    => $product->get_featured(),
			'short_description'           => $product->get_short_description(),
			'sku'                         => $product->get_short_description(),
			'currency'                    => get_woocommerce_currency(),
			'currency_symbol'             => get_woocommerce_currency_symbol(),
			'price'                       => $product->get_price(),
			'regular_price'               => $product->get_regular_price(),
			'sale_price'                  => $product->get_sale_price(),
			'price_display'               => $product->get_price(),
			'regular_price_display'       => $product->get_regular_price(),
			'sale_price_display'          => $product->get_sale_price(),
			'on_sale'                     => $product->is_on_sale(),
			'purchasable'                 => $product->is_purchasable(),
			'downloadable'                => $product->is_downloadable(),
			'display_add_to_cart'         => $product->is_purchasable() && $product->is_in_stock(),
			'change_thumbnail_image_size' => 'attention here!',
			'hide_buy_now_block'          => $product->is_type( 'variable' ),
//			'buy_now_action'                => $product->is_type( 'variable' ) ? 'select_options' : 'add_to_cart',
			'buy_now_action'              => 'attention here!',
			'buy_now_button_text'         => $product->is_type( 'variable' ) ? __( 'Select options', 'woocommerce' ) : __( 'Add to cart', 'woocommerce' ),
			'add_to_cart_button_text'     => __( 'Add to cart', 'woocommerce' ),
			'qty_config'                  => 'attention here',
			'stock_quantity'              => $product->get_stock_quantity(),
			'in_stock'                    => $product->is_in_stock(),
			'weight'                      => $product->get_weight(),
			'dimensions'                  => $product->get_dimensions(),
			'reviews_allowed'             => $product->get_reviews_allowed(),
			'display_rating'              => 'attention here!',
			'average_rating'              => $product->get_average_rating(),
			'rating_count'                => $product->get_rating_count(),
			'images'                      => $product->get_gallery_image_ids(),
			'thumbnail'                   => $product->get_image_id(),
			'thumbnail_meta'              => 'attention here!',
			'images_meta'                 => 'attention here!',
			'notify_backorder'            => $product->backorders_allowed(),
			'notify_backorder_label'      => 'attention here!',
			'color'                       => 'attention here!',
			'attributes'                  => 'attention here!',
			'default_attributes'          => 'attention here!',
			'product_in_webview'          => 'attention here!',
			'labels'                      => 'attention here!',
//			'variations'                    => $product->get_available_variations(),
			'variations'                  => $product->get_children(),
			'product_widgets'             => $product,
		];

		return $data;

	}

	public function api_permissions_check( $request ) {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		return true;
	}

}
