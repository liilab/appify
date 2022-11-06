<?php

namespace WebToApp\API;

/**
 * Class Order
 * @package WebToApp\API
 */

class Order extends \WP_REST_Controller
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
    protected $rest_base = 'orders';


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

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            array(
                array(
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => array($this, 'order_details'),
                    'permission_callback' => array($this, 'api_permissions_check'),
                ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            array(
                array(
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => array($this, 'order_detail'),
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

        $user_id = \WebToApp\User\Token::get_user_id_by_token($params['customer_token']);

        if (empty($user_id)) {
            return new \WP_REST_Response(array(
                'status' => 'error',
                'message' => 'Invalid customer token'
            ), 401);
            return;
        }

        $order = wc_create_order();
        $order->set_customer_id($user_id);

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
            'user_id'   => $order->get_customer_id()
        );

        //return $request->get_headers();

        return rest_ensure_response($response);
    }

    /**
     * Check if a given request has access to create items.
     *
     * @param \WP_REST_Request $request Full data about the request.
     *
     * @return \WP_Error|bool
     */

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


    /**
     * Order details.
     *
     * @param \WP_REST_Request $request Full data about the request.
     *
     * @return \WP_REST_Response
     */
    public function order_details($request)
    {
        $query = new \WC_Order_Query(array(
            'limit' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            // 'return' => 'ids',
        ));
        $orders = $query->get_orders();

        return rest_ensure_response($orders);
    }

    /**
     * Order detail.
     *
     * @param \WP_REST_Request $request Full data about the request.
     *
     * @return \WP_REST_Response
     */

    public function order_detail($request)
    {
    }

    /**
     * Check API permissions.
     *
     * @param \WP_REST_Request $request Full data about the request.
     *
     * @return bool|\WP_Error
     */

    public function api_permissions_check($request)
    {
        $token = $request->get_header('access_token');

        $user_id = \WebToApp\User\Token::get_user_id_by_token($token);

        if (empty($user_id) || empty($token)) {
            return new \WP_Error('rest_forbidden', esc_html__('Token header is required.', 'web-to-app'), array('status' => 401));
        }
        return true;
    }
}
