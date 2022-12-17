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
        add_action( 'admin_enqueue_scripts', 'wp_enqueue_media' );
    }

    // public function download_app(){
    // }

    public function wta_enqueue_scripts()
    {
        wp_enqueue_style('wta-admin', WTA_ASSETS . '/css/admin.css', null, WTA_VERSION);
        wp_enqueue_style('wta-admin-bootstrap-css', '//cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css');
        wp_enqueue_script('wta-admin-bootstrap-js', '//cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js');
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

	public function wta_setup_sections() {
		add_settings_section( 'wta_custom_section', '', array(), 'wta_custom' );
	}

	public function wta_setup_fields() {
		$fields = array(
                    array(
                        'section' => 'wta_custom_section',
                        'label' => 'First name',
                        'placeholder' => 'Sabbir',
                        'id' => 'user-first-name',
                        'desc' => 'Give your first name',
                        'type' => 'text',
                    ),
        
                    array(
                        'section' => 'wta_custom_section',
                        'label' => 'Last name',
                        'placeholder' => 'Talukdar',
                        'id' => 'user-last-name',
                        'desc' => 'Give your last name',
                        'type' => 'text',
                    ),
        
                    array(
                        'section' => 'wta_custom_section',
                        'label' => 'User name',
                        'placeholder' => 'satsabbir11',
                        'id' => 'user-name',
                        'desc' => 'Give your user name',
                        'type' => 'text',
                    ),
        
                    array(
                        'section' => 'wta_custom_section',
                        'label' => 'User email',
                        'placeholder' => 'satsabbir11@gmail.com',
                        'id' => 'user-email',
                        'desc' => 'Give your email id',
                        'type' => 'email',
                    ),
        
                    array(
                        'section' => 'wta_custom_section',
                        'label' => 'App name',
                        'placeholder' => 'Kolpo',
                        'id' => 'app-name',
                        'desc' => 'Give your app name',
                        'type' => 'text',
                    ),
        
                    array(
                        'section' => 'wta_custom_section',
                        'label' => 'App logo',
                        'placeholder' => 'https://picsum.photos/200/300',
                        'id' => 'app-logo',
                        'desc' => 'Upload your logo',
                        'type' => 'media',
                        'returnvalue' => 'url'
                    ),
        
                    array(
                        'section' => 'wta_custom_section',
                        'label' => 'Store name',
                        'placeholder' => 'Kolpo Store',
                        'id' => 'store-name',
                        'desc' => 'Give your store name',
                        'type' => 'text',
                    ),
        
                    array(
                        'section' => 'wta_custom_section',
                        'label' => 'Store logo',
                        'placeholder' => 'https://picsum.photos/200/300',
                        'id' => 'store-logo',
                        'desc' => 'Upload your logo',
                        'type' => 'media',
                        'returnvalue' => 'url'
                    ),
        
                    array(
                        'section' => 'wta_custom_section',
                        'label' => 'Domain name',
                        'placeholder' => 'https://tsabbir.com',
                        'id' => 'domain-name',
                        'desc' => 'Give your domain',
                        'type' => 'url',
                    )
		);
		foreach( $fields as $field ){
			add_settings_field( $field['id'], $field['label'], array( $this, 'wta_field_callback' ), 'wta_custom', $field['section'], $field );
			register_setting( 'wta_custom', $field['id'] );
		}
	}
	public function wta_field_callback( $field ) {
		$value = get_option( $field['id'] );
		$placeholder = '';
		if ( isset($field['placeholder']) ) {
			$placeholder = $field['placeholder'];
		}
		switch ( $field['type'] ) {
            
                        case 'media':
                            $field_url = '';
                            if ($value) {
                                if ($field['returnvalue'] == 'url') {
                                    $field_url = $value;
                                } else {
                                    $field_url = wp_get_attachment_url($value);
                                }
                            }
                            printf(
                                '<input style="display:none;" id="%s" name="%s" type="text" value="%s"  data-return="%s"><div id="preview%s" style="margin-right:10px;border:1px solid #e2e4e7;background-color:#fafafa;display:inline-block;width: 100px;height:100px;background-image:url(%s);background-size:cover;background-repeat:no-repeat;background-position:center;"></div><input style="width: 19%%;margin-right:5px;" class="button menutitle-media" id="%s_button" name="%s_button" type="button" value="Select" /><input style="width: 19%%;" class="button remove-media" id="%s_buttonremove" name="%s_buttonremove" type="button" value="Clear" />',
                                $field['id'],
                                $field['id'],
                                $value,
                                $field['returnvalue'],
                                $field['id'],
                                $field_url,
                                $field['id'],
                                $field['id'],
                                $field['id'],
                                $field['id']
                            );
                            break;

            
			default:
				printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />',
					$field['id'],
					$field['type'],
					$placeholder,
					$value
				);
		}
		if( isset($field['desc']) ) {
			if( $desc = $field['desc'] ) {
				printf( '<p class="description">%s </p>', $desc );
			}
		}
	}
}