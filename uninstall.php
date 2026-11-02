<?php
/**
 * Plugin Uninstall
 *
 * Fired when the plugin is uninstalled.
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Get settings to check if data should be deleted
$settings = get_option('ai_seo_writer_pro_settings', []);

if (!empty($settings['delete_data_on_uninstall'])) {
    global $wpdb;

    // Delete custom tables
    $tables = [
        $wpdb->prefix . 'ai_seo_writer_pro_history',
        $wpdb->prefix . 'ai_seo_writer_pro_api_logs',
        $wpdb->prefix . 'ai_seo_writer_pro_queue',
        $wpdb->prefix . 'ai_seo_writer_pro_templates',
        $wpdb->prefix . 'ai_seo_writer_pro_stats',
    ];

    foreach ($tables as $table) {
        $wpdb->query("DROP TABLE IF EXISTS $table");
    }

    // Delete options
    delete_option('ai_seo_writer_pro_version');
    delete_option('ai_seo_writer_pro_db_version');
    delete_option('ai_seo_writer_pro_settings');
    delete_option('ai_seo_writer_pro_api_keys');
    delete_option('ai_seo_writer_pro_license');
    delete_option('ai_seo_writer_pro_custom_api');

    // Delete transients
    delete_transient('ai_seo_writer_pro_license_status');

    // Clear scheduled cron events
    $timestamp = wp_next_scheduled('ai_seo_writer_pro_bulk_queue_cron');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'ai_seo_writer_pro_bulk_queue_cron');
    }
}
