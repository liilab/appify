<?php

namespace WebToApp\API;

class Webtoapp extends \WP_REST_Controller
{
    function __construct()
    {
        $this->namespace = 'web-to-app/v1';
        $this->rest_base = 'products';
    }

    public function register_routes()
    {
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array($this, 'get_items'),
                'permission_callback' => array($this, 'api_permissions_check'),
                'args'                => $this->get_collection_params(),
            ),

           'schema' => array($this, 'get_public_item_schema'),
        ));

        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
            array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array($this, 'get_item'),
                'permission_callback' => array($this, 'api_permissions_check'),
                'args'                => array(
                    'context' => $this->get_context_param(array('default' => 'view')),
                ),
            ),

            'schema' => array($this, 'get_public_item_schema'),
        ));
    }

    public function get_items($request)
    {

        // 'id'                      => (int) $product_obj->is_type( 'variation' ) ? $product_obj->get_variation_id() : APPMAKER_WC_Helper::get_id( $product ),
        // 'name'                    => $this->decode_html( $product_obj->get_title() ),
        // 'slug'                    => $post_data->post_name,
        // 'permalink'               => $this->ensure_absolute_link( $product_obj->get_permalink() ),
        // 'type'                    => $product_obj->get_type(),
        // 'featured'                => $product_obj->is_featured(),
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => -1,
        );

        $query = new \WP_Query($args);

        $posts = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $posts[] = array(
                    'id'    => get_the_ID(),
                    'name' => get_the_title(),
                    'slug' => get_post_field('post_name', get_the_ID()),
                    'permalink' => get_permalink(),
                    'type' => get_post_type(),
                    'featured' => get_post_meta(get_the_ID(), '_featured', true),
                    'short_description' => get_post_meta(get_the_ID(), '_short_description', true),
                    'sku' => get_post_meta(get_the_ID(), '_sku', true),
                    'currency' => get_woocommerce_currency(),
                    'currency_symbol' => get_woocommerce_currency_symbol(),
                    'price' => get_post_meta(get_the_ID(), '_price', true),
                    'regular_price' => get_post_meta(get_the_ID(), '_regular_price', true),
                    'sale_price' => get_post_meta(get_the_ID(), '_sale_price', true),
                    'price_display' => get_post_meta(get_the_ID(), '_price_display', true),
                    'regular_price_display' => get_post_meta(get_the_ID(), '_regular_price_display', true),
                    'sale_price_display' => get_post_meta(get_the_ID(), '_sale_price_display', true),
                    'on_sale' => get_post_meta(get_the_ID(), '_on_sale', true),
                    'sale_percentage' => get_post_meta(get_the_ID(), '_sale_percentage', true),
                    'purchasable' => get_post_meta(get_the_ID(), '_purchasable', true),
                    'downloadable' => get_post_meta(get_the_ID(), '_downloadable', true),
                    'display_add_to_cart' => get_post_meta(get_the_ID(), '_display_add_to_cart', true),
                    'change_thumbnail_image_size' => get_post_meta(get_the_ID(), '_change_thumbnail_image_size', true),
                    'hide_buy_now_block' => get_post_meta(get_the_ID(), '_hide_buy_now_block', true),
                    'buy_now_action' => get_post_meta(get_the_ID(), '_buy_now_action', true),
                    'buy_now_button_text' => get_post_meta(get_the_ID(), '_buy_now_button_text', true),
                    'add_to_cart_button_text' => get_post_meta(get_the_ID(), '_add_to_cart_button_text', true),
                    'qty_config' => get_post_meta(get_the_ID(), '_qty_config', true),
                    'stock_quantity' => get_post_meta(get_the_ID(), '_stock_quantity', true),
                    'in_stock' => get_post_meta(get_the_ID(), '_in_stock', true),
                    'weight' => get_post_meta(get_the_ID(), '_weight', true),
                    'dimensions' => get_post_meta(get_the_ID(), '_dimensions', true),
                    'reviwes_allowed' => get_post_meta(get_the_ID(), '_reviwes_allowed', true),
                    'display_rating' => get_post_meta(get_the_ID(), '_display_rating', true),
                    'avarage_rating' => get_post_meta(get_the_ID(), '_avarage_rating', true),
                    'rating_count' => get_post_meta(get_the_ID(), '_rating_count', true),
                    'images' => get_post_meta(get_the_ID(), '_images', true),
                    'thumbnail' => get_post_meta(get_the_ID(), '_thumbnail', true),
                    'thumbnail_meta' => get_post_meta(get_the_ID(), '_thumbnail_meta', true),
                    'images_meta' => get_post_meta(get_the_ID(), '_images_meta', true),
                    'notify_backorder' => get_post_meta(get_the_ID(), '_notify_backorder', true),
                    'notify_backorder_label' => get_post_meta(get_the_ID(), '_notify_backorder_label', true),
                    'color' => get_post_meta(get_the_ID(), '_color', true),
                    'attributes' => get_post_meta(get_the_ID(), '_attributes', true),
                    'default_attributes' => get_post_meta(get_the_ID(), '_default_attributes', true),
                    'product_in_webview' => get_post_meta(get_the_ID(), '_product_in_webview', true),
                    'label' => get_post_meta(get_the_ID(), '_label', true),

                );
            }
        }

        // $data      = array(
        //     'id'                      => (int) $product_obj->is_type( 'variation' ) ? $product_obj->get_variation_id() : APPMAKER_WC_Helper::get_id( $product ),
        //     'name'                    => $this->decode_html( $product_obj->get_title() ),
        //     'slug'                    => $post_data->post_name,
        //     'permalink'               => $this->ensure_absolute_link( $product_obj->get_permalink() ),
        //     'type'                    => $product_obj->get_type(),
        //     'featured'                => $product_obj->is_featured(),
        //     //'description'             => wpautop( do_shortcode( $post_data->post_content ) ),
        //     'short_description'       => ($hide_short_desc_product_list) ? '' : apply_filters( 'woocommerce_short_description', $post_data->post_excerpt ),
        //     'sku'                     => $product_obj->get_sku(),
        //     'currency'                => get_woocommerce_currency(),
        //     'currency_symbol'         => html_entity_decode(get_woocommerce_currency_symbol(),ENT_QUOTES, 'UTF-8'),
        //     'price'                   => $price,
        //     'regular_price'           => $regular_price,
        //     //'regular_price'           =>$product_obj->get_regular_price() ,
        //     'sale_price'              => $sale_price,
        //     'price_display'           => APPMAKER_WC_Helper::get_display_price($price),
        //     'regular_price_display'   => APPMAKER_WC_Helper::get_display_price( $regular_price ),
        //     'sale_price_display'      => APPMAKER_WC_Helper::get_display_price($sale_price),
        //    // 'price_html'              => $product_obj->get_price_html(),
        //     'on_sale'                 => ( $product_obj->get_price() < $product_obj->get_regular_price() || $product_obj->is_on_sale() ),
        //     'sale_percentage'         => ($sale_percentage != 0)? $sale_percentage.'%': false,
        //     'purchasable'             => $product_obj->is_purchasable(),
        //     'downloadable'            => $product_obj->is_downloadable(),
        //     'display_add_to_cart'     => $display_add_to_cart,
        //     'change_thumbnail_image_size'=>(bool) APPMAKER_WC::$api->get_settings( 'change_thumbnail_image_size', false ),
        //     'hide_buy_now_block'      => (bool) APPMAKER_WC::$api->get_settings( 'hide_buy_now_block', false ),
        //     'buy_now_action'          => $this->get_buy_now_action( $product ),
        //     'buy_now_button_text'     => $buy_now_text,
        //     'add_to_cart_button_text' => __( 'Add to cart', 'woocommerce' ),
        //     'qty_config'              => $cart_controller->get_qty_args( $product ),
        //     'stock_quantity'          => $product_obj->get_stock_quantity(),
        //     'in_stock'                => $product_obj->is_in_stock(),
        //     'weight'                  => $product_obj->get_weight(),
        //     'dimensions'              => array(
        //         'length' => $product_obj->get_length(),
        //         'width'  => $product_obj->get_width(),
        //         'height' => $product_obj->get_height(),
        //     ),
        //     'reviews_allowed'         => ( 'open' === $post_data->comment_status ),
        //     'display_rating'          => ( get_option( 'woocommerce_enable_review_rating' ) === 'no' ) ? false : true,
        //     'average_rating'          => APPMAKER_WC::$api->get_settings( 'hide_product_rating', false ) ? false : wc_format_decimal( $product_obj->get_average_rating(), 2 ),
        //     'rating_count'            => (int) $product_obj->get_rating_count(),
        //     //'view_more_reviews'       => ( (int) $product_obj->get_review_count() ) > 10 ? true : false ,
        //     //'categories'              => $this->get_taxonomy_terms( $product ),
        //     //'tags'                    => $this->get_taxonomy_terms( $product, 'tag' ),
        //     'images'                  => array(),
        //     'thumbnail'               => $thumbnail['url'],
        //     'thumbnail_meta'          => $thumbnail['size']
        // );

        $response = new \WP_REST_Response($posts);
        $response->set_status(200);
        return $response;
    }

    public function api_permissions_check($request)
    {
        if (current_user_can('manage_options')) {
            return true;
        }
        return true;
    }

    public function get_item_schema()
    {
        if ($this->schema) {
            return $this->add_additional_fields_schema($this->schema);
        }

        $weight_unit    = get_option( 'woocommerce_weight_unit' );
        $dimension_unit = get_option( 'woocommerce_dimension_unit' );

        $schema         = array(
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'product',
            'type'       => 'object',
            'properties' => array(
                'id'                 => array(
                    'description' => __( 'Unique identifier for the resource.', 'woocommerce' ),
                    'type'        => 'integer',
                    'context'     => array( 'view', 'edit' ),
                    'readonly'    => true,
                ),
                'name'               => array(
                    'description' => __( 'Product name.', 'woocommerce' ),
                    'type'        => 'string',
                    'context'     => array( 'view', 'edit' ),
                ),
                'slug'               => array(
                    'description' => __( 'Product slug.', 'woocommerce' ),
                    'type'        => 'string',
                    'context'     => array( 'view', 'edit' ),
                ),
                'permalink'          => array(
                    'description' => __( 'Product URL.', 'woocommerce' ),
                    'type'        => 'string',
                    'format'      => 'uri',
                    'context'     => array( 'view', 'edit' ),
                    'readonly'    => true,
                ),
                'type'               => array(
                    'description' => __( 'Product type.', 'woocommerce' ),
                    'type'        => 'string',
                    'default'     => 'simple',
                    'enum'        => array_keys( wc_get_product_types() ),
                    'context'     => array( 'view', 'edit' ),
                ),
                'featured'           => array(
                    'description' => __( 'Featured product.', 'woocommerce' ),
                    'type'        => 'boolean',
                    'default'     => false,
                    'context'     => array( 'view', 'edit' ),
                ),
                'description'        => array(
                    'description' => __( 'Product description.', 'woocommerce' ),
                    'type'        => 'string',
                    'context'     => array( 'view', 'edit' ),
                ),
                'short_description'  => array(
                    'description' => __( 'Product short description.', 'woocommerce' ),
                    'type'        => 'string',
                    'context'     => array( 'view', 'edit' ),
                ),
                'sku'                => array(
                    'description' => __( 'Unique identifier.', 'woocommerce' ),
                    'type'        => 'string',
                    'context'     => array( 'view', 'edit' ),
                ),
                'price'              => array(
                    'description' => __( 'Current product price.', 'woocommerce' ),
                    'type'        => 'string',
                    'context'     => array( 'view', 'edit' ),
                    'readonly'    => true,
                ),
                'regular_price'      => array(
                    'description' => __( 'Product regular price.', 'woocommerce' ),
                    'type'        => 'string',
                    'context'     => array( 'view', 'edit' ),
                ),
                'sale_price'         => array(
                    'description' => __( 'Product sale price.', 'woocommerce' ),
                    'type'        => 'string',
                    'context'     => array( 'view', 'edit' ),
                ),
                'price_html'         => array(
                    'description' => __( 'Price formatted in HTML.', 'woocommerce' ),
                    'type'        => 'string',
                    'context'     => array( 'view', 'edit' ),
                    'readonly'    => true,
                ),
                'on_sale'            => array(
                    'description' => __( 'Shows if the product is on sale.', 'woocommerce' ),
                    'type'        => 'boolean',
                    'context'     => array( 'view', 'edit' ),
                    'readonly'    => true,
                ),
                'purchasable'        => array(
                    'description' => __( 'Shows if the product can be bought.', 'woocommerce' ),
                    'type'        => 'boolean',
                    'context'     => array( 'view', 'edit' ),
                    'readonly'    => true,
                ),
                'downloadable'       => array(
                    'description' => __( 'If the product is downloadable.', 'woocommerce' ),
                    'type'        => 'boolean',
                    'default'     => false,
                    'context'     => array( 'view', 'edit' ),
                ),
                'external_url'       => array(
                    'description' => __( 'Product external URL. Only for external products.', 'woocommerce' ),
                    'type'        => 'string',
                    'format'      => 'uri',
                    'context'     => array( 'view', 'edit' ),
                ),
                'button_text'        => array(
                    'description' => __( 'Product external button text. Only for external products.', 'woocommerce' ),
                    'type'        => 'string',
                    'context'     => array( 'view', 'edit' ),
                ),
                'tax_status'         => array(
                    'description' => __( 'Tax status.', 'woocommerce' ),
                    'type'        => 'string',
                    'default'     => 'taxable',
                    'enum'        => array( 'taxable', 'shipping', 'none' ),
                    'context'     => array( 'view', 'edit' ),
                ),
                'tax_class'          => array(
                    'description' => __( 'Tax class.', 'woocommerce' ),
                    'type'        => 'string',
                    'context'     => array( 'view', 'edit' ),
                ),
                'manage_stock'       => array(
                    'description' => __( 'Stock management at product level.', 'woocommerce' ),
                    'type'        => 'boolean',
                    'default'     => false,
                    'context'     => array( 'view', 'edit' ),
                ),
                'stock_quantity'     => array(
                    'description' => __( 'Stock quantity.', 'woocommerce' ),
                    'type'        => 'integer',
                    'context'     => array( 'view', 'edit' ),
                ),
                'in_stock'           => array(
                    'description' => __( 'Controls whether or not the product is listed as "in stock" or "out of stock" on the frontend.', 'woocommerce' ),
                    'type'        => 'boolean',
                    'default'     => true,
                    'context'     => array( 'view', 'edit' ),
                ),
                'backorders'         => array(
                    'description' => __( 'If managing stock, this controls if backorders are allowed.', 'woocommerce' ),
                    'type'        => 'string',
                    'default'     => 'no',
                    'enum'        => array( 'no', 'notify', 'yes' ),
                    'context'     => array( 'view', 'edit' ),
                ),
                'backorders_allowed' => array(
                    'description' => __( 'Shows if backorders are allowed.', 'woocommerce' ),
                    'type'        => 'boolean',
                    'context'     => array( 'view', 'edit' ),
                    'readonly'    => true,
                ),
                'backordered'        => array(
                    'description' => __( 'Shows if the product is on backordered.', 'woocommerce' ),
                    'type'        => 'boolean',
                    'context'     => array( 'view', 'edit' ),
                    'readonly'    => true,
                ),
                'sold_individually'  => array(
                    'description' => __( 'Allow one item to be bought in a single order.', 'woocommerce' ),
                    'type'        => 'boolean',
                    'default'     => false,
                    'context'     => array( 'view', 'edit' ),
                ),
                'weight'             => array(
                    'description' => sprintf( __( 'Product weight (%s).', 'woocommerce' ), $weight_unit ),
                    'type'        => 'string',
                    'context'     => array( 'view', 'edit' ),
                ),
                'dimensions'         => array(
                    'description' => __( 'Product dimensions.', 'woocommerce' ),
                    'type'        => 'array',
                    'context'     => array( 'view', 'edit' ),
                    'properties'  => array(
                        'length' => array(
                            'description' => sprintf( __( 'Product length (%s).', 'woocommerce' ), $dimension_unit ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'width'  => array(
                            'description' => sprintf( __( 'Product width (%s).', 'woocommerce' ), $dimension_unit ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'height' => array(
                            'description' => sprintf( __( 'Product height (%s).', 'woocommerce' ), $dimension_unit ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                        ),
                    ),
                ),
                'shipping_required'  => array(
                    'description' => __( 'Shows if the product need to be shipped.', 'woocommerce' ),
                    'type'        => 'boolean',
                    'context'     => array( 'view', 'edit' ),
                    'readonly'    => true,
                ),
                'shipping_taxable'   => array(
                    'description' => __( 'Shows whether or not the product shipping is taxable.', 'woocommerce' ),
                    'type'        => 'boolean',
                    'context'     => array( 'view', 'edit' ),
                    'readonly'    => true,
                ),
                'shipping_class'     => array(
                    'description' => __( 'Shipping class slug.', 'woocommerce' ),
                    'type'        => 'string',
                    'context'     => array( 'view', 'edit' ),
                ),
                'shipping_class_id'  => array(
                    'description' => __( 'Shipping class ID.', 'woocommerce' ),
                    'type'        => 'string',
                    'context'     => array( 'view', 'edit' ),
                    'readonly'    => true,
                ),
                'reviews_allowed'    => array(
                    'description' => __( 'Allow reviews.', 'woocommerce' ),
                    'type'        => 'boolean',
                    'default'     => true,
                    'context'     => array( 'view', 'edit' ),
                ),
                'average_rating'     => array(
                    'description' => __( 'Reviews average rating.', 'woocommerce' ),
                    'type'        => 'string',
                    'context'     => array( 'view', 'edit' ),
                    'readonly'    => true,
                ),
                'rating_count'       => array(
                    'description' => __( 'Amount of reviews that the product have.', 'woocommerce' ),
                    'type'        => 'integer',
                    'context'     => array( 'view', 'edit' ),
                    'readonly'    => true,
                ),
                'parent_id'          => array(
                    'description' => __( 'Product parent ID.', 'woocommerce' ),
                    'type'        => 'integer',
                    'context'     => array( 'view', 'edit' ),
                ),
                'purchase_note'      => array(
                    'description' => __( 'Optional note to send the customer after purchase.', 'woocommerce' ),
                    'type'        => 'string',
                    'context'     => array( 'view', 'edit' ),
                ),
                'categories'         => array(
                    'description' => __( 'List of categories.', 'woocommerce' ),
                    'type'        => 'array',
                    'context'     => array( 'view', 'edit' ),
                    'properties'  => array(
                        'id'   => array(
                            'description' => __( 'Category ID.', 'woocommerce' ),
                            'type'        => 'integer',
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'name' => array(
                            'description' => __( 'Category name.', 'woocommerce' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                            'readonly'    => true,
                        ),
                        'slug' => array(
                            'description' => __( 'Category slug.', 'woocommerce' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                            'readonly'    => true,
                        ),
                    ),
                ),
                'tags'               => array(
                    'description' => __( 'List of tags.', 'woocommerce' ),
                    'type'        => 'array',
                    'context'     => array( 'view', 'edit' ),
                    'properties'  => array(
                        'id'   => array(
                            'description' => __( 'Tag ID.', 'woocommerce' ),
                            'type'        => 'integer',
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'name' => array(
                            'description' => __( 'Tag name.', 'woocommerce' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                            'readonly'    => true,
                        ),
                        'slug' => array(
                            'description' => __( 'Tag slug.', 'woocommerce' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                            'readonly'    => true,
                        ),
                    ),
                ),
                'images'             => array(
                    'description' => __( 'List of images.', 'woocommerce' ),
                    'type'        => 'array',
                    'context'     => array( 'view', 'edit' ),
                    'properties'  => array(
                        'id'            => array(
                            'description' => __( 'Image ID.', 'woocommerce' ),
                            'type'        => 'integer',
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'date_created'  => array(
                            'description' => __( "The date the image was created, in the site's timezone.", 'woocommerce' ),
                            'type'        => 'date-time',
                            'context'     => array( 'view', 'edit' ),
                            'readonly'    => true,
                        ),
                        'date_modified' => array(
                            'description' => __( "The date the image was last modified, in the site's timezone.", 'woocommerce' ),
                            'type'        => 'date-time',
                            'context'     => array( 'view', 'edit' ),
                            'readonly'    => true,
                        ),
                        'src'           => array(
                            'description' => __( 'Image URL.', 'woocommerce' ),
                            'type'        => 'string',
                            'format'      => 'uri',
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'name'          => array(
                            'description' => __( 'Image name.', 'woocommerce' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'alt'           => array(
                            'description' => __( 'Image alternative text.', 'woocommerce' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'position'      => array(
                            'description' => __( 'Image position. 0 means that the image is featured.', 'woocommerce' ),
                            'type'        => 'integer',
                            'context'     => array( 'view', 'edit' ),
                        ),
                    ),
                ),
                'attributes'         => array(
                    'description' => __( 'List of attributes.', 'woocommerce' ),
                    'type'        => 'array',
                    'context'     => array( 'view', 'edit' ),
                    'properties'  => array(
                        'id'        => array(
                            'description' => __( 'Attribute ID.', 'woocommerce' ),
                            'type'        => 'integer',
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'name'      => array(
                            'description' => __( 'Attribute name.', 'woocommerce' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'position'  => array(
                            'description' => __( 'Attribute position.', 'woocommerce' ),
                            'type'        => 'integer',
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'visible'   => array(
                            'description' => __( "Define if the attribute is visible on the \"Additional Information\" tab in the product's page.", 'woocommerce' ),
                            'type'        => 'boolean',
                            'default'     => false,
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'variation' => array(
                            'description' => __( 'Define if the attribute can be used as variation.', 'woocommerce' ),
                            'type'        => 'boolean',
                            'default'     => false,
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'options'   => array(
                            'description' => __( 'List of available term names of the attribute.', 'woocommerce' ),
                            'type'        => 'array',
                            'context'     => array( 'view', 'edit' ),
                        ),
                    ),
                ),
                'default_attributes' => array(
                    'description' => __( 'Defaults variation attributes.', 'woocommerce' ),
                    'type'        => 'array',
                    'context'     => array( 'view', 'edit' ),
                    'properties'  => array(
                        'id'     => array(
                            'description' => __( 'Attribute ID.', 'woocommerce' ),
                            'type'        => 'integer',
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'name'   => array(
                            'description' => __( 'Attribute name.', 'woocommerce' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'option' => array(
                            'description' => __( 'Selected attribute term name.', 'woocommerce' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                        ),
                    ),
                ),
                'variations'         => array(
                    'description' => __( 'List of variations.', 'woocommerce' ),
                    'type'        => 'array',
                    'context'     => array( 'view', 'edit' ),
                    'properties'  => array(
                        'id'                 => array(
                            'description' => __( 'Variation ID.', 'woocommerce' ),
                            'type'        => 'integer',
                            'context'     => array( 'view', 'edit' ),
                            'readonly'    => true,
                        ),
                        'date_created'       => array(
                            'description' => __( "The date the variation was created, in the site's timezone.", 'woocommerce' ),
                            'type'        => 'date-time',
                            'context'     => array( 'view', 'edit' ),
                            'readonly'    => true,
                        ),
                        'date_modified'      => array(
                            'description' => __( "The date the variation was last modified, in the site's timezone.", 'woocommerce' ),
                            'type'        => 'date-time',
                            'context'     => array( 'view', 'edit' ),
                            'readonly'    => true,
                        ),
                        'permalink'          => array(
                            'description' => __( 'Variation URL.', 'woocommerce' ),
                            'type'        => 'string',
                            'format'      => 'uri',
                            'context'     => array( 'view', 'edit' ),
                            'readonly'    => true,
                        ),
                        'sku'                => array(
                            'description' => __( 'Unique identifier.', 'woocommerce' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'price'              => array(
                            'description' => __( 'Current variation price.', 'woocommerce' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                            'readonly'    => true,
                        ),
                        'regular_price'      => array(
                            'description' => __( 'Variation regular price.', 'woocommerce' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'sale_price'         => array(
                            'description' => __( 'Variation sale price.', 'woocommerce' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'date_on_sale_from'  => array(
                            'description' => __( 'Start date of sale price.', 'woocommerce' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'date_on_sale_to'    => array(
                            'description' => __( 'End data of sale price.', 'woocommerce' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'on_sale'            => array(
                            'description' => __( 'Shows if the variation is on sale.', 'woocommerce' ),
                            'type'        => 'boolean',
                            'context'     => array( 'view', 'edit' ),
                            'readonly'    => true,
                        ),
                        'purchasable'        => array(
                            'description' => __( 'Shows if the variation can be bought.', 'woocommerce' ),
                            'type'        => 'boolean',
                            'context'     => array( 'view', 'edit' ),
                            'readonly'    => true,
                        ),
                        'visible'            => array(
                            'description' => __( 'If the variation is visible.', 'woocommerce' ),
                            'type'        => 'boolean',
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'virtual'            => array(
                            'description' => __( 'If the variation is virtual.', 'woocommerce' ),
                            'type'        => 'boolean',
                            'default'     => false,
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'downloadable'       => array(
                            'description' => __( 'If the variation is downloadable.', 'woocommerce' ),
                            'type'        => 'boolean',
                            'default'     => false,
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'downloads'          => array(
                            'description' => __( 'List of downloadable files.', 'woocommerce' ),
                            'type'        => 'array',
                            'context'     => array( 'view', 'edit' ),
                            'properties'  => array(
                                'id'   => array(
                                    'description' => __( 'File MD5 hash.', 'woocommerce' ),
                                    'type'        => 'string',
                                    'context'     => array( 'view', 'edit' ),
                                    'readonly'    => true,
                                ),
                                'name' => array(
                                    'description' => __( 'File name.', 'woocommerce' ),
                                    'type'        => 'string',
                                    'context'     => array( 'view', 'edit' ),
                                ),
                                'file' => array(
                                    'description' => __( 'File URL.', 'woocommerce' ),
                                    'type'        => 'string',
                                    'context'     => array( 'view', 'edit' ),
                                ),
                            ),
                            'download_limit'     => array(
                                'description' => __( 'Amount of times the variation can be downloaded.', 'woocommerce' ),
                                'type'        => 'integer',
                                'default'     => null,
                                'context'     => array( 'view', 'edit' ),
                            ),
                            'download_expiry'    => array(
                                'description' => __( 'Number of days that the customer has up to be able to download the variation.', 'woocommerce' ),
                                'type'        => 'integer',
                                'default'     => null,
                                'context'     => array( 'view', 'edit' ),
                            ),
                            'tax_status'         => array(
                                'description' => __( 'Tax status.', 'woocommerce' ),
                                'type'        => 'string',
                                'default'     => 'taxable',
                                'enum'        => array( 'taxable', 'shipping', 'none' ),
                                'context'     => array( 'view', 'edit' ),
                            ),
                            'tax_class'          => array(
                                'description' => __( 'Tax class.', 'woocommerce' ),
                                'type'        => 'string',
                                'context'     => array( 'view', 'edit' ),
                            ),
                            'manage_stock'       => array(
                                'description' => __( 'Stock management at variation level.', 'woocommerce' ),
                                'type'        => 'boolean',
                                'default'     => false,
                                'context'     => array( 'view', 'edit' ),
                            ),
                            'stock_quantity'     => array(
                                'description' => __( 'Stock quantity.', 'woocommerce' ),
                                'type'        => 'integer',
                                'context'     => array( 'view', 'edit' ),
                            ),
                            'in_stock'           => array(
                                'description' => __( 'Controls whether or not the variation is listed as "in stock" or "out of stock" on the frontend.', 'woocommerce' ),
                                'type'        => 'boolean',
                                'default'     => true,
                                'context'     => array( 'view', 'edit' ),
                            ),
                            'backorders'         => array(
                                'description' => __( 'If managing stock, this controls if backorders are allowed.', 'woocommerce' ),
                                'type'        => 'string',
                                'default'     => 'no',
                                'enum'        => array( 'no', 'notify', 'yes' ),
                                'context'     => array( 'view', 'edit' ),
                            ),
                            'backorders_allowed' => array(
                                'description' => __( 'Shows if backorders are allowed.', 'woocommerce' ),
                                'type'        => 'boolean',
                                'context'     => array( 'view', 'edit' ),
                                'readonly'    => true,
                            ),
                            'backordered'        => array(
                                'description' => __( 'Shows if the variation is on backordered.', 'woocommerce' ),
                                'type'        => 'boolean',
                                'context'     => array( 'view', 'edit' ),
                                'readonly'    => true,
                            ),
                            'weight'             => array(
                                'description' => sprintf( __( 'Variation weight (%s).', 'woocommerce' ), $weight_unit ),
                                'type'        => 'string',
                                'context'     => array( 'view', 'edit' ),
                            ),
                            'dimensions'         => array(
                                'description' => __( 'Variation dimensions.', 'woocommerce' ),
                                'type'        => 'array',
                                'context'     => array( 'view', 'edit' ),
                                'properties'  => array(
                                    'length' => array(
                                        'description' => sprintf( __( 'Variation length (%s).', 'woocommerce' ), $dimension_unit ),
                                        'type'        => 'string',
                                        'context'     => array( 'view', 'edit' ),
                                    ),
                                    'width'  => array(
                                        'description' => sprintf( __( 'Variation width (%s).', 'woocommerce' ), $dimension_unit ),
                                        'type'        => 'string',
                                        'context'     => array( 'view', 'edit' ),
                                    ),
                                    'height' => array(
                                        'description' => sprintf( __( 'Variation height (%s).', 'woocommerce' ), $dimension_unit ),
                                        'type'        => 'string',
                                        'context'     => array( 'view', 'edit' ),
                                    ),
                                ),
                            ),
                            'shipping_class'     => array(
                                'description' => __( 'Shipping class slug.', 'woocommerce' ),
                                'type'        => 'string',
                                'context'     => array( 'view', 'edit' ),
                            ),
                            'shipping_class_id'  => array(
                                'description' => __( 'Shipping class ID.', 'woocommerce' ),
                                'type'        => 'string',
                                'context'     => array( 'view', 'edit' ),
                                'readonly'    => true,
                            ),
                            'image'              => array(
                                'description' => __( 'Variation image data.', 'woocommerce' ),
                                'type'        => 'array',
                                'context'     => array( 'view', 'edit' ),
                                'properties'  => array(
                                    'id'            => array(
                                        'description' => __( 'Image ID.', 'woocommerce' ),
                                        'type'        => 'integer',
                                        'context'     => array( 'view', 'edit' ),
                                    ),
                                    'date_created'  => array(
                                        'description' => __( "The date the image was created, in the site's timezone.", 'woocommerce' ),
                                        'type'        => 'date-time',
                                        'context'     => array( 'view', 'edit' ),
                                        'readonly'    => true,
                                    ),
                                    'date_modified' => array(
                                        'description' => __( "The date the image was last modified, in the site's timezone.", 'woocommerce' ),
                                        'type'        => 'date-time',
                                        'context'     => array( 'view', 'edit' ),
                                        'readonly'    => true,
                                    ),
                                    'src'           => array(
                                        'description' => __( 'Image URL.', 'woocommerce' ),
                                        'type'        => 'string',
                                        'format'      => 'uri',
                                        'context'     => array( 'view', 'edit' ),
                                    ),
                                    'name'          => array(
                                        'description' => __( 'Image name.', 'woocommerce' ),
                                        'type'        => 'string',
                                        'context'     => array( 'view', 'edit' ),
                                    ),
                                    'alt'           => array(
                                        'description' => __( 'Image alternative text.', 'woocommerce' ),
                                        'type'        => 'string',
                                        'context'     => array( 'view', 'edit' ),
                                    ),
                                    'position'      => array(
                                        'description' => __( 'Image position. 0 means that the image is featured.', 'woocommerce' ),
                                        'type'        => 'integer',
                                        'context'     => array( 'view', 'edit' ),
                                    ),
                                ),
                            ),
                            'attributes'         => array(
                                'description' => __( 'List of attributes.', 'woocommerce' ),
                                'type'        => 'array',
                                'context'     => array( 'view', 'edit' ),
                                'properties'  => array(
                                    'id'     => array(
                                        'description' => __( 'Attribute ID.', 'woocommerce' ),
                                        'type'        => 'integer',
                                        'context'     => array( 'view', 'edit' ),
                                    ),
                                    'name'   => array(
                                        'description' => __( 'Attribute name.', 'woocommerce' ),
                                        'type'        => 'string',
                                        'context'     => array( 'view', 'edit' ),
                                    ),
                                    'option' => array(
                                        'description' => __( 'Selected attribute term name.', 'woocommerce' ),
                                        'type'        => 'string',
                                        'context'     => array( 'view', 'edit' ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'grouped_products'   => array(
                        'description' => __( 'List of grouped products ID.', 'woocommerce' ),
                        'type'        => 'array',
                        'context'     => array( 'view', 'edit' ),
                        'readonly'    => true,
                    ),
                    'menu_order'         => array(
                        'description' => __( 'Menu order, used to custom sort products.', 'woocommerce' ),
                        'type'        => 'integer',
                        'context'     => array( 'view', 'edit' ),
                    ),
                ),
            ),
        );

        $this->schema = $schema;
        return $this->add_additional_fields_schema($this->schema);
    }
}
