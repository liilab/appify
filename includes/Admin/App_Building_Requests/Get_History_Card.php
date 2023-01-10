<?php

namespace WebToApp\Admin\App_Building_Requests;

use WebToApp\Traits\Singleton;

/**
 * Class Get_History_Card
 * @package Appify\Admin\App_Building_Requests
 */

class Get_History_Card
{
    use Singleton;

    public function __construct()
    {
        $this->hooks();
    }

    public function hooks()
    {
        add_action('wp_ajax_get_build_history_card', [$this, 'get_build_history_card']);
    }


    private $base_url = WTA_BUILD_URL;

    private $website_id_meta_key = 'wta_website_id';
    private $build_id_meta_key = 'wta_build_id';
    private $is_build_meta_key = 'wta_is_building';
    private $binary_url_meta_key = 'wta_binary_url';
    private $preview_url_meta_key = 'wta_preview_url';


    public function get_build_history_card()
    {
        $user_id = \WebToApp\WtaHelper::get_current_user_id();
        $website_id = get_user_meta($user_id, $this->website_id_meta_key, true);

        $url = $this->base_url . "api/builder/v1/build-requests/?page_size=5&&website=" . $website_id;

        $config = array(
            'headers' => array(
                'Authorization' => $this->get_token(),
            ),
        );

        $response = wp_remote_get($url, $config);

        if ($response['response']['code'] == 404) {
            \WebToApp\WtaHelper::return_error_response("Get build history card error!");
        }

        $json_response = json_decode($response['body'], true);
        echo json_encode($json_response);
        wp_die();
    }

    public function get_token()
    {
        $user_id = \WebToApp\WtaHelper::get_current_user_id();
        return "Token " . get_user_meta($user_id, 'wta_access_token', true);
    }
}
