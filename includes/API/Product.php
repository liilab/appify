<?php

namespace WebToApp\API;

/**
 * Class Product
 * @package Appify\API
 */

class Product extends \WebToApp\Abstracts\WTA_WC_REST_Controller
{
	public $user_token;
	public $user;

	public function __construct()
	{
		$this->namespace = 'web-to-app/v1';
		$this->rest_base = 'products';

		$this->user       = wp_get_current_user();
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
				's'      => $search,
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
				's'      => $search,
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

		$product                   = wc_get_product($post->ID);
		$data                      = $this->format_wc_product($product);
		$data['variations']        = $this->get_variation_data($product);
		$data['description']       = $product->get_description();
		$data['short_description'] = $product->get_short_description();
		$data['specifications']    = $this->get_display_attributes($product);
		$data['related_products']  = $this->get_related_products($product);
		$data['reviews']           = $this->get_reviews($product);

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

		$return = array();

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
		$related     = [];
		foreach ($related_ids as $related_id) {
			$related[] = $this->format_wc_product(wc_get_product($related_id));
		}
		return $related;
	}

	public function get_reviews($product)
	{
		$comment = get_comments(array(
			'post_id' => $product->get_id(),
			'status'  => 'approve',
			'number'  => 3,
		));

		$reviews = [];

		foreach ($comment as $c) {
			$reviews[] = array(
				'id'     => (int) $c->comment_ID,
				'review' => $c->comment_content,
				'rating' => get_comment_meta($c->comment_ID, 'rating', true),
				'name'   => $c->comment_author,
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

			if ($image_meta) {
				$image_meta = array(
					'width'  => $image_meta['width'] ? $image_meta['width'] : 0,
					'height' => $image_meta['height'] ? $image_meta['height'] : 0,
				);
			} else {
				$image_meta = array(
					'width'  => 0,
					'height' => 0,
				);
			}

			$sale_percentage  = \WebToApp\WtaHelper::get_sale_percentage($variation);
			$sale_price       = $variation->get_sale_price();
			$price            = ((get_option('woocommerce_prices_include_tax', 'no') == 'no') && (get_option('woocommerce_tax_display_shop', 'inc') == 'incl')) ? $variation->get_price_including_tax() : $variation->get_price();
			$regular_price    = ((get_option('woocommerce_prices_include_tax', 'no') == 'no') && (get_option('woocommerce_tax_display_shop', 'inc') == 'incl')) ? wc_get_price_including_tax($variation, array('price' => $variation->get_regular_price())) : $variation->get_regular_price();
			$variation_status = $variation->get_status();
			if ($variation_status != 'private') {
				$variations[] = array(
					'id'                    => $variation->get_variation_id(),
					'permalink'             => $variation->get_permalink(),
					'sku'                   => $variation->get_sku(),
					'price'                 => $price ? $price : "",
					'regular_price'         => $regular_price ? $regular_price : "",
					'sale_price'            => $sale_price ? $sale_price : "",
					'price_display'          => $price ? (\WebToApp\WtaHelper::get_price_display($price)) : "",
					'regular_price_display'  => $regular_price ? (\WebToApp\WtaHelper::get_price_display($regular_price)) : "",
					'sale_price_display'     => $sale_price ? (\WebToApp\WtaHelper::get_price_display($sale_price)) : "",
					'on_sale'               => $variation->is_on_sale(),
					'downloadable'          => $variation->is_downloadable(),
					'in_stock'              => $variation->is_in_stock(),
					'status'                => $variation_status,
					'sale_percentage'       => ($sale_percentage != 0) ? $sale_percentage . '%' : false,
					'purchasable'           => $product->is_purchasable(),
					'dimensions'            => \WebToApp\WtaHelper::get_product_dimensions($variation),
					'image'                 => $images,
					'image_meta'            => $image_meta,
					'attributes'            => array_values($this->get_attributes($variation)),
				);
			}
		}

		return apply_filters('wta_wc_product_variations', $variations, $product);
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
			'max_value'   => apply_filters('woocommerce_quantity_input_max', $product->backorders_allowed() ? '' : (int)$product->get_stock_quantity(), $product),
			'min_value'   => apply_filters('woocommerce_quantity_input_min', 1, $product),
			'step'        => apply_filters('woocommerce_quantity_input_step', '1', $product),
		);

		$thumbnail      = \WebToApp\WtaHelper::get_thumbnail($product);
		$thumbnail_meta = \WebToApp\WtaHelper::get_thumbnail_meta($product);
		if ($thumbnail_meta) {
			$thumbnail_meta = array(
				'width'  => $thumbnail_meta['width'] ? $thumbnail_meta['width'] : 0,
				'height' => $thumbnail_meta['height'] ? $thumbnail_meta['height'] : 0,
			);
		} else {
			$thumbnail_meta = array(
				'width'  => 0,
				'height' => 0,
			);
		}

		$attributes    = $this->get_attributes($product, true, false);
		$price         = \WebToApp\WtaHelper::get_price($product);
		$regular_price = \WebToApp\WtaHelper::get_price($product, 'regular');
		$sale_price    = \WebToApp\WtaHelper::get_price($product, 'sale');

		$data = [
			'id'                     => $product->get_id(),
			'name'                   => $product->get_name(),
			'slug'                   => $product->get_slug(),
			'permalink'              => \WebToApp\WtaHelper::ensure_absolute_link($product->get_permalink()),
			'type'                   => $product->get_type(),
			'featured'               => $product->get_featured(),
			'short_description'      => $product->get_short_description(),
			'sku'                    => $product->get_sku(),
			'currency'               => get_woocommerce_currency(),
			'currency_symbol'        => html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8'),
			'price'                  => $price ? $price : "",
			'regular_price'          => $regular_price ? $regular_price : "",
			'sale_price'             => $sale_price ? $sale_price : "",
			'price_display'          => $price ? (\WebToApp\WtaHelper::get_price_display($price)) : "",
			'regular_price_display'  => $regular_price ? (\WebToApp\WtaHelper::get_price_display($regular_price)) : "",
			'sale_price_display'     => $sale_price ? (\WebToApp\WtaHelper::get_price_display($sale_price)) : "",
			'on_sale'                => $product->is_on_sale(),
			'sale_percentage'        => \WebToApp\WtaHelper::get_sale_percentage($product),
			'purchasable'            => $product->is_purchasable(),
			'downloadable'           => $product->is_downloadable(),
			'display_add_to_cart'    => $product->is_purchasable() && $product->is_in_stock() && $product->is_type('simple'),
			'qty_config'             => $default_config,
			'stock_quantity'         => $product->get_stock_quantity(),
			'in_stock'               => $product->is_in_stock(),
			'weight'                 => $product->get_weight(),
			'dimensions'             => \WebToApp\WtaHelper::get_product_dimensions($product),
			'reviews_allowed'        => $product->get_reviews_allowed(),
			'display_rating'         => (get_option('woocommerce_enable_review_rating') === 'no') ? false : true,
			'average_rating'         => wc_format_decimal($product->get_average_rating(), 2),
			'rating_count'           => $product->get_rating_count(),
			'images'                 => array(),
			'thumbnail'              => $thumbnail,
			'thumbnail_meta'         => $thumbnail_meta,
			'images_meta'            => array(),
			'notify_backorder_label' => __('On backorder', 'woocommerce'),
			'attributes'             => array_values($attributes),
			'default_attributes'     => array_values($attributes),
		];

		if ($product->managing_stock() && $product->is_on_backorder(1) && $product->backorders_require_notification() && $product->get_type() === 'simple') {
			$data['notify_backorder'] = true;
		} elseif (!$product->managing_stock() && $product->is_on_backorder(1) && $product->get_type() === 'simple') {
			$data['notify_backorder'] = true;
		} else {
			$data['notify_backorder'] = false;
		}

		$images      = $this->get_images($product);
		$images_meta = \WebToApp\WtaHelper::get_images_meta($product);

		$data['images']      = $images;
		$data['images_meta'] = $images_meta;

		if (empty($data['images'])) {
			$data['images']        = array($thumbnail);
			$size                  = $thumbnail_meta;
			$data['images_meta'][] = array('caption' => '', 'size' => $size);
		}

		return $data;
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
				$name = str_replace('attribute_', '', $attribute_name);
				if (0 === strpos($attribute_name, 'attribute_pa_')) {
					$attr_name = $this->get_attribute_taxonomy_label($name);
					if ($attr_name) { // fix for country code selector plugin which has empty attribute name
						$attributes[$name] = array(
							'id'     => $name,
							'name'   => $attr_name,
							'option' => $attribute,
						);
					}
				} else {
					$id              = $this->sanitize_id($name);
					$attributes[$id] = array(
						'id'     => $id,
						'name'   => html_entity_decode(str_replace('pa_', '', $name), ENT_QUOTES, 'UTF-8'),
						'option' => $attribute,
					);
				}
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
			$terms  = wc_get_product_terms(\WebToApp\WtaHelper::get_id($product), $attribute['name'], array('fields' => 'all'));
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
			$labels    = get_taxonomy_labels($tax);
			$tax_label = $labels->singular_name;
			// WPML attribute label transaltion
			if (class_exists('SitePress')) {
				$tax_label = apply_filters('wpml_translate_single_string', $tax_label, 'WordPress', 'taxonomy singular name: ' . $tax_label);
			}
			return html_entity_decode($tax_label, ENT_QUOTES, 'UTF-8');
		} else {
			return '';
		}
	}

	/*
     * Get the query params for collections
     *
     * @return array
     */

	protected function get_images($product)
	{
		$images  = array();
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
