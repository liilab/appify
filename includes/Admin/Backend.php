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

    // {
    //     "first_name": "Abir",
    //     "last_name":  "Wasim",
    //     "username": "asm_wasim",
    //     "email": "wasim.ve782@gmail.com",
    //     "app_name": "Shatkora",
    //     "app_logo": "https://play-lh.googleusercontent.com/BUB9hjJqtkBjHekgrqsINgzNMzA-G34nyZQDRmzmQdw6_qbpO8E9l78Z9wS0eCp8QFKE",
    //     "store_name": "Shatkora",
    //     "store_logo": "https://play-lh.googleusercontent.com/BUB9hjJqtkBjHekgrqsINgzNMzA-G34nyZQDRmzmQdw6_qbpO8E9l78Z9wS0eCp8QFKE",
    //     "domain": "https://liipress.abirwasim.me",
    //     "template": 1
    // }


    public function set_post_request()
    {



        $token = 'Token f6fbdfbff8f79e03c80048f8c8daf1906a9c3bb4';

        $urls = array(
            'http://192.168.0.129:8000/api/builder/v1/create-build-request/'
        );

        $args = array(
            'headers' => array(
                'Authorization' => $token,
            ),

            $id = $this->get_current_user_id(),

            $user_info = get_userdata($id),
            
            'body' => array(
                // 'first_name' => $user_info->first_name,
                // 'last_name' => $user_info->last_name,
                // 'username' => $user_info->user_login,
                // 'email' => $user_info->user_email,
                // 'app_name' => get_bloginfo('name'),
                // 'app_logo' => 'https://play-lh.googleusercontent.com/BUB9hjJqtkBjHekgrqsINgzNMzA-G34nyZQDRmzmQdw6_qbpO8E9l78Z9wS0eCp8QFKE',
                // 'store_name' => get_bloginfo('name'),
                // 'store_logo' => 'https://play-lh.googleusercontent.com/BUB9hjJqtkBjHekgrqsINgzNMzA-G34nyZQDRmzmQdw6_qbpO8E9l78Z9wS0eCp8QFKE',
                // 'domain' => get_bloginfo('url'),
                // "template" => 1,

                
    "first_name"=> "Abir",
    "last_name"=>  "Wasim",
    "username"=> "waim211111",
    "email"=> "wasim.ve211111@gmail.com",
    "app_name"=> "Shatkora",
    "app_logo"=> "https://play-lh.googleusercontent.com/BUB9hjJqtkBjHekgrqsINgzNMzA-G34nyZQDRmzmQdw6_qbpO8E9l78Z9wS0eCp8QFKE",
    "store_name"=> "Shatkora",
    "store_logo"=> "https://play-lh.googleusercontent.com/BUB9hjJqtkBjHekgrqsINgzNMzA-G34nyZQDRmzmQdw6_qbpO8E9l78Z9wS0eCp8QFKE",
    "domain"=> "https://liipress.abirwasim.me",
    "template"=> 1
            )
        );

        foreach ($urls as $url) {
            $response = wp_remote_post($url, $args);
        }

        wp_send_json( $response );

        // $url_param = 'https://auto.apkbuilder.co/job/web2app_' . $id . '/buildWithParameters';

        // $data = [
        //     'app_name' => get_bloginfo('name'),
        //     'app_logo' => get_site_icon_url(),
        //     'store_name' => get_bloginfo('name'),
        //     'store_logo' => get_site_icon_url(),
        //     'base_url' => get_bloginfo('url'),
        // ];

        // $response = wp_remote_post($url_param . '?' . http_build_query($data), $args);

        // if (is_wp_error($response)) {
        //     $error_message = $response->get_error_message();
        //     echo "Something went wrong: $error_message";
        // }

        // $url = 'https://auto.apkbuilder.co/job/web2app_' . $id . '/api/json?depth=0';

        // $response = wp_remote_get($url, $args);

        // $inQueue = json_decode($response['inQueue']);

        // if ($inQueue) {
        //     $last_build = json_decode($response['lastBuild']['number']);
        //     $url = 'https://auto.apkbuilder.co/job/web2app_'. $id .'/'.$last_build.'/api/json?depth=0';

        //     $response = wp_remote_get($url, $args);

        //     $result = json_decode($response['result']);

        //     if( $result == "SUCCESS"){
        //         $generate_url = "https://auto.apkbuilder.co/job/web2app_'. $id .'/ws/e-commerce-template/build/app/outputs/flutter-apk/app-release.apk";

        //         echo $generate_url;
        //     }
        // }

        wp_die();
    }

    public function get_current_user_id()
    {
        $current_user = wp_get_current_user();
        return $current_user->ID;
    }
}