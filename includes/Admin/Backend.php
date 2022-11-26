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
        add_action('wp_ajax_set_post_request', [$this, 'set_post_request']);
    }


    public function set_post_request()
    {
        $id = $this->get_current_user_id();

        $usename = 'web2app';
        $password = '1154bfe7ab8a14b684feb736ac33d9ea1b';
        $urls = array(
            'https://auto.apkbuilder.co/job/web2app/disable',
            'https://auto.apkbuilder.co/createItem?name=web2app_' . $id . '&mode=copy&from=web2app',
            'https://auto.apkbuilder.co/job/web2app/enable',
            'https://auto.apkbuilder.co/job/web2app_' . $id . '/enable'
        );

        $args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($usename . ':' . $password)
            )
        );

        foreach ($urls as $url) {
            wp_remote_post($url, $args);
        }

        $url_param = 'https://auto.apkbuilder.co/job/web2app_' . $id . '/buildWithParameters';

        $data = [
            'app_name' => get_bloginfo('name'),
            'app_logo' => get_site_icon_url(),
            'store_name' => get_bloginfo('name'),
            'store_logo' => get_site_icon_url(),
            'base_url' => get_bloginfo('url'),
        ];

        $response = wp_remote_post($url_param . '?' . http_build_query($data), $args);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            echo "Something went wrong: $error_message";
        }

        $url = 'https://auto.apkbuilder.co/job/web2app_' . $id . '/api/json?depth=0';

        $response = wp_remote_get($url, $args);

        $inQueue = json_decode($response['inQueue']);

        if ($inQueue) {
            $last_build = json_decode($response['lastBuild']['number']);
            $url = 'https://auto.apkbuilder.co/job/web2app_001/'.$last_build.'/api/json?depth=0';

            $response = wp_remote_get($url, $args);

            $result = json_decode($response['result']);

            if( $result == "SUCCESS"){
                $generate_url = "https://auto.apkbuilder.co/job/web2app_001/ws/e-commerce-template/build/app/outputs/flutter-apk/app-release.apk";
                
                echo $generate_url;
            }
        }

        wp_die();
    }

    public function get_current_user_id()
    {
        $current_user = wp_get_current_user();
        return $current_user->ID;
    }
}