<?php

/**
 * Appify - Convert WooCommerce Website to Mobile App
 *
 * @link              https://appify.liilab.com
 * @since             1.0
 * @package           Appify
 *
 * @wordpress-plugin
 * Plugin Name:       Appify
 * Plugin URI:        https://appify.liilab.com
 * Description:       A plugin for converting WooCommerce website to mobile app
 * Version:           1.0
 * Author:            liilab
 * Author URI:        https://liilab.com
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * License:           GPL-2.0+
 * Text Domain:       appify
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
 * Main initiation class
 * @since  1.0.0
 */

final class Appify
{

    /**
     * Plugin version
     *
     * @var string
     */
    const version = '1.0';

    private $build_url = 'https://appify.liilab.com/';

    /**
     * Class constructor
     */
    private function __construct()
    {
        $this->define_constants();

        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);

        add_action('plugins_loaded', [$this, 'init_plugin']);
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'plugin_action_links']);
    }


    /**
     * Plugin action links
     *
     * @param array $links
     *
     * @return array
     *@since  1.0.0
     *
     */
    public function plugin_action_links(array $links): array
    {

        $links[] = '<a href="' . admin_url('admin.php?page=appify') . '" class="fw-bold">' . __('Open tools', 'appify') . '</a>';
        return $links;
    }

    /**
     * Initializes a singleton instance
     *
     * @return Appify
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
        define('WTA_BUILD_URL', $this->build_url);
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

        do_action('Appify_WC_Plugin_activate');
    }


    /**
     * Do stuff upon plugin deactivation
     *
     * @return void
     */

    public function deactivate()
    {
        do_action('Appify_WC_Plugin_deactivate');
    }

    /**
     * Show warning if WooCommerce is not installed
     * @return void
     */

    public function admin_notice()
    {
?>
        <div class="notice notice-error is-dismissible alert alert-danger" role="alert">
            <span class="fw-bold">Appify </span><?php _e('requires ', 'appify'); ?><span class="fw-bold">WooCommerce </span><?php _e('to be installed and activated!', 'appify'); ?>
        </div>
<?php
    }
}


/**
 * Initializes the main plugin
 *
 * @return Appify
 */

function Appify()
{
    return Appify::init();
}

// kick-off the plugin
Appify();
