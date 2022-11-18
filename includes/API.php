<?php

namespace WebToApp;

/**
 * Class API
 * @package WebToApp
 */

class API
{
    public $routes = array();

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_api']);

        $this->routes = ['Auth', 'Product', 'Order', 'Address', 'StoreInfo', 'Cart'];
    }

    public function register_api()
    {
        foreach ($this->routes as $route) {
            $class = __NAMESPACE__ . '\\API\\' . $route;
            $instance = new $class();
            $instance->register_routes();
        }
    }
}
