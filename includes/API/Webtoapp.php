<?php
namespace WebToApp\API;
use WP_REST_Controller;

class Webtoapp extends WP_REST_Controller{
    function __construct(){
        $this->namespace = 'web-to-app/v1';
        $this->rest_base = 'settings';
    }

    public function register_routes(){
        register_rest_route( $this->namespace, '/' . $this->rest_base,
        [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_settings'],
                'permission_callback' => [$this, 'get_settings_permission_check'],
                'args'                => $this->get_collection_params(),
            ]
        ]
    );
    }
}