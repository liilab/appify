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
 * @package           Web to App
 *
 * @wordpress-plugin
 * Plugin Name:       Web to App - Rest API Maker
 * Plugin URI:        liilab.com
 * Description:       A plugin for creating rest api for your website.
 * Version:           1.0
 * Author:            LIILab
 * Author URI:        liilab.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wta-rest-api-maker
 * Domain Path:       /languages
 */

use WebToApp\API;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * The main plugin class
 */
final class Web_To_App
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
     * @return \Web_To_App
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
        define( 'WTA_VERSION', self::version );
        define( 'WTA_FILE', __FILE__ );
        define( 'WTA_DIR', __DIR__ );
        define( 'WTA_URL', plugins_url( '', WTA_FILE ) );
        define( 'WTA_ASSETS', WTA_URL . '/assets' );
        define( 'WTA_DIR_PATH', plugin_dir_path( __FILE__ ) );
    }

    /**
     * Initialize the plugin
     *
     * @return void
     */
    public function init_plugin()
    {

        if ( is_admin() ) {
            //new ajax\cart\Admin();
            //new WebToApp\API();
        } else {
            //new ajax\cart\Frontend();
        }

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
 * @return \Web_To_App
 */
function Web_To_App()
{
    return Web_To_App::init();
}

// kick-off the plugi
Web_To_App();