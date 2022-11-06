<?php

namespace WebToApp\API;

/**
 * Class Order
 * @package WebToApp\API
 */

class Order extends \WP_REST_Controller
{

    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'web-to-app/v1';

    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'orders';


    /**
     * Register the routes for products.
     */
    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            array(
                array(
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => array($this, 'order_details'),
                    'permission_callback' => array($this, 'api_permissions_check'),
                ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base.'/(?P<id>[\d]+)',
            array(
                array(
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => array($this, 'order_detail'),
                    'permission_callback' => array($this, 'api_permissions_check'),
                ),
            )
        );
    }


    /**
     * Order details.
     *
     * @param \WP_REST_Request $request Full data about the request.
     *
     * @return \WP_REST_Response
     */
    public function order_details($request)
    {

    }

    /**
     * Order detail.
     *
     * @param \WP_REST_Request $request Full data about the request.
     *
     * @return \WP_REST_Response
     */
    
    public function order_detail($request)
    {

    }

    /**
     * Check API permissions.
     *
     * @param \WP_REST_Request $request Full data about the request.
     *
     * @return bool|\WP_Error
     */

    public function api_permissions_check($request)
    {
        if (current_user_can('manage_options')) {
            return true;
        }

        return false;
    }
}
