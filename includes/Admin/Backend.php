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

        $token = 'Token f6fbdfbff8f79e03c80048f8c8daf1906a9c3bb4';

        $url = 'http://192.168.0.129:8000/api/builder/v1/create-build-request/';

        $user_id = $this->get_current_user_id();
        $user_info = get_userdata($user_id);
        $user_id = 1111;

       

        $is_url_exist = get_user_meta($user_id, 'build_url', true);

        if (!empty($is_url_exist)) {
            $send = [
                "ok" => true,
                "pending" => false,
                "status" => true,
                "binary" => $is_url_exist,
            ];


            echo json_encode($send);
            wp_die();
        }



        $args = array(
            'headers' => array(
                'Authorization' => $token,
            ),



            'body' => array(
                'first_name' => $user_info->first_name,
                'last_name' => $user_info->last_name,
                'username' => $user_info->user_login,
                'email' => $user_info->user_email,
                'app_name' => get_bloginfo('name'),
                'app_logo' => 'https://play-lh.googleusercontent.com/BUB9hjJqtkBjHekgrqsINgzNMzA-G34nyZQDRmzmQdw6_qbpO8E9l78Z9wS0eCp8QFKE',
                'store_name' => get_bloginfo('name'),
                'store_logo' => 'https://play-lh.googleusercontent.com/BUB9hjJqtkBjHekgrqsINgzNMzA-G34nyZQDRmzmQdw6_qbpO8E9l78Z9wS0eCp8QFKE',
                'domain' => get_bloginfo('url'),
                "template" => 1,


                // "first_name" => "Abir",
                // "last_name" =>  "Wasim",
                // "username" => "sabbir7123",
                // "email" => "sabbir7123@gmail.com",
                // "app_name" => "Shatkora",
                // "app_logo" => "https://play-lh.googleusercontent.com/BUB9hjJqtkBjHekgrqsINgzNMzA-G34nyZQDRmzmQdw6_qbpO8E9l78Z9wS0eCp8QFKE",
                // "store_name" => "Shatkora",
                // "store_logo" => "https://play-lh.googleusercontent.com/BUB9hjJqtkBjHekgrqsINgzNMzA-G34nyZQDRmzmQdw6_qbpO8E9l78Z9wS0eCp8QFKE",
                // "domain" => "https://liipress.abirwasim.me",
                // "template" => 1
            )
        );

        // echo json_encode($args['body']);

        $is_request_create_previously = get_user_meta($user_id, 'build_id', true);


        if (empty($is_request_create_previously)) {
            $response = wp_remote_post($url, $args);

            $body = $response['body'];


            $body =  json_decode($body, true);

            $is_pending = empty($body['message']) ? false : true;

            if ($is_pending == false) {
                $build_id =  $body['id'];
                update_user_meta($user_id, 'build_id', $build_id);
            }
        } else {
            $build_id = $is_request_create_previously;
            $is_pending = false;
        }

        if ($is_pending == false) {
            $args = array(
                'headers' => array(
                    'Authorization' => $token,
                ),
            );

            $is_loop = true;

            while ($is_loop) {

                $url = 'http://192.168.0.129:8000//api/builder/v1/build-request/' . $build_id . '/';

                $response = wp_remote_get($url, $args);

                $body = $response['body'];
                $body =  json_decode($body, true);

                $status = $body['status'];

                if ($status === "SUCCESS") {
                    update_user_meta($user_id, 'build_url', $body['binary']);
                    $send = [
                        "pending" => false,
                        "status" => true,
                        "binary" => $body['binary'],
                    ];

                    break;
                }

                sleep(5);
            }
        }


        echo json_encode($send);
        wp_die();
    }

    public function get_current_user_id()
    {
        $current_user = wp_get_current_user();
        return $current_user->ID;
    }
}
