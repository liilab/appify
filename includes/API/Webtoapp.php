<?php

namespace WebToApp\API;

class Webtoapp extends \WP_REST_Controller {
	function __construct() {
		$this->namespace = 'web-to-app/v1';
		$this->rest_base = 'products';
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

		$thumbnail_id = get_post_thumbnail_id( $product->is_type( 'variation' ) ? $product->variation_id : $product->get_id() );
		$image        = wp_get_attachment_image_src( $thumbnail_id, 'full' );

		$dt = array_map( function ( $attr ) {
			return $attr->get_id();
		}, $product->get_attributes() );
//
//		echo '<pre>';
//		print_r($dt);
//		echo '</pre>';

		return $this->format_wc_product( $product );
	}

	public function format_wc_product( \WC_Product $product ): array {

		$data = [
			'id'                    => $product->get_id(),
			'name'                  => $product->get_name(),
			'slug'                  => $product->get_slug(),
			'permalink'             => $product->get_permalink(),
			'type'                  => $product->get_type(),
			'featured'              => $product->get_featured(),
			'short_description'     => $product->get_short_description(),
			'sku'                   => $product->get_short_description(),
			'currency'              => get_woocommerce_currency(),
			'currency_symbol'       => get_woocommerce_currency_symbol(),
			'price'                 => $product->get_price(),
			'regular_price'         => $product->get_regular_price(),
			'sale_price'            => $product->get_sale_price() ? $product->get_sale_price() : ( ( $product->get_price() < $product->get_regular_price() ) ? $product->get_price() : '' ),

//			hudai
			'price_display'         => $product->get_price(),
			'regular_price_display' => $product->get_regular_price(),
			'sale_price_display'    => $product->get_sale_price(),
//			end hduai

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
			'dimensions'                  => array(
				'length' => $product->get_length(),
				'width'  => $product->get_width(),
				'height' => $product->get_height(),
			),
			'reviews_allowed'             => $product->get_reviews_allowed(),
			'display_rating'              => ( get_option( 'woocommerce_enable_review_rating' ) === 'no' ) ? false : true,
			'average_rating'              => $product->get_average_rating(),
			'rating_count'                => $product->get_rating_count(),
//			'images'                      => $this->get_images($product),
			'thumbnail'                   => $product->get_image_id(),
			'thumbnail_meta'              => 'attention here!',
			'images_meta'                 => 'attention here!',
			'notify_backorder'            => $product->backorders_allowed(),
			'notify_backorder_label'      => 'attention here!',
			'attributes'                  => $this->get_attributes( $product ),
			'default_attributes'          => $this->get_default_attributes( $product ),
			'product_in_webview'          => 'attention here!',
			'labels'                      => 'attention here!',
			'variations'                  => array_map( function ( $id ) {
				return $this->format_wc_product( wc_get_product( $id ) );
			}, $product->get_children() ),
			'product_widgets'             => $product,
		];

		return $data;

	}

	protected function get_default_attributes( $product ) {
		$default = array();

		if ( $product->is_type( 'variable' ) ) {
			foreach ( array_filter( (array) get_post_meta( $product->get_id(), '_default_attributes', true ), 'strlen' ) as $key => $value ) {
				if ( 0 === strpos( $key, 'pa_' ) ) {
					$default[] = array(
						'id'     => $key,
						'name'   => $this->get_attribute_taxonomy_label( $key ),
						'option' => $value,
					);
				} else {
					$default[] = array(
						'id'     => $key,
						'name'   => str_replace( 'pa_', '', $key ),
						'option' => $value,
					);
				}
			}
		}

		return $default;
	}

	protected function get_attributes( $product, $variations = true, $visible = true ) {
		$attributes = array();

		if ( $product->is_type( 'variation' ) ) {

			// Variation attributes.
			foreach ( $product->get_variation_attributes() as $attribute_name => $attribute ) {
				// if( !empty( $attribute) ){
				$name = str_replace( 'attribute_', '', $attribute_name );
				//$name = strtolower($name);
				// Taxonomy-based attributes are prefixed with `pa_`, otherwise simply `attribute_`.
				if ( 0 === strpos( $attribute_name, 'attribute_pa_' ) ) {
					$attr_name = $this->get_attribute_taxonomy_label( $name );
					if ( $attr_name ) {    // fix for country code selector plugin which has empty attribute name
						$attributes[ $name ] = array(
							'id'     => $name,
							'name'   => $attr_name,
							'option' => $attribute,
						);
					}
				} else {
					$id                = $name;
					$attributes[ $id ] = array(
						'id'     => $id,
						'name'   => html_entity_decode( str_replace( 'pa_', '', $name ), ENT_QUOTES, 'UTF-8' ),
						'option' => $attribute,
					);
				}
				//}

			}
		} else {
			if ( ! $product->is_type( 'variable' ) ) {
				$variations = false;
			}
			foreach ( $product->get_attributes() as $attribute ) {
				if ( $variations && ! $visible ) {
					$display = $attribute['is_variation'];
				} elseif ( ! $variations && $visible ) {
					$display = $attribute['is_visible'];
				} elseif ( ! $variations && ! $visible ) {
					$display = false;
				} else {
					$display = true;
				}
				$only_variations = $variations && ! $visible;
				$id              = $attribute['name'];
				if ( $attribute['is_taxonomy'] && $display ) {
					$attributes[ $id ] = array(
						'id'       => $id,
						'name'     => $this->get_attribute_taxonomy_label( $attribute['name'] ),
						'position' => (int) $attribute['position'],
						'visible'  => (bool) $attribute['is_visible'],
						'options'  => $this->get_attribute_options( $product, $attribute, $only_variations ),
					);
				} elseif ( $display ) {
					$attributes[ $id ] = array(
						'id'       => $id,
						'name'     => str_replace( 'pa_', '', $attribute['name'] ),
						'position' => (int) $attribute['position'],
						'visible'  => (bool) $attribute['is_visible'],
						'options'  => $this->get_attribute_options( $product, $attribute, $only_variations ),
					);
				}

				if ( $id == 'pa_color' && $display ) {
					foreach ( $attributes[ $id ]['options'] as $color_key => $color ) {
						$attributes[ $id ]['options'][ $color_key ]['color_code'] = apply_filters( 'appmaker_wc_color_code', $color['slug'] );
					}
				}
			}
		}

		return array_values( $attributes );
	}

	protected function get_attribute_taxonomy_label( $name ) {
		$tax = get_taxonomy( $name );
		if ( empty( $tax ) ) {
			$tax = get_taxonomy( urldecode( $name ) );
		}
		if ( ! empty( $tax ) ) {
			$labels    = get_taxonomy_labels( $tax );
			$tax_label = $labels->singular_name;
			// WPML attribute label transaltion
			if ( class_exists( 'SitePress' ) ) {
				$tax_label = apply_filters( 'wpml_translate_single_string', $tax_label, 'WordPress', 'taxonomy singular name: ' . $tax_label );
			}

			return html_entity_decode( $tax_label, ENT_QUOTES, 'UTF-8' );
		} else {
			return '';
		}
	}

	protected function get_attribute_options( $product, $attribute, $only_variations = false ) {
		if ( $product->is_type( 'variable' ) ) {
			$variation_attrs = $product->get_variation_attributes();
			if ( isset( $attribute['name'] ) && isset( $variation_attrs[ $attribute['name'] ] ) ) {
				$variation_attrs = $variation_attrs[ $attribute['name'] ];
			} else {
				$variation_attrs = array();
			}
		} else {
			$variation_attrs = array();
		}

		if ( isset( $attribute['is_taxonomy'] ) && $attribute['is_taxonomy'] ) {
			$terms  = wc_get_product_terms( $product->get_id(), $attribute['name'], array( 'fields' => 'all' ) );
			$return = array();
			foreach ( $terms as $term ) {
				$in_variation = $this->check_option_in_variation( $variation_attrs, $term->slug );
				if ( ! $only_variations || $in_variation ) {
					$return[] = array(
						'name' => htmlspecialchars_decode( $term->name ),
						'slug' => $term->slug,
					);
				}
			}

			return $return;
		} elseif ( isset( $attribute['value'] ) ) {
			$terms  = explode( '|', $attribute['value'] );
			$return = array();
			foreach ( $terms as $term ) {
				$in_variation = $this->check_option_in_variation( $variation_attrs, $term );
				if ( ! $only_variations || $in_variation ) {
					$return[] = array(
						'name' => trim( $term ),
						//'slug' => $this->sanitize_id( $term ),
						'slug' => trim( $term ),
					);
				}
			}

			return $return;
		}

		return array();
	}

	protected function check_option_in_variation( $variation_attrs, $option ) {
		if ( ! empty( $variation_attrs ) ) {
			$regex = preg_replace( '/\//', '\/', trim( preg_quote( $option ) ) );

			return empty( $variation_attrs ) ? false : ( preg_grep( '/(' . $regex . ')/i', $variation_attrs ) ? true : false );
		} else {
			return false;
		}
	}

	public function ensure_absolute_link( $url ) {
		if ( ! preg_match( '~^(?:f|ht)tps?://~i', $url ) ) {
			$url = get_site_url( null, $url );
		}
		if ( substr( $url, 0, 2 ) === '//' ) {
			$url = 'https:' . $url;
		}

		return $url;
	}


	public function api_permissions_check( $request ) {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		return true;
	}

}
