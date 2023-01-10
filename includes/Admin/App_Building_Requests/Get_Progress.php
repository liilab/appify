<?php

namespace WebToApp\Admin\App_Building_Requests;

use WebToApp\Traits\Singleton;

/**
 * Class Get_Progress
 * @package Appify\Admin\App_Building_Requests
 */

class Get_Progress
{
    use Singleton;

    public function __construct()
    {
        $this->hooks();
    }

    public function hooks()
    {
        add_action('wp_ajax_get_build_progress', [$this, 'get_build_progress']);
    }


    private $base_url = WTA_BUILD_URL;

    private $website_id_meta_key = 'wta_website_id';
    private $build_id_meta_key = 'wta_build_id';
    private $is_build_meta_key = 'wta_is_building';
    private $binary_url_meta_key = 'wta_binary_url';
    private $preview_url_meta_key = 'wta_preview_url';


    public function get_build_progress()
    {
        $user_id = \WebToApp\WtaHelper::get_current_user_id();

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

            if ($response['response']['code'] == 404) {
                \WebToApp\WtaHelper::return_error_response("Build progress error!");
            }

            $json_response = json_decode($response['body'], true);

            update_user_meta($user_id, $this->is_build_meta_key, $json_response['is_building']);
            update_user_meta($user_id, $this->binary_url_meta_key, $json_response['binary']);
            update_user_meta($user_id, $this->preview_url_meta_key, $json_response['preview']);

            $response = $json_response;
        }

        echo json_encode($response);
        wp_die();
    }

    public function get_token()
    {
        $user_id = \WebToApp\WtaHelper::get_current_user_id();
        return "Token " . get_user_meta($user_id, 'wta_access_token', true);
    }
}
