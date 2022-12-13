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
        add_action('wp_ajax_get_build_id', [$this, 'get_build_id']);
        add_action('wp_ajax_get_build_progress', [$this, 'get_build_progress']);
        add_action('wp_ajax_set_post_request', [$this, 'set_post_request']);
    }

    public function get_build_id()
    {
        $user_id = $this->get_current_user_id();
        // $user_id = 1131;

        $build_id = get_user_meta($user_id, 'build_id', true);

        $build_response = array(
            "build_id" => $build_id,
        );

        echo json_encode($build_response);
        wp_die();
    }

    public function get_build_progress()
    {
        $base_url = 'http://192.168.0.129:8000/';

        $token = 'Token f6fbdfbff8f79e03c80048f8c8daf1906a9c3bb4';

        $url = $base_url . 'api/builder/v1/create-build-request/';

        $user_id = $this->get_current_user_id();
        // $user_id = 1131;

        $build_id = get_user_meta($user_id, 'build_id', true);

        $build_response = array(
            "status" => 'NOT_BUILT',
            "binary" => '',
        );

        if (!empty($build_id)) {
            $args = array(
                'headers' => array(
                    'Authorization' => $token,
                ),
            );

            $request_cnt = 0;

            update_user_meta($user_id, 'is_pending', 1);

            while (true) {

                $url = $base_url . 'api/builder/v1/build-request/' . $build_id . '/';

                $response = wp_remote_get($url, $args);

                $body = $response['body'];
                $body =  json_decode($body, true);

                $status = $body['status'];

                if ($status !== "NOT_BUILT") {
                    update_user_meta($user_id, 'build_url', $body['binary']);
                    $build_response = [
                        "status" => $status,
                        "binary" => $body['binary'],
                    ];
                    break;
                }

                $request_cnt++;
                if ($request_cnt >= 100) {
                    $build_response = [
                        "status" => $status,
                        "binary" => $body['binary'],
                    ];
                    update_user_meta($user_id, 'is_pending', 0);
                    break;
                }

                sleep(10);
            }
        }

        echo json_encode($build_response);
        wp_die();
    }

    public function set_post_request()
    {

        $dummy_logo = 'https://play-lh.googleusercontent.com/BUB9hjJqtkBjHekgrqsINgzNMzA-G34nyZQDRmzmQdw6_qbpO8E9l78Z9wS0eCp8QFKE';

        $base_url = 'http://192.168.0.129:8000/';

        $token = 'Token f6fbdfbff8f79e03c80048f8c8daf1906a9c3bb4';

        $url = $base_url . 'api/builder/v1/create-build-request/';

        $user_id = $this->get_current_user_id();
        $user_info = get_userdata($user_id);
        // $user_id = 1131;

        // $previous_build_url = get_user_meta($user_id, 'build_url', true);
        $build_id = get_user_meta($user_id, 'build_id', true);

        $build_response = array(
            "status" => 'SUCCESS',
        );

        if (empty($build_id)) {
            $args = array(
                'headers' => array(
                    'Authorization' => $token,
                ),

                'body' => array(
                    'first_name' => empty($user_info->first_name) ? '--' : $user_info->first_name,
                    'last_name' => empty($user_info->last_name) ? '--' : $user_info->last_name,
                    'username' => $user_info->user_login,
                    'email' => $user_info->user_email,
                    'app_name' => get_bloginfo('name'),
                    'app_logo' => $dummy_logo,
                    'store_name' => get_bloginfo('name'),
                    'store_logo' => $dummy_logo,
                    'domain' => get_bloginfo('url'),
                    "template" => 1,
                ),
            );

            $response = wp_remote_post($url, $args);

            $body = $response['body'];

            $body =  json_decode($body, true);

            $is_pending_build_request = empty($body['message']) ? false : true;

            if ($is_pending_build_request == false) {
                $build_id =  $body['id'];
                update_user_meta($user_id, 'build_id', $build_id);
            }
        }


        echo json_encode($build_response);
        wp_die();
    }

    public function get_current_user_id()
    {
        $current_user = wp_get_current_user();
        return $current_user->ID;
    }
}
