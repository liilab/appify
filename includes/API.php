<?php

namespace WebToApp;

class API
{
    public function __construct()
    {
        add_action( 'rest_api_init', [$this, 'register_api'] );
    }

    public function register_api()
    {
        $webtoapp = new API\Webtoapp();
        $webtoapp->register_routes();
    }
}