<?php

namespace WebToApp\API;

class Webtoapp extends \WP_REST_Controller
{
    function __construct()
    {
        $this->namespace = 'web-to-app/v1';
        $this->rest_base = 'products';
    }

    public function register_routes()
    {
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array($this, 'get_items'),
                'permission_callback' => array($this, 'api_permissions_check'),
                'args'                => $this->get_collection_params(),
            ),

           'schema' => array($this, 'get_public_item_schema'),
        ));

        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
            array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array($this, 'get_item'),
                'permission_callback' => array($this, 'api_permissions_check'),
                'args'                => array(
                    'context' => $this->get_context_param(array('default' => 'view')),
                ),
            ),

            'schema' => array($this, 'get_public_item_schema'),
        ));
    }

    public function get_items($request)
    {
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => -1,
        );

        $query = new \WP_Query($args);

        $posts = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $posts[] = array(
                    'id'    => get_the_ID(),
                    'title' => get_the_title(),
                );
            }
        }

        $response = new \WP_REST_Response($posts);
        $response->set_status(200);
        return $response;
    }

    public function api_permissions_check($request)
    {
        if (current_user_can('manage_options')) {
            return true;
        }
        return true;
    }

    public function get_item_schema()
    {
        if ($this->schema) {
            return $this->add_additional_fields_schema($this->schema);
        }

        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'web-to-app',
            'type'       => 'object',
            'properties' => [
                'date'         => array(
                    'description' => __("The date the object was published, in the site's timezone."),
                    'type'        => 'string',
                    'format'      => 'date-time',
                    'context'     => array('view', 'edit', 'embed'),
                ),
                'date_gmt'     => array(
                    'description' => __('The date the object was published, as GMT.'),
                    'type'        => 'string',
                    'format'      => 'date-time',
                    'context'     => array('view', 'edit'),
                ),
                'guid'         => array(
                    'description' => __('The globally unique identifier for the object.'),
                    'type'        => 'object',
                    'context'     => array('view', 'edit'),
                    'readonly'    => true,
                    'properties'  => array(
                        'raw'      => array(
                            'description' => __('GUID for the object, as it exists in the database.'),
                            'type'        => 'string',
                            'context'     => array('edit'),
                        ),
                        'rendered' => array(
                            'description' => __('GUID for the object, transformed for display.'),
                            'type'        => 'string',
                            'context'     => array('view', 'edit'),
                        ),
                    ),
                ),
                'id'           => array(
                    'description' => __('Unique identifier for the object.'),
                    'type'        => 'integer',
                    'context'     => array('view', 'edit', 'embed'),
                    'readonly'    => true,
                ),
                'link'         => array(
                    'description' => __('URL to the object.'),
                    'type'        => 'string',
                    'format'      => 'uri',
                    'context'     => array('view', 'edit', 'embed'),
                    'readonly'    => true,
                ),
                'modified'     => array(
                    'description' => __("The date the object was last modified, in the site's timezone."),
                    'type'        => 'string',
                    'format'      => 'date-time',
                    'context'     => array('view', 'edit'),
                    'readonly'    => true,
                ),
                'modified_gmt' => array(
                    'description' => __('The date the object was last modified, as GMT.'),
                    'type'        => 'string',
                    'format'      => 'date-time',
                    'context'     => array('view', 'edit'),
                    'readonly'    => true,
                ),
                'password'     => array(
                    'description' => __('A password to protect access to the post.'),
                    'type'        => 'string',
                    'context'     => array('edit'),
                ),
                'slug'         => array(
                    'description' => __('An alphanumeric identifier for the object unique to its type.'),
                    'type'        => 'string',
                    'context'     => array('view', 'edit', 'embed'),
                    'arg_options' => array(
                        'sanitize_callback' => 'sanitize_title',
                    ),
                ),
                'status'       => array(
                    'description' => __('A named status for the object.'),
                    'type'        => 'string',
                    'enum'        => array_keys(get_post_stati(array('internal' => false))),
                    'context'     => array('edit'),
                ),
                'type'         => array(
                    'description' => __('Type of Post for the object.'),
                    'type'        => 'string',
                    'context'     => array('view', 'edit', 'embed'),
                    'readonly'    => true,
                ),
            ],
        ];
        $this->schema = $schema;
        return $this->add_additional_fields_schema($this->schema);
    }
}
