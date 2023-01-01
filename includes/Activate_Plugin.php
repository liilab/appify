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

        $url = 'https://test.wooapp.liilab.com/api/builder/v1/activate-plugin/';

        $user_info = get_userdata($user_id);

        $first_name = $user_info->first_name ? $user_info->first_name : 'default';
        $last_name = $user_info->last_name ? $user_info->last_name : 'default';
        $user_email = $user_info->user_email ? $user_info->user_email : 'default@gmail.com';
        $site_name = get_bloginfo('name')? get_bloginfo('name') : 'default';
        $state_name = get_option( 'woocommerce_store_state' ) ? get_option( 'woocommerce_store_state' ) : 'Sylhet';
        $country = get_option( 'woocommerce_default_country') ? get_option( 'woocommerce_default_country') : 'Bangladesh';
        $city = get_option( 'woocommerce_store_city' ) ? get_option( 'woocommerce_store_city' ) : 'Sylhet';


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
                        'organizational_unit' => \WebToApp\WtaHelper::clean(strtolower($site_name)) . '-e-commerce',
                    ),
                )
            ),
        );

        $response = wp_remote_post($url, $data);
        $json_response = json_decode($response['body'], true);

        if(!isset($json_response)){
            return;
        }

        if(empty($json_response['token']) || empty($json_response['user_id']) || empty($json_response['website_id'])){
            return;
        }

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
