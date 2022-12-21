<?php

namespace WebToApp\Admin;

use WebToApp\Traits\Singleton;

class Backend
{
    use Singleton;

    public function __construct()
    {
        $this->hooks();
    }

    public function hooks()
    {
        add_action('wp_ajax_get_build_history', [$this, 'get_build_history']);
        add_action('wp_ajax_create_build_request', [$this, 'create_build_request']);
        add_action('wp_ajax_get_build_progress', [$this, 'get_build_progress']);
    }

    private $base_url = 'http://192.168.0.129:8000/';

    public function get_build_history()
    {
        $user_id = $this->get_current_user_id();
        //        $user_id = $this->get_dummy_user_id();

        $build_id = get_user_meta($user_id, 'build_id', true);
        $is_building = get_user_meta($user_id, 'is_building', true);
        $binary_url = get_user_meta($user_id, 'binary_url', true);
        $preview_url = get_user_meta($user_id, 'preview_url', true);

        $response = array(
            "build_found" => false, // !empty($build_id),
            "is_building" => $is_building == "1",
            "build_id" => $build_id,
            "binary_url" => $binary_url,
            "preview_url" => $preview_url,
        );

        echo json_encode($response);
        wp_die();
    }

    public function create_build_request()
    {
        $url = $this->base_url . 'api/builder/v1/create-build-request/';

        $user_id = $this->get_current_user_id();
        $user_info = get_userdata($user_id);
        //        $user_id = $this->get_dummy_user_id();

        $website_id = get_user_meta($user_id, 'wta_website_id', true); 

        $config = array(
            'headers' => array(
                'Authorization' => $this->get_token(),
            ),

            'body' => array(
                'app_name' => get_option('app-name'),
                'app_logo' => "https://picsum.photos/200/300", //get_option('app-logo'),
                'store_name' => get_option('store-name'),
                'store_logo' => "https://picsum.photos/200/300",// get_option('store-logo'),
                "template" => 1,
                "website" =>  $website_id
            ),
        );

        $response = wp_remote_post($url, $config);

        $json_response = json_decode($response['body'], true);

        if (!empty($json_response['id'])) {
            $build_id = $json_response['id'];
            update_user_meta($user_id, 'build_id', $build_id);
        }

        echo json_encode($json_response);
        wp_die();
    }

    public function get_build_progress()
    {
        $user_id = $this->get_current_user_id();
        //        $user_id = $this->get_dummy_user_id();

        $build_id = get_user_meta($user_id, 'build_id', true);

        if (empty($build_id)) {
            $response = array(
                "message" => "Build ID not found",
            );
        } else {
            $args = array(
                'headers' => array(
                    'Authorization' => $this->get_token(),
                ),
            );

            $url = $this->base_url . 'api/builder/v1/build-request/' . $build_id . '/';

            $response = wp_remote_get($url, $args);

            $json_response = json_decode($response['body'], true);

            update_user_meta($user_id, 'is_building', $json_response['is_building']);
            update_user_meta($user_id, 'binary_url', $json_response['binary']);
            update_user_meta($user_id, 'preview_url', $json_response['preview']);

            $response = $json_response;
        }

        echo json_encode($response);
        wp_die();
    }

    public function get_dummy_user_id()
    {
        return 1152;
    }

    public function get_current_user_id(): int
    {
        $current_user = wp_get_current_user();
        return $current_user->ID;
    }

    public function get_token()
    {
        $user_id = $this->get_current_user_id();

        $token = "Token ".get_user_meta($user_id, 'wta_access_token', true); 

        return $token;
    }
}
