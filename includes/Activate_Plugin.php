<?php

namespace WebToApp;

use WebToApp\Traits\Singleton;

class Activate_Plugin
{
    use Singleton;

    public function __construct()
    {
        $this->wta_registration_save();
    }


    public function wta_registration_save()
    {
        $user_id = $this->get_current_user_id();

        $url = 'https://wooapp.liilab.com/api/builder/v1/activate-plugin/';

        $user_info = get_userdata($user_id);

        $data = array(

            'headers' => array(
                'Content-Type' => 'application/json',
            ),

            'body' => json_encode(
                array(
                    'user' => array(
                        'first_name' => $user_info->first_name ? $user_info->first_name : 'default',
                        'last_name' => $user_info->last_name ? $user_info->last_name : 'default',
                        'email' => $user_info->user_email ? $user_info->user_email : 'default@gmail.com',
                    ),
                    'website' => array(
                        'name' => get_bloginfo('name'),
                        'domain' => "https://test.tsabbir.com/", //get_bloginfo('url'),
                    ),

                    'keystore' => array(
                        'city' => WC()->countries->get_base_city() ? WC()->countries->get_base_city() : 'default',
                        'country' => WC()->countries->get_base_country(),
                        'name' =>  $user_info->first_name . ' ' . $user_info->last_name,
                        'state' => WC()->countries->get_states(WC()->countries->get_base_country())[WC()->countries->get_base_state()],
                        'organization' => get_bloginfo('name'),
                        'organizational_unit' => str_replace(" ", "-", strtolower(get_bloginfo('name'))) . '-e-commerce',
                    ),
                )
            ),
        );

        $response = wp_remote_post($url, $data);
        $json_response = json_decode($response['body'], true);



        $token = $json_response['token'];
        $userid = $json_response['user_id'];
        $website_id = $json_response['website_id'];

        update_user_meta($user_id, 'wta_user_id', $userid);
        update_user_meta($user_id, 'wta_access_token', $token);
        update_user_meta($user_id, 'wta_website_id', $website_id);
    }

    public function get_current_user_id(): int
    {
        $current_user = wp_get_current_user();
        return $current_user->ID;
    }
}
