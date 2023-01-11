<?php

namespace WebToApp\Admin\App_Building_Requests;

use WebToApp\Traits\Singleton;
use WebToApp\WtaHelper;

/**
 * Class Plugin_Activation_Info_Share
 * @package Appify\Admin\App_Building_Requests
 */

class Plugin_Activation_Info_Share
{
    use Singleton;

    public function __construct()
    {
        $this->hooks();
    }

    public function hooks()
    {
        add_action('wp_ajax_plugin_activation_post_request', [$this, 'plugin_activation_post_request']);
    }

    private $base_url = WTA_BUILD_URL;

    public function plugin_activation_post_request()
    {
        $user_id = WtaHelper::get_current_user_id();

        $url = $this->base_url . 'api/builder/v1/activate-plugin/';

        $user_info = get_userdata($user_id);

        $first_name = $user_info->first_name ? $user_info->first_name : 'John';
        $last_name = $user_info->last_name ? $user_info->last_name : 'Doe';
        $user_email = $user_info->user_email ? $user_info->user_email : 'example@gmail.com';
        $site_name = get_bloginfo('name') ? get_bloginfo('name') : 'Example';
        $state_name = WC()->countries->get_states(WC()->countries->get_base_country())[WC()->countries->get_base_state()] ? WC()->countries->get_states(WC()->countries->get_base_country())[WC()->countries->get_base_state()] : 'Sylhet';
        $country =  WC()->countries->get_base_country() ? WC()->countries->get_base_country() : 'Bangladesh';
        $city = WC()->countries->get_base_city() ? WC()->countries->get_base_city() : 'Sylhet Sadar';


        $data = array(

            'headers' => array(
                'Content-Type' => 'application/json',
            ),

            'body' => json_encode(
                array(
                    'user' => array(
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'email' => $user_email,
                    ),
                    'website' => array(
                        'name' => get_bloginfo('name'),
                        'domain' => get_bloginfo('url'),
                    ),

                    'keystore' => array(
                        'city' => $city,
                        'country' => $country,
                        'name' =>  $first_name . ' ' . $last_name,
                        'state' => $state_name,
                        'organization' => $site_name,
                        'organizational_unit' => WtaHelper::clean(strtolower($site_name)) . '-e-commerce',
                    ),
                )
            ),
        );

        $response = wp_remote_post($url, $data);

        if ($response['response']['code'] == 404) {
            WtaHelper::return_error_response("Plugin activation post request error!");
        }

        $json_response = json_decode($response['body'], true);

        if (!isset($json_response)) {
            return;
        }

        if (empty($json_response['token']) || empty($json_response['user_id']) || empty($json_response['website_id'])) {
            return;
        }

        $token = $json_response['token'];
        $userid = $json_response['user_id'];
        $website_id = $json_response['website_id'];

        update_user_meta($user_id, 'wta_user_id', $userid);
        update_user_meta($user_id, 'wta_access_token', $token);
        update_user_meta($user_id, 'wta_website_id', $website_id);

        $response = array(
            'status' => 'success',
            'message' => 'Plugin activated successfully',
        );

        echo json_encode($response);

        wp_die();
    }
}
