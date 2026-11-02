<?php
/**
 * Plugin Name: AI SEO Writer Pro
 * Plugin URI: https://example.com/ai-seo-writer-pro
 * Description: Advanced AI-Powered WordPress SEO Article Generator Plugin. Generate high-quality, SEO-optimized articles using AI directly from your WordPress Admin Dashboard.
 * Version: 1.0.0
 * Author: AI SEO Writer Pro Team
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ai-seo-writer-pro
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('AI_SEO_WRITER_PRO_VERSION', '1.0.0');
define('AI_SEO_WRITER_PRO_PLUGIN_FILE', __FILE__);
define('AI_SEO_WRITER_PRO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AI_SEO_WRITER_PRO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AI_SEO_WRITER_PRO_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('AI_SEO_WRITER_PRO_TEXT_DOMAIN', 'ai-seo-writer-pro');

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'AI_SEO_Writer_Pro\\';
    $base_dir = AI_SEO_WRITER_PRO_PLUGIN_DIR . 'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . 'class-' . strtolower(str_replace('_', '-', $relative_class)) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Activation hook
register_activation_hook(__FILE__, ['AI_SEO_Writer_Pro\\Plugin_Core', 'activate']);

// Deactivation hook
register_deactivation_hook(__FILE__, ['AI_SEO_Writer_Pro\\Plugin_Core', 'deactivate']);

// Initialize the plugin
add_action('plugins_loaded', function () {
    \AI_SEO_Writer_Pro\Plugin_Core::get_instance()->init();
});
