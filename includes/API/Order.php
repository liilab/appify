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

        $token = $request->get_header('access_token');
        $user_id = \WebToApp\User\Token::get_user_id_by_token($token);

        if (empty($user_id) || empty($token)) {
            return new \WP_REST_Response(array(
                'status' => 'error',
                'message' => 'Invalid token'
            ), 401);
        }

        $order = wc_create_order();
        $order->set_customer_id($user_id);

        $params = $request->get_params();
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

    public function prepare_order_for_response($order, $request)
    {

        $user_can_cancel  = current_user_can('cancel_order', $order->get_id());
        $statuses_for_cancel = apply_filters('woocommerce_valid_order_statuses_for_cancel', array(
            'pending',
            'failed',

        ), $order);

        $statuses_for_cancel = apply_filters('wta_wc_valid_order_statuses_for_cancel', $statuses_for_cancel);
        $order_can_cancel = $order->has_status($statuses_for_cancel);
        $order_can_repeat  = $order->has_status(apply_filters('woocommerce_valid_order_statuses_for_order_again', array('completed')));
        $order_needs_payment   = $order->needs_payment();

        $enable_order_repeat = true; // need to add option to enable/disable order repeat
        $show_payment_in_order = true; // need to add option to enable/disable payment in order

        // $featured_image = wp_get_attachment_image_src(get_post_thumbnail_id($order->get_id()));

        // $product = wc_get_product($order->get_product_id());
        // $product_sku = $product->get_sku();
        // $product_id = $product->get_id();
        // $variation_id = $order->get_variation_id();


        // $line_item = array(
        //     'id'           => $order->get_id(),
        //     'name'         => $order->name,
        //     'featured_src' => $featured_image,
        //     'sku'          => $product_sku,
        //     'product_id'   => (int) $product_id,
        //     'variation_id' => (int) $variation_id,
        //     // 'quantity'     => wc_stock_amount($item['qty']),
        //     // 'tax_class'    => !empty($item['tax_class']) ? $item['tax_class'] : '',
        //     // 'price'        => $order->get_item_total($item, false, false),
        //     // 'subtotal'     => $order->get_line_subtotal($item, false, false),
        //     // 'subtotal_tax' =>  $item['line_subtotal_tax'],
        //     // 'total'        => $order->get_line_total($item, false, false),
        //     // 'total_tax'    =>  $item['line_tax'],
        //     // 'taxes'        => array(),
        // );


        $data = array(
            'id' => $order->get_id(),
            'status_label' => $this->get_order_status_label($order->get_status()),
            'status' => $order->get_status(),
            'order_key'     => $this->get_order_key($order),
            'number'        => $order->get_order_number(),
            'currency'      => method_exists($order, 'get_currency') ? $order->get_currency() : $order->order_currency,
            'version'       => method_exists($order, 'get_version') ? $order->get_version() : $order->order_version,
            'date_created'  => $this->wc_rest_prepare_date_response($order->post_date_gmt),
            'date_modified' => $this->wc_rest_prepare_date_response($order->post_modified_gmt),
            'discount_total' =>  $order->get_total_discount(),
            //'discount_'         => wc_format_decimal( $order->cart_discount_tax, $dp ),
            'shipping_total' =>  $order->get_total_shipping(),
            'shipping_tax'   =>  $order->get_shipping_tax(),
            'cart_tax'       =>  $order->get_cart_tax(),
            'subtotal'       =>  $order->get_subtotal(),
            'total'          =>  $order->get_total(),
            'total_tax'      =>  $order->get_total_tax(),

            'billing'              => $order->get_address('billing'),
            'shipping'             => $order->get_address('shipping'),
            'payment_method_title' => method_exists($order, 'get_payment_method_title') ? $order->get_payment_method_title() : $order->payment_method_title,
            'date_completed'       => $this->wc_rest_prepare_date_response(method_exists($order, 'get_date_completed') ? $order->get_date_completed() : $order->completed_date),
            'line_items'           => array(),
            'tax_lines'            => array(),
            'shipping_lines'       => array(),
            'fee_lines'            => array(),
            'coupon_lines'         => array(),
            'refunds'              => array(),
            'can_cancel_order'     => $user_can_cancel && $order_can_cancel,
            'can_repeat_order'     => $order_can_repeat && $enable_order_repeat,
            'repeat_order_title'   => __('Order again', 'woocommerce'),
            'should_make_payment'  => $show_payment_in_order && $order_needs_payment,
            'payment_url'          => $order->get_checkout_payment_url(),
            'show_tax'             => wc_tax_enabled(),
        );

       // $data['line_items'][] = $line_item;
           
        return $data;
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

        $token = $request->get_header('access_token');
        $user_id = \WebToApp\User\Token::get_user_id_by_token($token);

        if (empty($user_id) || empty($token)) {
            return new \WP_REST_Response(array(
                'status' => 'error',
                'message' => 'Invalid token'
            ), 401);
        }

        $orders = wc_get_orders(array(
            'customer' => $user_id,
            'limit' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        ));

        $data = array();
        foreach ($orders as $order) {
            
            $data[] = $this->prepare_order_data($order);
        }

        return new \WP_REST_Response($data, 200);
        
    }

    /**
     * Order details.
     *
     * @param \WP_REST_Request $request Full data about the request.
     *
     * @return \WP_REST_Response
     */

     public function prepare_order_data($order)
     {
        $data = array(
            'id'=> $order->get_id(),
            'total'=> $order->get_total(),
            'status'=> $order->get_status(),
            'total_items'=> $order->get_item_count(),
        );
        return $data;
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
        $token = $request->get_header('access_token');
        $user_id = \WebToApp\User\Token::get_user_id_by_token($token);

        if (empty($user_id) || empty($token)) {
            return new \WP_REST_Response(array(
                'status' => 'error',
                'message' => 'Invalid token'
            ), 401);
        }

        $order_id = $request->get_param('id');

        $order = wc_get_order($order_id);

        $response = $this->prepare_order_for_response($order, $request);

        return rest_ensure_response($response);
    }





    /**
     * Parses and formats a MySQL datetime (Y-m-d H:i:s) for ISO8601/RFC3339.
     *
     * Requered WP 4.4 or later.
     * See https://developer.wordpress.org/reference/functions/mysql_to_rfc3339/
     *
     * @since 2.6.0
     *
     * @param string $date
     *
     * @return string|null ISO8601/RFC3339 formatted datetime.
     */
    static function wc_rest_prepare_date_response($date)
    {
        // Check if mysql_to_rfc3339 exists first!
        if (!function_exists('mysql_to_rfc3339')) {
            return null;
        }

        // Return null if $date is empty/zeros.
        if ('0000-00-00 00:00:00' === $date || empty($date)) {
            return null;
        }

        // Return the formatted datetime.
        return mysql_to_rfc3339($date);
    }

    /**
     * @param WC_Order $order
     *
     * @return mixed
     */
    public function get_order_key($order)
    {
        if (method_exists($order, 'get_order_key')) {
            return $order->get_order_key();
        } else {
            return $order->order_key;
        }
    }

    /**
     * Get order status.
     *
     * @return string
     */
    protected function get_order_status_label($order_status)
    {
        $order_statuses = array();

        foreach (wc_get_order_statuses() as $key => $status) {
            $key                    = str_replace('wc-', '', $key);
            $order_statuses[$key] = $status;
        }
        if (isset($order_statuses[$order_status])) {
            return $order_statuses[$order_status];
        } else {
            return '';
        }
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
            return new \WP_Error('rest_forbidden', esc_html__('token header is required.', 'web-to-app'), array('status' => 401));
        }
        return true;
    }
}
