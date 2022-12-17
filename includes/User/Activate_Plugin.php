<?php

namespace WebToApp\User;

class Activate_Plugin
{

    public function __construct()
    {
        add_action('user_register', [$this, 'wta_registration_save']);
    }

    public function wta_registration_save($user_id)
    {
        $this->create_user_token($user_id);
    }


    private function create_user_token($user_id, $force_new = false)
    {

        $url = 'http://192.168.0.129:8000/api/builder/v1/activate-plugin/';



        $user_info = get_userdata($user_id);

        $data = array(
            'user' => array(
                'first_name' => $user_info->first_name ? $user_info->first_name : '',
                'last_name' => $user_info->last_name ? $user_info->last_name : '',
                'email' => $user_info->user_email ? $user_info->user_email : '',
            ),
            'website' => array(
                'name' => get_bloginfo('name'),
                'domain' => get_bloginfo('url'),
            ),

            'keystore' => array(
                'city' => WC()->countries->get_base_city(),
                'country' => WC()->countries->get_base_country(),
                'name' =>  $user_info->first_name . ' ' . $user_info->last_name,
                'state' => WC()->countries->get_states(WC()->countries->get_base_country())[WC()->countries->get_base_state()],
                'organization' => get_bloginfo('name'),
                'organizational_unit' => str_replace(" ", "-", strtolower(get_bloginfo('name'))) . '-e-commerce',
            ),
        );

        $response = $this->create_plugin_activate_post_request($url, $data);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            echo "Something went wrong: $error_message";
        } else {
            $this->save_user_data($response);
        }
    }

    public function create_plugin_activate_post_request($url, $data)
    {
        $args = array(
            'method' => 'POST',
            'body' => $data,
        );
        $response = wp_remote_post($url, $args);
        return $response;
    }

    public function save_user_data($response)
    {
        $token = $response['token'];
        $user_id = $response['user_id'];
        $website_id = $response['website_id'];

        $id = $this->get_current_user_id();

        update_user_meta($id, 'wta_wc_user_id', $user_id);
        update_user_meta($id, 'wta_wc_access_token', $token);
        update_user_meta($id, 'wta_wc_website_id', $website_id);
    }

    public function get_current_user_id(): int
    {
        $current_user = wp_get_current_user();
        return $current_user->ID;
    }
}
