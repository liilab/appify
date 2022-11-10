<?php

namespace WebToApp\API;

/**
 * Class Product
 * @package WebToApp\API
 */

class Product extends \WP_REST_Controller
{
	public $user_token;
	public $user;

	public function __construct()
	{
		$this->namespace = 'web-to-app/v1';
		$this->rest_base = 'products';

		$this->user = wp_get_current_user();
		$this->user_token = \WebToApp\User\Token::get_user_access_token($this->user->ID);
	}

	/**
	 * Register the routes for the objects of the controller.
	 */

	public function register_routes()
	{
		/**
		 * Get product details
		 */
		register_rest_route($this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array($this, 'get_items'),
				'permission_callback' => array($this, 'api_permissions_check'),
				'args'                => $this->get_collection_params(),
			),
		));

		/**
		 * Get product details by id
		 */

		register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array($this, 'get_item'),
				'permission_callback' => array($this, 'api_permissions_check'),
				'args'                => array(
					'context' => $this->get_context_param(array('default' => 'view')),
					'id'      => [
						'description' => __('Unique identifier for the object.'),
						'type'        => 'integer',
					],
				),
			),
		));
	}

	/**
	 * Check if a given request has access to get items
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|\WP_Error
	 */

	public function api_permissions_check($request)
    {
		if (current_user_can('manage_options')) {
			return true;
		}

		return true;
    }

	/**
	 * Get a collection of items
	 */

	public function get_items($request)
	{

		$products = wc_get_products(array(
			'limit'  => -1,
			'page'   => 1,
			'status' => 'publish',
		));

		foreach ($products as $product) {
			$data[] = $this->format_wc_product($product);
		}


		$response = new \WP_REST_Response($data);
		$response->set_status(200);

		return $response;
	}

	/**
	 * Get one item from the collection
	 */

	public function get_item($request)
	{
		$id   = (int) $request['id'];
		$post = get_post($id);
		$product = wc_get_product($post->ID);
		$data[] = $this->format_wc_product($product);
		$response = new \WP_REST_Response($data);
		$response->set_status(200);
		return $response;
	}

	/**
	 * Get the query params for collections
	 *
	 * @return array
	 */

	public function format_wc_product($product)
	{
		$thumbnail = $this->get_thumbnail( $product );

		$data = [
			'id'                          => $product->get_id(),
			'name'                        => $product->get_name(),
			'slug'                        => $product->get_slug(),
			'permalink'                   => $this->ensure_absolute_link($product->get_permalink()),
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
			'hide_buy_now_block'          => $product->is_type('variable'),
			'buy_now_action'              => $this->get_buy_now_action( $product ),
			'buy_now_button_text'         => $product->is_type('variable') ? __('Select options', 'woocommerce') : __('Add to cart', 'woocommerce'),
			'add_to_cart_button_text'     => __('Add to cart', 'woocommerce'),
			'stock_quantity'              => $product->get_stock_quantity(),
			'in_stock'                    => $product->is_in_stock(),
			'weight'                      => $product->get_weight(),
			'dimensions'                  => $product->get_dimensions(),
			'reviews_allowed'             => $product->get_reviews_allowed(),
			'average_rating'              => $product->get_average_rating(),
			'rating_count'                => $product->get_rating_count(),
			'images'                      => array(),
			'thumbnail'                   => $thumbnail['url'],
			'thumbnail_meta'              => $thumbnail['size'],
			'images_meta'                 => array(),
			'notify_backorder'            => $product->backorders_allowed(),
			'notify_backorder_label'      => __( 'On backorder', 'woocommerce' ),
			'variations'                  => $product->get_children(),
			'product_widgets'             => $product,

			'change_thumbnail_image_size' => 'attention here!',
			'color'                       => 'attention here!',
			'attributes'                  => 'attention here!',
			'default_attributes'          => 'attention here!',
			'product_in_webview'          => 'attention here!',
			'labels'                      => 'attention here!',
			'qty_config'                  => 'attention here',
			'display_rating'              => 'attention here!',
		];

		$images              = $this->get_images( $product, $expanded =false, $product->ID );            
        $data['images']      = $images['images'];
        $data['images_meta'] = $this->get_images_meta( $images['attachment_ids'] );

        if(empty($data['images'])){
            $data['images'] = array($thumbnail['url']);
            $size = $thumbnail['size'];  
            $data['images_meta'][] = array('caption'=>'','size'=>$size);         
        } 

		return $data;
	}

	/**
	 * Get the query params for collections
	 *
	 * @return array
	 */

	protected function get_buy_now_action( $product ) {
        if ( $product->is_type( 'external' ) ) {
            return array(
                'type'   => 'OPEN_URL',
                'params' => array( 'url' => $product->get_product_url() ),
            );
        } else {
            return array(
                'type'   => 'normal',
                'params' => array(),
            );
        }
    }

	/*
	 * Get the query params for collections
	 *
	 * @return array
	 */

	public function get_thumbnail( $product ) {

        
        $size='medium';

        $size = apply_filters( 'wta_wc_product_image_size', $size );
        $thumbnail_id = get_post_thumbnail_id( $product->is_type( 'variation' ) ? $product->variation_id : $product->ID);
        $image = wp_get_attachment_image_src( $thumbnail_id,  $size  );
        if ( empty( $image ) ) {
            $image =  array(
                "url" => $this->ensure_absolute_link( wc_placeholder_img_src() ),
                "size" => wc_get_image_size( $size )
            );
        } else {
            $thumb_post = get_post($thumbnail_id);
            $image['url'] = $this->ensure_absolute_link( $image[0] );
            if( !empty($image[1]) && !empty($image[2])  ) {               
                $image['size']  = array(
                        'width'  => $image[1],
                        'height' => $image[2],
                    );
            }else {
                $image['size'] = $this->get_image_dimensions($thumb_post ,$size ,true );
            }

        }
        $image = apply_filters('wta_wc_product_image_url',$image,$size);
        return $image;
    }

	/*
	 * Get the query params for collections
	 *
	 * @return array
	 */

	public function ensure_absolute_link( $url )
    {
        if (! preg_match('~^(?:f|ht)tps?://~i', $url) ) {
            $url = get_site_url(null, $url);
        }
        if (substr($url, 0, 2) === '//' ) {
            $url = 'https:' . $url;
        }
        return $url;
    }

	/*
	 * Get the query params for collections
	 *
	 * @return array
	 */

	protected function get_images_meta( $attachment_ids ) {
        $data = array();
        foreach ( $attachment_ids as $id ) {
            $attachment = get_post( $id );
            $data[]     = array(
                'caption' => function_exists( 'wp_get_attachment_caption' ) && wp_get_attachment_caption( $id ) ? $attachment->post_excerpt : '',
                'size'  => $this->get_image_dimensions($attachment)
            );
        }

        return $data;
    }

	/*
	 * Get the query params for collections
	 *
	 * @return array
	 */

	public static function get_image_dimensions( $item, $size = false, $thumbnail = false ) {

		$cacheEnabled = false;
		if ( is_string( $item ) ) {
			$id   = attachment_url_to_postid( $item );
			$item = get_post( $id );
		}
		if ( ! is_a( $item, 'WP_Post' ) ) {
			return false;
		}
		if( $size === false ){
			$size='medium';
		}		
		$image_url = wp_get_attachment_image_src( $item->ID,  $size  );
	
		if( is_array( $image_url ) && ! empty( $image_url[1] )  ) {
			if( empty( $image_url[2] ) ) {
				$image_url[2] = 1;
			}
			return array(
					'width'  => $image_url[1],
					'height' => $image_url[2],
				);
		}

		$size_array = array();

		if ( $cacheEnabled ) {
			$cache_key = 'wta_wc_image_dimension_item_' . $item->ID;
			$response  = get_transient( $cache_key );
			if ( ! empty( $response ) ) {
				return $response;
			}
		} else {
			$meta = wp_get_attachment_metadata( $item->ID );
		}

		if ( empty( $meta ) ) {
			$attachment_path = get_attached_file( $item->ID );
			$attach_data     = wp_generate_attachment_metadata( $item->ID, $attachment_path );
			wp_update_attachment_metadata( $item->ID, $attach_data );
			// Wrap the data in a response object
			$meta = wp_get_attachment_metadata( $item->ID );
		}
		if ( isset( $meta['sizes'] ) && is_array( $meta['sizes'] ) && ! empty( $meta['sizes'] ) && ! empty( $size ) ) {
			foreach ( $meta['sizes'] as $meta_size_id => $meta_size ) {
				if ( $meta_size_id == $size && isset( $meta_size['width'], $meta_size['height'] ) ) {
					$size_array = array(
						'width'  => $meta_size['width'],
						'height' => $meta_size['height'],
					);
				}
			}
		}

		if ( empty( $size_array ) ) {
			if ( isset( $meta['width'], $meta['height'] ) && ! $thumbnail ) {

				$size_array = array(
					'width'  => $meta['width'],
					'height' => $meta['height'],
				);
			} else {
				$size_array = array(
					'width'  => 300,
					'height' => 300,
				);
			}
		}

		if ( $cacheEnabled ) {
			$cache_key = 'wta_wc_image_dimension_item_' . $item->ID;
			set_transient( $cache_key, $size_array, 60 * 60 * 24 * 30 );
		}
		return $size_array;
	}

	/*
	 * Get the query params for collections
	 *
	 * @return array
	 */

	protected function get_images( $product, $merge_images = false, $merge_id = 0 ) {
        $images         = array();
        $attachment_ids = array();

        if($merge_images){

            if ( $product->is_type( 'variation' ) ) {
                if ( has_post_thumbnail( $product->get_variation_id() ) ) {
    
                    // Add variation image if set.
                    $attachment_ids[] = get_post_thumbnail_id( $product->get_variation_id() );
                } elseif ( has_post_thumbnail($product->ID ) ) {
                    // Otherwise use the parent product featured image if set.
                    $attachment_ids[] = get_post_thumbnail_id( $product->ID );
                }
            } else {
                // Add featured image.
                if ( has_post_thumbnail( $product->ID ) ) {
                    $attachment_ids[] = get_post_thumbnail_id( $product->ID );
                }
                // Add gallery images.
                if ( method_exists( $product, 'get_gallery_image_ids' ) ) {
                    $attachment_ids = array_merge( $attachment_ids, $product->get_gallery_image_ids() );
                } else {
                    $attachment_ids = array_merge( $attachment_ids, $product->get_gallery_attachment_ids() );
                }
            }
    
            // Build image data.
            foreach ( $attachment_ids as $position => $attachment_id ) {
                $attachment_post = get_posts( $attachment_id );
                if ( is_null( $attachment_post ) ) {
                    continue;
                }
    
                $attachment = wp_get_attachment_image_src( $attachment_id, 'full' );
                if ( ! is_array( $attachment ) ) {
                    continue;
                }
               $image    = $this->ensure_absolute_link( current( $attachment ) );
                $images[] = $image;
    
            }
    
            if ( true === $merge_images ) {
                if ( ! isset( $this->product_images[ $merge_id ] ) ) {
                    $this->product_images[ $merge_id ]    = array();
                    $this->product_image_ids[ $merge_id ] = array();
                }
                $this->product_images[ $merge_id ]    = array_merge( $this->product_images[ $merge_id ], $images );
                $this->product_image_ids[ $merge_id ] = array_merge( $this->product_image_ids[ $merge_id ], $attachment_ids );
    
            } elseif ( empty( $images ) ) {
                // Set a placeholder image if the product has no images set.
                $image    = $this->ensure_absolute_link( wc_placeholder_img_src() );
                $images[] = $image;
            }

        }        
        $images = apply_filters( 'wta_wc_product_images', $images );
        return array( 'images' => $images, 'attachment_ids' => $attachment_ids );
    }
}
