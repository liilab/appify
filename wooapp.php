<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              liilab.com
 * @since             1.0
 * @package           Wooapp
 *
 * @wordpress-plugin
 * Plugin Name:       Wooapp - Convert WooCommerce Website to Mobile App
 * Plugin URI:        liilab.com
 * Description:       A plugin for convert WooCommerce website to mobile App
 * Version:           1.0
 * Author:            LIILab
 * Author URI:        liilab.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wta-headles-woocommerce
 * Domain Path:       /languages
 */


if ( !defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * The main plugin class
 */


final class Wooapp
{

    /**
     * Plugin version
     *
     * @var string
     */
    const version = '1.0';

    /**
     * Class construcotr
     */
    private function __construct()
    {
        $this->define_constants();

        register_activation_hook( __FILE__, [$this, 'activate'] );

        add_action( 'plugins_loaded', [$this, 'init_plugin'] );
    }

    /**
     * Initializes a singleton instance
     *
     * @return \Wooapp
     */
    
    public static function init()
    {
        static $instance = false;

        if ( !$instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Define the required plugin constants
     *
     * @return void
     */
    public function define_constants()
    {
        define( 'WTA_VERSION', self::version ); //  1.0
        define( 'WTA_FILE', __FILE__ ); // C:\Program Files\Ampps\www\wordpress1\wp-content\plugins\web-to-app\wooapp.php
        define( 'WTA_DIR', __DIR__ ); // C:\Program Files\Ampps\www\wordpress1\wp-content\plugins\web-to-app
        define( 'WTA_URL', plugins_url( '', WTA_FILE ) ); // http://localhost/wordpress1/wp-content/plugins/web-to-app
        define( 'WTA_ASSETS', WTA_URL . '/assets' ); // http://localhost/wordpress1/wp-content/plugins/web-to-app/assets
        define( 'WTA_BUILD', WTA_URL . '/build' ); // http://localhost/wordpress1/wp-content/plugins/web-to-app/build
        define( 'WTA_DIR_PATH', plugin_dir_path( __FILE__ ) ); // C:\Program Files\Ampps\www\wordpress1\wp-content\plugins\web-to-app/
    }

    /**
     * Initialize the plugin
     *
     * @return void
     */
    public function init_plugin()
    {
        if ( is_admin() ) {
            WebToApp\Admin::get_instance();
        }

        WebToApp\WtaHelper::get_instance();
        WebToApp\API::get_instance();
        WebToApp\User::get_instance();
        WebToApp\Frontend::get_instance(); //curently not using

    }

    /**
     * Do stuff upon plugin activation
     *
     * @return void
     */
    public function activate()
    {
        $installed = get_option( 'wta_installed' );

        if ( !$installed ) {
            update_option( 'wta_installed', time() );
        }

        update_option( 'wta_version', WTA_VERSION );

    }
}

/**
 * Initializes the main plugin
 *
 * @return \Wooapp
 */

function Wooapp()
{
    return Wooapp::init();
}

// kick-off the plugi
Wooapp();