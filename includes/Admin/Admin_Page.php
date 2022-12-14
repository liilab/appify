<?php

namespace WebToApp\Admin;

use WebToApp\Traits\Singleton;

/**
 * Class Admin
 * @package WebToApp
 */

class Admin_Page
{
    use Singleton;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'create_settings'));
        add_action('admin_init', array($this, 'wta_setup_sections'));
        add_action('admin_init', array($this, 'wta_setup_fields'));
        add_action('admin_enqueue_scripts', array($this, 'wta_enqueue_scripts'));
        add_action('wp_ajax_download_app', array($this, 'download_app'));
    }

    // public function download_app(){
    // }

    public function wta_enqueue_scripts()
    {
        wp_enqueue_style('wta-admin', WTA_ASSETS . '/css/admin.css', null, WTA_VERSION);
        wp_enqueue_style('wta-admin-bootstrap', '//cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css');
        wp_enqueue_script('wta-admin-js', WTA_ASSETS . '/js/admin.js', array('jquery'), '1.0.0', true);
        wp_localize_script('wta-admin-js', 'wta_ajax', array(
            'admin_ajax' => admin_url( 'admin-ajax.php'),

        ));
    }

    public function create_settings()
    {
        $parent_slug = 'woocommerce';
        $page_title  = 'WooApp';
        $menu_title  = 'WooApp';
        $capability  = 'manage_options';
        $slug        = 'web-to-app';
        $callback    = array($this, 'wta_settings_content');
        add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $slug, $callback);
    }

    public function wta_settings_content()
    {
        require_once WTA_DIR_PATH . 'templates/admin/setting-page.php';
    }

    public function wt_settings_content()
    { ?>
<div class="wrap">
    <h1>Welcome to WooApp</h1>
    <?php settings_errors(); ?>
    <form method="POST" action="options.php">
        <?php
                settings_fields('WooApp');
                do_settings_sections('WooApp');
                //submit_button();
                ?>
    </form>
</div> <?php
            }

            public function wta_setup_sections()
            {
                add_settings_section('wta_section', '', array(), 'WooApp');
                add_settings_section('wta_getapp_section', '', array(), 'WooApp');
            }

            public function wta_setup_fields()
            {

                $fields = array(
                    // array(
                    //     'section' => 'wta_section',
                    //     'label' => 'Access Key',
                    //     'id' => 'access_key',
                    //     'type' => 'textarea',
                    // )

                     array(
                        'section' => 'wta_getapp_section',
                        'label' => 'Get Our App',
                        'id' => 'getapp',
                        'type' => 'button',
                    )
                );
                foreach ($fields as $field) {
                    add_settings_field($field['id'], $field['label'], array($this, 'wta_field_callback'), 'WooApp', $field['section'], $field);
                    register_setting('WooApp', $field['id']);
                }
            }
            public function wta_field_callback($field)
            {

                $access_key = $this->get_access_key();

                $value = get_option($field['id']);
                $value = $access_key;
                $placeholder = '';
                if (isset($field['placeholder'])) {
                    $placeholder = $field['placeholder'];
                }
                switch ($field['type']) {


                    case 'textarea':
                        printf(
                            '<textarea class="wta-textarea" readonly name="%1$s" id="%1$s" rows="5" cols="50">%2$s</textarea>',
                            $field['id'],
                            $value
                        );
                        printf(
                            '<p><a id="%1$s" class="button button-primary">%2$s</a></p>',
                            "copy_button",
                            "Copy Key"
                        );
                        break;

                    case 'button':
                        printf(
                            '<a id="%1$s" class="button button-primary">%2$s</a>',
                            "getapp_button",
                            "Download Now"
                        );
                        break;

                    default:
                        printf(
                            '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />',
                            $field['id'],
                            $field['type'],
                            $placeholder,
                            $value
                        );
                }
                
                if (isset($field['desc'])) {
                    if ($desc = $field['desc']) {
                        printf('<p class="description">%s </p>', $desc);
                    }
                }
            }

            public function get_access_key()
            {
                $access_key = get_option('wta_access_key');

                if (empty($access_key)) {
                    $access_key = $this->generate_access_key();
                    update_option('wta_access_key', $access_key);
                }

                return $access_key;
            }


            public function generate_access_key()
            {
                $site_url = get_site_url();



                $api_details = [
                    'url'         => $site_url,
                ];
                $encoded_access_key = base64_encode(json_encode($api_details));
                return $encoded_access_key;
            }
        }