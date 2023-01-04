<?php

namespace WebToApp\Abstracts;

/**
 * Class WTA_WC_REST_Controller
 * @package Appify\Abstracts
 */

abstract class WTA_WC_REST_Controller extends \WP_REST_Controller
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
    protected $rest_base = '';

    /**
     * Check if a given request has access to read items.
     *
     * @param  WP_REST_Request $request Full details about the request.
     * @return WP_Error|boolean
     */
    public function get_items_permissions_check($request)
    {
        return true;
    }

    /**
     * Check if a given request has access to read a specific item.
     *
     * @param  WP_REST_Request $request Full details about the request.
     * @return WP_Error|boolean
     */
    public function get_item_permissions_check($request)
    {
        return true;
    }

    /**
     * Check if a given request has access to create items.
     *
     * @param  WP_REST_Request $request Full details about the request.
     * @return WP_Error|boolean
     */
    public function create_item_permissions_check($request)
    {
        return true;
    }

    /**
     * Check if a given request has access to update a specific item.
     *
     * @param  WP_REST_Request $request Full details about the request.
     * @return WP_Error|boolean
     */
    public function update_item_permissions_check($request)
    {
        return true;
    }

    /**
     * Check if a given request has access to delete a specific item.
     *
     * @param  WP_REST_Request $request Full details about the request.
     * @return WP_Error|boolean
     */
    public function delete_item_permissions_check($request)
    {
        return true;
    }

    /**
     * Check if a given request has access to batch items.
     *
     * @param  WP_REST_Request $request Full details about the request.
     * @return WP_Error|boolean
     */
    public function batch_items_permissions_check($request)
    {
        return true;
    }

    /**
     * Prepare the item for create or update operation.
     *
     * @param  WP_REST_Request $request Request object.
     * @return WP_Error|object $prepared_item
     */
    protected function prepare_item_for_database($request)
    {
        return new \WP_Error('woocommerce_rest_method_not_implemented', sprintf(__('The %1$s method has not been implemented.', 'woocommerce'), 'prepare_item_for_database'), array('status' => 405));
    }
}
