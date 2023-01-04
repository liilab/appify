<?php

namespace WebToApp\API;

/**
 * Class Order
 * @package Appify\API
 */

class Order extends \WebToApp\Abstracts\WTA_WC_REST_Controller
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
     * Order notes.
     * 
     * @var string
     */

    protected $order_notes;


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
     * 
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
        $order_items = $params['items'];

        foreach ($order_items as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $variation_id = $item['variation_id'];
            $variation = $item['variation'];

            if ($product_id && $quantity) {
                if ($variation_id) {
                    $order->add_product(wc_get_product($variation_id), $quantity, array(
                        'variation' => $variation
                    ));
                } else {
                    $order->add_product(wc_get_product($product_id), $quantity);
                }
            } else {
                return new \WP_REST_Response(array(
                    'status' => 'error',
                    'message' => 'Invalid product id or quantity'
                ), 400);
            }
        }

        $order->calculate_totals();
        $order->update_status('pending');

        $this->order_notes = $params['order_notes'];

        if (!empty($this->order_notes)) {
            $order->add_order_note(
                $this->order_notes
            );
        } else {
            $order->add_order_note(
                ''
            );
        }

        $shipping_address = $params['shipping_address'][0];
        $billing_address = $params['billing_address'][0];

        if (empty($shipping_address)) {
            $shipping_address = $this->get_address('shipping', $request);
        }

        if (empty($billing_address)) {
            $billing_address = $this->get_address('billing', $request);
        }

        if (!empty($billing_address)) {
            $order->set_address($billing_address, 'billing');
        }

        if (!empty($shipping_address)) {
            $order->set_address($shipping_address, 'shipping');
        } else if (!empty($billing_address)) {
            $order->set_address($billing_address, 'shipping');
        }

        $order_id = $order->get_id();

        $order = wc_get_order($order_id);

        $response = $this->prepare_order_for_response($order, $request);

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


        $data = array(
            'id' => $order->get_id(),
            'status_label' => $this->get_order_status_label($order->get_status()),
            'status' => $order->get_status(),
            'order_key'     => $this->get_order_key($order),
            'order_notes'   => $this->get_order_notes($order),
            'total_items'   => $order->get_item_count(),
            'items'         => $this->get_order_items($order),
            'currency'      => method_exists($order, 'get_currency') ? $order->get_currency() : $order->order_currency,
            'version'       => method_exists($order, 'get_version') ? $order->get_version() : $order->order_version,
            'date_created'  => $order->get_date_created() ? wc_rest_prepare_date_response($order->get_date_created()) : null,
            'date_modified' => $order->get_date_modified() ? wc_rest_prepare_date_response($order->get_date_modified()) : null,
            'discount_total' =>  $order->get_total_discount(),
            'shipping_total' =>  $order->get_total_shipping(),
            'shipping_tax'   =>  $order->get_shipping_tax(),
            'cart_tax'       =>  $order->get_cart_tax(),
            'subtotal'       =>  $order->get_subtotal(),
            'total'          =>  $order->get_total(),
            'total_tax'      =>  $order->get_total_tax(),
            'payment_method' =>  $order->get_payment_method(),
            'billing'              => $this->get_address('billing', $request),
            'shipping'             => $this->get_address('shipping', $request),
            'payment_method_title' => method_exists($order, 'get_payment_method_title') ? $order->get_payment_method_title() : $order->payment_method_title,
            'date_completed'       => $this->wc_rest_prepare_date_response(method_exists($order, 'get_date_completed') ? $order->get_date_completed() : $order->completed_date),
            'can_cancel_order'     => $user_can_cancel && $order_can_cancel,
            'can_repeat_order'     => $order_can_repeat && $enable_order_repeat,
            'repeat_order_title'   => __('Order again', 'woocommerce'),
            'should_make_payment'  => $show_payment_in_order && $order_needs_payment,
            'payment_url'          => $order->get_checkout_payment_url(),
            'show_tax'             => wc_tax_enabled(),
        );

        return $data;
    }

    /**
     * Get order note.
     *
     * @return string
     */

    public function get_order_notes($order)
    {
        $notes = wc_get_order_notes([
            'order_id' => $order->get_id()
        ]);

        return $notes[0]->content;
    }


    public function get_address($address, $request)
    {
        $token = $request->get_header('access_token');
        $user_id = \WebToApp\User\Token::get_user_id_by_token($token);

        $data = array();

        $customer = new \WC_Customer($user_id);

        if ($address == 'billing') {
            // Customer billing information details (from account)
            $billing_first_name = $customer->get_billing_first_name();
            $billing_last_name  = $customer->get_billing_last_name();
            $billing_company    = $customer->get_billing_company();
            $billing_address_1  = $customer->get_billing_address_1();
            $billing_address_2  = $customer->get_billing_address_2();
            $billing_city       = $customer->get_billing_city();
            $billing_email     = $customer->get_billing_email();
            $billing_phone     = $customer->get_billing_phone();
            $billing_state      = $customer->get_billing_state();
            $billing_postcode   = $customer->get_billing_postcode();
            $billing_country    = $customer->get_billing_country();

            $data = array(
                'first_name' => $billing_first_name,
                'last_name' => $billing_last_name,
                'company' => $billing_company,
                'address_1' => $billing_address_1,
                'address_2' => $billing_address_2,
                'email' => $billing_email,
                'phone' => $billing_phone,
                'city' => $billing_city,
                'state' => $billing_state,
                'postcode' => $billing_postcode,
                'country' => $billing_country,
            );
        } else if ($address == 'shipping') {
            // Customer shipping information details (from account)
            $shipping_first_name = $customer->get_shipping_first_name();
            $shipping_last_name  = $customer->get_shipping_last_name();
            $shipping_company    = $customer->get_shipping_company();
            $shipping_address_1  = $customer->get_shipping_address_1();
            $shipping_address_2  = $customer->get_shipping_address_2();
            $shipping_city       = $customer->get_shipping_city();
            $shipping_state      = $customer->get_shipping_state();
            $shipping_postcode   = $customer->get_shipping_postcode();
            $shipping_country    = $customer->get_shipping_country();

            $data = array(
                'first_name' => $shipping_first_name,
                'last_name' => $shipping_last_name,
                'company' => $shipping_company,
                'address_1' => $shipping_address_1,
                'address_2' => $shipping_address_2,
                'city' => $shipping_city,
                'state' => $shipping_state,
                'postcode' => $shipping_postcode,
                'country' => $shipping_country,
            );
        }

        return $data;
    }

    /**
     * Get the order for the given ID.
     *
     * @param int $id Order ID.
     *
     * @return WC_Order
     */

    public function get_order_items($order)
    {

        $items = $order->get_items();
        $data = array();

        foreach ($items as $item) {
            $product = $item->get_product();
            $featured_image = wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()));
            $product_sku = $product->get_sku();
            $product_id = $product->get_id();
            $variation_id = $item->get_variation_id();

            $line_item = array(
                'id'           => $item->get_id(),
                'name'         => $item->get_name(),
                'featured_src' => $featured_image,
                'sku'          => $product_sku,
                'product_id'   => (int) $product_id,
                'variation_id' => (int) $variation_id,
                'quantity'     => wc_stock_amount($item->get_quantity()),
                'tax_class'    => !empty($item->get_tax_class()) ? $item->get_tax_class() : '',
                'price'        => $order->get_item_total($item, false, false),
                'subtotal'     => $order->get_line_subtotal($item, false, false),
                'subtotal_tax' =>  $item->get_subtotal_tax(),
                'total'        => $order->get_line_total($item, false, false),
                'total_tax'    =>  $item->get_total_tax(),
                'taxes'        => array(),
            );

            $data[] = $line_item;
        }

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
            'id' => $order->get_id(),
            'total' => $order->get_total(),
            'status' => $order->get_status(),
            'total_items' => $order->get_item_count(),
            'currency' => $order->get_currency(),
            'date_created' => $order->get_date_created() ? wc_rest_prepare_date_response($order->get_date_created()) : null,
            'order_key' => $order->get_order_key(),
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

        if (empty($order)) {
            return new \WP_REST_Response(array(
                'status' => 'error',
                'message' => 'Order not found'
            ), 404);
        }

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
