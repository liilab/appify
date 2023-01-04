<?php

namespace WebToApp\API;

/**
 * Class Address
 * @package Appify\API
 */

class Address extends \WebToApp\Abstracts\WTA_WC_REST_Controller
{

    public function register_routes()
    {
        register_rest_route('web-to-app/v1', '/address', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array($this, 'get_address'),
            'permission_callback' => array($this, 'api_permissions_check'),
        ));

        register_rest_route('web-to-app/v1', '/address', array(
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => array($this, 'create_address'),
            'permission_callback' => array($this, 'api_permissions_check'),
        ));

        register_rest_route('web-to-app/v1', '/address', array(
            'methods' => \WP_REST_Server::EDITABLE,
            'callback' => array($this, 'create_address'),
            'permission_callback' => array($this, 'api_permissions_check'),
        ));
    }

    public function create_address($request)
    {
        $token = $request->get_header('access_token');
        $user_id = \WebToApp\User\Token::get_user_id_by_token($token);
        $billing_address = $request['billing_address'];
        $shipping_address = $request['shipping_address'];

        $customer = new \WC_Customer();
        $customer->set_id($user_id);

		if ($billing_address) {
			$customer->set_billing_first_name($billing_address['first_name']);
			$customer->set_billing_last_name($billing_address['last_name']);
			$customer->set_billing_company($billing_address['company']);
			$customer->set_billing_address_1($billing_address['address_1']);
			$customer->set_billing_address_2($billing_address['address_2']);
			$customer->set_billing_city($billing_address['city']);
			$customer->set_billing_state($billing_address['state']);
			$customer->set_billing_postcode($billing_address['postcode']);
			$customer->set_billing_country($billing_address['country']);
			$customer->set_billing_email($billing_address['email']);
			$customer->set_billing_phone($billing_address['phone']);
		}

		if($shipping_address) {
			$customer->set_shipping_first_name($shipping_address['first_name']);
			$customer->set_shipping_last_name($shipping_address['last_name']);
			$customer->set_shipping_company($shipping_address['company']);
			$customer->set_shipping_address_1($shipping_address['address_1']);
			$customer->set_shipping_address_2($shipping_address['address_2']);
			$customer->set_shipping_city($shipping_address['city']);
			$customer->set_shipping_state($shipping_address['state']);
			$customer->set_shipping_postcode($shipping_address['postcode']);
			$customer->set_shipping_country($shipping_address['country']);
		}


        $customer->save();

        $data = $this->get_user_address_info($user_id);

        return new \WP_REST_Response($data, 200);
    }

    public function get_address($request)
    {
        $token = $request->get_header('access_token');
        $user_id = \WebToApp\User\Token::get_user_id_by_token($token);

        $data = $this->get_user_address_info($user_id);

        return new \WP_REST_Response($data, 200);
    }

    public function get_user_address_info($user_id){

        $customer = new \WC_Customer($user_id);

        $user_info = get_userdata($user_id);

        $username     = $user_info->user_login;
        $user_email   = $user_info->user_email;
        $first_name   = $user_info->first_name;
        $last_name    = $user_info->last_name;
        $display_name = $user_info->display_name;

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

        $data['billing_address'] = array(
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

        $data['shipping_address'] = array(
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
