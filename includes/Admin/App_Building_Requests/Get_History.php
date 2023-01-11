<?php

namespace WebToApp\Admin\App_Building_Requests;

use WebToApp\Traits\Singleton;
use WebToApp\WtaHelper;

/**
 * Class Get_History
 * @package Appify\Admin\App_Building_Requests
 */

class Get_History
{
    use Singleton;

    public function __construct()
    {
        $this->hooks();
    }

    public function hooks()
    {
        add_action('wp_ajax_get_build_history', [$this, 'get_build_history']);
    }


    private $base_url = WTA_BUILD_URL;

    private $build_id_meta_key = 'wta_build_id';
    private $is_build_meta_key = 'wta_is_building';
    private $binary_url_meta_key = 'wta_binary_url';
    private $preview_url_meta_key = 'wta_preview_url';


    public function get_build_history()
    {
        $user_id = WtaHelper::get_current_user_id();

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



    public function get_token()
    {
        $user_id = WtaHelper::get_current_user_id();
        return "Token " . get_user_meta($user_id, 'wta_access_token', true);
    }
}
