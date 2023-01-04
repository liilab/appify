<?php

namespace WebToApp\Admin;

use WebToApp\Traits\Singleton;

/**
 * Class Admin Page
 * @package Appify\Admin
 */
class Admin_Page
{
    use Singleton;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'create_settings'));
        add_action('admin_enqueue_scripts', array($this, 'wta_enqueue_scripts'));
        add_action('admin_enqueue_scripts', 'wp_enqueue_media');
    }

    public function wta_enqueue_scripts()
    {
        wp_enqueue_style('wta-admin', WTA_ASSETS . '/build/css/admin.css', null, WTA_VERSION);
        wp_enqueue_style('wta-admin-bootstrap-css', '//cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css');
        wp_enqueue_style('wta-admin-bootstrap-icon-css', '//cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css');
        wp_enqueue_script('wta-admin-bootstrap-js', '//cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js');
        wp_enqueue_script('wta-admin-moment-js', '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js');
        wp_enqueue_script('wta-admin-swal-js', '//unpkg.com/sweetalert/dist/sweetalert.min.js');
        wp_enqueue_script('wta-admin-js', WTA_ASSETS . '/build/js/admin.js', array('jquery'), '1.0.0', true);
        wp_localize_script('wta-admin-js', 'wta_ajax', array(
            'admin_ajax' => admin_url('admin-ajax.php'),

        ));
    }

    public function create_settings()
    {
        $page_title  = 'Appify';
        $menu_title  = 'Appify';
        $capability  = 'manage_options';
        $slug        = 'appify';
        $callback    = array($this, 'wta_settings_content');
        $icon        = WTA_ASSETS . '/build/img/appify-icon-20x20.png';
        $position    = 100;
        add_menu_page($page_title, $menu_title, $capability, $slug, $callback, $icon, $position);
    }

    public function wta_settings_content()
    {
        require_once WTA_DIR_PATH . 'templates/admin/setting-page.php';
    }
}
