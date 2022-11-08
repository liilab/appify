<?php

namespace WebToApp\API;

class Address
{

    public function register_routes()
    {
        register_rest_route('web-to-app/v1', '/address', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_address'),
            'permission_callback' => array($this, 'api_permissions_check'),
        ));
    }

    public function get_address($request)
    {
        $token = $request->get_header('access_token');
        $user_id = \WebToApp\User\Token::get_user_id_by_token($token);

        $data = array();

        $customer = new \WC_Customer($user_id);

        $username     = $customer->get_username(); // Get username
        $user_email   = $customer->get_email(); // Get account email
        $first_name   = $customer->get_first_name();
        $last_name    = $customer->get_last_name();
        $display_name = $customer->get_display_name();

        // Customer billing information details (from account)
        $billing_first_name = $customer->get_billing_first_name();
        $billing_last_name  = $customer->get_billing_last_name();
        $billing_company    = $customer->get_billing_company();
        $billing_address_1  = $customer->get_billing_address_1();
        $billing_address_2  = $customer->get_billing_address_2();
        $billing_city       = $customer->get_billing_city();
        $billing_state      = $customer->get_billing_state();
        $billing_postcode   = $customer->get_billing_postcode();
        $billing_country    = $customer->get_billing_country();

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
            'username' => $username,
            'user_email' => $user_email,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => $display_name,
        );

        $data['billing'] = array(
            'first_name' => $billing_first_name,
            'last_name' => $billing_last_name,
            'company' => $billing_company,
            'address_1' => $billing_address_1,
            'address_2' => $billing_address_2,
            'city' => $billing_city,
            'state' => $billing_state,
            'postcode' => $billing_postcode,
            'country' => $billing_country,
        );

        $data['shipping'] = array(
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

        return $data;
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
