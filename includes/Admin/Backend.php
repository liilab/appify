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
        add_action('wp_ajax_get_build_history_card', [$this, 'get_build_history_card']);
    }


    private $base_url = 'https://wooapp.liilab.com/';

    private $website_id_meta_key = 'wta_website_id';
    private $build_id_meta_key = 'wta_build_id';
    private $is_build_meta_key = 'wta_is_building';
    private $binary_url_meta_key = 'wta_binary_url';
    private $preview_url_meta_key = 'wta_preview_url';

    public function get_build_history_card()
    {
        $user_id = $this->get_current_user_id();
        $website_id = get_user_meta($user_id, $this->website_id_meta_key, true);

        $url = $this->base_url . "api/builder/v1/build-requests/?page_size=5&&website=" . $website_id;

        $config = array(
            'headers' => array(
                'Authorization' => $this->get_token(),
            ),
        );

        $response = wp_remote_get($url, $config);

        $json_response = json_decode($response['body'], true);
        echo json_encode($json_response);
        wp_die();
    }


    public function get_build_history()
    {
        $user_id = $this->get_current_user_id();

        // delete_user_meta($user_id, $this->build_id_meta_key);
        // delete_user_meta($user_id, $this->is_build_meta_key);
        // delete_user_meta($user_id, $this->binary_url_meta_key);
        // delete_user_meta($user_id, $this->preview_url_meta_key);


        $build_id = get_user_meta($user_id, $this->build_id_meta_key, true);
        $is_building = get_user_meta($user_id, $this->is_build_meta_key, true);
        $binary_url = get_user_meta($user_id, $this->binary_url_meta_key, true);
        $preview_url = get_user_meta($user_id, $this->preview_url_meta_key, true);

        $response = array(
            "build_found" => !empty($build_id),
            "is_building" => $is_building == "1",
            "build_id" => $build_id,
            "binary_url" => $binary_url,
            "preview_url" => $preview_url,
            "token" => $this->get_token(),
        );

        echo json_encode($response);
        wp_die();
    }

    public function create_build_request()
    {
        $nonce = $_POST['app_create_nonce'];
        if (!wp_verify_nonce($nonce, 'wooapp-create-app-nonce-action')) {
            $response = array(
               
                "success" => false,
                "message" => "Nonce not verified",
            );
            echo json_encode($response);
            wp_die();
        }

        $appname = sanitize_text_field($_POST['app_name']);
        $storename = sanitize_text_field($_POST['store_name']);
        $icon =  'https://picsum.photos/200'; //$_POST['icon_url'] ? sanitize_url($_POST['icon_url']) : 'https://picsum.photos/200'; 

        $url = $this->base_url . 'api/builder/v1/create-build-request/';

        $user_id = $this->get_current_user_id();

        $website_id = get_user_meta($user_id, $this->website_id_meta_key, true);

        $config = array(
            'headers' => array(
                'Authorization' => $this->get_token(),
            ),

            'body' => array(
                'success' => true,
                'app_name' => $this->clean($appname),
                'app_logo' =>  $icon,
                'store_name' => $this->clean($storename),
                'store_logo' => $icon,
                "template" => 1,
                "website" => $website_id
            ),
        );

        $response = wp_remote_post($url, $config);

        $json_response = json_decode($response['body'], true);

        if (!empty($json_response['id'])) {
            $build_id = $json_response['id'];
            update_user_meta($user_id, $this->build_id_meta_key, $build_id);
        }

        echo json_encode($json_response);
        wp_die();
    }


    public function clean($string)
    {
        $string = preg_replace('/[^A-Za-z0-9\- ]/', '', $string); // Removes special chars. /[^A-Za-z0-9]/
        return $string;
    }

    public function get_build_progress()
    {
        $user_id = $this->get_current_user_id();

        $build_id = get_user_meta($user_id, $this->build_id_meta_key, true);

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

            update_user_meta($user_id, $this->is_build_meta_key, $json_response['is_building']);
            update_user_meta($user_id, $this->binary_url_meta_key, $json_response['binary']);
            update_user_meta($user_id, $this->preview_url_meta_key, $json_response['preview']);

            $response = $json_response;
        }

        echo json_encode($response);
        wp_die();
    }

    public function get_current_user_id(): int
    {
        $current_user = wp_get_current_user();
        return $current_user->ID;
    }

    public function get_token()
    {
        $user_id = $this->get_current_user_id();
        return "Token " . get_user_meta($user_id, 'wta_access_token', true);
    }
}
