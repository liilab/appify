<?php

namespace WebToApp;

use Automattic\WooCommerce\Admin\API\Coupons;

/**
 * Class Admin
 * @package WebToApp
 */

class Admin
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        //add_action('admin_notices', [$this, 'admin_notices']);
    }

    public function admin_menu()
    {
        add_menu_page('Web to App', 'Web to App', 'manage_options', 'web-to-app', [$this, 'plugin_page'], 'dashicons-smartphone', 110);
    }

    public function register_settings()
    {
        register_setting('web-to-app', 'wta_settings');
    }

    public function admin_notices()
    {
    //     global $woocommerce;

    // include_once(  WP_PLUGIN_DIR . '/woocommerce/includes/abstracts/abstract-wc-session.php' ); // Abstract for session implementations
    // include_once(  WP_PLUGIN_DIR . '/woocommerce/includes/class-wc-session-handler.php' );    // WC Session class

    $session = new \WC_Session_Handler();
    $session->init();

    $cart = $session->get('cart');
    $cart = maybe_unserialize($cart);
    $coupon = $cart['applied_coupons'][0];

    echo '<pre>';
    print_r($cart);
    echo '</pre>';
    }
}