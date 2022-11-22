<?php

namespace WebToApp;
use WebToApp\Traits\Singleton;

/**
 * Class Frontend
 * @package WebToApp
 */

class Frontend
{
    use Singleton;

    public function __construct()
    {
        // add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        // add_action('wp_footer', [$this, 'render_app']);
    }

    public function enqueue_scripts()
    {
        wp_enqueue_style('wta-frontend', WTA_ASSETS . '/css/frontend.css', null, WTA_VERSION);
        wp_enqueue_script('wta-frontend', WTA_ASSETS . '/js/frontend.js', ['jquery'], WTA_VERSION, true);
    }

    public function render_app()
    {
        $hello =  WC()->session->cart;
        echo '<pre>';
        print_r($hello);
        echo '</pre>';
    }
}
