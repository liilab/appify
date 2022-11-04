<?php

namespace WebToApp\API;

class Create_Order extends \WP_REST_Controller
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
    protected $rest_base = 'create-order';


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
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => array($this, 'create_order'),
                    'permission_callback' => array($this, 'api_permissions_check'),
                ),
            )
        );
    }


    /**
     * Create order.
     *
     * @param \WP_REST_Request $request Full data about the request.
     *
     * @return \WP_REST_Response
     */
    public function create_order($request)
    {

        $params = $request->get_params();


        $customer_id = $params['customer_id'];

        $user_token = \WebToApp\User\Token::get_user_access_token($customer_id);

        if ($user_token != $params['customer_token']) {
            return new \WP_REST_Response(array(
                'status' => 'error',
                'message' => 'Invalid customer id or token'
            ), 200);
            return;
        }

        $order = wc_create_order();
        $order->set_customer_id($params['customer_id']);

        $order_items = $params['order_items'];

        foreach ($order_items as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $variation_id = $item['variation_id'];
            $variation = $item['variation'];

            $order->add_product(wc_get_product($product_id), $quantity, array(
                'variation' => $variation,
                'variation_id' => $variation_id
            ));
        }

        $order->calculate_totals();
        $order->update_status('pending');

        $response = array(
            'status' => 'success',
            'order'  => $order->get_id(),
        );

        return rest_ensure_response($response);
    }

    public function get_order_items($items)
    {

        $order_items = array();

        foreach ($items as $item) {
            $order_items[] = array(
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            );
        }

        return $order_items;
    }

    public function api_permissions_check($request)
    {
        if (current_user_can('manage_options')) {
            return true;
        }

        return false;
    }
}
