<?php

namespace WebToApp\API;

class Cart
{

    public function register_routes()
    {
        register_rest_route('web-to-app/v1', '/cart', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array($this, 'get_cart'),
            'permission_callback' => array($this, 'api_permissions_check'),
        ));

        register_rest_route('web-to-app/v1', '/cart', array(
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => array($this, 'create_cart'),
            'permission_callback' => array($this, 'api_permissions_check'),
        ));

        register_rest_route('web-to-app/v1', '/cart', array(
            'methods' => \WP_REST_Server::EDITABLE,
            'callback' => array($this, 'create_cart'),
            'permission_callback' => array($this, 'api_permissions_check'),
        ));
    }

    public function create_cart($request)
    {
        $token = $request->get_header('access_token');
        $user_id = \WebToApp\User\Token::get_user_id_by_token($token);
        $cart = $request['cart'];

        $customer = new \WC_Customer();
        $customer->set_id($user_id);

        foreach ($cart as $item) {
            $product = wc_get_product($item['product_id']);
            $quantity = $item['quantity'];
            $variation_id = $item['variation_id'];
            $variation = $item['variation'];

            $cart_item_key = $customer->add_to_cart($product->get_id(), $quantity, $variation_id, $variation);
        }

        $response = array(
            'status' => 'success',
            'message' => 'Cart created successfully',
            'data' => $cart_item_key
        );

        return new \WP_REST_Response($response, 200);
    }

    public function get_cart($request)
    {
        $token = $request->get_header('access_token');
        $user_id = \WebToApp\User\Token::get_user_id_by_token($token);


        // Loop over $cart items
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            $product_id = $cart_item['product_id'];
            $variation_id = $cart_item['variation_id'];
            $quantity = $cart_item['quantity'];
            $price = WC()->cart->get_product_price($product);
            $subtotal = WC()->cart->get_product_subtotal($product, $cart_item['quantity']);
            $link = $product->get_permalink($cart_item);
            // Anything related to $product, check $product tutorial
            $attributes = $product->get_attributes();
            $whatever_attribute = $product->get_attribute('whatever');
            $whatever_attribute_tax = $product->get_attribute('pa_whatever');
            $any_attribute = $cart_item['variation']['attribute_whatever'];
            $meta = wc_get_formatted_cart_item_data($cart_item);
        }



        return new \WP_REST_Response($product, 200);
    }

    public function api_permissions_check($request)
    {
        $token = $request->get_header('access_token');
        $user_id = \WebToApp\User\Token::get_user_id_by_token($token);

        if ($user_id) {
            return true;
        } else {
            return false;
        }
    }
}
