<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Wooapp Uninstall
 *
 * Uninstalling Wooapp deletes some user meta data.
 *
 * @package Wooapp\Uninstaller
 *
 * @since 1.0.0
 */
class Wooapp_Uninstaller
{
    /**
     * Constructor for the class Wooapp_Uninstaller
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        global $wpdb;

        // Delete user_meta
        $wpdb->query("DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'wta_build_id';");
        $wpdb->query("DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'wta_is_building';");
        $wpdb->query("DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'wta_binary_url';");
        $wpdb->query("DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'wta_preview_ur';");

        // Clear any cached data that has been removed.
        wp_cache_flush();
    }
}

new Wooapp_Uninstaller();
