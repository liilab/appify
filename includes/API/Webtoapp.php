<?php
namespace WebToApp\API;
use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Response;

class Webtoapp extends WP_REST_Controller{
    function __construct(){
        $this->namespace = 'web-to-app/v1';
        $this->rest_base = 'settings';
    }

    public function register_routes(){
        register_rest_route( $this->namespace, '/' . $this->rest_base,
        [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_settings'],
                'permission_callback' => [$this, 'get_settings_permission_check'],
                'args'                => $this->get_collection_params(),
            ],
            'schema' => [$this, 'get_item_schema'],
        ]
    );
    }

    public function get_settings($request){
        $settings = get_option('wta_settings');
        $response = new WP_REST_Response($settings);
        $response->set_status(200);
        return 'sabbir';
    }

    public function get_settings_permission_check($request){
        if(current_user_can('manage_options')){
            return true;
        }
        return true;
    }

    public function get_item_schema(){
        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'web-to-app',
            'type'       => 'object',
            'properties' => [
                'id' => [
                    'description' => esc_html__( 'Unique identifier for the object.', 'web-to-app' ),
                    'type'        => 'integer',
                    'context'     => [ 'view', 'edit', 'embed' ],
                    'readonly'    => true,
                ],
                'title' => [
                    'description' => esc_html__( 'Title for the object.', 'web-to-app' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit', 'embed' ],
                    'readonly'    => true,
                ],
                'content' => [
                    'description' => esc_html__( 'Content for the object.', 'web-to-app' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit', 'embed' ],
                    'readonly'    => true,
                ],
            ],
        ];
        return $schema;
    }
}