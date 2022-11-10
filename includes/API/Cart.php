<?php

namespace WebToApp\API;

class Cart
{

    public function register_routes()
    {
        register_rest_route('web-to-app/v1', '/cart', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array($this, 'get_cart'),
        ));

       
    }


    public function get_cart($request)
    {

        $cart = WC()->cart->get_cart();
        $cart_items = array();

        foreach ($cart as $key => $value) {
            $product = wc_get_product($value['product_id']);
            $cart_items[] = array(
                'product_id' => $value['product_id'],
                'product_name' => $product->get_name(),
                'product_price' => $product->get_price(),
                'product_image' => wp_get_attachment_image_src($product->get_image_id(), 'full')[0],
                'product_quantity' => $value['quantity'],
                'product_total' => $value['line_total'],
            );
        }

        $response = array(
            'cart_items' => $cart_items,
            'cart_total' => WC()->cart->get_cart_total(),
        );

        return $response;
       
    }
}
