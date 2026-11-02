<?php
namespace AI_SEO_Writer_Pro;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin Core - Main bootstrap class
 */
class Plugin_Core {

    /**
     * Singleton instance
     * @var Plugin_Core|null
     */
    private static $instance = null;

    /**
     * Module instances
     * @var array
     */
    private $modules = [];

    /**
     * Get singleton instance
     * @return Plugin_Core
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor
     */
    private function __construct() {
        $this->load_dependencies();
    }

    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        $files = [
            'class-database-manager.php',
            'class-security-manager.php',
            'class-settings-manager.php',
            'class-license-manager.php',
            'class-ai-providers.php',
            'class-openai-provider.php',
            'class-gemini-provider.php',
            'class-claude-provider.php',
            'class-openrouter-provider.php',
            'class-custom-provider.php',
            'class-seo-optimizer.php',
            'class-article-generator.php',
            'class-seo-analyzer.php',
            'class-content-templates.php',
            'class-image-generator.php',
            'class-publishing-manager.php',
            'class-bulk-generator.php',
        ];

        foreach ($files as $file) {
            $path = AI_SEO_WRITER_PRO_PLUGIN_DIR . 'includes/' . $file;
            if (file_exists($path)) {
                require_once $path;
            }
        }

        // Admin files
        if (is_admin()) {
            $admin_file = AI_SEO_WRITER_PRO_PLUGIN_DIR . 'admin/class-admin-dashboard.php';
            if (file_exists($admin_file)) {
                require_once $admin_file;
            }
        }
    }

    /**
     * Initialize the plugin
     */
    public function init() {
        $this->modules['database'] = new Database_Manager();
        $this->modules['security'] = new Security_Manager();
        $this->modules['settings'] = new Settings_Manager();
        $this->modules['license'] = new License_Manager();
        $this->modules['ai_providers'] = new AI_Providers();
        $this->modules['seo_optimizer'] = new SEO_Optimizer();
        $this->modules['article_generator'] = new Article_Generator($this->modules['ai_providers'], $this->modules['seo_optimizer']);
        $this->modules['seo_analyzer'] = new SEO_Analyzer();
        $this->modules['content_templates'] = new Content_Templates();
        $this->modules['image_generator'] = new Image_Generator($this->modules['ai_providers']);
        $this->modules['publishing_manager'] = new Publishing_Manager();
        $this->modules['bulk_generator'] = new Bulk_Generator($this->modules['article_generator'], $this->modules['publishing_manager']);

        if (is_admin()) {
            $this->modules['admin'] = new Admin_Dashboard(
                $this->modules['settings'],
                $this->modules['ai_providers'],
                $this->modules['article_generator'],
                $this->modules['seo_analyzer'],
                $this->modules['content_templates'],
                $this->modules['image_generator'],
                $this->modules['bulk_generator'],
                $this->modules['publishing_manager'],
                $this->modules['license']
            );
        }

        $this->modules['database']->init();
        $this->modules['security']->init();
        $this->modules['settings']->init();
        $this->modules['license']->init();
        $this->modules['ai_providers']->init();
        $this->modules['seo_optimizer']->init();
        $this->modules['article_generator']->init();
        $this->modules['seo_analyzer']->init();
        $this->modules['content_templates']->init();
        $this->modules['image_generator']->init();
        $this->modules['publishing_manager']->init();
        $this->modules['bulk_generator']->init();

        if (is_admin()) {
            $this->modules['admin']->init();
        }

        do_action('ai_seo_writer_pro_init', $this);
    }

    /**
     * Get a module instance
     * @param string $name
     * @return mixed|null
     */
    public function get_module($name) {
        return $this->modules[$name] ?? null;
    }

    /**
     * Plugin activation
     */
    public static function activate() {
        require_once AI_SEO_WRITER_PRO_PLUGIN_DIR . 'includes/class-database-manager.php';
        $db = new Database_Manager();
        $db->create_tables();
        $db->maybe_upgrade();

        // Set default options
        add_option('ai_seo_writer_pro_version', AI_SEO_WRITER_PRO_VERSION);
        add_option('ai_seo_writer_pro_settings', self::get_default_settings());

        // Schedule WP-Cron events
        if (!wp_next_scheduled('ai_seo_writer_pro_bulk_queue_cron')) {
            wp_schedule_event(time(), 'ai_seo_writer_pro_one_minute', 'ai_seo_writer_pro_bulk_queue_cron');
        }

        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public static function deactivate() {
        $timestamp = wp_next_scheduled('ai_seo_writer_pro_bulk_queue_cron');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'ai_seo_writer_pro_bulk_queue_cron');
        }

        flush_rewrite_rules();
    }

    /**
     * Get default settings
     * @return array
     */
    private static function get_default_settings() {
        return [
            'default_ai_provider' => 'openai',
            'default_model' => 'gpt-4o',
            'default_language' => 'en',
            'default_tone' => 'professional',
            'default_content_type' => 'blog',
            'default_article_length' => 1500,
            'max_generation_tokens' => 4000,
            'temperature' => 0.7,
            'free_daily_limit' => 5,
            'seo_analyzer_enabled' => true,
            'auto_save_drafts' => true,
            'image_generation_enabled' => false,
            'logging_enabled' => true,
            'rate_limit_per_minute' => 10,
        ];
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new \Exception('Cannot unserialize singleton');
    }
}
