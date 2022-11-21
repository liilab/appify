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
				'args'                => $this->get_collection_params(),
				'permission_callback' => array($this, 'get_items_permissions_check'),
			),
		));

		/**
		 * Get product details by id
		 */

		register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array($this, 'get_item'),
				'args'                => array(
					'context' => $this->get_context_param(array('default' => 'view')),
					'id'      => [
						'description' => __('Unique identifier for the object.'),
						'type'        => 'integer',
					],
				),
				'permission_callback' => array($this, 'get_items_permissions_check'),
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

	public function get_items_permissions_check($request)
	{
		return true;
	}

	/**
	 * Get a collection of items
	 */

	public function get_items($request)
	{
		if (isset($request['per_page'])) {
			$limit = $request['per_page'];
		} else {
			$limit = -1;
		}

		if (isset($request['page'])) {
			$page = $request['page'];
		} else {
			$page = 1;
		}

		if (isset($request['search'])) {
			$search = $request['search'];
		} else {
			$search = '';
		}

		$types = array('simple');

		$products = wc_get_products(
			array(
				'limit'  => $limit,
				'page'   => $page,
				'status' => 'publish',
				'type'   => $types,
				's' => $search
			)
		);

		foreach ($products as $product) {
			$data[] = $this->format_wc_product($product);
		}

		$types = array('variable');

		$products = wc_get_products(
			array(
				'limit'  => $limit,
				'page'   => $page,
				'status' => 'publish',
				'type'   => $types,
				's' => $search
			)
		);

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

		if (empty($post)) {
			return new \WP_Error('rest_post_invalid_id', __('Invalid post ID.'), array('status' => 404));
		}

		$product = wc_get_product($post->ID);
		$data = $this->format_wc_product($product);
		$data['variations'] = $this->get_variation_data($product);
		$data['description'] = $product->get_description();
		$data['short_description'] = $product->get_short_description();
		$data['specifications'] = $this->get_display_attributes($product);
		$data['related_products'] = $this->get_related_products($product);
		$data['reviews'] = $this->get_reviews($product);

		$response = new \WP_REST_Response($data);
		$response->set_status(200);
		return $response;
	}


	/**
	 * @param WC_Product|WC_Product_Simple $product
	 *
	 * @return array
	 */
	protected function get_display_attributes($product)
	{
		$attributes = $this->get_attributes($product, false, true);
		$attributes = apply_filters('woocommerce_display_product_attributes', $attributes, $product);

		$return     = array();

		if ($product->enable_dimensions_display()) {
			$addional_attributes = array();
			if ($product->has_weight()) {
				$addional_attributes[] = array(
					'name'    => __('Weight', 'woocommerce'),
					'options' => array(array('name' => wc_format_localized_decimal($product->get_weight()) . ' ' . esc_attr(get_option('woocommerce_weight_unit')))),
				);
			}
			if ($product->has_dimensions()) {
				$addional_attributes[] = array(
					'name'    => __('Dimensions', 'woocommerce'),
					'options' => array(array('name' => $product->get_dimensions())),
				);
			}
			$attributes = array_merge($addional_attributes, $attributes);
		}

		foreach ($attributes as $attribute) {
			if (isset($attribute['options']) && is_array($attribute['options'])) {
				$value = '';
				foreach ($attribute['options'] as $option) {
					$value .= $option['name'] . ', ';
				}

				$return[] = array(
					'label' => wc_attribute_label($attribute['name']),
					'value' => html_entity_decode(trim($value, " \t\n\r \v,")),
				);
			}
			if (isset($attribute['label']) && isset($attribute['value'])) {
				$return[] = array(
					'label' => wc_attribute_label($attribute['label']),
					'value' => html_entity_decode(trim($attribute['value'], " \t\n\r \v,")),
				);
			}
		}

		return $return;
	}

	public function get_related_products($product)
	{
		$related_ids = $product->get_related(3);
		$related = [];
		foreach ($related_ids as $related_id) {
			$related[] = $this->format_wc_product(wc_get_product($related_id));
		}
		return $related;
	}

	public function get_reviews($product)
	{
		$comment = get_comments(array(
			'post_id' => $product->get_id(),
			'status' => 'approve',
			'number' => 3,
		));

		$reviews = [];

		foreach ($comment as $c) {
			$reviews[] = array(
				'id' => (int)$c->comment_ID,
				'review' => $c->comment_content,
				'rating' => get_comment_meta($c->comment_ID, 'rating', true),
				'name' => $c->comment_author,
				'avatar' => get_avatar_url($c->comment_author_email),
			);
		}

		return $reviews;
	}

	protected function get_variation_data($product)
	{
		$variations = array();

		foreach ($product->get_children() as $child_id) {
			$variation = $product->get_child($child_id);
			if (!$variation->exists()) {
				continue;
			}

			$images = array();

			if (has_post_thumbnail($variation->get_id())) {
				$images = wp_get_attachment_image_src(get_post_thumbnail_id($variation->get_id()), 'full')[0];
			}

			$image_meta = wp_get_attachment_metadata(get_post_thumbnail_id($variation->get_id()));

			$image_meta = array(
				'width' => $image_meta['width'],
				'height' => $image_meta['height'],
			);

			$sale_percentage = $this->get_sale_percentage($variation);
			$sale_price = $variation->get_sale_price();
			$price = ((get_option('woocommerce_prices_include_tax', 'no') == 'no') && (get_option('woocommerce_tax_display_shop', 'inc') == 'incl')) ? $variation->get_price_including_tax() : $variation->get_price();
			$regular_price = ((get_option('woocommerce_prices_include_tax', 'no') == 'no') && (get_option('woocommerce_tax_display_shop', 'inc') == 'incl')) ? wc_get_price_including_tax($variation, array('price' => $variation->get_regular_price())) : $variation->get_regular_price();
			$variation_status = $variation->get_status();
			if ($variation_status != 'private') {
				$variations[] = array(
					'id'                    => $variation->get_variation_id(),
					'permalink'             => $variation->get_permalink(),
					'sku'                   => $variation->get_sku(),
					'price'                   => $price,
					'regular_price'           => $regular_price,
					'sale_price'            => $sale_price,
					'price_display'           => $this->get_display_price($price),
					'regular_price_display'   => $this->get_display_price($regular_price),
					'sale_price_display'    => $this->get_display_price($sale_price),
					'on_sale'               => $variation->is_on_sale(),
					'downloadable'          => $variation->is_downloadable(),
					'in_stock'              => $variation->is_in_stock(),
					'status'                => $variation_status,
					'sale_percentage'       => ($sale_percentage != 0) ? $sale_percentage . '%' : false,
					'purchasable'           => $product->is_purchasable(),
					'dimensions'            => array(
						'length' => $variation->get_length(),
						'width'  => $variation->get_width(),
						'height' => $variation->get_height(),
					),
					'image'                 => $images,
					'image_meta'           => $image_meta,
					'attributes'            => array_values($this->get_attributes($variation)),
				);
			}
		}

		return apply_filters('wta_wc_product_variations', $variations, $product);
	}

	public function get_display_price($price)
	{
		$price = wc_price($price);
		$price = strip_tags($price);
		$price = preg_replace('/&nbsp;/', ' ', $price);
		$price = html_entity_decode($price, ENT_QUOTES, 'UTF-8');

		return $price;
	}

	/**
	 * @param WC_Product|WC_Product_Variable $product
	 *
	 * @return float|int
	 */
	public function get_sale_percentage($product)
	{
		$maximumper = 0;
		if (!is_a($product, 'WC_Product_Variable')) {
			$sale_price = $product->get_sale_price() ? $product->get_sale_price() : (($product->get_price() < $product->get_regular_price()) ? $product->get_price() : '');
			if ($sale_price) {
				$maximumper = ($product->is_on_sale() && 0 != $product->get_regular_price() && ($product->get_regular_price() > $sale_price)) ? round((($product->get_regular_price() - $sale_price) / $product->get_regular_price()) * 100) : 0;
			}
		} else {
			$maximumper = apply_filters('wta_wc_sale_percentage', $maximumper, $product);
			if ($maximumper != 0 && !empty($maximumper)) {
				return $maximumper;
			}
			$regular_price = $product->get_variation_regular_price();
			$sales_price = $product->get_variation_price();
			$percentage        = (0 != $regular_price) ? round(((($regular_price - $sales_price) / $regular_price) * 100), 1) : 0;
			if ($percentage > $maximumper && ($percentage > 0 && $percentage < 100)) {
				$maximumper = $percentage;
			}
			//}
			//}
		}

		$percentage =  ($maximumper <= 0 || $maximumper >= 100) ? 0 : $maximumper;

		$percentage = $this->tostring($percentage);

		return $percentage;
	}

	public function tostring($percentage)
	{
		$percentage = number_format((float)$percentage, 2, '.', '');
		$percentage = (string) $percentage;
		return $percentage;
	}
	/**
	 * Get the query params for collections
	 *
	 * @return array
	 */

	public function format_wc_product($product)
	{
		$default_config = array(
			'type'        => 'normal',
			'label'       => __('Quantity', 'woocommerce'),
			'display'     => !$product->is_sold_individually(),
			'input_value' => '1',
			'max_value'   => apply_filters('woocommerce_quantity_input_max', $product->backorders_allowed() ? '' : $product->get_stock_quantity(), $product),
			'min_value'   => apply_filters('woocommerce_quantity_input_min', '1', $product),
			'step'        => apply_filters('woocommerce_quantity_input_step', '1', $product),
		);

		$thumbnail = $this->get_thumbnail($product);
		$thumbnail_meta = $this->get_thumbnail_meta($product);
		$thumbnail_meta = array(
			'width' => $thumbnail_meta['width'],
			'height' => $thumbnail_meta['height'],
		);

		$attributes      = $this->get_attributes($product, true, false);
		$price 			 = $this->get_price($product);
		$regular_price 	 = $this->get_regular_price($product);
		$sale_price 	 = $this->get_sale_price($product);

		$data = [
			'id'                          => $product->get_id(),
			'name'                        => $product->get_name(),
			'slug'                        => $product->get_slug(),
			'permalink'                   => $this->ensure_absolute_link($product->get_permalink()),
			'type'                        => $product->get_type(),
			'featured'                    => $product->get_featured(),
			'short_description'           => $product->get_short_description(),
			'sku'                         => $product->get_sku(),
			'currency'                    => get_woocommerce_currency(),
			'currency_symbol'             => html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8'),
			'price'                       => $price,
			'regular_price'               => $regular_price,
			'sale_price'                  => $sale_price,
			'price_display'               => $price . html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8'),
			'regular_price_display'       => $regular_price . html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8'),
			'sale_price_display'          => $this->get_sale_price_display($sale_price),
			'on_sale'                     => $product->is_on_sale(),
			'sale_percentage'             => $this->get_sale_percentage($product),
			'purchasable'                 => $product->is_purchasable(),
			'downloadable'                => $product->is_downloadable(),
			'display_add_to_cart'         => $product->is_purchasable() && $product->is_in_stock() && $product->is_type('simple'),
			'qty_config'                  => $default_config,
			'stock_quantity'              => $product->get_stock_quantity(),
			'in_stock'                    => $product->is_in_stock(),
			'weight'                      => $product->get_weight(),
			'dimensions'                  => $this->get_product_dimensions($product),
			'reviews_allowed'             => $product->get_reviews_allowed(),
			'display_rating'              => (get_option('woocommerce_enable_review_rating') === 'no') ? false : true,
			'average_rating'              => wc_format_decimal($product->get_average_rating(), 2),
			'rating_count'                => $product->get_rating_count(),
			'images'                      => array(),
			'thumbnail'                   => $thumbnail,
			'thumbnail_meta'              => $thumbnail_meta,
			'images_meta'                 => array(),
			'notify_backorder_label'      => __('On backorder', 'woocommerce'),
			//'variations'                  => $product->get_children(),
			'attributes'                  => array_values($attributes),
			'default_attributes'          => array_values($attributes),
			//'labels'                      => 'attention here///////',
		];

		if( $product->managing_stock() && $product->is_on_backorder( 1 ) && $product->backorders_require_notification() && $product->get_type() === 'simple'  ){
            $data['notify_backorder'] = true ;
        } elseif ( ! $product->managing_stock() && $product->is_on_backorder( 1 ) && $product->get_type() === 'simple' ) {
			$data['notify_backorder'] = true ;
        } else {
            $data['notify_backorder'] = false ;
        }

		$images              = $this->get_images($product);
		$images_meta         = $this->get_images_meta($product);

		$data['images']      = $images;
		$data['images_meta'] = $images_meta;

		if (empty($data['images'])) {
			$data['images'] = array($thumbnail);
			$size = $thumbnail_meta;
			$data['images_meta'][] = array('caption' => '', 'size' => $size);
		}

		return $data;
	}

	public function get_sale_price_display($sale_price)
	{
		if ($sale_price) {
			return $sale_price . html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8');
		} else {
			return '';
		}
	}

	public function get_price($product)
	{
		if ($product->get_type() == 'variable') {
			$price = $product->get_variation_price('min', true) . '-' . $product->get_variation_price('max', true);
		} else {
			$price = $product->get_price();
		}

		return $price;
	}

	public function get_regular_price($product)
	{
		if ($product->get_type() == 'variable') {
			$price = $product->get_variation_regular_price('max', true);
		} else {
			$price = $product->get_regular_price();
		}

		return $price;
	}

	public function get_sale_price($product)
	{
		if ($product->get_type() == 'variable') {
			$price = $product->get_variation_sale_price('min', true) . '-' . $product->get_variation_sale_price('max', true);
		} else {
			$price = $product->get_sale_price();
		}

		return $price;
	}

	public function get_images_meta($product)
	{
		$images_meta = array();
		$attachment_ids = $product->get_gallery_image_ids();

		foreach ($attachment_ids as $attachment_id) {
			$attachment = get_post($attachment_id);
			$size = wc_get_image_size('shop_single');
			$size = array('width' => $size['width'], 'height' => $size['height']);
			$images_meta[] = array('caption' => $attachment->post_excerpt, 'size' => $size);
		}

		return $images_meta;
	}

	public function get_thumbnail_meta($product)
	{
		$thumbnail_id = $product->get_image_id();
		$thumbnail_meta = array();
		if ($thumbnail_id) {
			$thumbnail_meta = wp_get_attachment_metadata($thumbnail_id);
		}
		return $thumbnail_meta;
	}

	/**
	 * Get the query params for collections
	 *
	 * @return array
	 */

	public function get_product_dimensions($product)
	{
		$dimensions = array(
			'length' => '',
			'width'  => '',
			'height' => '',
			'unit'   => get_option('woocommerce_dimension_unit'),
		);

		if ($product->has_dimensions()) {
			$dimensions['length'] = wc_format_localized_decimal($product->get_length());
			$dimensions['width']  = wc_format_localized_decimal($product->get_width());
			$dimensions['height'] = wc_format_localized_decimal($product->get_height());
		}

		return $dimensions;
	}

	public function get_image_size($attachment_id)
	{
		$attachment = get_post($attachment_id);
		$meta = wp_get_attachment_metadata($attachment_id);
		$size = array(
			'width' => $meta['width'],
			'height' => $meta['height'],
		);
		return $size;
	}

	/**
	 * Get the attributes for a product or product variation.
	 *
	 * @param WC_Product|WC_Product_Variation|WC_Product_Variable $product
	 *
	 * @param bool $variations
	 * @param bool $visible
	 *
	 * @return array
	 */
	protected function get_attributes($product, $variations = true, $visible = true)
	{
		$attributes = array();

		if ($product->is_type('variation')) {

			// Variation attributes.
			foreach ($product->get_variation_attributes() as $attribute_name => $attribute) {
				// if( !empty( $attribute) ){
				$name = str_replace('attribute_', '', $attribute_name);
				//$name = strtolower($name);
				// Taxonomy-based attributes are prefixed with `pa_`, otherwise simply `attribute_`.
				if (0 === strpos($attribute_name, 'attribute_pa_')) {
					$attr_name = $this->get_attribute_taxonomy_label($name);
					if ($attr_name) {    // fix for country code selector plugin which has empty attribute name
						$attributes[$name] = array(
							'id'     => $name,
							'name'   => $attr_name,
							'option' => $attribute,
						);
					}
				} else {
					$id                = $this->sanitize_id($name);
					$attributes[$id] = array(
						'id'     => $id,
						'name'   => html_entity_decode(str_replace('pa_', '', $name), ENT_QUOTES, 'UTF-8'),
						'option' => $attribute,
					);
				}
				//}

			}
		} else {
			if (!$product->is_type('variable')) {
				$variations = false;
			}
			foreach ($product->get_attributes() as $attribute) {
				if ($variations && !$visible) {
					$display = $attribute['is_variation'];
				} elseif (!$variations && $visible) {
					$display = $attribute['is_visible'];
				} elseif (!$variations && !$visible) {
					$display = false;
				} else {
					$display = true;
				}
				$only_variations = $variations && !$visible;
				$id              = $this->sanitize_id($attribute['name']);
				if ($attribute['is_taxonomy'] && $display) {
					$attributes[$id] = array(
						'id'       => $id,
						'name'     => $this->get_attribute_taxonomy_label($attribute['name']),
						'position' => (int) $attribute['position'],
						'visible'  => (bool) $attribute['is_visible'],
						'options'  => $this->get_attribute_options($product, $attribute, $only_variations),
					);
				} elseif ($display) {
					$attributes[$id] = array(
						'id'       => $id,
						'name'     => str_replace('pa_', '', $attribute['name']),
						'position' => (int) $attribute['position'],
						'visible'  => (bool) $attribute['is_visible'],
						'options'  => $this->get_attribute_options($product, $attribute, $only_variations),
					);
				}

				if ($id == 'pa_color' && $display) {
					foreach ($attributes[$id]['options'] as $color_key => $color) {
						$attributes[$id]['options'][$color_key]['color_code'] = apply_filters('wta_wc_color_code', $color['slug']);
					}
				}
			}
		}

		return apply_filters('wta_wc_product_attributes', $attributes, $product, $variations, $visible);
	}

	protected $sanitize_attribute_ids = true;

	private function sanitize_id($title)
	{
		$title = trim($title);
		//$title = strtolower( $title );
		return ($this->sanitize_attribute_ids) ? sanitize_title($title) : $title;
	}

	/**
	 * Get attribute options.
	 *
	 * @param WC_Product_Variation $product
	 * @param array $attribute
	 *
	 * @return array
	 */
	protected function get_attribute_options($product, $attribute, $only_variations = false)
	{
		if ($product->is_type('variable')) {
			$variation_attrs = $product->get_variation_attributes();
			if (isset($attribute['name']) && isset($variation_attrs[$attribute['name']])) {
				$variation_attrs = $variation_attrs[$attribute['name']];
			} else {
				$variation_attrs = array();
			}
		} else {
			$variation_attrs = array();
		}

		if (isset($attribute['is_taxonomy']) && $attribute['is_taxonomy']) {
			$terms  = wc_get_product_terms($this->get_id($product), $attribute['name'], array('fields' => 'all'));
			$return = array();
			foreach ($terms as $term) {
				$in_variation = $this->check_option_in_variation($variation_attrs, $term->slug);
				if (!$only_variations || $in_variation) {
					$return[] = array(
						'name' => htmlspecialchars_decode($term->name),
						'slug' => $term->slug,
					);
				}
			}

			return $return;
		} elseif (isset($attribute['value'])) {
			$terms  = explode('|', $attribute['value']);
			$return = array();
			foreach ($terms as $term) {
				$in_variation = $this->check_option_in_variation($variation_attrs, $term);
				if (!$only_variations || $in_variation) {
					$return[] = array(
						'name' => trim($term),
						//'slug' => $this->sanitize_id( $term ),
						'slug' => trim($term),
					);
				}
			}

			return $return;
		}

		return array();
	}

	public function get_id($object)
	{
		if (method_exists($object, 'get_id')) {

			return $object->get_id();
		} elseif (is_object($object)) {
			return $object->id;
		}
	}

	/**
	 * Check option in variation.
	 *
	 * @param array $variation_attrs $variation_attrs Array.
	 * @param string $option option to check.
	 *
	 * @return bool
	 */
	protected function check_option_in_variation($variation_attrs, $option)
	{
		if (!empty($variation_attrs)) {
			$regex = preg_replace('/\//', '\/', trim(preg_quote($option)));

			return empty($variation_attrs) ? false : (preg_grep('/(' . $regex . ')/i', $variation_attrs) ? true : false);
		} else {
			return false;
		}
	}

	/**
	 * Get attribute taxonomy label.
	 *
	 * @param  string $name
	 *
	 * @return string
	 */
	protected function get_attribute_taxonomy_label($name)
	{
		$tax = get_taxonomy($name);
		if (empty($tax)) {
			$tax = get_taxonomy(urldecode($name));
		}
		if (!empty($tax)) {
			$labels = get_taxonomy_labels($tax);
			$tax_label = $labels->singular_name;
			// WPML attribute label transaltion
			if (class_exists('SitePress')) {
				$tax_label =  apply_filters('wpml_translate_single_string', $tax_label, 'WordPress', 'taxonomy singular name: ' . $tax_label);
			}
			return html_entity_decode($tax_label, ENT_QUOTES, 'UTF-8');
		} else {
			return '';
		}
	}

	/**
	 * Get the query params for collections
	 *
	 * @return array
	 */

	protected function get_buy_now_action($product)
	{
		if ($product->is_type('external')) {
			return array(
				'type'   => 'OPEN_URL',
				'params' => array('url' => $product->get_product_url()),
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

	public function get_thumbnail($product)
	{
		$thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()), 'full');
		if ($thumbnail) {
			return $thumbnail[0];
		} else {
			return wc_placeholder_img_src();
		}
	}

	/*
	 * Get the query params for collections
	 *
	 * @return array
	 */

	public function ensure_absolute_link($url)
	{
		if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
			$url = get_site_url(null, $url);
		}
		if (substr($url, 0, 2) === '//') {
			$url = 'https:' . $url;
		}
		return $url;
	}

	/*
	 * Get the query params for collections
	 *
	 * @return array
	 */



	/*
	 * Get the query params for collections
	 *
	 * @return array
	 */

	public static function get_image_dimensions($item, $size = false, $thumbnail = false)
	{

		$cacheEnabled = false;
		if (is_string($item)) {
			$id   = attachment_url_to_postid($item);
			$item = get_post($id);
		}
		if (!is_a($item, 'WP_Post')) {
			return false;
		}
		if ($size === false) {
			$size = 'medium';
		}
		$image_url = wp_get_attachment_image_src($item->ID,  $size);

		if (is_array($image_url) && !empty($image_url[1])) {
			if (empty($image_url[2])) {
				$image_url[2] = 1;
			}
			return array(
				'width'  => $image_url[1],
				'height' => $image_url[2],
			);
		}

		$size_array = array();

		if ($cacheEnabled) {
			$cache_key = 'wta_wc_image_dimension_item_' . $item->ID;
			$response  = get_transient($cache_key);
			if (!empty($response)) {
				return $response;
			}
		} else {
			$meta = wp_get_attachment_metadata($item->ID);
		}

		if (empty($meta)) {
			$attachment_path = get_attached_file($item->ID);
			$attach_data     = wp_generate_attachment_metadata($item->ID, $attachment_path);
			wp_update_attachment_metadata($item->ID, $attach_data);
			// Wrap the data in a response object
			$meta = wp_get_attachment_metadata($item->ID);
		}
		if (isset($meta['sizes']) && is_array($meta['sizes']) && !empty($meta['sizes']) && !empty($size)) {
			foreach ($meta['sizes'] as $meta_size_id => $meta_size) {
				if ($meta_size_id == $size && isset($meta_size['width'], $meta_size['height'])) {
					$size_array = array(
						'width'  => $meta_size['width'],
						'height' => $meta_size['height'],
					);
				}
			}
		}

		if (empty($size_array)) {
			if (isset($meta['width'], $meta['height']) && !$thumbnail) {

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

		if ($cacheEnabled) {
			$cache_key = 'wta_wc_image_dimension_item_' . $item->ID;
			set_transient($cache_key, $size_array, 60 * 60 * 24 * 30);
		}
		return $size_array;
	}

	/*
	 * Get the query params for collections
	 *
	 * @return array
	 */

	protected function get_images($product)
	{
		$images = array();
		$gallery = $product->get_gallery_image_ids();
		if (!empty($gallery)) {
			foreach ($gallery as $image_id) {
				$image = wp_get_attachment_image_src($image_id, 'full');
				if ($image) {
					$images[] = $image[0];
				}
			}
		}
		return $images;
	}
}
