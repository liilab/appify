<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Appify Uninstall
 *
 * Uninstalling Appify deletes some user meta data.
 *
 * @package Appify\Uninstaller
 *
 * @since 1.0.0
 */
class Appify_Uninstaller
{
    /**
     * Constructor for the class Appify_Uninstaller
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

new Appify_Uninstaller();
