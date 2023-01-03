<?php

/**
 * WooApp - Convert WooCommerce Website to Mobile App
 *
 * @link              https://wooapp.liilab.com
 * @since             1.0
 * @package           Wooapp
 *
 * @wordpress-plugin
 * Plugin Name:       WooApp - Convert WooCommerce Website to Mobile App
 * Plugin URI:        https://wooapp.liilab.com
 * Description:       A plugin for converting WooCommerce website to mobile App
 * Version:           1.0
 * Author:            liilab
 * Author URI:        https://liilab.com
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * License:           GPL-2.0+
 * Text Domain:       wooapp
 * Domain Path:       /languages
 */

/**
 * Bootstrap the plugin.
 */


if (!defined('ABSPATH')) {
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
     * Class constructor
     */
    private function __construct()
    {
        $this->define_constants();

        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook( __FILE__, [$this, 'deactivate'] );

        add_action('plugins_loaded', [$this, 'init_plugin']);
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'plugin_action_links']);
    }


    /**
     * Plugin action links
     *
     * @param array $links
     *
     * @since  1.0.0
     *
     * @return array
     */
    public function plugin_action_links($links)
    {

        $links[] = '<a href="' . admin_url('admin.php?page=wooapp') . '" class="text-warning fw-bold">' . __('Open Wooapp Tools', 'wooapp') . '</a>';
        return $links;
    }

    /**
     * Initializes a singleton instance
     *
     * @return \Wooapp
     */

    public static function init()
    {
        static $instance = false;

        if (!$instance) {
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
        define('WTA_VERSION', self::version);
        define('WTA_FILE', __FILE__);
        define('WTA_DIR', __DIR__);
        define('WTA_DIR_PATH', plugin_dir_path(__FILE__));
        define('WTA_URL', plugins_url('', WTA_FILE));
        define('WTA_ASSETS', WTA_URL . '/assets');
    }

    /**
     * Initialize the plugin
     *
     * @return void
     */
    public function init_plugin()
    {
        if (current_user_can('manage_options')) {
            WebToApp\Admin::get_instance();
        }
        if (class_exists('WooCommerce')) {
            WebToApp\WtaHelper::get_instance();
            WebToApp\API::get_instance();
            WebToApp\User::get_instance();
        } else {
            add_action('admin_notices', [$this, 'admin_notice'], 100);
        }
    }

    /**
     * Do stuff upon plugin activation
     *
     * @return void
     */
    public function activate()
    {
        $installed = get_option('wta_installed');

        if (!$installed) {
            update_option('wta_installed', time());
        }
        update_option('wta_version', WTA_VERSION);
        
        do_action( 'Wooapp_WC_Plugin_activate' );
    }


    /**
     * Do stuff upon plugin deactivation
     *
     * @return void
     */

    public function deactivate()
    {
        do_action( 'Wooapp_WC_Plugin_deactivate' );
    }

    /**
     * Show warning if WooCommerce is not installed
     * @return void
     */

    public function admin_notice()
    {
?>
        <div class="notice notice-error is-dismissible alert alert-danger" role="alert">
            <span class="fw-bold">WooApp </span><?php _e('requires ', 'wooapp'); ?><span class="fw-bold">WooCommerce </span><?php _e('to be installed and activated!', 'wooapp'); ?>
        </div>
<?php
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

// kick-off the plugin
Wooapp();
