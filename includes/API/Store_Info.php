<?php

namespace WebToApp\API;

/**
 * Class StoreInfo
 * @package WebToApp\API
 */

class Store_Info extends \WebToApp\Abstracts\WTA_WC_REST_Controller
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
    protected $rest_base = 'store-info';

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
                    'callback'            => array($this, 'get_store_info'),
                    'permission_callback' => array($this, 'get_store_info_permissions'),
                ),
            )
        );
    }

    public function get_store_info()
    {

        $data = array(
            'name' => get_user_meta(13, 'wta_website_id', true),
            'description' => get_user_meta(13, 'wta_access_token', true),
            'url' => get_bloginfo('url'),
            'currency' => get_woocommerce_currency(),
            'currency_symbol' => html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8'),
            'currency_position' => get_option('woocommerce_currency_pos'),
            'thousand_separator' => get_option('woocommerce_price_thousand_sep'),
            'decimal_separator' => get_option('woocommerce_price_decimal_sep'),
            'number_of_decimals' => (int)get_option('woocommerce_price_num_decimals'),
            'version' => WC()->version,
            'language' => get_bloginfo('language'),
            'timezone' => get_option('timezone_string'),
            'date_format' => get_option('date_format'),
            'time_format' => get_option('time_format'),
            'weight_unit' => get_option('woocommerce_weight_unit'),
            'dimension_unit' => get_option('woocommerce_dimension_unit'),
            'tax_included' => get_option('woocommerce_prices_include_tax'),
            'tax_display_cart' => get_option('woocommerce_tax_display_cart'),
            'tax_round_at_subtotal' => get_option('woocommerce_tax_round_at_subtotal'),
            'tax_total_display' => get_option('woocommerce_tax_total_display'),
            'display_cart_prices_excluding_tax' => get_option('woocommerce_tax_display_cart') == 'excl',
            'display_cart_prices_including_tax' => get_option('woocommerce_tax_display_cart') == 'incl',
            'display_shop_prices_excluding_tax' => get_option('woocommerce_tax_display_shop') == 'excl',
            'display_shop_prices_including_tax' => get_option('woocommerce_tax_display_shop') == 'incl',
            'display_prices_including_tax' => get_option('woocommerce_tax_display_shop') == 'incl',
            'display_prices_excluding_tax' => get_option('woocommerce_tax_display_shop') == 'excl',
            'display_totals_excluding_tax' => !wc_tax_enabled() || 'excl' === get_option('woocommerce_tax_display_cart'),
            'display_totals_including_tax' => wc_tax_enabled() && 'incl' === get_option('woocommerce_tax_display_cart'),
            'round_at_subtotal' => 'yes' === get_option('woocommerce_tax_round_at_subtotal'),
            'tax_based_on' => get_option('woocommerce_tax_based_on'),
            'shipping_tax_class' => get_option('woocommerce_shipping_tax_class'),
            'is_ssl' => is_ssl(),
            'is_https' => is_ssl(),
            'is_ajax' => defined('DOING_AJAX'),
            'is_admin' => is_admin(),
            'is_checkout' => is_checkout(),
            'is_cart' => is_cart(),
            'is_account_page' => is_account_page(),
            'is_add_payment_method_page' => is_add_payment_method_page(),
            'is_checkout_pay_page' => is_checkout_pay_page(),
            'is_view_order_page' => is_view_order_page(),
            'is_order_received_page' => is_order_received_page(),
            'is_product' => is_product(),
            'is_product_category' => is_product_category(),
            'is_product_tag' => is_product_tag(),
            'is_product_taxonomy' => is_product_taxonomy(),
            'is_shop' => is_shop(),
            'is_product_category' => is_product_category(),
            'is_product_tag' => is_product_tag(),
            'is_product_taxonomy' => is_product_taxonomy(),
        );

        return new \WP_REST_Response($data, 200);
    }

    public function get_store_info_permissions()
    {
        return true;
    }
}
