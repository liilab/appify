<?php

namespace WebToApp\Admin\App_Building_Requests;

use WebToApp\Traits\Singleton;
use WebToApp\WtaHelper;

/**
 * Class Create_Request
 * @package Appify\Admin\App_Building_Requests
 */

class Create_Request
{
    use Singleton;

    public function __construct()
    {
        $this->hooks();
    }

    public function hooks()
    {
        add_action('wp_ajax_create_build_request', [$this, 'create_build_request']);
    }


    private $base_url = WTA_BUILD_URL;

    private $website_id_meta_key = 'wta_website_id';
    private $build_id_meta_key = 'wta_build_id';
    private $is_build_meta_key = 'wta_is_building';
    private $binary_url_meta_key = 'wta_binary_url';
    private $preview_url_meta_key = 'wta_preview_url';


    public function create_build_request()
    {
        $nonce = sanitize_text_field($_POST['app_create_nonce']);

        if (!wp_verify_nonce($nonce, 'wooapp-create-app-nonce-action')) {
            $response = array(

                "success" => false,
                "message" => "Nonce not verified",
            );
            echo json_encode($response);
            wp_die();
        }

        if (isset($_POST['app_name'])) {
            $appname = sanitize_text_field($_POST['app_name']);
        }

        if (isset($_POST['store_name'])) {
            $storename = sanitize_text_field($_POST['store_name']);
        }

        if (isset($_POST['icon_url'])) {
            $icon = sanitize_url($_POST['icon_url']);
        }

        $icon = "https://picsum.photos/200/300"; //look here

        $url = $this->base_url . 'api/builder/v1/create-build-request/';

        $user_id = WtaHelper::get_current_user_id();

        $website_id = get_user_meta($user_id, $this->website_id_meta_key, true);

        $config = array(
            'headers' => array(
                'Authorization' => $this->get_token(),
            ),

            'body' => [
                'success' => true,
                'app_name' => $this->clean($appname),
                'app_logo' =>  $icon,
                'store_name' => $this->clean($storename),
                'store_logo' => $icon,
                "template" => 1,
                "website" => $website_id
            ],
        );

        $response = wp_remote_post($url, $config);

        if ($response['response']['code'] == 404) {
            WtaHelper::return_error_response("Create build request error!");
        }

        $json_response = json_decode($response['body'], true);

        if (!empty($json_response['id'])) {
            $build_id = $json_response['id'];
            update_user_meta($user_id, $this->build_id_meta_key, $build_id);
        }

        echo json_encode($json_response);
        wp_die();
    }

    public function get_token()
    {
        $user_id = WtaHelper::get_current_user_id();
        return "Token " . get_user_meta($user_id, 'wta_access_token', true);
    }

    public function clean($string)
    {
        $string = preg_replace('/[^A-Za-z0-9\- ]/', '', $string);
        return $string;
    }
}
