<?php

namespace WebToApp\Admin\Pages;

use WebToApp\Traits\Singleton;

/**
 * Class Admin Page
 * @package Appify\Admin\Pages
 */
class Admin_Page
{
    use Singleton;

    public $version = WTA_VERSION;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'create_settings'));
        add_action('admin_enqueue_scripts', array($this, 'wta_enqueue_scripts'));
        add_action('admin_enqueue_scripts', 'wp_enqueue_media');
    }

    public function wta_enqueue_scripts()
    {
        wp_enqueue_style('wta-admin-bootstrap', WTA_ASSETS . '/build/css/bootstrap.min.css', array(), $this->version);
        wp_enqueue_style('wta-admin-bootstrap-icon',  WTA_ASSETS . '/build/css/bootstrap-icons.css', array(), $this->version);
        wp_enqueue_style('wta-admin-swal2',  WTA_ASSETS . '/build/css/sweetalert2.min.css', array(), $this->version);
        wp_enqueue_style('wta-admin', WTA_ASSETS . '/build/css/admin.css', null, $this->version);

        wp_enqueue_script('wta-admin-bootstrap', WTA_ASSETS . '/build/js/bootstrap.bundle.min.js', array(), $this->version, true);
        wp_enqueue_script('wta-admin-moment', WTA_ASSETS . '/build/js/moment.min.js', array(), $this->version, true);
        wp_enqueue_script('wta-admin-swal2',  WTA_ASSETS . '/build/js/sweetalert2.min.js', array('jquery'), $this->version, true);
        wp_enqueue_script('wta-admin', WTA_ASSETS . '/build/js/admin.js', array('jquery'), $this->version, true);
        wp_localize_script('wta-admin', 'wta_ajax', array(
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
