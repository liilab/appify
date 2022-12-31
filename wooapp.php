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
 * Plugin Name:       WooApp - Convert WooCommerce Website to Mobile App
 * Plugin URI:        liilab.com
 * Description:       A plugin for converting WooCommerce website to mobile App
 * Version:           1.0
 * Author:            LIILab
 * Author URI:        liilab.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wooapp
 * Domain Path:       /languages
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

        add_action('plugins_loaded', [$this, 'init_plugin']);
        add_action('load-plugins.php', [$this, 'load_plugin']);
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'plugin_action_links']);
    }

    public function load_plugin()
    {
        /**
         * Initialize the default settings
         */

         if (class_exists('WooCommerce')) {
            WebToApp\Activate_Plugin::get_instance();
        }
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

        $links[] = '<a href="' . admin_url('admin.php?page=wooapp') . '">' . __('Settings', 'wooapp') . '</a>';
        $links[] = '<a href="https://test.tsabbir.com" target="_blank">' . __('Documentation', 'wooapp') . '</a>';

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
        if (is_admin()) {
            WebToApp\Admin::get_instance();
        }
        if (class_exists('WooCommerce')) {
            WebToApp\WtaHelper::get_instance();
            WebToApp\API::get_instance();
            WebToApp\User::get_instance();
        } else {
            add_action('admin_notices', [$this, 'admin_notice']);
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
    }

    public function admin_notice()
    {
?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e('WooApp requires WooCommerce to be installed and activated!', 'wooapp'); ?></p>
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
